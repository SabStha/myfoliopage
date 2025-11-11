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
        <button
            onClick={handleToggle}
            className="fixed top-4 right-4 z-50 bg-[#ffb400] hover:bg-[#e6a200] text-[#111] px-4 py-2 rounded-lg shadow-lg transition-all duration-200 flex items-center gap-2 font-semibold text-sm"
            title={isJapanese ? 'Switch to English' : 'Switch to Japanese'}
            style={{
                boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
            }}
        >
            <span>{isJapanese ? '🇬🇧 EN' : '🇯🇵 JA'}</span>
            <span className="hidden sm:inline">
                {isJapanese ? 'English' : '日本語'}
            </span>
        </button>
    );
};

export default TranslationButton;

