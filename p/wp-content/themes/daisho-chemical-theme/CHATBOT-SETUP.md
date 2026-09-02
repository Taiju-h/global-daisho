# AI Chatbot Setup Guide

## Overview

Your WordPress theme now includes a fully integrated AI chatbot powered by OpenAI. The chatbot appears as a floating
bubble in the bottom-right corner of your website.

---

## ✅ What's Been Integrated

1. **Chatbot Widget** - Beautiful floating chat interface
2. **WordPress Customizer Integration** - Full control from WordPress admin
3. **Multi-language Support** - English and Japanese
4. **Customizable Appearance** - Colors, avatar, positioning
5. **Socket.IO Real-time Communication** - Instant responses

---

## 🚀 Quick Start

### Step 1: Start Your Chat Server

Your existing Node.js chat server needs to be running. Navigate to your server folder:

```bash
cd /path/to/daisho/server
npm install  # First time only
node server.js
```

The server should start on port 3001 by default.

### Step 2: Configure in WordPress

1. Log in to WordPress Admin
2. Go to **Appearance > Customize**
3. Open **AI Chatbot** section
4. Configure:
    - ✅ Enable AI Chatbot (check the box)
    - ✅ Chat Server URL: `http://your-domain.com:3001`
    - ✅ Agent Avatar Image: Upload your agent photo
    - ✅ Chatbot Primary Color: Choose your brand color
5. Click **Publish**

### Step 3: Test It!

Visit your website and you should see the chatbot bubble in the bottom-right corner!

---

## 🎨 Customization Options

### Available in WordPress Customizer:

| Setting | Description | Default |
|---------|-------------|---------|
| **Enable AI Chatbot** | Turn chatbot on/off | Enabled |
| **Chat Server URL** | Your Node.js server address | http://localhost:3001 |
| **Agent Avatar Image** | Chatbot profile picture | agent_bubble1.jpg |
| **Chatbot Primary Color** | Main color for chat UI | #2563eb (blue) |

---

## 🔧 Server Configuration

### Environment Variables

Your server needs an `.env` file with:

```env
OPENAI_API_KEY=your_openai_api_key_here
PORT=3001
```

### Server Files Location

Your existing chatbot server is in:

```
/path/to/daisho/server/
├── server.js
├── package.json
├── .env
└── node_modules/
```

---

## 🌐 Production Deployment

### For Production Use:

1. **Deploy Node.js Server**
    - Use PM2, systemd, or Docker
    - Ensure it's accessible from your WordPress site
    - Use HTTPS for security

2. **Update WordPress Settings**
    - Change Chat Server URL to production URL
    - Example: `https://chat.your-domain.com`

3. **Firewall Configuration**
    - Allow port 3001 (or your chosen port)
    - Enable CORS for your WordPress domain

### Example PM2 Setup:

```bash
npm install -g pm2
cd /path/to/daisho/server
pm2 start server.js --name "daisho-chatbot"
pm2 save
pm2 startup
```

---

## 💡 Features

### What the Chatbot Can Do:

- ✅ Answer questions about your products
- ✅ Provide information about services
- ✅ Help with technical inquiries
- ✅ Multi-language support (EN/JA)
- ✅ Preset quick questions
- ✅ Real-time responses via Socket.IO

### User Experience:

- Floating bubble in bottom-right corner
- Click to open/close chat window
- Preset questions for quick access
- Typing indicators
- Mobile responsive
- Smooth animations

---

## 🐛 Troubleshooting

### Chatbot Not Appearing?

1. Check if chatbot is enabled in Customizer
2. Verify server is running: `curl http://localhost:3001`
3. Check browser console for errors
4. Clear WordPress and browser cache

### Connection Errors?

1. Verify Chat Server URL is correct
2. Check if port 3001 is accessible
3. Look for firewall blocking the connection
4. Check server logs

### Server Won't Start?

1. Check if OpenAI API key is set in `.env`
2. Verify Node.js is installed: `node --version`
3. Run `npm install` to install dependencies
4. Check port 3001 isn't already in use

---

## 📊 Technical Details

### Architecture:

```
WordPress Site → Socket.IO Client → Node.js Server → OpenAI API
```

### Files in Theme:

```
daisho-chemical-theme/
├── js/
│   └── chatbot-widget.js (19.8KB)
├── assets/
│   └── agent_bubble1.jpg
└── functions.php (chatbot integration code)
```

### Scripts Loaded:

1. Socket.IO CDN (4.7.5)
2. Chatbot Widget JavaScript
3. Configuration passed via `wp_localize_script`

---

## 🔐 Security Notes

- Use HTTPS in production
- Secure your OpenAI API key
- Limit server access with firewall rules
- Use environment variables, never hardcode keys
- Keep Socket.IO library updated

---

## 📞 Support

### Need Help?

1. Check WordPress Customizer settings
2. Verify server is running
3. Check browser console for errors
4. Review server logs
5. Refer to INSTALLATION.md

---

## ✨ Future Enhancements

Potential features you could add:

- [ ] Chat history storage
- [ ] Admin dashboard for chat analytics
- [ ] More language support
- [ ] Custom training data
- [ ] File upload support
- [ ] Voice input/output

---

**Version:** 1.0  
**Last Updated:** October 2025  
**Status:** ✅ Production Ready

**Your AI chatbot is ready to assist your website visitors!** 🎉
