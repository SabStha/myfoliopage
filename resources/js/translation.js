// Translation Service for Japanese Translation
class TranslationService {
    constructor() {
        this.currentLanguage = localStorage.getItem('app-language') || 'en';
        this.translations = {};
        this.isTranslating = false;
        this.observer = null;
    }

    // Initialize translation service
    async init() {
        // Load saved language preference
        if (this.currentLanguage === 'ja') {
            await this.translateToJapanese();
        }

        // Watch for dynamically added content
        this.setupMutationObserver();
    }

    // Setup mutation observer to translate dynamically added content
    setupMutationObserver() {
        if (this.observer) return;

        this.observer = new MutationObserver((mutations) => {
            if (this.currentLanguage === 'ja') {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            this.translateTextNodes(node);
                        }
                    });
                });
            }
        });

        this.observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Translate entire page to Japanese
    async translateToJapanese() {
        if (this.isTranslating) return;
        
        console.log('Starting translation to Japanese...');
        this.isTranslating = true;
        this.currentLanguage = 'ja';
        localStorage.setItem('app-language', 'ja');

        // Add data attribute to body for CSS targeting
        document.body.setAttribute('data-lang', 'ja');
        document.documentElement.lang = 'ja';

        // Use Google Translate widget for reliable translation
        this.useGoogleTranslateWidget();

        // Add visual indicator
        this.showTranslationIndicator();

        // Dispatch event
        window.dispatchEvent(new CustomEvent('language-changed', { detail: { lang: 'ja' } }));

        this.isTranslating = false;
        console.log('Translation to Japanese initiated');
    }

    // Translate back to English
    translateToEnglish() {
        this.currentLanguage = 'en';
        localStorage.setItem('app-language', 'en');
        document.body.removeAttribute('data-lang');
        document.documentElement.lang = 'en';

        // Reset Google Translate widget
        const select = document.querySelector('.goog-te-combo');
        if (select) {
            select.value = '';
            select.dispatchEvent(new Event('change'));
        }

        // Remove Google Translate classes and restore original
        setTimeout(() => {
            document.body.classList.remove('translated-ltr', 'translated-rtl');
            const skipLinks = document.querySelectorAll('.skiptranslate');
            skipLinks.forEach(link => link.classList.remove('skiptranslate'));
            
            // Reload page to fully restore (Google Translate modifies DOM deeply)
            // Alternative: just reset the select
            if (select) {
                select.value = '';
                select.dispatchEvent(new Event('change'));
            }
        }, 100);

        // Hide translation indicator
        this.hideTranslationIndicator();

        // Dispatch event
        window.dispatchEvent(new CustomEvent('language-changed', { detail: { lang: 'en' } }));
    }

    // Get all text nodes from an element
    getTextNodes(element) {
        const textNodes = [];
        const walker = document.createTreeWalker(
            element,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function(node) {
                    // Skip script and style tags (check parent element)
                    const parent = node.parentElement;
                    if (parent && (parent.tagName === 'SCRIPT' || parent.tagName === 'STYLE')) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    // Skip if parent has data-translated attribute
                    if (parent && parent.hasAttribute && parent.hasAttribute('data-translated')) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    // Only translate nodes with meaningful text
                    const text = node.textContent.trim();
                    if (text.length === 0 || text.length > 5000) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    return NodeFilter.FILTER_ACCEPT;
                }
            }
        );

        let node;
        while (node = walker.nextNode()) {
            textNodes.push(node);
        }

        return textNodes;
    }

    // Translate text nodes (fallback method if widget doesn't work)
    translateTextNodes(element) {
        // This is a fallback - Google Translate widget handles most cases
        // Mark parent elements as translated instead of text nodes
        const textNodes = this.getTextNodes(element);
        textNodes.forEach(node => {
            const parent = node.parentElement;
            if (parent && parent.hasAttribute) {
                parent.setAttribute('data-translated', 'true');
            }
        });
    }

    // Use Google Translate Widget (free, no API key needed)
    useGoogleTranslateWidget() {
        const self = this; // Store reference to this
        
        console.log('Initializing Google Translate widget...');
        
        // Create widget container first (hidden)
        let widgetContainer = document.getElementById('google-translate-widget');
        if (!widgetContainer) {
            widgetContainer = document.createElement('div');
            widgetContainer.id = 'google-translate-widget';
            widgetContainer.style.cssText = 'position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden;';
            document.body.appendChild(widgetContainer);
            console.log('Created Google Translate widget container');
        }

        // Function to trigger translation - improved version
        const triggerTranslation = () => {
            // Try multiple selectors
            const selectors = [
                '.goog-te-combo',
                'select.goog-te-combo',
                '#google_translate_element select',
                'body > div:first-child select'
            ];
            
            let select = null;
            for (const selector of selectors) {
                select = document.querySelector(selector);
                if (select) {
                    console.log('Found select element with selector:', selector);
                    break;
                }
            }

            if (select) {
                console.log('Setting select value to Japanese...');
                select.value = 'ja';
                
                // Try multiple ways to trigger the change
                const events = ['change', 'input', 'click'];
                events.forEach(eventType => {
                    const event = new Event(eventType, { bubbles: true, cancelable: true });
                    select.dispatchEvent(event);
                });
                
                // Also try native methods
                if (select.onchange) {
                    try {
                        select.onchange();
                    } catch (e) {
                        console.warn('onchange error:', e);
                    }
                }
                
                // Force update
                if (select.dispatchEvent) {
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
                
                // Also try setting selectedIndex if available
                if (select.options) {
                    for (let i = 0; i < select.options.length; i++) {
                        if (select.options[i].value === 'ja') {
                            select.selectedIndex = i;
                            break;
                        }
                    }
                }
                
                console.log('Translation trigger attempted');
                return true;
            } else {
                console.warn('Could not find Google Translate select element');
                // Try to find it in iframes
                const iframes = document.querySelectorAll('iframe');
                iframes.forEach(iframe => {
                    try {
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        const iframeSelect = iframeDoc.querySelector('select');
                        if (iframeSelect && iframeSelect.value) {
                            console.log('Found select in iframe');
                            iframeSelect.value = 'ja';
                            iframeSelect.dispatchEvent(new Event('change'));
                        }
                    } catch (e) {
                        // Cross-origin iframe, can't access
                    }
                });
            }
            return false;
        };

        // Add Google Translate script if not already loaded
        if (!window.googleTranslateElementInit) {
            window.googleTranslateElementInit = function() {
                console.log('Google Translate callback called');
                const container = document.getElementById('google-translate-widget');
                if (container && window.google && window.google.translate) {
                    try {
                        console.log('Initializing Google Translate element...');
                        new google.translate.TranslateElement(
                            {
                                pageLanguage: 'en',
                                includedLanguages: 'ja',
                                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                                autoDisplay: false
                            },
                            'google-translate-widget'
                        );

                        // Trigger translation after widget loads - try multiple times
                        const attemptTranslation = (attempt = 1) => {
                            console.log(`Translation attempt ${attempt}...`);
                            if (triggerTranslation()) {
                                console.log('Translation triggered successfully');
                            } else if (attempt < 5) {
                                setTimeout(() => attemptTranslation(attempt + 1), 500);
                            } else {
                                console.warn('Failed to trigger translation after 5 attempts');
                            }
                        };
                        
                        setTimeout(() => attemptTranslation(), 1000);
                    } catch (error) {
                        console.error('Error initializing Google Translate:', error);
                    }
                } else {
                    console.warn('Google Translate widget container or API not ready');
                    if (!container) console.warn('Container missing');
                    if (!window.google) console.warn('window.google missing');
                    if (!window.google?.translate) console.warn('window.google.translate missing');
                }
            };

            // Check if script already exists
            const existingScript = document.querySelector('script[src*="translate.google.com"]');
            if (!existingScript) {
                console.log('Loading Google Translate script...');
                const script = document.createElement('script');
                script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
                script.async = true;
                script.onerror = () => {
                    console.error('Failed to load Google Translate script');
                };
                script.onload = () => {
                    console.log('Google Translate script loaded');
                };
                document.head.appendChild(script);
            } else {
                console.log('Google Translate script already exists');
                // Script exists, check if Google Translate is ready
                if (window.google && window.google.translate) {
                    console.log('Google Translate API ready, initializing...');
                    window.googleTranslateElementInit();
                } else {
                    console.log('Waiting for Google Translate API to load...');
                    // Wait for script to load
                    let attempts = 0;
                    const checkGoogleTranslate = setInterval(() => {
                        attempts++;
                        if (window.google && window.google.translate) {
                            console.log('Google Translate API loaded after', attempts * 100, 'ms');
                            clearInterval(checkGoogleTranslate);
                            window.googleTranslateElementInit();
                        }
                        if (attempts > 50) {
                            console.error('Timeout waiting for Google Translate API');
                            clearInterval(checkGoogleTranslate);
                        }
                    }, 100);
                }
            }
        } else {
            console.log('Google Translate already initialized, triggering translation...');
            // Already initialized, trigger translation with retries
            const attemptTrigger = (attempt = 1) => {
                if (triggerTranslation()) {
                    console.log('Translation triggered successfully');
                } else if (attempt < 5) {
                    setTimeout(() => attemptTrigger(attempt + 1), 500);
                } else {
                    console.warn('Translation trigger failed after 5 attempts, reinitializing...');
                    window.googleTranslateElementInit();
                }
            };
            attemptTrigger();
        }
    }

    // Show translation indicator
    showTranslationIndicator() {
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

    // Hide translation indicator
    hideTranslationIndicator() {
        const indicator = document.getElementById('translation-indicator');
        if (indicator) {
            indicator.style.display = 'none';
        }
    }

    // Toggle translation
    toggleTranslation() {
        console.log('Toggle translation called, current language:', this.currentLanguage);
        if (this.currentLanguage === 'ja') {
            this.translateToEnglish();
        } else {
            this.translateToJapanese();
        }
    }

    // Alternative: Direct page translation using Google Translate URL
    translatePageDirectly() {
        // This method reloads the page with Google Translate
        const currentUrl = window.location.href;
        const translateUrl = `https://translate.google.com/translate?sl=en&tl=ja&u=${encodeURIComponent(currentUrl)}`;
        window.location.href = translateUrl;
    }
}

// Initialize translation service
const translationService = new TranslationService();

// Export for use in other files
if (typeof window !== 'undefined') {
    window.translationService = translationService;
}

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        translationService.init();
    });
} else {
    translationService.init();
}

export default translationService;

