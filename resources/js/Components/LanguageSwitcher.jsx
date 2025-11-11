import React, { useState, useEffect } from 'react';
import i18n from '../i18n';

// React Hook for translations
export const useTranslation = () => {
    const [locale, setLocaleState] = useState(i18n.getLocale());
    const [translations, setTranslations] = useState(i18n.translations);

    useEffect(() => {
        const handleTranslationsLoaded = (event) => {
            setLocaleState(event.detail.locale);
            setTranslations(event.detail.translations);
        };

        window.addEventListener('translations-loaded', handleTranslationsLoaded);
        
        // Initial load
        if (i18n.translations && Object.keys(i18n.translations).length > 0) {
            setTranslations(i18n.translations);
        }

        return () => {
            window.removeEventListener('translations-loaded', handleTranslationsLoaded);
        };
    }, []);

    const t = (key, params = {}) => {
        return i18n.t(key, params);
    };

    const changeLanguage = async (newLocale) => {
        await i18n.setLocale(newLocale);
        setLocaleState(newLocale);
    };

    return {
        t,
        locale,
        changeLanguage,
        translations,
    };
};

// Language Switcher Component
const LanguageSwitcher = () => {
    const { t, locale, changeLanguage } = useTranslation();
    const [isChanging, setIsChanging] = useState(false);

    const handleToggle = async () => {
        setIsChanging(true);
        const newLocale = locale === 'en' ? 'ja' : 'en';
        await changeLanguage(newLocale);
        setIsChanging(false);
    };

    return (
        <button
            onClick={handleToggle}
            disabled={isChanging}
            className="fixed top-4 right-4 z-50 bg-[#ffb400] hover:bg-[#e6a200] text-[#111] px-4 py-2 rounded-lg shadow-lg transition-all duration-200 flex items-center gap-2 font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            title={t('language.switch')}
            style={{
                boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
            }}
        >
            {isChanging ? (
                <span className="animate-spin">⟳</span>
            ) : (
                <>
                    <span>{locale === 'en' ? '🇯🇵 JA' : '🇬🇧 EN'}</span>
                    <span className="hidden sm:inline">
                        {locale === 'en' ? t('language.japanese') : t('language.english')}
                    </span>
                </>
            )}
        </button>
    );
};

export default LanguageSwitcher;



