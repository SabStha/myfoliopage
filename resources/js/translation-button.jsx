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
                #translation-button-root button {
                    position: fixed !important;
                    z-index: 9999 !important;
                    pointer-events: auto !important;
                }
                @media (max-width: 640px) {
                    #translation-button-root button {
                        bottom: 1rem !important;
                        right: 1rem !important;
                        min-width: 50px !important;
                        height: 45px !important;
                        padding: 0.5rem 1rem !important;
                        font-size: 0.875rem !important;
                    }
                }
                @media (min-width: 641px) {
                    #translation-button-root button {
                        bottom: 1.5rem !important;
                        right: 1.5rem !important;
                    }
                }
            `}</style>
            <button
                onClick={handleToggle}
                className="fixed bottom-4 right-4 z-[9999] bg-white dark:bg-gray-800 border-2 border-[#ffb400] hover:bg-[#ffb400] hover:border-[#e6a200] text-[#111] dark:text-gray-100 px-4 py-2.5 rounded-full shadow-xl transition-all duration-200 flex items-center justify-center gap-2 font-bold text-base min-w-[60px] h-[50px] backdrop-blur-sm"
                title={isJapanese ? 'Switch to English' : 'Switch to Japanese'}
                style={{
                    boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15), 0 2px 4px rgba(0, 0, 0, 0.1)',
                }}
            >
                <span className="text-lg font-bold">{isJapanese ? 'EN' : '日本語'}</span>
            </button>
        </>
    );
};

export default TranslationButton;

