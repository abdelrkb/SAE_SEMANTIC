import React from 'react';
import './main.css';
import { ChatManager } from './Components';
import WordGraph from './WordGraph'; // Importer le composant WordGraph
import SinjeOff from './sinje_off.png'; // Assurez-vous de mettre le bon chemin vers l'image

function substituteHost(s: string): string {
  return s.replace('myhost', document.location.host).replace('myprotocol', document.location.protocol === 'http:' ? 'ws:' : 'wss:');
}

function App() {
  return (
    <div className="App">
      <main>
          <div className="ChatManager">
            <div className="HeaderImageContainer">
          <img src={SinjeOff} alt="Sinje Off" className="HeaderImage" />
            </div>
            <ChatManager socketUrl={substituteHost(process.env.REACT_APP_BACKEND_URL || 'ws://localhost:8090/chat')} />
        </div>
      </main>
    </div>
  );
}

export default App;
