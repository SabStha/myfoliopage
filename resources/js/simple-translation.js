// Simple and Reliable Translation Service using Google Translate
class SimpleTranslationService {
    constructor() {
        this.currentLanguage = localStorage.getItem('app-language') || 'en';
        this.isTranslating = false;
    }

    init() {
        console.log('SimpleTranslationService initialized');
        if (this.currentLanguage === 'ja') {
            this.translateToJapanese();
        }
    }

    translateToJapanese() {
        console.log('Translating to Japanese...');
        this.currentLanguage = 'ja';
        localStorage.setItem('app-language', 'ja');
        document.body.setAttribute('data-lang', 'ja');
        document.documentElement.lang = 'ja';

        // Use Google Translate widget
        this.initGoogleTranslate();
        this.showIndicator();
        
        window.dispatchEvent(new CustomEvent('language-changed', { detail: { lang: 'ja' } }));
    }

    translateToEnglish() {
        console.log('Translating to English...');
        this.currentLanguage = 'en';
        localStorage.setItem('app-language', 'en');
        document.body.removeAttribute('data-lang');
        document.documentElement.lang = 'en';

        // Reset Google Translate
        const select = document.querySelector('.goog-te-combo');
        if (select) {
            select.value = '';
            select.dispatchEvent(new Event('change'));
        }

        this.hideIndicator();
        window.dispatchEvent(new CustomEvent('language-changed', { detail: { lang: 'en' } }));
    }

    initGoogleTranslate() {
        // Create container
        let container = document.getElementById('google-translate-element');
        if (!container) {
            container = document.createElement('div');
            container.id = 'google-translate-element';
            container.style.cssText = 'position: absolute; left: -9999px; width: 1px; height: 1px;';
            document.body.appendChild(container);
        }

        // Initialize Google Translate
        if (!window.google || !window.google.translate) {
            if (!window.googleTranslateCallback) {
                window.googleTranslateCallback = () => {
                    if (window.google && window.google.translate) {
                        new google.translate.TranslateElement({
                            pageLanguage: 'en',
                            includedLanguages: 'ja',
                            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                            autoDisplay: false
                        }, 'google-translate-element');

                        // Trigger translation
                        setTimeout(() => {
                            const select = document.querySelector('.goog-te-combo');
                            if (select) {
                                select.value = 'ja';
                                select.dispatchEvent(new Event('change'));
                            }
                        }, 2000);
                    }
                };

                const script = document.createElement('script');
                script.src = `//translate.google.com/translate_a/element.js?cb=googleTranslateCallback`;
                script.async = true;
                document.head.appendChild(script);
            }
        } else {
            // Already loaded, just trigger
            setTimeout(() => {
                const select = document.querySelector('.goog-te-combo');
                if (select) {
                    select.value = 'ja';
                    select.dispatchEvent(new Event('change'));
                }
            }, 500);
        }
    }

    showIndicator() {
        let indicator = document.getElementById('translation-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'translation-indicator';
            indicator.innerHTML = '🇯🇵 日本語';
            indicator.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #ffb400;
                color: #111;
                padding: 8px 16px;
                border-radius: 8px;
                font-weight: bold;
                z-index: 9998;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                font-size: 14px;
                pointer-events: none;
            `;
            document.body.appendChild(indicator);
        }
        indicator.style.display = 'block';
    }

    hideIndicator() {
        const indicator = document.getElementById('translation-indicator');
        if (indicator) {
            indicator.style.display = 'none';
        }
    }

    toggle() {
        if (this.currentLanguage === 'ja') {
            this.translateToEnglish();
        } else {
            this.translateToJapanese();
        }
    }
}

// Create and expose service
const simpleTranslationService = new SimpleTranslationService();
if (typeof window !== 'undefined') {
    window.translationService = simpleTranslationService;
    window.simpleTranslationService = simpleTranslationService; // Backup name
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => simpleTranslationService.init());
} else {
    simpleTranslationService.init();
}

export default simpleTranslationService;




