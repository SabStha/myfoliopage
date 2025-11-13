import React, { useState, useEffect } from 'react';

const TranslationButton = () => {
    const [isJapanese, setIsJapanese] = useState(false);

    useEffect(() => {
        // Check saved language preference
        const savedLang = localStorage.getItem('app-language');
        setIsJapanese(savedLang === 'ja');

        // Wait for translation service to be available
        const checkTranslationService = () => {
            if (window.translationService) {
                console.log('Translation service available');
                return true;
            }
            return false;
        };

        // Check immediately
        if (!checkTranslationService()) {
            // Wait for service to load (max 5 seconds)
            let attempts = 0;
            const interval = setInterval(() => {
                attempts++;
                if (checkTranslationService() || attempts > 50) {
                    clearInterval(interval);
                    if (!checkTranslationService()) {
                        console.warn('Translation service not found after waiting');
                    }
                }
            }, 100);
        }

        // Listen for translation changes
        const handleLanguageChange = () => {
            const currentLang = localStorage.getItem('app-language');
            setIsJapanese(currentLang === 'ja');
        };

        window.addEventListener('language-changed', handleLanguageChange);
        return () => {
            window.removeEventListener('language-changed', handleLanguageChange);
        };
    }, []);

    const useGoogleTranslateWebsite = () => {
        const currentUrl = window.location.href;
        const lang = localStorage.getItem('app-language') === 'ja' ? 'en' : 'ja';
        
        if (lang === 'ja') {
            // Translate to Japanese
            const translateUrl = `https://translate.google.com/translate?sl=en&tl=ja&u=${encodeURIComponent(currentUrl)}`;
            window.location.href = translateUrl;
        } else {
            // Back to English - just reload original page
            window.location.reload();
        }
    };

    const handleToggle = () => {
        console.log('Translation button clicked');
        
        // Try both service names
        const service = window.translationService || window.simpleTranslationService;
        
        if (service) {
            console.log('Translation service found, toggling...');
            try {
                service.toggle();
                const newLang = localStorage.getItem('app-language') || 'en';
                setIsJapanese(newLang === 'ja');
                
                // Dispatch custom event
                window.dispatchEvent(new CustomEvent('language-changed', { 
                    detail: { lang: newLang } 
                }));
            } catch (error) {
                console.error('Error toggling translation:', error);
                // Fallback: Use direct Google Translate
                useGoogleTranslateWebsite();
            }
        } else {
            console.error('Translation service not found!');
            // Fallback: Use Google Translate website
            useGoogleTranslateWebsite();
        }
    };

    return (
        <>
            <style>{`
                /* Continuous pulse animation to catch attention */
                @keyframes pulse-glow {
                    0%, 100% {
                        transform: translateY(0) scale(1);
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12), 0 1px 3px rgba(0, 0, 0, 0.08), 0 0 0 0 rgba(255, 180, 0, 0.4);
                    }
                    50% {
                        transform: translateY(-4px) scale(1.05);
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.16), 0 2px 6px rgba(0, 0, 0, 0.12), 0 0 20px rgba(255, 180, 0, 0.6);
                    }
                }
                
                /* Hover animation - more pronounced */
                .translation-button-pulse:hover {
                    animation: pulse-glow-hover 1s ease-in-out infinite;
                }
                
                @keyframes pulse-glow-hover {
                    0%, 100% {
                        transform: translateY(-2px) scale(1.08);
                        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2), 0 3px 8px rgba(0, 0, 0, 0.15), 0 0 30px rgba(255, 180, 0, 0.8);
                    }
                    50% {
                        transform: translateY(-6px) scale(1.12);
                        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.24), 0 4px 10px rgba(0, 0, 0, 0.18), 0 0 40px rgba(255, 180, 0, 1);
                    }
                }
                
                /* Container: positioned but doesn't block clicks or overlap content */
                #translation-button-root {
                    position: fixed;
                    bottom: 1rem;
                    right: 1rem;
                    z-index: 30;
                    pointer-events: none;
                    margin: 0;
                    padding: 0;
                    /* Ensure it never overlaps content */
                    max-width: calc(100vw - 2rem);
                    max-height: calc(100vh - 2rem);
                }
                /* Button: only this element receives clicks */
                #translation-button-root button {
                    pointer-events: auto;
                    position: relative;
                    margin: 0;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;
                    /* Ensure button doesn't overflow viewport */
                    max-width: 100%;
                    max-height: 100%;
                }
                /* Mobile: smaller button and more spacing from edges */
                @media (max-width: 640px) {
                    #translation-button-root {
                        bottom: 0.75rem;
                        right: 0.75rem;
                    }
                    #translation-button-root button {
                        min-width: 44px !important;
                        height: 44px !important;
                        padding: 0.5rem !important;
                        font-size: 0.8125rem !important;
                    }
                }
                /* Tablet and up: standard spacing */
                @media (min-width: 641px) {
                    #translation-button-root {
                        bottom: 1.25rem;
                        right: 1.25rem;
                    }
                }
                /* Lower z-index when modals are open (modals use z-50) */
                body:has([class*="z-50"]:not([class*="translation"])) #translation-button-root {
                    z-index: 20;
                }
                /* Ensure it doesn't interfere with admin sidebar on large screens */
                @media (min-width: 1024px) {
                    /* Admin pages have sidebar, button stays in corner */
                    body:has([class*="lg:ml-\\[280px\\]"]) #translation-button-root {
                        right: 1rem;
                    }
                }
            `}</style>
            <div id="translation-button-root">
                <button
                    onClick={handleToggle}
                    className="bg-white dark:bg-gray-800 border-2 border-[#ffb400] hover:bg-[#ffb400] hover:border-[#e6a200] text-[#111] dark:text-gray-100 px-3 py-2 rounded-full shadow-lg transition-all duration-200 flex items-center justify-center font-bold text-sm min-w-[56px] h-[56px] backdrop-blur-sm hover:scale-105 active:scale-95 translation-button-pulse"
                    title={isJapanese ? 'Switch to English' : 'Switch to Japanese'}
                    style={{
                        boxShadow: '0 2px 8px rgba(0, 0, 0, 0.12), 0 1px 3px rgba(0, 0, 0, 0.08)',
                        animation: 'pulse-glow 2s ease-in-out infinite',
                    }}
                >
                    <span className="text-base font-bold leading-none">{isJapanese ? 'EN' : '日本語'}</span>
                </button>
            </div>
        </>
    );
};

export default TranslationButton;

