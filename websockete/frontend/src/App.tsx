import React from 'react';
import './App.css';
import { ChatManager } from './Components';
import WordGraph from './WordGraph'; // Importer le composant WordGraph


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
        {/*<WordGraph />*/}
      </main>
    </div>
  );
}

export default App;