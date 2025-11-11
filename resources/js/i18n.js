// React i18n Service - Works with Laravel translations
class I18nService {
    constructor() {
        // Get locale from Laravel session or localStorage
        const laravelLocale = document.documentElement.lang?.split('-')[0] || 'en';
        this.currentLocale = localStorage.getItem('app-locale') || laravelLocale || 'en';
        
        // Validate locale
        if (!['en', 'ja'].includes(this.currentLocale)) {
            this.currentLocale = 'en';
        }
        
        this.translations = {};
        this.loadTranslations();
    }

    async loadTranslations() {
        try {
            // Fetch translations from Laravel backend
            const response = await fetch(`/api/translations/${this.currentLocale}`);
            if (response.ok) {
                const data = await response.json();
                this.translations = data;
            } else {
                // Fallback to default translations
                this.translations = {};
            }
        } catch (error) {
            console.warn('Failed to load translations from server, using defaults:', error);
            this.translations = {};
        }
        
        // Notify React components
        window.dispatchEvent(new CustomEvent('translations-loaded', { 
            detail: { locale: this.currentLocale, translations: this.getDefaultTranslations() } 
        }));
    }

    getDefaultTranslations() {
        const defaults = {
            en: {
                nav: {
                    home: 'Home',
                    about: 'About',
                    projects: 'Projects',
                    skills: 'Skills',
                    contact: 'Contact',
                    blog: 'Blog',
                    timeline: 'Timeline',
                },
                common: {
                    read_more: 'Read More',
                    view_all: 'View All',
                    loading: 'Loading...',
                    close: 'Close',
                    back: 'Back',
                },
                sections: {
                    certificates: 'Certificates',
                    projects: 'Projects',
                    skills: 'Skills',
                    progress: 'My Progress',
                },
                language: {
                    english: 'English',
                    japanese: 'Japanese',
                    switch: 'Switch Language',
                },
            },
            ja: {
                nav: {
                    home: 'ホーム',
                    about: 'について',
                    projects: 'プロジェクト',
                    skills: 'スキル',
                    contact: 'お問い合わせ',
                    blog: 'ブログ',
                    timeline: 'タイムライン',
                },
                common: {
                    read_more: '続きを読む',
                    view_all: 'すべて表示',
                    loading: '読み込み中...',
                    close: '閉じる',
                    back: '戻る',
                },
                sections: {
                    certificates: '証明書',
                    projects: 'プロジェクト',
                    skills: 'スキル',
                    progress: '進捗状況',
                },
                language: {
                    english: '英語',
                    japanese: '日本語',
                    switch: '言語を切り替え',
                },
            },
        };
        
        // Merge with Laravel translations if available
        const base = defaults[this.currentLocale] || defaults.en;
        if (this.translations && this.translations.app) {
            return { ...base, ...this.translations.app };
        }
        return base;
    }

    t(key, params = {}) {
        const translations = this.getDefaultTranslations();
        const keys = key.split('.');
        let value = translations;
        
        for (const k of keys) {
            if (value && typeof value === 'object' && k in value) {
                value = value[k];
            } else {
                // Fallback to key if translation not found
                return key;
            }
        }

        // Replace parameters if provided
        if (typeof value === 'string' && Object.keys(params).length > 0) {
            return value.replace(/\{(\w+)\}/g, (match, paramKey) => {
                return params[paramKey] || match;
            });
        }

        return value || key;
    }

    async setLocale(locale) {
        if (!['en', 'ja'].includes(locale)) {
            console.error(`Invalid locale: ${locale}`);
            return;
        }

        this.currentLocale = locale;
        localStorage.setItem('app-locale', locale);
        
        // Update Laravel session via API
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch(`/api/locale/${locale}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin', // Include cookies
            });
            
            if (!response.ok) {
                console.error('Failed to update locale:', await response.text());
            } else {
                const data = await response.json();
                console.log('Locale updated successfully:', data);
            }
        } catch (error) {
            console.error('Failed to update server locale:', error);
            // Still proceed with locale change even if API fails
        }

        // Set cookie manually as fallback
        document.cookie = `locale=${locale}; path=/; max-age=${60 * 60 * 24 * 30}`;
        
        // Reload page to apply Laravel translations
        // Add locale to URL to ensure it's picked up
        const url = new URL(window.location.href);
        url.searchParams.set('lang', locale);
        window.location.href = url.toString();
    }

    getLocale() {
        return this.currentLocale;
    }
}

// Initialize and expose
const i18n = new I18nService();
if (typeof window !== 'undefined') {
    window.i18n = i18n;
}

export default i18n;

