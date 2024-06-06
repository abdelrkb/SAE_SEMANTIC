import React, { useEffect, useState } from 'react';
import './main.css';
import WordGraph from './WordGraph';
import CurrentScore from './CurrentScore';

export interface WaitingRoom {
    name: string;
    attendeeNumber: number;
    description: string;
}

export interface Message {
    sender: string;
    timestamp: number;
    content: string;
}

export interface PythonResultDisplayerProps {
    result: any;
}

export const getQueryParam = (param: string) => {
    const params = new URLSearchParams(window.location.search);
    return params.get(param);
};

const WaitingRoomSelector = (props: { rooms: WaitingRoom[], onChosenRoom: (username: string, waitingRoom: string) => void }) => {
    const pseudoFromUrl = getQueryParam('pseudo') || "";
    const [selectedRoom, setSelectedRoom] = React.useState(props.rooms.length > 0 ? props.rooms[0].name : "");

    return (
        <div className="WaitingRoomSelector">
            <div><input type="text" value={pseudoFromUrl} disabled className="transparent-input" /></div>
            <div>
                {props.rooms.map(room => (
                    <div key={room.name}>
                        <input
                            type="radio"
                            name="room"
                            value={room.name}
                            checked={selectedRoom === room.name}
                            onChange={() => setSelectedRoom(room.name)}
                        />
                    </div>
                ))}
            </div>
            <button
                className="btn-green"
                onClick={() => props.onChosenRoom(pseudoFromUrl, selectedRoom)}
                disabled={pseudoFromUrl === "" || selectedRoom === "" || props.rooms.findIndex(x => x.name === selectedRoom) === -1}
            >
                Rejoindre la salle d'attente
            </button>
        </div>
    );
};

export default WaitingRoomSelector;

export const RoomWaiter = (props: { roomName: string, startTimestamp: number, onLeaving: () => void }) => {
    const [currentTimestamp, setCurrentTimestamp] = React.useState(performance.now());
    React.useEffect(() => {
        const handle = setInterval(() => setCurrentTimestamp(performance.now()), 1000);
        return () => clearTimeout(handle);
    }, []);
    return <div className="RoomWaiter">
        <div>En attente... {Math.floor((currentTimestamp - props.startTimestamp) / 1000)} s.</div>
        <br></br>
        <div><button className='btn-red' onClick={() => props.onLeaving()}>Quitter la file d'attente</button></div>
    </div>;
}

export const ChatMessageDisplayer = (props: { message: Message }) => {
    const isWordInsertion = props.message.content.startsWith('!');
    const formattedContent = isWordInsertion 
        ? `${props.message.sender} a essayé d'insérer le mot : ${props.message.content.substring(1)}` 
        : props.message.content;

    const messageClass = props.message.sender === 'admin'
        ? 'UserMessage'
        : isWordInsertion
        ? 'WordInsertionMessage'
        : 'TextMessage';
    
    return (
        <div className={`ChatMessageDisplayer ${messageClass}`}>
            <div>{props.message.sender}</div>
            <div style={{ flex: 1 }}>{formattedContent}</div>
        </div>
    );
}

export const ChatMessagesDisplayer = (props: { messages: Message[] }) => {
    return (
        <div className="ChatMessagesDisplayer">
            {props.messages.map((message, index) => (
                <ChatMessageDisplayer key={index} message={message} />
            ))}
        </div>
    );
}

export const MessageSender = (props: { onMessageWritten: (content: string) => void }) => {
    const [content, setContent] = React.useState("");
    return <div className="MessageSender">
        <input
            type="text"
            value={content}
            className="transparent-input"
            onChange={event => setContent(event.target.value)}
        />
        <button className="btn-green" onClick={() => { props.onMessageWritten(content); setContent('') }}>Envoyer</button>
    </div>;
}


export const ChatSession = (props: {
    messages: Message[],
    active: boolean,
    onMessageWritten: (content: string) => void,
    onLeaving: () => void,
    onClosing: () => void,
    onNewGame: () => void,
    onEndGame: () => void,
    currentTurn: string,
    userName: string
}) => {
    return (
        <div className="ChatSession">
            <ChatMessagesDisplayer messages={props.messages} />
            {props.active && <MessageSender onMessageWritten={props.onMessageWritten} />}
            <div className="ButtonContainer">
                <button className="btn-green" onClick={props.onNewGame}>Nouvelle partie</button>
                <button className="btn-red" onClick={() => props.onLeaving()} disabled={!props.active}>Quitter le chat de partie</button>
                <button className="btn-red" onClick={props.onEndGame}>Fin de la partie</button>
            </div>
            <div className="CurrentTurn">
                {props.currentTurn === props.userName ? "C'est votre tour!" : `Tour de ${props.currentTurn}`}
            </div>
        </div>
    );
}

interface DisconnectedState { disconnected: true }
interface ConnectingState { connecting: true }
interface RoomSelectionState { roomSelection: true }
interface WaitingState { startTimestamp: number, waitingRoomName: string }
interface ChattingState { startTimestamp: number, messages: Message[], active: boolean }
type ChatState = DisconnectedState | ConnectingState | RoomSelectionState | WaitingState | ChattingState

export const ChatManager = (props: { socketUrl: string }) => {
    const [chatState, setChatState] = React.useState<ChatState>({ disconnected: true });
    const [connected, setConnected] = React.useState(false);
    const [socket, setSocket] = React.useState<WebSocket | null>(null);
    const [error, setError] = React.useState<string>('');
    const [waitingRooms, setWaitingRooms] = React.useState<WaitingRoom[]>([]);
    const [pythonResult, setPythonResult] = React.useState<any>(null);
    const [graphKey, setGraphKey] = React.useState<string>('initial');
    const [currentScore, setCurrentScore] = React.useState<string>("");
    const [currentTurn, setCurrentTurn] = React.useState<string>("");

    const pseudoFromUrl = getQueryParam('pseudo') || "";

    const onNewSocketMessage = (kind: string, content: Record<string, any>) => {
        console.debug("Received message from websocket", content);
        const addChatMessage = (sender: string, content: string) => {
            let message: Message = { sender: sender, timestamp: Date.now(), content: content };
            setChatState(oldState => {
                if ('messages' in oldState)
                    return { ...oldState, messages: [...oldState.messages, message] };
                else return oldState;
            });
        }
        const readWaitingRooms = (c: Record<string, any>) => {
            let waitingRooms = [];
            for (let [name, v] of Object.entries(c['waiting_rooms'])) {
                let value = v as any;
                let room: WaitingRoom = { name: name, attendeeNumber: value.attendee_number, description: value.description };
                waitingRooms.push(room);
            }
            return waitingRooms;
        }
    
        switch (kind) {
            case 'waiting_room_list':
                setWaitingRooms(readWaitingRooms(content));
                setChatState({ roomSelection: true });
                break;
    
            case 'in_waiting_room':
                let name = content.waiting_room_name;
                setChatState({ waitingRoomName: name, startTimestamp: performance.now() });
                break;
    
            case 'waiting_room_left':
                setChatState({ roomSelection: true });
                break;
    
            case 'waiting_room_join_refused':
                setError(`Cannot join the room: ${content.reason}`);
                break;
    
            case 'chat_session_started':
                setChatState({ startTimestamp: performance.now(), messages: [], active: true });
                addChatMessage('admin', content.welcome_message);
                break;
    
            case 'chat_message_received':
                addChatMessage(content.sender, content.content);
                break;
    
            case 'attendee_left':
                addChatMessage('admin', `Attendee ${content.attendee} left the chat session.`);
                break;
    
            case 'chat_session_left':
                setChatState(oldState => ('messages' in oldState) ? { ...oldState, active: false } : oldState);
                break;
    
            case 'chat_session_ended':
                setChatState(oldState => ('messages' in oldState) ? { ...oldState, active: false } : oldState);
                addChatMessage('admin', "End of the chat session due to time limit.");
                addChatMessage('admin', content.exit_message);
                break;
    
            case 'python_execution_result':
            case 'new_game_result':
                setPythonResult(content.output);
                setGraphKey(`graph_${Date.now()}`);
                if (content.output && content.output.Distances) {
                    const minDistance = Math.min(...Object.values(content.output.Distances).map((value: any) => parseFloat(value.toString())));
                    setCurrentScore(minDistance.toString());
                }
                break;

            case 'turn_update':
                setCurrentTurn(content.current_turn_client);
                break;

            case 'not_your_turn':
                addChatMessage('admin', content.content);
                break;
    
            case 'server_shutdown':
                setError('Server will shutdown now! Please reconnect later.');
                break;
                
                case 'game_ended':
                    // Handle game ended logic here
                    const pseudoFromUrl = getQueryParam('pseudo') || "";
                    const tokenFromUrl = getQueryParam('token') || "";
                    const redirectUrl = 'http://localhost:3000/?pseudo=' + pseudoFromUrl + '&token=' + tokenFromUrl;
                    window.location.replace(redirectUrl);  // Redirect to home.php with absolute URL
                    break;
    
            default:
                setError(`Received non-understandable message: kind=${kind} content=${JSON.stringify(content)}`);
        }
    }
    
    const sendToSocket = React.useCallback((kind: string, body: Record<string, any>) => {
        const to_send = { kind: kind, ...body };
        const stringified = JSON.stringify(to_send);
        console.debug(`Sending message on the websocket`, to_send);
        socket?.send(stringified);
    }, [socket]);

    const connectToWaitingRoom = React.useCallback((username: string, waitingRoomName: string) => {
        sendToSocket('join_waiting_room', { 'token': username, 'waiting_room_name': waitingRoomName });
    }, [sendToSocket]);
    const leaveWaitingRoom = React.useCallback(() => {
        sendToSocket('leave_waiting_room', {});
    }, [sendToSocket]);
    const sendChatMessage = React.useCallback((content: string) => {
        if (content.startsWith('!')) {
            sendToSocket('execute_python', { command: content.substring(1) });
            sendToSocket('send_chat_message', { content: content });
        } else {
            sendToSocket('send_chat_message', { content: content });
        }
    }, [sendToSocket]);

    const leaveChatSession = React.useCallback(() => {
        sendToSocket('leave_chat_session', {});
    }, [sendToSocket]);
    const closeChatSession = React.useCallback(() => {
        setChatState({ roomSelection: true });
    }, []);

    const executeNewGame = React.useCallback(() => {
        sendToSocket('new_game', {});
    }, [sendToSocket]);

    const endGame = React.useCallback(() => {
        sendToSocket('end_game', {});
    }, [sendToSocket]);

    useEffect(() => {
        if ('connecting' in chatState) {
            setConnected(true);
        } else if ('disconnected' in chatState) {
            setConnected(false);
        }
    }, [chatState]);

    useEffect(() => {
        if (connected) {
            console.debug(`Opening the websocket with the URL ${props.socketUrl}`);
            const newSocket = new WebSocket(props.socketUrl);
            setSocket(newSocket);
            newSocket.addEventListener('open', (event) => {
                console.debug("WebSocket connection opened", event);
                setChatState({ roomSelection: true });
            });
            newSocket.addEventListener('message', (event) => {
                const data = event.data;
                if (typeof (data) === 'string') {
                    let json = null;
                    let kind = null;
                    try {
                        json = JSON.parse(data);
                        kind = json['kind'];
                        console.debug("WebSocket message received", json);
                    } catch (error) {
                        console.error("Received invalid JSON", data);
                        setError(`Received invalid JSON: ${data}`);
                    }
                    if (json !== null && kind !== null)
                        onNewSocketMessage(kind, json);
                }
            });
            newSocket.addEventListener('error', (event) => {
                console.error("WebSocket error", event);
                setChatState({ disconnected: true });
                setError(`WebSocket connection error: ${event}`);
            });
            newSocket.addEventListener('close', (event) => {
                console.error("WebSocket closed", event);
                setChatState({ disconnected: true });
                setError(`WebSocket connection closed: ${event.code} ${event.reason}`);
            });
            return () => {
                newSocket.close();
                setWaitingRooms([]);
                setSocket(null);
            };
        }
    }, [connected, props.socketUrl]);

    return <div className="ChatGraphContainer">
        <div className="ChatManager">
            <h1> Chat de jeu</h1>

            {error !== '' &&
                <div className="Error">Error: {error} <button onClick={() => setError('')}>OK</button></div>}
            {'disconnected' in chatState &&
                <div className="Disconnected">
                    <div>Connectez vous au chat du jeu pour commencer à jouer</div>
                    <br></br>
                    <button className="btn-green" onClick={() => setChatState({ connecting: true })}>Se connecter</button></div>}
            {'connecting' in chatState &&
                <div className="Connecting">
                    <div>Connecting to server {props.socketUrl}</div>
                </div>}
            {'roomSelection' in chatState &&
                <WaitingRoomSelector rooms={waitingRooms} onChosenRoom={connectToWaitingRoom} />}
            {'waitingRoomName' in chatState &&
                <RoomWaiter roomName={chatState.waitingRoomName} startTimestamp={chatState.startTimestamp} onLeaving={leaveWaitingRoom} />}
            {'messages' in chatState &&
                <ChatSession messages={chatState.messages} active={chatState.active} onMessageWritten={sendChatMessage} onLeaving={leaveChatSession} onClosing={closeChatSession} onNewGame={executeNewGame} onEndGame={endGame} currentTurn={currentTurn} userName={pseudoFromUrl} />}
        </div>
        {pythonResult && 
        <div className="WordGraphContainer">         
            <CurrentScore score={currentScore} />
            <WordGraph key={graphKey} data={pythonResult} />
        </div>}
    </div>;
}
