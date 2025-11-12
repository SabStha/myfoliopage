# Dual Language Implementation Guide

## Overview
This application now supports dual language (English/Japanese) using Laravel's built-in localization system combined with a React i18n service.

## Architecture

### Backend (Laravel)
- **Translation Files**: `resources/lang/en/app.php` and `resources/lang/ja/app.php`
- **Middleware**: `App\Http\Middleware\SetLocale` - Handles locale detection and setting
- **API Routes**: 
  - `POST /api/locale/{locale}` - Set locale
  - `GET /api/translations/{locale}` - Get translations

### Frontend (React)
- **i18n Service**: `resources/js/i18n.js` - Main translation service
- **React Hook**: `useTranslation()` in `LanguageSwitcher.jsx` - Hook for React components
- **Language Switcher**: `resources/js/Components/LanguageSwitcher.jsx` - UI component

## Usage

### In Blade Templates
```blade
{{ __('app.nav.home') }}
{{ __('app.common.read_more') }}
{{ __('app.sections.certificates') }}
```

### In React Components
```jsx
import { useTranslation } from './Components/LanguageSwitcher';

function MyComponent() {
    const { t } = useTranslation();
    
    return (
        <div>
            <h1>{t('sections.certificates')}</h1>
            <button>{t('common.read_more')}</button>
        </div>
    );
}
```

### In JavaScript (non-React)
```javascript
// Access via window.i18n
const text = window.i18n.t('sections.certificates');
```

## Adding New Translations

1. Add to `resources/lang/en/app.php`:
```php
'my_section' => [
    'title' => 'My Title',
    'description' => 'My Description',
],
```

2. Add to `resources/lang/ja/app.php`:
```php
'my_section' => [
    'title' => '私のタイトル',
    'description' => '私の説明',
],
```

3. Use in code:
```blade
{{ __('app.my_section.title') }}
```

Or in React:
```jsx
{t('my_section.title')}
```

## Language Switcher

The language switcher button is automatically mounted on non-admin pages. It:
- Shows current language (🇬🇧 EN or 🇯🇵 JA)
- Switches between English and Japanese
- Persists choice in localStorage and Laravel session
- Reloads page to apply Laravel translations

## How It Works

1. **Initial Load**: Middleware detects locale from cookie/session/request
2. **User Clicks Switcher**: React component calls `i18n.setLocale()`
3. **API Call**: POST to `/api/locale/{locale}` updates Laravel session
4. **Page Reload**: Page reloads with new locale, all translations update

## Next Steps

1. Add more translations to `app.php` files
2. Update React components to use `useTranslation()` hook
3. Update Blade templates to use `__()` helper
4. Test thoroughly in both languages









