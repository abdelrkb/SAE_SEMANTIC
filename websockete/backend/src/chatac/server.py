#! /usr/bin/env python3
"""
chatac backend
An backend that implements a chat system using websockets.
by Michel Chilowicz <chilowi@u-pem.fr>

All exchanged messages on the websocket are JSON strings containing objects with a field named 'kind'.

First when a client opens a websocket connection, it receives from the server the list of all the waiting rooms:
< {"kind": "waiting_room_list", "waiting_rooms": {"default": {"description": "A default waiting room", "attendee_number": 2}}}

The user must send a message like this to join a waiting room:
> {"kind": "join_waiting_room", "waiting_room_name": "default", "token": "foobar"}
The token is a way for the user to authenticate.
The hook on_client_connection is used to do the authentication.

Then the server answers on the websocket to indicate that the client has been put in the waiting room:
< {"kind": "in_waiting_room", "waiting_room_name": "default"}
The server can also refuse this request if the identity has not been provided:
< {"kind": "waiting_room_join_refused", "reason": "identity_invalid"}
In this case the client must send again a new 'join_waiting_room' message with a valid identity.

When several clients are in the waiting room, the server can decide to start a chat session.
The server sends to each client in the session this message with the identities of all the attendees:
< {"kind": "chat_session_started", "attendees": ["foo", "bar"], "welcome_message": "server is welcoming you!"}

When the chat session is started, the attenddees can send their chat messages:
> {"kind": "send_chat_message", "content": "hello everybody"}
The chat message will be broadcasted to all the attendees of the chat session:
< {"kind": "chat_message_received", "sender": "bar", "content": "hello everybody"}

An attendee can decide to leave the chat session by sending a message:
> {"kind": "leave_chat_session"}
The server will inform all the attendees that the user has leaved:
< {"kind": "attendee_left", "attendee": "bar"}
The server will inform the attendee that has left that its demand has been acknowledged:
< {"kind": "chat_session_left"}

The server can also close the chat session when the time is up:
< {"kind": "chat_session_ended"}

Here are the possible states of a client:
- connected (not in a waiting room, neither in a chat session)
- waiting (in a waiting room)
- chatting (in a chat session)
When a client leaves a chat session its state goes from 'chatting' to 'connected'.
Then it can ask to join a waiting room again.

Note that this server is very raw:
it is up to you to adapt it for you real needs with custom hooks! Good luck!
"""

import asyncio, logging, os, sys, json, time, subprocess
import traceback
from aiohttp import web, WSMsgType, WSCloseCode, ClientConnectionError
import aiohttp
from typing import Dict, Any, List
from .hooks import ChatHooks
from .utils import nonify_exception, cancel_and_get_result

logger = logging.getLogger(__name__)

MESSAGE_CONTENT_LIMIT = 2048  # limit in chars for a message content
MESSAGE_SEND_TIMEOUT = 5.0  # timeout to send a message on a websocket

class Client(object):
    _COUNTER = 0  # to give a unique id for each client
    def __init__(self, websocket):
        self.id = Client._COUNTER
        Client._COUNTER += 1
        self.websocket = websocket
        self.state: str = 'connected'
        self.identity: Any = None
        self.waiting_room = None
        self.chat_session = None

    def __str__(self):
        return f"Client[id={self.id}, state={self.state}, identity={self.state}]"

    async def send_message(self, kind: str, **kwargs):
        message = {'kind': kind}
        message.update(kwargs)
        try:
            await asyncio.wait_for(self.websocket.send_json(message), MESSAGE_SEND_TIMEOUT)
            return True
        except:
            return False


class WaitingRoom(object):
    def __init__(self, name: str, info: Dict[str, Any]):
        self.name = name
        self.info = info
        self.attendee_number = info.get('attendee_number', 2)
        self.description = info.get('description', '')
        self._condition = asyncio.Condition()
        self._queue = []
        self.manager_task: Optional[Task] = None

    def __str__(self):
        return f"WaitingRoom[name={self.name}, attendee_number={self.attendee_number}, info={self.info}, queue_length={len(self._queue)}]"

    async def queue_client_id(self, client_id: int):
        async with self._condition:
            self._queue.append(client_id)
            self._condition.notify()

    async def cancel_client_id(self, client_id: int):
        async with self._condition:
            try:
                self._queue.remove(client_id)
            except ValueError:
                pass

    async def wait_for_attendees(self):
        async with self._condition:
            await self._condition.wait_for(lambda: len(self._queue) >= self.attendee_number)
            attendee_ids = self._queue[:self.attendee_number]
            self._queue = self._queue[self.attendee_number:]
            return attendee_ids



class ChatSession(object):
    _COUNTER = 0  # for a unique id for each chat session
    def __init__(self, clients, hooks, server):
        self.id = ChatSession._COUNTER
        ChatSession._COUNTER += 1
        self.clients = clients
        self.hooks = hooks
        self.server = server
        self.deadline = None
        self.welcome_message = None
        self.manager_task: Optional[Task] = None

        self._chat_message_queue = asyncio.Queue()  # queue for chat messages to be sent
        self._leave_queue = asyncio.Queue()  # queue for attendees that want to leave

        self.turn_index = 0  # Initialiser l'index du tour

    def get_current_turn_client(self):
        client_ids = list(self.clients.keys())
        return self.clients[client_ids[self.turn_index]]

    async def next_turn(self):
        self.turn_index = (self.turn_index + 1) % len(self.clients)
        current_turn_client = self.get_current_turn_client()
        await self.send_message(None, 'turn_update', current_turn_client=current_turn_client.identity.get('name'))

    def not_empty_message_queue(self):
        return not self._chat_message_queue.empty()

    async def put_in_message_queue(self, addressees, kind, **kwargs):
        message = {'kind': kind, 'addressees': addressees}
        message.update(**kwargs)
        await self._chat_message_queue.put(message)

    async def handle_chat_message(self, client, content):
        if content.startswith('!'):
            if client != self.get_current_turn_client():
                await self.put_in_message_queue([client.id], 'not_your_turn', content="vous avez essayé d'insérer un mot mais ce n'est pas à votre tour de jouer")
            else:
                command = content[1:]
                await self.server.execute_python_script(client, command)
                await self.next_turn()
        else:
            to_send = await self.hooks.on_chat_message(self.id, client.id, content)
            for (addressee_id, body) in to_send.items():
                await self.put_in_message_queue([addressee_id], 'chat_message_received', sender=client.identity.get('name'), content=body)

    async def get_next_message(self):
        return await self._chat_message_queue.get()

    async def put_in_leave_queue(self, client: Client):
        return await self._leave_queue.put(client)

    async def get_next_leave(self):
        return await self._leave_queue.get()

    async def send_message(self, addressees, kind, **kwargs):
        """
        Send a JSON message to all the clients in the chat session
        """
        clients = self.clients.values() if addressees is None else addressees
        # create the sending coroutines for all the clients
        sending_coroutines = [client.send_message(kind, **kwargs) for client in clients if client is not None]
        # gather the coroutines in one task
        await asyncio.gather(*sending_coroutines)
    
    async def remove_attendee(self, attendee: Client):
        """
        Called when an attendee leaves the chat session
        """
        old_clients = list(self.clients)
        self.clients.pop(attendee.id, None)
        attendee.state = 'connected'
        attendee.chat_session = None
        # warn the other attendees that the client has left
        await self.put_in_message_queue(old_clients, 'attendee_left', attendee=attendee.identity['name'])
        # warn the attendee itself that its query has been acknowledged
        await self.put_in_message_queue([attendee.id], 'chat_session_left')

    async def terminate(self, exit_message=''):
        """
        Terminate the chat session for everybody
        """
        for client in self.clients.values():
            client.state = 'connected' 
        await self.send_message(None, 'chat_session_ended', exit_message=exit_message)

    async def execute_python_script(self, client, command):
        try:
            script_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../../../../api/add_word.py'))
            output_json_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../../game_data_multi.json'))

            result = subprocess.run(['python3', script_path, command], capture_output=True, text=True, timeout=10)
            output = result.stdout + result.stderr
            
            if os.path.exists(output_json_path):
                with open(output_json_path, 'r') as json_file:
                    json_content = json.load(json_file)
                await self.broadcast_message('python_execution_result', json_content)
            else:
                await client.send_message('python_execution_result', output="Error: JSON file not found")
        except subprocess.TimeoutExpired:
            await client.send_message('python_execution_result', output="Error: Command timed out")
        except Exception as e:
            await client.send_message('python_execution_result', output=f"Error: {str(e)}")


class ChatServer(object):
    def __init__(self, interface: str, port: int, hooks: ChatHooks, hooks_params: Any):
        self.interface: str = interface
        self.port: int = port
        self.hooks = hooks
        self.hooks_params = hooks_params

        self._connected_clients = {}
        self._waiting_rooms = {}
        self._chat_sessions = {}

    async def _waiting_room_manager(self, waiting_room: WaitingRoom):
        logger.info(f"Starting the waiting room manager for room {waiting_room}")
        try:
            while True:
                attendee_ids = await waiting_room.wait_for_attendees()
                attendees = {x: self._connected_clients.get(x) for x in attendee_ids}
                chat_session = ChatSession(attendees, self.hooks, self)  # Pass the server instance
                chat_session_params = await self.hooks.on_chat_session_start(waiting_room.name, chat_session.id, {id: x.identity for (id, x) in attendees.items()})
                chat_session.deadline = time.monotonic() + chat_session_params['duration']
                chat_session.welcome_message = chat_session_params.get('welcome_message', '')
                self._chat_sessions[chat_session.id] = chat_session
                manager_task = asyncio.create_task(self._chat_session_manager(chat_session))
                chat_session.manager_task = manager_task
        except asyncio.CancelledError:
            pass
        except Exception as e:
            logger.error(f"Error in waiting room manager: {waiting_room.name}")
            logger.error(traceback.format_exc())
        finally:
            logger.info(f"The waiting room manager for {waiting_room.name} is terminated")

    async def _chat_session_manager(self, chat_session: ChatSession):
        """
        A manager to deal with a chat session
        """
        logger.info(f"Starting the chat session manager for chat session {chat_session}")
        for client in chat_session.clients.values():
            client.state = 'chatting'
            client.waiting_room = None
            client.chat_session = chat_session
        try:
            await chat_session.send_message(None, 'chat_session_started', welcome_message=chat_session.welcome_message)
            
            remaining_time = chat_session.deadline - time.monotonic()
            while remaining_time > 0 and (chat_session.clients or chat_session.not_empty_message_queue()):
                message_coroutine = asyncio.create_task(chat_session.get_next_message())
                leave_coroutine = asyncio.create_task(chat_session.get_next_leave())
                await asyncio.wait([message_coroutine, leave_coroutine], timeout=remaining_time, return_when=asyncio.FIRST_COMPLETED)
                message = await cancel_and_get_result(message_coroutine)
                leave = await cancel_and_get_result(leave_coroutine)

                if message:
                    # send the message to anybody
                    kind = message.pop('kind')
                    addressees = message.pop('addressees', None)
                    addressees2 = {self._connected_clients.get(id) for id in addressees} if addressees else None
                    await chat_session.send_message(addressees2, kind, **message)

                if leave:
                    await chat_session.remove_attendee(leave)

                remaining_time = chat_session.deadline - time.monotonic()
        except asyncio.CancelledError:
            pass
        except Exception as e:
            import traceback
            traceback.print_exc()
        finally:
            # the session is over
            exit_message = await self.hooks.on_chat_session_end(chat_session.id)
            await chat_session.terminate(exit_message)
            self._chat_sessions.pop(chat_session.id)
            logger.info(f"The chat session manager for {chat_session} is ended.")

    async def _websocket_handler(self, request):
        ws = web.WebSocketResponse()
        await ws.prepare(request)
        client = Client(ws)
        logger.info(f"A new websocket has been open for {client}")
        self._connected_clients[client.id] = client

        def get_waiting_rooms_desc():
            return {k: {
                    'attendee_number': v.attendee_number, 
                    'description': v.description} for (k, v) in self._waiting_rooms.items() }
            
        try:
            await client.send_message('waiting_room_list', waiting_rooms=get_waiting_rooms_desc())

            async for msg in ws:
                if msg.type == WSMsgType.TEXT:
                    try:
                        decoded_msg = json.loads(msg.data)
                        msg_kind = decoded_msg['kind']
                        logger.info(f"Received message kind: {msg_kind}, content: {decoded_msg}")
                    except Exception as e:
                        logger.error(f"Failed to decode message: {msg.data}")
                        logger.error(traceback.format_exc())
                        await client.send_message('json_invalid')
                        continue

                    if msg_kind == 'execute_python':
                        command = decoded_msg.get('command', '')
                        if command:
                            await self.execute_python_script(client, command)
                        else:
                            await client.send_message('command_invalid')
                    elif msg_kind == 'new_game':
                        await self.execute_new_game_script(client)
                    elif msg_kind == 'end_game':
                        await self.end_game(client)
                    elif msg_kind == 'join_waiting_room':
                        waiting_room_name = str(decoded_msg.get('waiting_room_name', '')).strip()
                        token = str(decoded_msg.get('token', '')).strip()
                        if not waiting_room_name or waiting_room_name not in self._waiting_rooms:
                            await client.send_message('waiting_room_name_invalid')
                        elif not token:
                            await client.send_message('token_empty')
                        elif client.state not in ('connected', 'waiting'):
                            await client.send_message('state_invalid', state=client.state)
                        else:
                            client_identity = await self.hooks.on_client_connection(waiting_room_name, token)
                            if not isinstance(client_identity, dict):
                                await client.send_message('waiting_room_join_refused', reason=str(client_identity))
                            else:
                                waiting_room = self._waiting_rooms[waiting_room_name]
                                if client.state == 'waiting':
                                    await client.waiting_room.cancel_client_id(client.id)
                                client.identity = client_identity
                                await waiting_room.queue_client_id(client.id)
                                client.state = 'waiting'
                                client.waiting_room = waiting_room
                                await client.send_message('in_waiting_room')

                    elif msg_kind == 'leave_waiting_room':
                        if client.state == 'waiting':
                            wr = client.waiting_room
                            await wr.cancel_client_id(client.id)
                            client.state = 'connected'
                            await client.send_message('waiting_room_left', 
                                waiting_room_name=wr.name)
                        else:
                            await client.send_message('state_invalid', state=client.state)
                    elif msg_kind == 'send_chat_message':
                        if client.chat_session:
                            content = decoded_msg.get('content')
                            if content:
                                await client.chat_session.handle_chat_message(client, content)
                            else:
                                await client.send_message('message_not_provided')
                        else:
                            await client.send_message('not_chatting')
                    elif msg_kind == 'leave_chat_session':
                        if client.chat_session:
                            await client.chat_session.put_in_leave_queue(client)
                        else:
                            await client.send_message('state_invalid', state=client.state)
                    else:
                        await client.send_message('message_kind_not_understood')

                elif msg.type == WSMsgType.ERROR:
                    logger.error(f"Websocket connection closed with error for {client}: {ws.exception()}")
                else:
                    await client.send_message('message_invalid')
        except asyncio.CancelledError:
            await client.send_message("server_shutdown")
        except ClientConnectionError:
            logger.info(f"Connection error for client {client}")
        except Exception as e:
            logger.error(f"Unexpected error for client {client}")
            logger.error(traceback.format_exc())
        finally:
            client.state = 'disconnected'
            if client.waiting_room is not None:
                await client.waiting_room.cancel_client_id(client.id)
            elif client.chat_session is not None:
                await client.chat_session.put_in_leave_queue(client)
            try:
                await client.websocket.close()
            except:
                pass
            self._connected_clients.pop(client.id)
            logger.info(f"The client {client} has left")


    async def _background_tasks(self, app):
        # inspired from https://docs.aiohttp.org/en/stable/web_advanced.html#background-tasks
        
        # launch the waiting room manager tasks
        logger.info("Starting the waiting rooms")
        rooms = await self.hooks.on_server_start(self.hooks_params)
        for name, params in rooms.items():
            wr = WaitingRoom(name, params)
            wr.manager_task = asyncio.create_task(self._waiting_room_manager(wr))
            self._waiting_rooms[name] = wr
        
        yield

        # stop the waiting room manager
        logger.info(f"Stopping the {len(self._waiting_rooms)} waiting rooms...")
        for room in self._waiting_rooms.values():
            room.manager_task.cancel()
        await asyncio.gather(*[x.manager_task for x in self._waiting_rooms.values()])
        logger.info("Waiting rooms stopped.")
        
        # stop all the chat session managers
        logger.info(f"Stopping the {len(self._chat_sessions)} chat sessions...")
        for chat_session in self._chat_sessions.values():
            chat_session.manager_task.cancel()
        await asyncio.gather(*[x.manager_task for x in self._chat_sessions.values()])
        logger.info("Chat sessions stopped.")

        
    async def execute_python_script(self, client, command):
        try:
            script_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../../../../api/add_word.py'))
            output_json_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../../game_data_multi.json'))

            result = subprocess.run(['python3', script_path, command], capture_output=True, text=True, timeout=10)
            output = result.stdout + result.stderr
            
            if os.path.exists(output_json_path):
                with open(output_json_path, 'r') as json_file:
                    json_content = json.load(json_file)
                await self.broadcast_message('python_execution_result', json_content)
            else:
                await client.send_message('python_execution_result', output="Error: JSON file not found")
        except subprocess.TimeoutExpired:
            await client.send_message('python_execution_result', output="Error: Command timed out")
        except Exception as e:
            await client.send_message('python_execution_result', output=f"Error: {str(e)}")

    async def execute_new_game_script(self, client):
        try:
            # Construire le chemin absolu du script
            script_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../../../../api/new_game.py'))
            # Chemin du fichier JSON généré
            output_json_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../../game_data_multi.json'))
            
            print(f"Executing script: {script_path}")  # Debug
            print(f"Expected output JSON path: {output_json_path}")  # Debug

            # Exécuter le script sans arguments
            result = subprocess.run(['python3', script_path], capture_output=True, text=True)
            output = result.stdout + result.stderr
            print(f"Script output: {output}")  # Debug: afficher la sortie de l'exécution

            # Lire le fichier JSON généré
            if os.path.exists(output_json_path):
                print("JSON file found")  # Debug
                with open(output_json_path, 'r') as json_file:
                    json_content = json.load(json_file)
                # Diffuser à tous les clients connectés
                await self.broadcast_message('new_game_result', json_content)
            else:
                print("JSON file not found")  # Debug
                await client.send_message('new_game_result', output="Error: JSON file not found")
        except subprocess.TimeoutExpired:
            await client.send_message('new_game_result', output="Error: Command timed out")
        except Exception as e:
            await client.send_message('new_game_result', output=f"Error: {str(e)}")

    async def end_game(self, client):
        try:
            # Construct the absolute path to the game data JSON file
            output_json_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '../../game_data_multi.json'))
            logger.info(f"Looking for game data in: {output_json_path}")

            # Check if the game data file exists
            if os.path.exists(output_json_path):
                with open(output_json_path, 'r') as json_file:
                    game_data = json.load(json_file)
                logger.info(f"Game data loaded: {game_data}")

                pseudo = client.identity.get('name')

                # Extract the minimum distance
                distances = game_data.get('Distances', {})
                if distances:
                    min_distance = min(distances.values())
                else:
                    min_distance = "No score found"

                # Prepare the payload for the POST request
                payload = {'pseudo': pseudo, 'score': min_distance}
                logger.info(f"Sending payload to the server: {payload}")

                # Construct the correct URL for the server
                url = 'http://localhost:8888/SAE_SEMANTIC/ajax/insert_score_ajax_multi.php'  # Adjust this URL as necessary
                async with aiohttp.ClientSession() as session:
                    async with session.post(url, json=payload) as resp:
                        response_text = await resp.text()
                        logger.info(f"Score submission response text: {response_text}")
                        try:
                            response_data = await resp.json()
                            logger.info(f"Score submission response JSON: {response_data}")
                            await client.send_message('game_ended', response=response_data, redirect_url='/SAE_SEMANTIC/home.php')
                        except json.JSONDecodeError:
                            logger.error("Failed to decode JSON response")
                            await client.send_message('game_ended', response="Error: Failed to decode JSON response", redirect_url='/SAE_SEMANTIC/home.php')
            else:
                logger.error("Game data JSON file not found")
                await client.send_message('game_ended', response="Error: JSON file not found", redirect_url='/SAE_SEMANTIC/home.php')
        except Exception as e:
            logger.error(f"Error ending game: {str(e)}")
            await client.send_message('game_ended', response=f"Error: {str(e)}", redirect_url='/SAE_SEMANTIC/home.php')

            
    async def broadcast_message(self, kind: str, content: Any):
            message = {'kind': kind, 'output': content}
            for client in self._connected_clients.values():
                await client.send_message(kind, output=content)  

    def run(self):
        app = web.Application()
        app.cleanup_ctx.append(self._background_tasks)
        app.router.add_route('GET', '/chat', self._websocket_handler)
        logger.info(f"Starting server on {self.interface}:{self.port}")
        web.run_app(app, host=self.interface, port=self.port, shutdown_timeout=100)


def main(args):
    # use argparse library to parse the arguments
    from argparse import ArgumentParser
    p = ArgumentParser(description="Backend for SpiderChat")
    p.add_argument("-p", "--port", type=int, default=8090, help="Port of the server")
    p.add_argument("-i", "--interface", type=str, default="localhost", help="Interface of the server")
    p.add_argument("-H", "--hooks", default="chatac.hooks.DefaultChatHooks", type=str, help="Path to the Python class implementing the hooks")
    p.add_argument("-P", "--hooks-params", default=None, type=str, help="Path to the YAWL file with the hooks params")
    p.add_argument("-l", "--log-level", default="info", type=str, choices=('debug', 'info', 'warning', 'error'), help="Minimal log level")
    parsed_args = p.parse_args(args)
    logging.basicConfig(level=parsed_args.log_level.upper())
    logger.info(f"Started with options {args}")
    # load the hooks class
    from .utils import load_class
    hooks = load_class(parsed_args.hooks)()

    # load the hooks_params
    hooks_params = {}
    if parsed_args.hooks_params:
        import yaml
        with open(parsed_args.hooks_params, 'r') as f:
            hooks_params = yaml.safe_load(f)

    server = ChatServer(parsed_args.interface, parsed_args.port, hooks, hooks_params)
    server.run()


def main0():
    return main(sys.argv[1:])


if __name__ == "__main__":
   main0()
