const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');
const path = require('path');
require('dotenv').config();

const chatRouter = require('./routes/chat');
const {generateAIResponse} = require('./utils/ai');

const app = express();
const server = http.createServer(app);

// Configure CORS for Socket.IO
const io = socketIo(server, {
    cors: {
        origin: process.env.ALLOWED_ORIGINS?.split(',') || ["http://localhost:3000"],
        methods: ["GET", "POST"],
        credentials: true
    }
});

// Middleware
app.use(cors({
    origin: process.env.ALLOWED_ORIGINS?.split(',') || ["http://localhost:3000"],
    credentials: true
}));
app.use(express.json());
app.use(express.static(path.join(__dirname, '../client')));

// Mount REST route at /api/chat
app.use('/api/chat', chatRouter);

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({status: 'ok', timestamp: new Date().toISOString()});
});

// Socket.IO connection handling
io.on('connection', (socket) => {
    console.log('User connected:', socket.id);

    // Join a room based on session ID for multi-language support
    socket.on('join-session', (sessionId) => {
        socket.join(sessionId);
        console.log(`User ${socket.id} joined session: ${sessionId}`);
    });

    // Handle chat messages
    socket.on('chat-message', async (data) => {
        try {
            const {message, sessionId, language = 'en'} = data;

            // Emit typing indicator
            socket.to(sessionId).emit('typing', {isTyping: true});

            // Process message through chat route logic
            // This will be handled by the chat route for actual AI processing
            socket.emit('message-received', {
                message,
                sessionId,
                language,
                timestamp: new Date().toISOString()
            });

        } catch (error) {
            console.error('Error processing chat message:', error);
            socket.emit('error', {message: 'Failed to process message'});
        }
    });

    socket.on('disconnect', () => {
        console.log('User disconnected:', socket.id);
    });
});

const PORT = process.env.PORT || 3000;

server.listen(PORT, () => {
    console.log(`AI Chat Widget Server running on port ${PORT}`);
    console.log(`Environment: ${process.env.NODE_ENV || 'development'}`);
});