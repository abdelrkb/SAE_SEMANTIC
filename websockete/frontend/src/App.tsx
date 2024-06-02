import React from 'react';
import './App.css';
import { ChatManager } from './Components';

// Définition des configurations
const DEFAULT_ROOMS = {
  "default": {
    "attendee_number": 2,
    "duration": 600,
    "welcome_message": "Bienvenue dans votre chat de partie!"
  }
};

const DEFAULT_ROOMS1 = {
  "default": {
    "attendee_number": 2,
    "duration": 600,
    "welcome_message": "Entrez ici vos mots"
  }
};

function substituteHost(s: string): string {
  return s.replace('myhost', document.location.host).replace('myprotocol', document.location.protocol === 'http:' ? 'ws:' : 'wss:');
}

function App() {
  return (
    <div className="App">
      <header className="App-header">
        <div>Semantic</div>
      </header>
      <main>
        {/* Passer la configuration appropriée à chaque ChatManager */}
        <ChatManager socketUrl={substituteHost(process.env.REACT_APP_BACKEND_URL || 'ws://localhost:8090/chat')} />
      </main>
    </div>
  );
}

export default App;
