<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationController extends Controller
{
    /**
     * Set the application locale.
     */
    public function setLocale(Request $request, string $locale)
    {
        if (!in_array($locale, ['en', 'ja'])) {
            return response()->json(['error' => 'Invalid locale'], 400);
        }

        Session::put('locale', $locale);
        App::setLocale($locale);

        return response()->json(['success' => true, 'locale' => $locale]);
    }

    /**
     * Get translations for a specific locale.
     */
    public function getTranslations(string $locale)
    {
        // This logic was previously in routes/web.php
        // Ideally, this should be fetched from language files or a database
        // For now, we'll keep the structure but move it here
        
        $translations = [
            'en' => [
                'my_works' => 'My Works',
                'view_all' => 'View All',
                'certificates' => 'Certificates',
                'courses' => 'Courses',
                'rooms' => 'Rooms',
                'badges' => 'Badges',
                'games' => 'Games',
                'simulations' => 'Simulations',
                'programs' => 'Programs',
                'issued_at' => 'Issued',
                'completed_at' => 'Completed',
                'provider' => 'Provider',
                'level' => 'Level',
                'status' => 'Status',
                'difficulty' => 'Difficulty',
                'loading' => 'Loading...',
                'error_loading' => 'Error loading content',
                'no_content' => 'No content available',
                'search_placeholder' => 'Search...',
                'filter_all' => 'All',
                'back_to_home' => 'Back to Home',
            ],
            'ja' => [
                'my_works' => '作品集',
                'view_all' => 'すべて見る',
                'certificates' => '資格',
                'courses' => 'コース',
                'rooms' => 'ルーム',
                'badges' => 'バッジ',
                'games' => 'ゲーム',
                'simulations' => 'シミュレーション',
                'programs' => 'プログラム',
                'issued_at' => '発行日',
                'completed_at' => '完了日',
                'provider' => '提供元',
                'level' => 'レベル',
                'status' => 'ステータス',
                'difficulty' => '難易度',
                'loading' => '読み込み中...',
                'error_loading' => '読み込みエラー',
                'no_content' => 'コンテンツがありません',
                'search_placeholder' => '検索...',
                'filter_all' => 'すべて',
                'back_to_home' => 'ホームに戻る',
            ]
        ];

        return response()->json($translations[$locale] ?? $translations['en']);
    }

    /**
     * Translate text using external API.
     */
    public function translate(Request $request)
    {
        $text = $request->input('text');
        $targetLang = $request->input('target_lang');
        $sourceLang = $request->input('source_lang', 'en');

        if (!$text || !$targetLang) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        // Check cache first
        $cacheKey = "trans_{$sourceLang}_{$targetLang}_" . md5($text);
        if (cache()->has($cacheKey)) {
            return response()->json(['translated_text' => cache()->get($cacheKey)]);
        }

        // Use Google Translate API
        $apiKey = env('GOOGLE_TRANSLATE_API_KEY');
        if (!$apiKey) {
            // Fallback to MyMemory API (free, rate limited)
            return $this->translateWithMyMemory($text, $sourceLang, $targetLang);
        }

        try {
            $response = Http::post("https://translation.googleapis.com/language/translate/v2?key={$apiKey}", [
                'q' => $text,
                'target' => $targetLang,
                'source' => $sourceLang,
                'format' => 'text'
            ]);

            if ($response->successful()) {
                $translatedText = $response->json()['data']['translations'][0]['translatedText'];
                // Cache for 1 month
                cache()->put($cacheKey, $translatedText, 60 * 24 * 30);
                return response()->json(['translated_text' => $translatedText]);
            }
        } catch (\Exception $e) {
            Log::error('Translation API Error: ' . $e->getMessage());
        }

        // Fallback
        return $this->translateWithMyMemory($text, $sourceLang, $targetLang);
    }

    private function translateWithMyMemory($text, $sourceLang, $targetLang)
    {
        try {
            // Split into chunks if too long (MyMemory limit is 500 chars)
            if (strlen($text) > 450) {
                // Simple chunking logic here or return error
                return response()->json(['error' => 'Text too long for free translation'], 400);
            }

            $response = Http::get("https://api.mymemory.translated.net/get", [
                'q' => $text,
                'langpair' => "{$sourceLang}|{$targetLang}",
            ]);

            if ($response->successful()) {
                $translatedText = $response->json()['responseData']['translatedText'];
                return response()->json(['translated_text' => $translatedText]);
            }
        } catch (\Exception $e) {
            Log::error('MyMemory API Error: ' . $e->getMessage());
        }

        return response()->json(['error' => 'Translation failed'], 500);
    }
}
