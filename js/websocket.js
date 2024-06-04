// js/websocket.js

document.addEventListener('DOMContentLoaded', (event) => {
    const executeButton = document.getElementById('executeButtonMulti');
    
    executeButton.addEventListener('click', () => {
        const ws = new WebSocket('ws://localhost:8090/chat'); // Remplacez l'URL par celle de votre backend si différente

        ws.onopen = function(event) {
            console.log('WebSocket is open now.');
            // Vous pouvez envoyer un message initial si nécessaire
            // ws.send(JSON.stringify({type: 'init', message: 'Hello Server!'}));
        };

        ws.onmessage = function(event) {
            const message = event.data;
            console.log('Received:', message);
            // Vous pouvez ajouter la logique de traitement des messages ici
        };

        ws.onclose = function(event) {
            console.log('WebSocket is closed now.');
        };

        ws.onerror = function(event) {
            console.error('WebSocket error observed:', event);
        };
    });
});
