// AI Chat Widget for Daisho Chemical WordPress Theme
(function () {
    'use strict';

    // Multi-language dictionary for UI text
    const labels = {
        en: {
            title: 'Daisho Chemical AI Assistant',
            placeholder: 'Ask me anything…',
            typing: 'AI is typing...',
            send: 'Send',
            close: 'Close',
            welcomeMessage: 'Hello! How can I help you today?',
            presetQuestions: [
                'Can you suggest good additives based on soil conditions?',
                'Are there any good retarders?',
                'What products do you recommend?',
                'Tell me about your timeline',
                'How can I contact you?'
            ]
        },
        ja: {
            title: 'ダイショー化学AIアシスタント',
            placeholder: '何でも聞いてください…',
            typing: 'AIが入力中...',
            send: '送信',
            close: '閉じる',
            welcomeMessage: 'こんにちは！どのようなご質問でしょうか？',
            presetQuestions: [
                '土壌状況に基づいた良い添加剤を提案できますか？',
                '良い遅延剤はありますか？',
                'どの製品をお勧めしますか？',
                'タイムラインについて教えてください',
                '連絡方法を教えてください'
            ]
        }
    };

    // Get configuration from WordPress (passed via wp_localize_script)
    const config = window.daishoChatConfig || {
        serverUrl: 'http://localhost:3001',
        agentImage: '',
        primaryColor: '#2563eb'
    };

    let daishoChatWidget = null;

    class DaishoChatWidget {
        constructor() {
            this.lang = document.documentElement.lang || 'en';
            if (!labels[this.lang]) {
                this.lang = 'en';
            }

            this.socket = null;
            this.isOpen = false;
            this.chatElement = null;
            this.messagesContainer = null;
            this.inputField = null;
            this.sendButton = null;

            this.init();
        }

        init() {
            this.createStyles();
            this.createChatElement();
            this.connectSocket();
            this.setupEventListeners();
        }

        createStyles() {
            const primaryColor = config.primaryColor || '#2563eb';
            const style = document.createElement('style');
            style.textContent = `
                #daisho-chat {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    z-index: 10000;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }

                .daisho-chat-bubble {
                    width: 96px;
                    height: 96px;
                    background: linear-gradient(135deg, ${primaryColor}, ${this.darkenColor(primaryColor, 20)});
                    border-radius: 50%;
                    color: white;
                    font-size: 24px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3);
                    transition: all 0.3s ease;
                    border: none;
                    padding: 0;
                    overflow: hidden;
                }

                .daisho-chat-bubble:hover {
                    transform: scale(1.1);
                    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
                }

                .daisho-agent-avatar {
                    width: 90px;
                    height: 90px;
                    border-radius: 50%;
                    object-fit: cover;
                    object-position: 50% 15%;
                    border: 2.5px solid #fff;
                    background: #fff;
                    display: block;
                    margin: 0 auto;
                }

                .daisho-chat-window {
                    position: absolute;
                    bottom: 110px;
                    right: 0;
                    width: 380px;
                    height: 550px;
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
                    border: 1px solid #e1e5e9;
                    display: none;
                    flex-direction: column;
                    animation: slideUp 0.3s ease-out;
                }

                .daisho-chat-window.open {
                    display: flex;
                }

                @keyframes slideUp {
                    from {
                        transform: translateY(20px);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }

                .daisho-chat-header {
                    background: linear-gradient(135deg, ${primaryColor}, ${this.darkenColor(primaryColor, 20)});
                    color: white;
                    padding: 16px;
                    border-radius: 12px 12px 0 0;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }

                .daisho-chat-title {
                    font-weight: 600;
                    font-size: 16px;
                }

                .daisho-chat-close {
                    background: none;
                    border: none;
                    color: white;
                    font-size: 20px;
                    cursor: pointer;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 4px;
                    transition: background 0.2s;
                }

                .daisho-chat-close:hover {
                    background: rgba(255, 255, 255, 0.1);
                }

                .daisho-chat-messages {
                    flex: 1;
                    overflow-y: auto;
                    padding: 16px;
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    background: #f9fafb;
                }

                .daisho-message {
                    max-width: 85%;
                    padding: 12px 16px;
                    border-radius: 18px;
                    font-size: 14px;
                    line-height: 1.5;
                    word-wrap: break-word;
                }

                .daisho-message.user {
                    background: ${primaryColor};
                    color: white;
                    align-self: flex-end;
                    border-bottom-right-radius: 4px;
                }

                .daisho-message.bot {
                    background: white;
                    color: #374151;
                    align-self: flex-start;
                    border-bottom-left-radius: 4px;
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                }

                .daisho-message.error {
                    background: #fee2e2;
                    color: #991b1b;
                    align-self: flex-start;
                    border-bottom-left-radius: 4px;
                }

                .daisho-typing {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 12px 16px;
                    color: #6b7280;
                    font-size: 14px;
                    align-self: flex-start;
                }

                .daisho-typing-dots {
                    display: flex;
                    gap: 4px;
                }

                .daisho-typing-dot {
                    width: 6px;
                    height: 6px;
                    background: #6b7280;
                    border-radius: 50%;
                    animation: daisho-typing 1.4s infinite;
                }

                .daisho-typing-dot:nth-child(2) {
                    animation-delay: 0.2s;
                }

                .daisho-typing-dot:nth-child(3) {
                    animation-delay: 0.4s;
                }

                @keyframes daisho-typing {
                    0%, 60%, 100% {
                        opacity: 0.3;
                    }
                    30% {
                        opacity: 1;
                    }
                }

                .daisho-chat-input {
                    padding: 16px;
                    border-top: 1px solid #e5e7eb;
                    display: flex;
                    gap: 8px;
                    align-items: center;
                    background: white;
                }

                .daisho-chat-input input {
                    flex: 1;
                    padding: 12px 16px;
                    border: 1px solid #d1d5db;
                    border-radius: 24px;
                    font-size: 14px;
                    outline: none;
                    transition: border-color 0.2s;
                }

                .daisho-chat-input input:focus {
                    border-color: ${primaryColor};
                }

                .daisho-send-button {
                    background: ${primaryColor};
                    color: white;
                    border: none;
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: background 0.2s;
                }

                .daisho-send-button:hover {
                    background: ${this.darkenColor(primaryColor, 20)};
                }

                .daisho-send-button:disabled {
                    background: #9ca3af;
                    cursor: not-allowed;
                }

                .preset-questions {
                    display: flex;
                    flex-direction: column;
                    gap: 8px;
                    padding: 12px 16px;
                    background: white;
                    border-top: 1px solid #e5e7eb;
                }

                .preset-question {
                    background: #f3f4f6;
                    color: #374151;
                    padding: 10px 14px;
                    border-radius: 8px;
                    font-size: 13px;
                    cursor: pointer;
                    text-align: left;
                    transition: all 0.2s;
                    border: 1px solid #e5e7eb;
                }

                .preset-question:hover {
                    background: ${primaryColor};
                    color: white;
                    border-color: ${primaryColor};
                }

                @media (max-width: 768px) {
                    .daisho-chat-window {
                        width: 340px;
                        height: 500px;
                    }
                    
                    #daisho-chat {
                        bottom: 15px;
                        right: 15px;
                    }

                    .daisho-chat-bubble {
                        width: 70px;
                        height: 70px;
                    }

                    .daisho-agent-avatar {
                        width: 65px;
                        height: 65px;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        darkenColor(color, percent) {
            const num = parseInt(color.replace("#", ""), 16);
            const amt = Math.round(2.55 * percent);
            const R = (num >> 16) - amt;
            const G = (num >> 8 & 0x00FF) - amt;
            const B = (num & 0x0000FF) - amt;
            return "#" + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
                (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
                (B < 255 ? B < 1 ? 0 : B : 255))
                .toString(16).slice(1);
        }

        createChatElement() {
            const ui = labels[this.lang];

            this.chatElement = document.createElement('div');
            this.chatElement.id = 'daisho-chat';

            const bubble = document.createElement('button');
            bubble.className = 'daisho-chat-bubble';
            bubble.innerHTML = `<img src="${config.agentImage}" alt="AI Assistant" class="daisho-agent-avatar">`;
            bubble.setAttribute('aria-label', ui.title);

            const window = document.createElement('div');
            window.className = 'daisho-chat-window';
            window.innerHTML = `
                <div class="daisho-chat-header">
                    <div class="daisho-chat-title">${ui.title}</div>
                    <button class="daisho-chat-close" aria-label="${ui.close}">&times;</button>
                </div>
                <div class="daisho-chat-messages"></div>
                <div class="preset-questions"></div>
                <div class="daisho-chat-input">
                    <input type="text" placeholder="${ui.placeholder}" maxlength="500">
                    <button class="daisho-send-button" aria-label="${ui.send}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
            `;

            this.chatElement.appendChild(bubble);
            this.chatElement.appendChild(window);
            document.body.appendChild(this.chatElement);

            this.chatBubble = bubble;
            this.chatWindow = window;
            this.messagesContainer = window.querySelector('.daisho-chat-messages');
            this.presetQuestionsContainer = window.querySelector('.preset-questions');
            this.inputField = window.querySelector('.daisho-chat-input input');
            this.sendButton = window.querySelector('.daisho-send-button');
            this.closeButton = window.querySelector('.daisho-chat-close');

            this.renderPresetQuestions();
        }

        renderPresetQuestions() {
            const ui = labels[this.lang];
            this.presetQuestionsContainer.innerHTML = '';

            ui.presetQuestions.forEach((question) => {
                const presetQuestion = document.createElement('div');
                presetQuestion.className = 'preset-question';
                presetQuestion.textContent = question;
                presetQuestion.addEventListener('click', () => {
                    this.inputField.value = question;
                    this.sendMessage();
                });

                this.presetQuestionsContainer.appendChild(presetQuestion);
            });
        }

        connectSocket() {
            const serverUrl = config.serverUrl;

            this.socket = io(serverUrl, {
                transports: ['websocket', 'polling'],
                timeout: 10000
            });

            this.socket.on('connect', () => {
                console.log('Connected to Daisho Chemical chat server');
                this.showWelcomeMessage();
            });

            this.socket.on('disconnect', () => {
                console.log('Disconnected from chat server');
            });

            this.socket.on('connect_error', (error) => {
                console.warn('Chat server connection error:', error);
                this.addMessage('Unable to connect to chat server. Please try again later.', 'error');
            });

            this.socket.on('botMessage', (data) => {
                this.hideTypingIndicator();
                this.addMessage(data.reply, data.error ? 'error' : 'bot');
                this.sendButton.disabled = false;
            });
        }

        setupEventListeners() {
            this.chatBubble.addEventListener('click', () => {
                this.openChat();
            });

            this.closeButton.addEventListener('click', () => {
                this.closeChat();
            });

            this.sendButton.addEventListener('click', () => {
                this.sendMessage();
            });

            this.inputField.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.sendMessage();
                }
            });

            document.addEventListener('click', (e) => {
                if (this.isOpen && !this.chatElement.contains(e.target)) {
                    this.closeChat();
                }
            });
        }

        openChat() {
            this.isOpen = true;
            this.chatWindow.classList.add('open');
            this.chatBubble.style.display = 'none';
            this.showPresetQuestions();

            setTimeout(() => {
                this.inputField.focus();
            }, 100);
        }

        closeChat() {
            this.isOpen = false;
            this.chatWindow.classList.remove('open');
            this.chatBubble.style.display = 'flex';
        }

        sendMessage() {
            const message = this.inputField.value.trim();
            if (!message || this.sendButton.disabled) return;

            this.hidePresetQuestions();
            this.addMessage(message, 'user');
            this.inputField.value = '';
            this.sendButton.disabled = true;
            this.showTypingIndicator();

            this.socket.emit('userMessage', {
                msg: message,
                lang: this.lang
            });
        }

        addMessage(text, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `daisho-message ${type}`;
            messageDiv.textContent = text;

            this.messagesContainer.appendChild(messageDiv);
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }

        showTypingIndicator() {
            this.hideTypingIndicator();

            const ui = labels[this.lang];
            const typingDiv = document.createElement('div');
            typingDiv.className = 'daisho-typing';
            typingDiv.innerHTML = `
                <span>${ui.typing}</span>
                <div class="daisho-typing-dots">
                    <div class="daisho-typing-dot"></div>
                    <div class="daisho-typing-dot"></div>
                    <div class="daisho-typing-dot"></div>
                </div>
            `;

            this.messagesContainer.appendChild(typingDiv);
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }

        hideTypingIndicator() {
            const typingIndicator = this.messagesContainer.querySelector('.daisho-typing');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        hidePresetQuestions() {
            if (this.presetQuestionsContainer) {
                this.presetQuestionsContainer.style.display = 'none';
            }
        }

        showPresetQuestions() {
            if (this.presetQuestionsContainer) {
                this.presetQuestionsContainer.style.display = 'flex';
            }
        }

        showWelcomeMessage() {
            const ui = labels[this.lang];
            setTimeout(() => {
                this.addMessage(ui.welcomeMessage, 'bot');
            }, 1000);
        }
    }

    function initWidget() {
        if (daishoChatWidget) return;
        daishoChatWidget = new DaishoChatWidget();
        window.daishoChatWidget = daishoChatWidget;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWidget);
    } else {
        initWidget();
    }

})();
