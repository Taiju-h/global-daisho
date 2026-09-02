# Chat Server URL Setup Guide

## What is the Chat Server URL?

The **Chat Server URL** is the web address where your Node.js chatbot backend is running. WordPress needs this URL to
connect the chat widget to your AI server.

---

## 📍 Where to Find It

### Option 1: Running Locally (Testing)

If you're testing on your computer:

```
http://localhost:3001
```

**How to start:**

```bash
cd /path/to/daisho/server
node server.js
```

You'll see: `DaishoBot server running on http://0.0.0.0:3001`

---

### Option 2: Running on Production Server

If your server is deployed online:

```
http://your-domain.com:3001
```

Or if using HTTPS:

```
https://your-domain.com:3001
```

**Examples:**

- `http://chat.daishokagaku.com:3001`
- `https://api.daishokagaku.com:3001`
- `http://123.45.67.89:3001` (IP address)

---

## 🔧 How to Set It in WordPress

1. Go to **WordPress Admin**
2. Navigate to **Appearance > Customize**
3. Click **AI Chatbot** section
4. Find **Chat Server URL** field
5. Enter your server URL (e.g., `http://localhost:3001`)
6. Click **Publish**

---

## ✅ How to Test If It's Working

### Method 1: Browser Test

Open your server URL in a browser:

```
http://localhost:3001
```

You should see a "Cannot GET /" message (this is normal - it means server is running)

### Method 2: Command Line Test

```bash
curl http://localhost:3001
```

If you get a response, server is running!

### Method 3: Check Server Logs

When you run `node server.js`, you should see:

```
DaishoBot server running on http://0.0.0.0:3001
OpenAI API Status: Configured
```

---

## 🚀 Quick Start (First Time)

### Step 1: Install Dependencies

```bash
cd /Volumes/EXTERNAL_USB/WebstormProjects/Dashio_Website/daisho/server
npm install
```

### Step 2: Create .env File

Create a file called `.env` in the server folder:

```env
OPENAI_API_KEY=your_openai_api_key_here
PORT=3001
```

### Step 3: Start Server

```bash
node server.js
```

### Step 4: Set URL in WordPress

Use: `http://localhost:3001` (for local testing)

---

## 🌐 Production Deployment

### Option 1: Same Server as WordPress

If WordPress and Node.js are on the same server:

```
http://localhost:3001
```

### Option 2: Different Server

If chatbot is on a separate server:

```
http://chat-server-ip:3001
```

### Option 3: Using Domain Name

Point a subdomain to your chat server:

```
https://chat.yourdomain.com
```

Then use port 3001:

```
https://chat.yourdomain.com:3001
```

---

## 🔒 Security Notes

### For Production:

1. **Use HTTPS** (not HTTP)
   ```
   https://your-domain.com:3001
   ```

2. **Firewall Rules**
    - Allow port 3001 only from your WordPress server IP
    - Block public access if possible

3. **Environment Variables**
    - Never commit `.env` file to git
    - Keep OpenAI API key secure

---

## 🐛 Troubleshooting

### "Chat server connection error"

**Problem:** WordPress can't connect to server

**Solutions:**

1. Make sure server is running: `node server.js`
2. Check if port 3001 is accessible
3. Verify firewall allows port 3001
4. Try `http://localhost:3001` first (for testing)

### "Cannot connect to localhost"

**Problem:** Server not running

**Solution:**

```bash
cd daisho/server
node server.js
```

Keep this terminal window open!

### "OPENAI_API_KEY Missing"

**Problem:** No API key configured

**Solution:**

1. Get API key from https://platform.openai.com/
2. Create `.env` file in server folder
3. Add: `OPENAI_API_KEY=sk-your-key-here`

---

## 📋 Quick Reference

| Scenario | URL to Use |
|----------|------------|
| Local testing | `http://localhost:3001` |
| Production (same server) | `http://localhost:3001` |
| Production (different server) | `http://server-ip:3001` |
| Production (with domain) | `https://chat.domain.com:3001` |

---

## 💡 Recommended Setup

### For Development:

```
Chat Server URL: http://localhost:3001
```

### For Production:

```
Chat Server URL: https://your-domain.com:3001
```

Using PM2 to keep server running:

```bash
npm install -g pm2
pm2 start server.js --name daisho-chatbot
pm2 save
pm2 startup
```

---

## ✅ Summary

**Your Chat Server URL is where your Node.js chatbot backend is running.**

**Default:** `http://localhost:3001`

**To start server:**

```bash
cd daisho/server
node server.js
```

**To set in WordPress:**
Appearance > Customize > AI Chatbot > Chat Server URL

---

**Need help?** Check `CHATBOT-SETUP.md` for complete chatbot documentation!
