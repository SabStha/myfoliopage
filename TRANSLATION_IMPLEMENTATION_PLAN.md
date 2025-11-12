# Dual Language Implementation Plan

## Approach: JSON Columns for Translations

### Strategy
Store translations as JSON in existing columns. For example:
- `heading_text` stores: `{"en": "Projects", "ja": "プロジェクト"}`
- When locale is 'en', show `heading_text['en']`
- When locale is 'ja', show `heading_text['ja']`

### Benefits
1. ✅ No schema changes needed for new languages
2. ✅ Works with existing database structure
3. ✅ Easy to add more languages later (e.g., 'fr', 'es')
4. ✅ Admin forms can show both language inputs side-by-side
5. ✅ Automatic fallback to English if Japanese translation missing

### Implementation Steps

1. **Create a Trait** (`HasTranslations`) for models
   - Provides `getTranslated($field)` method
   - Handles JSON encoding/decoding
   - Falls back to English if translation missing

2. **Update Models** to use the trait
   - HeroSection, Certificate, Course, Room, Blog, etc.

3. **Create Migration** to convert existing data
   - Convert `"Projects"` → `{"en": "Projects", "ja": ""}`
   - Keep existing English text

4. **Update Admin Forms**
   - Show both English and Japanese inputs
   - Save as JSON

5. **Update Views**
   - Use `$model->getTranslated('field')` instead of `$model->field`

### Example Usage

**Before:**
```php
$heroSection->heading_text = "Projects";
```

**After:**
```php
$heroSection->heading_text = json_encode([
    'en' => 'Projects',
    'ja' => 'プロジェクト'
]);
```

**In Views:**
```blade
{{ $heroSection->getTranslated('heading_text') }}
// Shows "Projects" if locale is 'en'
// Shows "プロジェクト" if locale is 'ja'
```

### Database Structure

**Current:**
```
hero_sections.heading_text = "Projects" (string)
```

**New:**
```
hero_sections.heading_text = '{"en":"Projects","ja":"プロジェクト"}' (JSON)
```

### Migration Strategy

1. Add JSON cast to models
2. Create migration to convert existing strings to JSON
3. Update admin forms to handle both languages
4. Update views to use translated getters





