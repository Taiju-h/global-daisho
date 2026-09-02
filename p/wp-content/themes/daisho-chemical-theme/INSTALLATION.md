# Daisho Chemical Theme - Installation Guide

## Quick Installation

### Method 1: Via WordPress Admin Panel (Recommended)

1. **Compress the theme folder:**
    - Zip the entire `daisho-chemical-theme` folder
    - Name it: `daisho-chemical-theme.zip`

2. **Upload to WordPress:**
    - Log in to your WordPress admin panel
    - Go to **Appearance > Themes**
    - Click **Add New**
    - Click **Upload Theme**
    - Choose the `daisho-chemical-theme.zip` file
    - Click **Install Now**
    - Click **Activate**

### Method 2: Via FTP/File Manager

1. **Upload via FTP:**
    - Connect to your server via FTP
    - Navigate to `/wp-content/themes/`
    - Upload the entire `daisho-chemical-theme` folder
    - Go to WordPress admin: **Appearance > Themes**
    - Activate "Daisho Chemical Theme"

### Method 3: Via cPanel File Manager

1. **Compress the folder** to `daisho-chemical-theme.zip`
2. **In cPanel File Manager:**
    - Navigate to `public_html/wp-content/themes/`
    - Click **Upload**
    - Upload the zip file
    - Right-click the zip file and select **Extract**
    - Delete the zip file after extraction
3. **In WordPress admin:**
    - Go to **Appearance > Themes**
    - Activate "Daisho Chemical Theme"

---

## Post-Installation Setup

### Step 1: Customize Your Content

After activation, go to **Appearance > Customize** to edit all content:

#### 1. **Colors & Styling** (NEW!)

- Primary/Accent Color (default: yellow #ffc107)
- Main Text Color
- Button Text Color (for all CTA and Timeline buttons)
- Font Family (7 options including Inter, Roboto, Montserrat)
- Custom CSS field for advanced styling

#### 2. **Navigation**

- Site logo text
- Menu item labels
- Language button text

#### 3. **Hero Section**

- Background image (upload your own)
- Subtitle
- Main title (3 parts)
- Button text

#### 4. **Services Section**

- Section title and subtitle
- For each of 3 services:
    - Icon (emoji or character)
    - Title
    - Description

#### 5. **Products Section**

- Section title and subtitle
- For each of 4 products:
    - Name
    - Description
    - Applications
    - Packaging

#### 6. **Timeline Section**

- Section title and subtitle
- 3 timeline periods with years, titles, descriptions
- 4 advancement items
- CTA button text

#### 7. **Contact Section**

- Section title and subtitle
- Company details (name, address, phone, email)
- Copyright text
- Map Image (upload a map image)

#### 8. **AI Chatbot** (NEW!)

- Enable/Disable Chatbot
- Chat Server URL (your Node.js server URL)
- Agent Avatar Image (chatbot profile picture)
- Chatbot Primary Color

---

## Chatbot Setup

The theme includes an AI chatbot powered by OpenAI. To use it:

### Requirements:

1. Node.js server running (see your `daisho/server` folder)
2. OpenAI API key configured in server

### Quick Setup:

1. Make sure your Node.js chatbot server is running
2. In WordPress Customizer, go to **AI Chatbot** section
3. Set **Chat Server URL** to your server (e.g., `http://your-domain.com:3001`)
4. Customize agent image and color as needed
5. The chatbot will appear in bottom-right corner of your site

### To Run Chat Server:

```bash
cd /path/to/daisho/server
npm install
node server.js
```

Make sure your `.env` file has `OPENAI_API_KEY=your_api_key_here`

### Step 2: Add Your Background Image

1. In Customizer, go to **Hero Section**
2. Click **Hero Background Image**
3. Upload your image (recommended: 1920x1080px or larger)
4. Save changes

### Step 3: Customize Colors (Optional)

1. Go to **Colors & Styling** in Customizer
2. Choose your primary/accent color
3. Adjust text color if needed
4. Select your preferred font family
5. Save changes

---

## What's Editable in This Theme?

### ✅ FULLY CUSTOMIZABLE (via WordPress Customizer):

- **All Text Content:** Every heading, paragraph, and label
- **Background Images:** Hero section background
- **Colors:** Primary/accent color, text color
- **Fonts:** Choose from 7 font families
- **Service Icons:** Use any emoji or character
- **Company Information:** All contact details
- **Button Text:** All CTA buttons

### 🎨 ADVANCED CUSTOMIZATION:

- **Custom CSS Field:** Add your own CSS for unlimited styling
- **Layout:** One-page structure (fixed, but sections are reorderable via CSS)

---

## Troubleshooting

### Theme doesn't appear after upload

- Ensure the folder structure is correct: `/wp-content/themes/daisho-chemical-theme/`
- Check that `style.css` and `index.php` are in the root of the theme folder

### Images not showing

- Re-upload the background image via Customizer
- Check file permissions (should be 644 for files, 755 for folders)

### Colors not changing

- Clear browser cache and WordPress cache
- Make sure you clicked "Publish" in the Customizer

### Font not loading

- Try a different font from the dropdown
- Check your internet connection (fonts load from Google Fonts CDN)

---

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- Modern web browser

---

## Support

For customization help or issues:

- Check WordPress Codex: https://codex.wordpress.org/
- Review the README.md file included with the theme

---

## Theme Features

✨ **One-page design** with smooth scrolling
✨ **Fully responsive** on all devices
✨ **Customizer-ready** - no coding required
✨ **Modern animations** on scroll
✨ **SEO-friendly** markup
✨ **Fast loading** with optimized code

---

**Version:** 1.0  
**Last Updated:** 2025

Enjoy your new website! 🎉
