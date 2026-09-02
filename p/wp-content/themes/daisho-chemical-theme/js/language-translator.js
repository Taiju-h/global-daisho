// Language Translator for Daisho Chemical Theme
(function () {
    'use strict';

    class LanguageTranslator {
        constructor() {
            this.currentLang = this.getSavedLanguage() || 'en';
            this.languages = {
                'ja': '日本語',
                'en': 'ENGLISH',
                'de': 'DEUTSCH',
                'fr': 'FRANÇAIS',
                'pl': 'POLSKI',
                'es': 'ESPAÑOL',
                'nl': 'NEDERLANDS'
            };
            this.translationAttempted = false; // Prevent refresh loops
            this.init();
        }

        init() {
            this.createLanguageDropdown();
            this.loadGoogleTranslate();
            this.updateHTMLLang();
        }

        createLanguageDropdown() {
            // Find the language button
            const langButton = document.querySelector('.language-btn');
            if (!langButton) return;

            // Create dropdown container
            const dropdown = document.createElement('div');
            dropdown.className = 'language-dropdown';
            dropdown.style.display = 'none';

            // Add language options
            Object.entries(this.languages).forEach(([code, name]) => {
                const option = document.createElement('button');
                option.className = 'language-option';
                option.textContent = name;
                option.dataset.lang = code;
                if (code === this.currentLang) {
                    option.classList.add('active');
                }
                option.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.changeLanguage(code);
                });
                dropdown.appendChild(option);
            });

            // Insert dropdown after button
            langButton.parentNode.insertBefore(dropdown, langButton.nextSibling);

            // Toggle dropdown on button click
            langButton.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = dropdown.style.display === 'block';
                dropdown.style.display = isOpen ? 'none' : 'block';
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', () => {
                dropdown.style.display = 'none';
            });

            // Update button text with current language
            this.updateButtonText(langButton);
        }

        updateButtonText(button) {
            const currentLangName = this.languages[this.currentLang];
            button.innerHTML = `${currentLangName} <span style="margin-left: 4px;">▼</span>`;
        }

        loadGoogleTranslate() {
            // Don't load if already loaded
            if (window.google && window.google.translate) {
                this.checkAndApplyTranslation();
                return;
            }

            // Create hidden Google Translate element first
            const gtElement = document.createElement('div');
            gtElement.id = 'google_translate_element';
            gtElement.style.display = 'none';
            document.body.appendChild(gtElement);

            window.googleTranslateElementInit = () => {
                new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    includedLanguages: 'ja,en,de,fr,pl,es,nl',
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                    autoDisplay: false
                }, 'google_translate_element');

                // Check and apply saved language after Google Translate loads
                setTimeout(() => {
                    this.checkAndApplyTranslation();
                }, 1000);
            };

            // Load Google Translate script
            const script = document.createElement('script');
            script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
            script.async = true;
            document.head.appendChild(script);
        }

        checkAndApplyTranslation() {
            // Prevent multiple attempts
            if (this.translationAttempted) {
                return;
            }

            const savedLang = this.getSavedLanguage();
            if (savedLang && savedLang !== 'en') {
                // Check if page is already translated
                const currentPageLang = this.getCurrentPageLanguage();
                if (currentPageLang !== savedLang) {
                    this.translationAttempted = true;
                    this.triggerGoogleTranslate(savedLang);
                }
            }
        }

        getCurrentPageLanguage() {
            // Check Google Translate cookie
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === 'googtrans') {
                    const match = value.match(/\/en\/(.+)/);
                    return match ? match[1] : 'en';
                }
            }
            return 'en';
        }

        changeLanguage(langCode) {
            this.currentLang = langCode;
            this.saveLanguage(langCode);

            // Update UI immediately
            document.querySelectorAll('.language-option').forEach(opt => {
                opt.classList.toggle('active', opt.dataset.lang === langCode);
            });

            const langButton = document.querySelector('.language-btn');
            if (langButton) {
                this.updateButtonText(langButton);
            }

            const dropdown = document.querySelector('.language-dropdown');
            if (dropdown) {
                dropdown.style.display = 'none';
            }

            // Apply translation
            this.applyTranslation(langCode);
        }

        applyTranslation(langCode) {
            console.log('Applying translation to:', langCode);

            if (langCode === 'en') {
                // Reset to English - clear cookies and reload
                this.setCookie('googtrans', '', -1);
                this.setCookie('googtrans', '', -1, '/');
                window.location.reload();
                return;
            }

            // Set Google Translate cookie
            const cookieValue = `/en/${langCode}`;
            this.setCookie('googtrans', cookieValue, 365);
            this.setCookie('googtrans', cookieValue, 365, '/');

            // Trigger translation and reload ONCE
            setTimeout(() => {
                window.location.reload();
            }, 100);
        }

        triggerGoogleTranslate(langCode) {
            // Try to trigger Google Translate dropdown
            setTimeout(() => {
                const selectElement = document.querySelector('.goog-te-combo');
                if (selectElement) {
                    selectElement.value = langCode;
                    const event = new Event('change', {bubbles: true});
                    selectElement.dispatchEvent(event);
                }
            }, 500);
        }

        setCookie(name, value, days, path = '/') {
            let expires = '';
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = '; expires=' + date.toUTCString();
            }
            const pathStr = path ? `; path=${path}` : '';
            document.cookie = name + '=' + (value || '') + expires + pathStr + '; SameSite=Lax';
        }

        saveLanguage(lang) {
            localStorage.setItem('daisho_language', lang);
        }

        getSavedLanguage() {
            return localStorage.getItem('daisho_language');
        }

        updateHTMLLang() {
            document.documentElement.lang = this.currentLang;
        }
    }

    // Add styles for dropdown
    const style = document.createElement('style');
    style.textContent = `
        .language-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 8px 0;
            min-width: 180px;
            z-index: 1001;
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .language-option {
            display: block;
            width: 100%;
            padding: 10px 20px;
            border: none;
            background: white;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
            color: #333;
            transition: background 0.2s;
        }

        .language-option:hover {
            background: #f3f4f6;
        }

        .language-option.active {
            background: #ffc107;
            color: white;
            font-weight: 600;
        }

        .language-btn {
            position: relative;
            cursor: pointer;
        }

        /* Hide Google Translate elements */
        .goog-te-banner-frame.skiptranslate {
            display: none !important;
        }

        body {
            top: 0 !important;
        }

        .goog-te-gadget {
            color: transparent !important;
            font-size: 0 !important;
        }

        .goog-te-gadget .goog-te-combo {
            display: none !important;
        }

        #google_translate_element {
            display: none !important;
        }

        .goog-logo-link {
            display: none !important;
        }

        .goog-te-gadget span {
            display: none !important;
        }

        .goog-te-balloon-frame {
            display: none !important;
        }

        /* Fix body top issue from Google Translate */
        body.translated-ltr {
            top: 0 !important;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .language-dropdown {
                right: -10px;
                min-width: 160px;
            }
        }
    `;
    document.head.appendChild(style);

    // Initialize translator when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new LanguageTranslator();
        });
    } else {
        new LanguageTranslator();
    }

})();
