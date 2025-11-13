<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Lab;
use App\Models\NavItem;
use App\Models\Profile;
use App\Models\Category;
use App\Models\NavLink;
use App\Models\Testimonial;

// API endpoints for i18n
Route::post('/api/locale/{locale}', function (Request $request, string $locale) {
    if (!in_array($locale, ['en', 'ja'])) {
        return response()->json(['error' => 'Invalid locale'], 400);
    }
    
    // Set locale in session
    session(['locale' => $locale]);
    
    // Also set cookie for immediate access
    $response = response()->json(['success' => true, 'locale' => $locale]);
    return $response->cookie('locale', $locale, 60 * 24 * 30); // 30 days
})->name('api.locale.set');

Route::get('/api/translations/{locale}', function (string $locale) {
    if (!in_array($locale, ['en', 'ja'])) {
        $locale = 'en';
    }
    
    $translations = [];
    $files = ['app'];
    
    foreach ($files as $file) {
        $path = resource_path("lang/{$locale}/{$file}.php");
        if (file_exists($path)) {
            $translations[$file] = require $path;
        }
    }
    
    return response()->json($translations);
})->name('api.translations.get');

// Translation API endpoint
Route::post('/api/translate', function (Request $request) {
    $request->validate([
        'text' => 'required|string|max:5000',
        'from' => 'required|string|in:en,ja',
        'to' => 'required|string|in:en,ja',
    ]);
    
    $text = $request->input('text');
    $from = $request->input('from');
    $to = $request->input('to');
    
    // If same language, return as is
    if ($from === $to) {
        return response()->json(['translated' => $text]);
    }
    
    // Try to use Google Translate API if available
    $apiKey = env('GOOGLE_TRANSLATE_API_KEY');
    if ($apiKey) {
        try {
            $client = new \Google\Cloud\Translate\V2\TranslateClient(['key' => $apiKey]);
            $result = $client->translate($text, [
                'source' => $from,
                'target' => $to,
            ]);
            return response()->json(['translated' => $result['text']]);
        } catch (\Exception $e) {
            // Fallback to simple method
        }
    }
    
    // Fallback: Use MyMemory Translation API (free tier)
    try {
        $url = 'https://api.mymemory.translated.net/get?q=' . urlencode($text) . '&langpair=' . $from . '|' . $to;
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0',
            ]
        ]);
        $response = @file_get_contents($url, false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data['responseData']['translatedText']) && !empty(trim($data['responseData']['translatedText']))) {
                return response()->json(['translated' => $data['responseData']['translatedText']]);
            }
        }
    } catch (\Exception $e) {
        \Log::warning('Translation API error: ' . $e->getMessage());
    }
    
    // If all else fails, return empty string (user can manually translate)
    // But log it so we know translations are failing
    \Log::info('Translation failed for text: ' . substr($text, 0, 100) . '... from ' . $from . ' to ' . $to);
    return response()->json(['translated' => '']);
})->name('api.translate');

// API endpoints to get data as JSON (for modals)
Route::get('/api/courses/{course}', function (Course $course) {
    $course->load('media', 'tags', 'categories');
    return response()->json([
        'id' => $course->id,
        'type' => 'course',
        'title' => $course->getTranslated('title'),
        'provider' => $course->getTranslated('provider'),
        'credential_id' => $course->credential_id,
        'verify_url' => $course->verify_url,
        'issued_at' => $course->issued_at,
        'completed_at' => $course->completed_at,
        'description' => $course->description ?? null,
        'imageUrl' => $course->media->first() ? asset('storage/' . $course->media->first()->path) : null,
        'categories' => $course->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->getTranslated('name')]),
        'tags' => $course->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
    ]);
})->name('api.courses.show');

Route::get('/api/book-pages/{bookPage:slug}', function (\App\Models\BookPage $bookPage) {
    $bookPage->load('media', 'tags', 'categories');
    return response()->json([
        'id' => $bookPage->id,
        'type' => 'book-page',
        'title' => $bookPage->getTranslated('title'),
        'content' => $bookPage->getTranslated('content'),
        'summary' => $bookPage->getTranslated('summary'),
        'author' => $bookPage->author,
        'book_title' => $bookPage->book_title,
        'page_number' => $bookPage->page_number,
        'read_at' => $bookPage->read_at,
        'key_objectives' => $bookPage->key_objectives,
        'reflection' => $bookPage->reflection,
        'applied_snippet' => $bookPage->applied_snippet,
        'references' => $bookPage->references,
        'how_to_run' => $bookPage->how_to_run,
        'result_evidence' => $bookPage->result_evidence,
        'difficulty' => $bookPage->difficulty,
        'time_spent' => $bookPage->time_spent,
        'status' => $bookPage->status,
        'imageUrl' => $bookPage->media->first() ? asset('storage/' . $bookPage->media->first()->path) : null,
        'categories' => $bookPage->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->getTranslated('name')]),
        'tags' => $bookPage->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
    ]);
})->name('api.book-pages.show');

Route::get('/api/code-summaries/{codeSummary:slug}', function (\App\Models\CodeSummary $codeSummary) {
    $codeSummary->load('media', 'tags', 'categories');
    return response()->json([
        'id' => $codeSummary->id,
        'type' => 'code-summary',
        'title' => $codeSummary->getTranslated('title'),
        'code' => $codeSummary->code,
        'summary' => $codeSummary->getTranslated('summary'),
        'language' => $codeSummary->language,
        'file_path' => $codeSummary->file_path,
        'repository_url' => $codeSummary->repository_url,
        // Context & Purpose
        'problem_statement' => $codeSummary->problem_statement,
        'learning_goal' => $codeSummary->learning_goal,
        'use_case' => $codeSummary->use_case,
        // Proof & Reproducibility
        'how_to_run' => $codeSummary->how_to_run,
        'expected_output' => $codeSummary->expected_output,
        'dependencies' => $codeSummary->dependencies,
        'test_status' => $codeSummary->test_status,
        // Evaluation & Reflection
        'complexity_notes' => $codeSummary->complexity_notes,
        'security_notes' => $codeSummary->security_notes,
        'reflection' => $codeSummary->reflection,
        // Traceability
        'commit_sha' => $codeSummary->commit_sha,
        'license' => $codeSummary->license,
        'file_path_repo' => $codeSummary->file_path_repo,
        // Metadata
        'framework' => $codeSummary->framework,
        'difficulty' => $codeSummary->difficulty,
        'time_spent' => $codeSummary->time_spent,
        'status' => $codeSummary->status,
        'imageUrl' => $codeSummary->media->first() ? asset('storage/' . $codeSummary->media->first()->path) : null,
        'categories' => $codeSummary->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->getTranslated('name')]),
        'tags' => $codeSummary->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
    ]);
})->name('api.code-summaries.show');

// API endpoint to get all content items for a section (CategoryItem)
Route::get('/api/sections/{section}/content', function (\App\Models\CategoryItem $section) {
    $section->load([
        'bookPages.media',
        'bookPages.tags',
        'bookPages.categories',
        'codeSummaries.media',
        'codeSummaries.tags',
        'codeSummaries.categories',
        'rooms.media',
        'rooms.tags',
        'rooms.categories',
        'certificates.media',
        'certificates.tags',
        'certificates.categories',
        'courses.media',
        'courses.tags',
        'courses.categories'
    ]);
    
    $locale = app()->getLocale();
    $resolveString = function ($value, $fallback = '') use ($locale, &$resolveString) {
        if (is_null($value)) {
            return $fallback;
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                return $value;
            }
        }
        
        if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
            $value = $value->toArray();
        }
        
        if (is_array($value)) {
            $preferredKeys = [$locale, 'en', 'ja'];
            foreach ($preferredKeys as $key) {
                if (isset($value[$key]) && is_string($value[$key]) && $value[$key] !== '') {
                    return $value[$key];
                }
            }
            foreach ($value as $v) {
                $resolved = $resolveString($v, null);
                if (is_string($resolved) && $resolved !== '') {
                    return $resolved;
                }
            }
            return $fallback;
        }
        
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }
        
        if (is_scalar($value)) {
            return (string) $value;
        }
        
        return $fallback;
    };
    
    return response()->json([
        'section' => [
            'id' => $section->id,
            'title' => $resolveString($section->getTranslated('title'), $section->slug),
            'slug' => $section->slug,
            'summary' => $resolveString($section->getTranslated('summary')),
        ],
        'bookPages' => $section->bookPages->map(function($item) use ($resolveString) {
            return [
                'id' => $item->id,
                'type' => 'book-page',
                'title' => $resolveString($item->getTranslated('title'), $item->slug),
                'slug' => $item->slug,
                'content' => $resolveString($item->getTranslated('content')),
                'summary' => $resolveString($item->getTranslated('summary')),
                'author' => $item->author,
                'book_title' => $item->book_title,
                'page_number' => $item->page_number,
                'read_at' => $item->read_at,
                'key_objectives' => $item->key_objectives,
                'reflection' => $item->reflection,
                'applied_snippet' => $item->applied_snippet,
                'references' => $item->references,
                'how_to_run' => $item->how_to_run,
                'result_evidence' => $item->result_evidence,
                'difficulty' => $item->difficulty,
                'time_spent' => $item->time_spent,
                'status' => $item->status,
                'imageUrl' => $item->media->first() ? asset('storage/' . $item->media->first()->path) : null,
                'categories' => $item->categories->map(function($c) use ($resolveString) {
                    return [
                        'id' => $c->id,
                        'name' => $resolveString($c->getTranslated('name'), $c->slug),
                    ];
                }),
                'tags' => $item->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
            ];
        }),
        'codeSummaries' => $section->codeSummaries->map(function($item) use ($resolveString) {
            return [
                'id' => $item->id,
                'type' => 'code-summary',
                'title' => $resolveString($item->getTranslated('title'), $item->slug),
                'slug' => $item->slug,
                'code' => $item->code,
                'summary' => $resolveString($item->getTranslated('summary')),
                'language' => $item->language,
                'file_path' => $item->file_path,
                'repository_url' => $item->repository_url,
                // Context & Purpose
                'problem_statement' => $item->problem_statement,
                'learning_goal' => $item->learning_goal,
                'use_case' => $item->use_case,
                // Proof & Reproducibility
                'how_to_run' => $item->how_to_run,
                'expected_output' => $item->expected_output,
                'dependencies' => $item->dependencies,
                'test_status' => $item->test_status,
                // Evaluation & Reflection
                'complexity_notes' => $item->complexity_notes,
                'security_notes' => $item->security_notes,
                'reflection' => $item->reflection,
                // Traceability
                'commit_sha' => $item->commit_sha,
                'license' => $item->license,
                'file_path_repo' => $item->file_path_repo,
                // Metadata
                'framework' => $item->framework,
                'difficulty' => $item->difficulty,
                'time_spent' => $item->time_spent,
                'status' => $item->status,
                'imageUrl' => $item->media->first() ? asset('storage/' . $item->media->first()->path) : null,
                'categories' => $item->categories->map(function($c) use ($resolveString) {
                    return [
                        'id' => $c->id,
                        'name' => $resolveString($c->getTranslated('name'), $c->slug),
                    ];
                }),
                'tags' => $item->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
            ];
        }),
        'rooms' => $section->rooms->map(function($item) use ($resolveString) {
            return [
                'id' => $item->id,
                'type' => 'room',
                'title' => $resolveString($item->getTranslated('title'), $item->slug),
                'slug' => $item->slug,
                'description' => $resolveString($item->getTranslated('description')),
                'summary' => $resolveString($item->getTranslated('summary')),
                'platform' => $item->platform,
                'room_url' => $item->room_url,
                'difficulty' => $item->difficulty,
                'completed_at' => $item->completed_at,
                // Learning & Purpose
                'objective_goal' => $item->objective_goal,
                'key_techniques_used' => $item->key_techniques_used,
                'tools_commands_used' => $item->tools_commands_used,
                'attack_vector_summary' => $item->attack_vector_summary,
                'flag_evidence_proof' => $item->flag_evidence_proof,
                'time_spent' => $item->time_spent,
                'reflection_takeaways' => $item->reflection_takeaways,
                'difficulty_confirmation' => $item->difficulty_confirmation,
                // Reproducibility
                'walkthrough_summary_steps' => $item->walkthrough_summary_steps,
                'tools_environment' => $item->tools_environment,
                'command_log_snippet' => $item->command_log_snippet,
                'room_id_author' => $item->room_id_author,
                'completion_screenshot_report_link' => $item->completion_screenshot_report_link,
                // Traceability & Meta
                'platform_username' => $item->platform_username,
                'platform_profile_link' => $item->platform_profile_link,
                'status' => $item->status,
                'score_points_earned' => $item->score_points_earned,
                'imageUrl' => $item->media->first() ? asset('storage/' . $item->media->first()->path) : null,
                'categories' => $item->categories->map(function($c) use ($resolveString) {
                    return [
                        'id' => $c->id,
                        'name' => $resolveString($c->getTranslated('name'), $c->slug),
                    ];
                }),
                'tags' => $item->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
            ];
        }),
        'certificates' => $section->certificates->map(function($item) use ($resolveString) {
            return [
                'id' => $item->id,
                'type' => 'certificate',
                'title' => $resolveString($item->getTranslated('title')),
                'provider' => $resolveString($item->getTranslated('provider')),
                'credential_id' => $item->credential_id,
                'verify_url' => $item->verify_url,
                'issued_at' => $item->issued_at,
                'description' => $item->description ?? null,
                'imageUrl' => $item->media->first() ? asset('storage/' . $item->media->first()->path) : null,
                'categories' => $item->categories->map(function($c) use ($resolveString) {
                    return [
                        'id' => $c->id,
                        'name' => $resolveString($c->getTranslated('name'), $c->slug),
                    ];
                }),
                'tags' => $item->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
            ];
        }),
        'courses' => $section->courses->map(function($item) use ($resolveString) {
            return [
                'id' => $item->id,
                'type' => 'course',
                'title' => $resolveString($item->getTranslated('title')),
                'provider' => $resolveString($item->getTranslated('provider')),
                'credential_id' => $item->credential_id,
                'verify_url' => $item->verify_url,
                'issued_at' => $item->issued_at,
                'completed_at' => $item->completed_at,
                'description' => $item->description ?? null,
                'imageUrl' => $item->media->first() ? asset('storage/' . $item->media->first()->path) : null,
                'categories' => $item->categories->map(function($c) use ($resolveString) {
                    return [
                        'id' => $c->id,
                        'name' => $resolveString($c->getTranslated('name'), $c->slug),
                    ];
                }),
                'tags' => $item->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
            ];
        }),
    ]);
})->name('api.sections.content');

Route::get('/api/rooms/{room:slug}', function (\App\Models\Room $room) {
    $room->load('media', 'tags', 'categories');
    return response()->json([
        'id' => $room->id,
        'type' => 'room',
        'title' => $room->getTranslated('title'),
        'description' => $room->getTranslated('description'),
        'summary' => $room->getTranslated('summary'),
        'platform' => $room->platform,
        'room_url' => $room->room_url,
        'difficulty' => $room->difficulty,
        'completed_at' => $room->completed_at,
        // Learning & Purpose
        'objective_goal' => $room->objective_goal,
        'key_techniques_used' => $room->key_techniques_used,
        'tools_commands_used' => $room->tools_commands_used,
        'attack_vector_summary' => $room->attack_vector_summary,
        'flag_evidence_proof' => $room->flag_evidence_proof,
        'time_spent' => $room->time_spent,
        'reflection_takeaways' => $room->reflection_takeaways,
        'difficulty_confirmation' => $room->difficulty_confirmation,
        // Reproducibility
        'walkthrough_summary_steps' => $room->walkthrough_summary_steps,
        'tools_environment' => $room->tools_environment,
        'command_log_snippet' => $room->command_log_snippet,
        'room_id_author' => $room->room_id_author,
        'completion_screenshot_report_link' => $room->completion_screenshot_report_link,
        // Traceability & Meta
        'platform_username' => $room->platform_username,
        'platform_profile_link' => $room->platform_profile_link,
        'status' => $room->status,
        'score_points_earned' => $room->score_points_earned,
        'imageUrl' => $room->media->first() ? asset('storage/' . $room->media->first()->path) : null,
        'categories' => $room->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->getTranslated('name')]),
        'tags' => $room->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
    ]);
})->name('api.rooms.show');

Route::get('/api/certificates/{certificate}', function (Certificate $certificate) {
    $certificate->load('media', 'tags', 'categories');
    return response()->json([
        'id' => $certificate->id,
        'type' => 'certificate',
        'title' => $certificate->getTranslated('title'),
        'provider' => $certificate->getTranslated('provider'),
        'credential_id' => $certificate->credential_id,
        'verify_url' => $certificate->verify_url,
        'issued_at' => $certificate->issued_at,
        'learning_outcomes' => $certificate->getTranslated('learning_outcomes'),
        'reflection' => $certificate->getTranslated('reflection'),
        'imageUrl' => $certificate->media->first() ? asset('storage/' . $certificate->media->first()->path) : null,
        'categories' => $certificate->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->getTranslated('name')]),
        'tags' => $certificate->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
    ]);
})->name('api.certificates.show');

Route::get('/api/blogs', function (Request $request) {
    $username = $request->query('username');
    $query = \App\Models\Blog::where('is_published', true);
    
    // Filter by user if username is provided
    if ($username) {
        $user = \App\Models\User::where('username', $username)
            ->orWhere('slug', $username)
            ->first();
        if ($user) {
            $query->where('user_id', $user->id);
        }
    }
    
    $blogs = $query->with(['media', 'tags'])
        ->orderBy('published_at', 'desc')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($blog) {
            $image = $blog->media->where('type', 'image')->first();
            $publishedAt = $blog->published_at ? $blog->published_at : $blog->created_at;
            return [
                'id' => $blog->id,
                'title' => $blog->getTranslated('title'),
                'slug' => $blog->slug,
                'excerpt' => $blog->getTranslated('excerpt') ?? substr(strip_tags($blog->getTranslated('content') ?? ''), 0, 150) . '...',
                'content' => $blog->getTranslated('content'),
                'category' => $blog->category ?? 'Uncategorized',
                'published_at' => $publishedAt->format('M d, Y'),
                'published_at_raw' => $publishedAt->toIso8601String(),
                'imageUrl' => $image ? asset('storage/' . $image->path) : null,
                'linkedin_url' => $blog->linkedin_url,
                'tags' => $blog->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
            ];
        });
    
    return response()->json($blogs);
})->name('api.blogs.index');

Route::get('/api/blogs/{blog:slug}', function (\App\Models\Blog $blog) {
    if (!$blog->is_published) {
        abort(404);
    }
    
    $blog->load('media', 'tags');
    $image = $blog->media->where('type', 'image')->first();
    $publishedAt = $blog->published_at ? $blog->published_at : $blog->created_at;
    
    // Helper function to extract content from various formats
    $extractContent = function($field) use ($blog) {
        $locale = app()->getLocale();
        
        // Get raw value from database before casting
        $rawValue = $blog->getRawOriginal($field);
        
        // If null or empty, try getTranslated
        if (is_null($rawValue) || $rawValue === '') {
            $translated = $blog->getTranslated($field);
            return $translated ?: '';
        }
        
        // Helper to decode nested JSON strings recursively
        $decodeNestedJson = function($str, $maxDepth = 5) use ($locale, &$decodeNestedJson) {
            if (!is_string($str) || $maxDepth <= 0) return $str;
            
            $trimmed = trim($str);
            // Check if it looks like JSON
            if (strpos($trimmed, '{') === false && strpos($trimmed, '"{') !== 0 && strpos($trimmed, '"{\\"') !== 0) {
                return $str; // Not JSON, return as-is
            }
            
            // Strategy 1: Try direct JSON decode
            $decoded = json_decode($str, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $result = $decoded[$locale] ?? $decoded['en'] ?? ($decoded['ja'] ?? '');
                // If result is still a JSON string, decode recursively
                if (is_string($result) && $result !== $str) {
                    return $decodeNestedJson($result, $maxDepth - 1);
                }
                return $result;
            }
            
            // Strategy 2: If it's a quoted JSON string, remove outer quotes
            if (preg_match('/^"(.+)"$/s', $str, $matches)) {
                $inner = $matches[1];
                // Unescape the inner string - handle multiple levels of escaping
                $inner = str_replace(['\\\\"', '\\"', '\\n', '\\r', '\\t', '\\\\', '\\u'], ['"', '"', "\n", "\r", "\t", '\\', '\\u'], $inner);
                $decoded = json_decode($inner, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $result = $decoded[$locale] ?? $decoded['en'] ?? ($decoded['ja'] ?? '');
                    // If result is still a JSON string, decode recursively
                    if (is_string($result) && $result !== $inner) {
                        return $decodeNestedJson($result, $maxDepth - 1);
                    }
                    return $result;
                }
            }
            
            // Strategy 2.5: Handle triple-encoded JSON (escaped quotes within escaped quotes)
            if (preg_match('/^"(.+)"$/s', $str, $matches)) {
                $inner = $matches[1];
                // Try multiple unescape passes
                for ($i = 0; $i < 3; $i++) {
                    $inner = stripslashes($inner);
                    // Remove outer quotes if they appear after unescaping
                    if (preg_match('/^"(.+)"$/s', $inner, $innerMatches)) {
                        $inner = $innerMatches[1];
                    }
                    $decoded = json_decode($inner, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $result = $decoded[$locale] ?? $decoded['en'] ?? ($decoded['ja'] ?? '');
                        // If result is still a JSON string, decode recursively
                        if (is_string($result) && $result !== $inner) {
                            return $decodeNestedJson($result, $maxDepth - 1);
                        }
                        return $result;
                    }
                }
            }
            
            // Strategy 3: Try unescaping the entire string
            $unescaped = stripslashes($str);
            if ($unescaped !== $str) {
                $decoded = json_decode($unescaped, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $result = $decoded[$locale] ?? $decoded['en'] ?? ($decoded['ja'] ?? '');
                    // If result is still a JSON string, decode recursively
                    if (is_string($result) && $result !== $unescaped) {
                        return $decodeNestedJson($result, $maxDepth - 1);
                    }
                    return $result;
                }
            }
            
            // Strategy 4: Try removing escaped quotes and decode
            if (strpos($str, '\\"') !== false) {
                $unquoted = str_replace('\\"', '"', $str);
                // Remove outer quotes if present
                if (preg_match('/^"(.+)"$/s', $unquoted, $matches)) {
                    $unquoted = $matches[1];
                }
                $decoded = json_decode($unquoted, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $result = $decoded[$locale] ?? $decoded['en'] ?? ($decoded['ja'] ?? '');
                    // If result is still a JSON string, decode recursively
                    if (is_string($result) && $result !== $unquoted) {
                        return $decodeNestedJson($result, $maxDepth - 1);
                    }
                    return $result;
                }
            }
            
            return $str; // Return original if can't decode
        };
        
        // If it's already a string, check if it's JSON
        if (is_string($rawValue)) {
            $decoded = json_decode($rawValue, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // It's JSON, extract translation with fallback
                // Priority: current locale -> English -> Japanese
                $content = '';
                if (!empty($decoded[$locale]) && trim($decoded[$locale]) !== '') {
                    $content = $decoded[$locale];
                } elseif (!empty($decoded['en']) && trim($decoded['en']) !== '') {
                    $content = $decoded['en'];
                } elseif (!empty($decoded['ja']) && trim($decoded['ja']) !== '') {
                    $content = $decoded['ja'];
                }
                
                // If we have content, try to decode nested JSON
                if (!empty($content) && is_string($content)) {
                    $finalContent = $decodeNestedJson($content);
                    // Only return if we got something meaningful
                    if (!empty($finalContent) && trim($finalContent) !== '') {
                        // Decode Unicode escape sequences (\uXXXX)
                        if (strpos($finalContent, '\\u') !== false) {
                            $finalContent = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/i', function ($match) {
                                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                            }, $finalContent);
                        }
                        // Decode other escape sequences
                        $finalContent = stripcslashes($finalContent);
                        return $finalContent;
                    }
                }
                
                // Return empty string if no content found
                return '';
            } else {
                // It's a plain string, but might be a quoted JSON string
                $decoded = $decodeNestedJson($rawValue);
                if ($decoded !== $rawValue) {
                    return $decoded;
                }
                return $rawValue;
            }
        }
        
        // If it's an array (after casting), extract translation
        if (is_array($rawValue)) {
            $content = '';
            // Priority: current locale -> English -> Japanese
            if (!empty($rawValue[$locale]) && trim($rawValue[$locale]) !== '') {
                $content = $rawValue[$locale];
            } elseif (!empty($rawValue['en']) && trim($rawValue['en']) !== '') {
                $content = $rawValue['en'];
            } elseif (!empty($rawValue['ja']) && trim($rawValue['ja']) !== '') {
                $content = $rawValue['ja'];
            }
            
            // If we have content, try to decode nested JSON
            if (!empty($content) && is_string($content)) {
                $decoded = $decodeNestedJson($content);
                // Only return if we got something meaningful
                if (!empty($decoded) && trim($decoded) !== '') {
                    return $decoded;
                }
            }
            
            // Return empty string if no content found
            return '';
        }
        
        // Final fallback: try getTranslated
        $translated = $blog->getTranslated($field);
        return $translated ?: '';
    };
    
    // Get content and excerpt
    $content = $extractContent('content');
    $excerpt = $extractContent('excerpt');
    
    // If content is empty, use excerpt as fallback
    if (empty($content) || trim($content) === '') {
        $content = $excerpt;
    }
    
    // Final fallback
    if (empty($content) || trim($content) === '') {
        $content = 'No content available.';
    }
    
    return response()->json([
        'id' => $blog->id,
        'title' => $blog->getTranslated('title') ?: 'Untitled',
        'slug' => $blog->slug,
        'excerpt' => $excerpt,
        'content' => $content,
        'category' => $blog->category ?? 'Uncategorized',
        'published_at' => $publishedAt->format('M d, Y'),
        'published_at_raw' => $publishedAt->toIso8601String(),
        'imageUrl' => $image ? asset('storage/' . $image->path) : null,
        'linkedin_url' => $blog->linkedin_url,
        'tags' => $blog->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]),
    ]);
})->name('api.blogs.show');

Route::get('/api/testimonials/{testimonial}', function (\App\Models\Testimonial $testimonial) {
    if (!$testimonial->is_published) {
        abort(404);
    }
    
    $testimonial->load('media');
    $images = $testimonial->media->where('type', 'image');
    $mainPhoto = $testimonial->photo_url;
    if (!$mainPhoto && $images->first()) {
        $mainPhoto = asset('storage/' . $images->first()->path);
    }
    
    // Helper function to extract translatable content
    $extractTranslatable = function($field) use ($testimonial) {
        $rawValue = $testimonial->getAttribute($field);
        
        // If it's already a string (not JSON), return it
        if (is_string($rawValue) && !is_numeric($rawValue)) {
            $decoded = json_decode($rawValue, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $locale = app()->getLocale();
                return $decoded[$locale] ?? $decoded['en'] ?? ($decoded['ja'] ?? '');
            } else {
                return $rawValue;
            }
        }
        
        // If it's an array (after casting), extract translation
        if (is_array($rawValue)) {
            $locale = app()->getLocale();
            return $rawValue[$locale] ?? $rawValue['en'] ?? ($rawValue['ja'] ?? '');
        }
        
        // Try getTranslated as fallback
        $translated = $testimonial->getTranslated($field);
        if (!empty($translated) && trim($translated) !== '') {
            return $translated;
        }
        
        return '';
    };
    
    return response()->json([
        'id' => $testimonial->id,
        'name' => $testimonial->name,
        'company' => $extractTranslatable('company'),
        'title' => $testimonial->title,
        'quote' => $extractTranslatable('quote'),
        'sns_url' => $testimonial->sns_url,
        'mainPhoto' => $mainPhoto,
        'images' => $images->map(fn($m) => asset('storage/' . $m->path))->values()->all(),
    ]);
})->name('api.testimonials.show');

Route::get('/api/home-page-sections/{homePageSection}', function (\App\Models\HomePageSection $homePageSection) {
    return response()->json([
        'id' => $homePageSection->id,
        'nav_item_id' => $homePageSection->nav_item_id,
        'position' => $homePageSection->position,
        'text_alignment' => $homePageSection->text_alignment,
        'animation_style' => $homePageSection->animation_style,
        'title' => $homePageSection->title, // Array format: {en: '', ja: ''}
        'subtitle' => $homePageSection->subtitle, // Array format: {en: '', ja: ''}
        'enabled' => $homePageSection->enabled,
        'selected_nav_link_ids' => $homePageSection->selected_nav_link_ids,
        'subsection_configurations' => $homePageSection->subsection_configurations,
    ]);
})->name('api.home-page-sections.show');

// Public course show page (kept for backward compatibility, but modals are preferred)
Route::get('/courses/{course}', function (Course $course) {
    $course->load('media', 'tags', 'categories');
    return view('courses.show', compact('course'));
})->name('courses.show');

// Landing page route - MUST be before /{username} route
Route::get('/', [\App\Http\Controllers\LandingController::class, 'index'])->name('landing');

// OLD HOME ROUTE - MOVED TO PORTFOLIO CONTROLLER
/*
Route::get('/', function () {
    $profile = Profile::with('media')->first();
    // Refresh heroSection to get latest data
    $heroSection = \App\Models\HeroSection::first();
    // Get engagement section
    $engagementSection = \App\Models\EngagementSection::first();
    if ($heroSection) {
        $heroSection->refresh(); // Ensure we have latest data
    }
    
    // Get engagement section video
    $engagementVideo = null;
    if ($engagementSection) {
        $videoMedia = $engagementSection->media()->where('type', 'video')->first();
        if ($videoMedia) {
            $path = $videoMedia->path;
            if (strpos($path, 'storage/') === 0 || strpos($path, '/storage/') === 0) {
                $engagementVideo = asset($path);
            } elseif (strpos($path, 'http') === 0) {
                $engagementVideo = $path;
            } else {
                $engagementVideo = asset('storage/' . $path);
            }
        }
    }
    
    // Fallback to default video if no video uploaded
    if (!$engagementVideo) {
        // Check multiple possible locations for the fallback video
        $fallbackPaths = [
            'storage/videos/engagement-01.mp4',
            'engagement/engagement-01.mp4',
            'videos/engagement-01.mp4',
        ];
        
        foreach ($fallbackPaths as $fallbackPath) {
            if (file_exists(public_path($fallbackPath))) {
                $engagementVideo = asset($fallbackPath);
                break;
            }
        }
        
        // If still not found, use the default path (will show 404 but won't break)
        if (!$engagementVideo) {
            $engagementVideo = asset('storage/videos/engagement-01.mp4');
        }
    }
    
    // Get hero section profile images
    $heroProfileImages = [];
    if ($heroSection) {
        foreach ($heroSection->media()->where('type', 'image')->get() as $media) {
            $path = $media->path;
            if (strpos($path, 'storage/') === 0 || strpos($path, '/storage/') === 0) {
                $heroProfileImages[] = asset($path);
            } elseif (strpos($path, 'http') === 0) {
                $heroProfileImages[] = $path;
            } elseif (strpos($path, 'images/') === 0) {
                // Handle images in public/images directory
                $heroProfileImages[] = asset($path);
            } else {
                $heroProfileImages[] = asset('storage/' . $path);
            }
        }
    }
    
    // If no hero images, try default images from public/images/
    if (empty($heroProfileImages)) {
        for ($i = 1; $i <= 3; $i++) {
            $defaultImage = "images/pp{$i}.jpg";
            if (file_exists(public_path($defaultImage))) {
                $heroProfileImages[] = asset($defaultImage);
            }
        }
    }
    
    // Ensure heroSection is not null for view
    if (!$heroSection) {
        $heroSection = new \App\Models\HeroSection();
    }
    
    // Collect profile images from media relationship
    $profileImages = [];
    if ($profile && $profile->media) {
        foreach ($profile->media->where('type', 'image') as $media) {
            // Handle path - it might already include 'storage/' or be relative
            $path = $media->path;
            if (strpos($path, 'storage/') === 0 || strpos($path, '/storage/') === 0) {
                // Path already includes storage, use as-is
                $profileImages[] = asset($path);
            } elseif (strpos($path, 'http') === 0) {
                // Full URL, use directly
                $profileImages[] = $path;
            } elseif (strpos($path, 'images/') === 0) {
                // Handle images in public/images directory
                $profileImages[] = asset($path);
            } else {
                // Relative path, prepend storage/
                $profileImages[] = asset('storage/' . $path);
            }
        }
    }
    
    // Also scan for profile images in public/images/ directory (pp1.jpg, pp2.jpg, pp3.jpg)
    if (empty($profileImages)) {
        for ($i = 1; $i <= 3; $i++) {
            $defaultImage = "images/pp{$i}.jpg";
            if (file_exists(public_path($defaultImage))) {
                $profileImages[] = asset($defaultImage);
            }
        }
    }
    
    // Also scan for profile images in storage/app/public/profile/
    $profileDir = storage_path('app/public/profile');
    if (is_dir($profileDir)) {
        $files = glob($profileDir . '/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);
        if (!empty($files)) {
            foreach ($files as $file) {
                if (!file_exists($file)) continue;
                $filename = basename($file);
                $profileImages[] = asset('storage/profile/' . $filename);
            }
        }
    }
    
    // If we have the old photo_path, add it too
    if ($profile && $profile->photo_path) {
        // Handle different path formats
        $photoPath = $profile->photo_path;
        if (strpos($photoPath, 'images/') === 0) {
            // Path starts with images/ - it's in public/images/, use asset() directly
            $photoUrl = asset($photoPath);
        } elseif (strpos($photoPath, 'storage/') === 0 || strpos($photoPath, '/storage/') === 0) {
            // Path already includes storage/, use as-is
            $photoUrl = asset($photoPath);
        } elseif (strpos($photoPath, 'http') === 0) {
            // Full URL, use directly
            $photoUrl = $photoPath;
        } else {
            // Relative path in storage, prepend storage/
            $photoUrl = asset('storage/' . $photoPath);
        }
        
        if (!in_array($photoUrl, $profileImages)) {
            $profileImages[] = $photoUrl;
        }
    }
    
    // Fallback to default image if no images found
    if (empty($profileImages)) {
        $profileImages[] = asset('images/profile_main.png');
    }
    
    // Remove duplicates
    $profileImages = array_unique($profileImages);
    $categories = Category::orderBy('position')->get();
    $services = $categories->map(function($c){
        return [
            'icon' => '<span>⭐</span>',
            'title' => $c->name,
            'desc' => $c->navLinksMany()->count().' items', // Use many-to-many relationship
        ];
    });
    // Fetch certificates with media for the certificates section
    $certificates = Certificate::with('media')->latest('issued_at')->limit(6)->get();
    
    // Scan for images in public/certificates/ or storage/app/public/certificates/ directories
    $certificatesDirs = [
        public_path('certificates'),
        storage_path('app/public/certificates'),
        storage_path('app/public/certficates'), // Handle typo in directory name
    ];
    $publicCertificates = [];
    
    foreach ($certificatesDirs as $certificatesDir) {
        if (is_dir($certificatesDir)) {
            $files = glob($certificatesDir . '/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);
            // Only process if files were found
            if (!empty($files)) {
                foreach ($files as $file) {
                    // Verify file actually exists
                    if (!file_exists($file)) {
                        continue;
                    }
                    $filename = basename($file);
                    // Determine the correct URL path
                    if (strpos($certificatesDir, 'storage/app/public') !== false) {
                        // Images in storage need /storage/ path
                        if (strpos($certificatesDir, 'certficates') !== false) {
                            // Handle typo in directory name
                            $publicCertificates[] = '/storage/certficates/' . $filename;
                        } else {
                            $publicCertificates[] = '/storage/certificates/' . $filename;
                        }
                    } else {
                        // Images in public directory (shouldn't happen, but just in case)
                        // Verify file actually exists before adding
                        if (file_exists($certificatesDir . '/' . $filename)) {
                            $publicCertificates[] = '/certificates/' . $filename;
                        }
                    }
                }
            }
        }
    }
    
    // Remove duplicates and sort
    $publicCertificates = array_unique($publicCertificates);
    sort($publicCertificates);
    
    // Ensure all paths are correct - fix any incorrect paths
    $publicCertificates = array_map(function($path) {
        // If path incorrectly starts with /certificates/ but file is in storage, fix it
        if (strpos($path, '/certificates/') === 0) {
            $filename = basename($path);
            // Check if file actually exists in storage/certficates
            if (file_exists(storage_path('app/public/certficates/' . $filename))) {
                return '/storage/certficates/' . $filename;
            }
        }
        return $path;
    }, $publicCertificates);
    
    // Prepare certificates data for React component
    // If we have more images than certificates, create virtual certificate entries for extra images
    $certificatesData = $certificates->map(function($cert, $index) use ($publicCertificates) {
        $mediaArray = $cert->media->map(function($m) {
            return [
                'id' => $m->id,
                'type' => $m->type,
                'path' => $m->path,
                'title' => $m->title,
            ];
        })->toArray();
        
        // If no media attached, try to use image from certificates directories
        // Try to match by certificate ID first, then by index
        if (empty($mediaArray)) {
            // Check multiple locations for certificate images
            $searchPaths = [
                public_path('certificates/certificate-' . $cert->id . '.*'),
                storage_path('app/public/certificates/certificate-' . $cert->id . '.*'),
                storage_path('app/public/certficates/certificate-' . $cert->id . '.*'), // Handle typo
            ];
            
            $matchingFile = null;
            $imagePath = null;
            
            foreach ($searchPaths as $searchPath) {
                $files = glob($searchPath);
                if (!empty($files)) {
                    $matchingFile = $files[0];
                    $filename = basename($matchingFile);
                    // Determine correct URL path based on which directory was found
                    $dirPath = dirname($matchingFile);
                    if (strpos($dirPath, 'certficates') !== false) {
                        // Handle typo in directory name
                        $imagePath = '/storage/certficates/' . $filename;
                    } elseif (strpos($dirPath, 'storage/app/public/certificates') !== false) {
                        $imagePath = '/storage/certificates/' . $filename;
                    } elseif (strpos($dirPath, 'public/certificates') !== false) {
                        $imagePath = '/certificates/' . $filename;
                    } else {
                        // Default to storage path
                        $imagePath = '/storage/certficates/' . $filename;
                    }
                    break;
                }
            }
            
            if ($imagePath) {
                $mediaArray[] = [
                    'id' => 'public-' . $cert->id,
                    'type' => 'image',
                    'path' => $imagePath,
                    'title' => $cert->getTranslated('title'),
                ];
            } elseif (isset($publicCertificates[$index])) {
                // Fallback to index-based matching - ensure path is correct
                $fallbackPath = $publicCertificates[$index];
                // Double-check path exists in storage
                if (strpos($fallbackPath, '/certificates/') === 0) {
                    $filename = basename($fallbackPath);
                    if (file_exists(storage_path('app/public/certficates/' . $filename))) {
                        $fallbackPath = '/storage/certficates/' . $filename;
                    }
                }
                $mediaArray[] = [
                    'id' => 'public-' . $index,
                    'type' => 'image',
                    'path' => $fallbackPath,
                    'title' => $cert->getTranslated('title'),
                ];
            }
        }
        
        // Handle issued_at date - could be string or Carbon instance
        $issuedAt = null;
        if ($cert->issued_at) {
            if (is_string($cert->issued_at)) {
                $issuedAt = $cert->issued_at;
            } elseif (method_exists($cert->issued_at, 'toDateString')) {
                $issuedAt = $cert->issued_at->toDateString();
            } else {
                $issuedAt = (string) $cert->issued_at;
            }
        }
        
        return [
            'id' => $cert->id,
            'title' => $cert->getTranslated('title'),
            'provider' => $cert->getTranslated('provider'),
            'credential_id' => $cert->credential_id,
            'verify_url' => $cert->verify_url,
            'issued_at' => $issuedAt,
            'media' => $mediaArray,
        ];
    })->toArray();
    
    // NO VIRTUAL ENTRIES - Only use real certificates from database
    
    // Fetch courses with media for the courses section
    $courses = Course::with('media')->latest('completed_at')->latest('issued_at')->get();
    
    // Scan for course images in public/courses/ or storage/app/public/courses/ directories
    $coursesDirs = [
        public_path('courses'),
        storage_path('app/public/courses'),
        storage_path('app/public/coursess'), // Handle potential typo
    ];
    $publicCourses = [];
    
    foreach ($coursesDirs as $coursesDir) {
        if (is_dir($coursesDir)) {
            $files = glob($coursesDir . '/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);
            if (!empty($files)) {
                foreach ($files as $file) {
                    if (!file_exists($file)) {
                        continue;
                    }
                    $filename = basename($file);
                    if (strpos($coursesDir, 'storage/app/public') !== false) {
                        if (strpos($coursesDir, 'coursess') !== false) {
                            $publicCourses[] = '/storage/coursess/' . $filename;
                        } else {
                            $publicCourses[] = '/storage/courses/' . $filename;
                        }
                    } else {
                        if (file_exists($coursesDir . '/' . $filename)) {
                            $publicCourses[] = '/courses/' . $filename;
                        }
                    }
                }
            }
        }
    }
    
    $publicCourses = array_unique($publicCourses);
    sort($publicCourses);
    
    $publicCourses = array_map(function($path) {
        if (strpos($path, '/courses/') === 0) {
            $filename = basename($path);
            if (file_exists(storage_path('app/public/courses/' . $filename))) {
                return '/storage/courses/' . $filename;
            }
        }
        return $path;
    }, $publicCourses);
    
    // Prepare courses data for React component (similar to certificates)
    $coursesData = $courses->map(function($course, $index) use ($publicCourses) {
        $mediaArray = $course->media->map(function($m) {
            return [
                'id' => $m->id,
                'type' => $m->type,
                'path' => $m->path,
                'title' => $m->title,
            ];
        })->toArray();
        
        if (empty($mediaArray)) {
            // Try multiple matching strategies
            $titleMatch = [];
            $courseNumber = null;
            if (preg_match('/Course\s+(\d+)/i', $course->getTranslated('title'), $titleMatch)) {
                $courseNumber = $titleMatch[1];
            }
            
            $searchPaths = [
                // Match by course ID
                public_path('courses/course-' . $course->id . '.*'),
                storage_path('app/public/courses/course-' . $course->id . '.*'),
                storage_path('app/public/coursess/course-' . $course->id . '.*'),
            ];
            
            // Add course number matching if found
            if ($courseNumber) {
                $searchPaths[] = public_path('courses/course-' . $courseNumber . '.*');
                $searchPaths[] = storage_path('app/public/courses/course-' . $courseNumber . '.*');
                $searchPaths[] = storage_path('app/public/coursess/course-' . $courseNumber . '.*');
            }
            
            $matchingFile = null;
            $imagePath = null;
            
            foreach (array_filter($searchPaths) as $searchPath) {
                $files = glob($searchPath);
                if (!empty($files)) {
                    $matchingFile = $files[0];
                    $filename = basename($matchingFile);
                    $dirPath = dirname($matchingFile);
                    if (strpos($dirPath, 'storage/app/public/courses') !== false || strpos($dirPath, 'storage/app/public/coursess') !== false) {
                        if (strpos($dirPath, 'coursess') !== false) {
                            $imagePath = '/storage/coursess/' . $filename;
                        } else {
                            $imagePath = '/storage/courses/' . $filename;
                        }
                    } elseif (strpos($dirPath, 'public/courses') !== false) {
                        $imagePath = '/courses/' . $filename;
                    } else {
                        $imagePath = '/storage/courses/' . $filename;
                    }
                    break;
                }
            }
            
            if ($imagePath) {
                $mediaArray[] = [
                    'id' => 'public-' . $course->id,
                    'type' => 'image',
                    'path' => $imagePath,
                    'title' => $course->getTranslated('title'),
                ];
            } elseif (isset($publicCourses[$index])) {
                $fallbackPath = $publicCourses[$index];
                if (strpos($fallbackPath, '/courses/') === 0) {
                    $filename = basename($fallbackPath);
                    if (file_exists(storage_path('app/public/courses/' . $filename))) {
                        $fallbackPath = '/storage/courses/' . $filename;
                    }
                }
                $mediaArray[] = [
                    'id' => 'public-' . $index,
                    'type' => 'image',
                    'path' => $fallbackPath,
                    'title' => $course->getTranslated('title'),
                ];
            }
        }
        
        $issuedAt = null;
        if ($course->issued_at) {
            if (is_string($course->issued_at)) {
                $issuedAt = $course->issued_at;
            } elseif (method_exists($course->issued_at, 'toDateString')) {
                $issuedAt = $course->issued_at->toDateString();
            } else {
                $issuedAt = (string) $course->issued_at;
            }
        }
        
        return [
            'id' => $course->id,
            'title' => $course->getTranslated('title'),
            'provider' => $course->getTranslated('provider'),
            'credential_id' => $course->credential_id,
            'verify_url' => $course->verify_url,
            'issued_at' => $issuedAt,
            'media' => $mediaArray,
        ];
    })->toArray();
    
    // NO VIRTUAL ENTRIES - Only use real courses from database
    
    // Fetch labs/rooms with media for the rooms section
    $labs = Lab::with('media')->latest('completed_at')->get();
    
    // Scan for room images in public/rooms/ or storage/app/public/rooms/ directories
    $roomsDirs = [
        public_path('rooms'),
        storage_path('app/public/rooms'),
    ];
    $publicRooms = [];
    
    foreach ($roomsDirs as $roomsDir) {
        if (is_dir($roomsDir)) {
            $files = glob($roomsDir . '/*.{jpg,jpeg,png,gif,webp,svg}', GLOB_BRACE);
            if (!empty($files)) {
                foreach ($files as $file) {
                    if (!file_exists($file)) {
                        continue;
                    }
                    $filename = basename($file);
                    if (strpos($roomsDir, 'storage/app/public') !== false) {
                        $publicRooms[] = '/storage/rooms/' . $filename;
                    } else {
                        if (file_exists($roomsDir . '/' . $filename)) {
                            $publicRooms[] = '/rooms/' . $filename;
                        }
                    }
                }
            }
        }
    }
    
    $publicRooms = array_unique($publicRooms);
    sort($publicRooms);
    
    // Prepare rooms data for React component
    $roomsData = $labs->map(function($lab, $index) use ($publicRooms) {
        $mediaArray = $lab->media->map(function($m) {
            return [
                'id' => $m->id,
                'type' => $m->type,
                'path' => $m->path,
                'title' => $m->title,
            ];
        })->toArray();
        
        if (empty($mediaArray)) {
            $searchPaths = [
                public_path('rooms/room-' . $lab->id . '.*'),
                public_path('rooms/' . $lab->slug . '.*'),
                storage_path('app/public/rooms/room-' . $lab->id . '.*'),
                storage_path('app/public/rooms/' . $lab->slug . '.*'),
            ];
            
            $matchingFile = null;
            $imagePath = null;
            
            foreach ($searchPaths as $searchPath) {
                $files = glob($searchPath);
                if (!empty($files)) {
                    $matchingFile = $files[0];
                    $filename = basename($matchingFile);
                    $dirPath = dirname($matchingFile);
                    if (strpos($dirPath, 'storage/app/public/rooms') !== false) {
                        $imagePath = '/storage/rooms/' . $filename;
                    } elseif (strpos($dirPath, 'public/rooms') !== false) {
                        $imagePath = '/rooms/' . $filename;
                    } else {
                        $imagePath = '/storage/rooms/' . $filename;
                    }
                    break;
                }
            }
            
            if ($imagePath) {
                $mediaArray[] = [
                    'id' => 'public-' . $lab->id,
                    'type' => 'image',
                    'path' => $imagePath,
                    'title' => $lab->getTranslated('title'),
                ];
            } elseif (isset($publicRooms[$index])) {
                $fallbackPath = $publicRooms[$index];
                // Always use /storage/rooms/ path if file exists in storage
                $filename = basename($fallbackPath);
                if (file_exists(storage_path('app/public/rooms/' . $filename))) {
                    $fallbackPath = '/storage/rooms/' . $filename;
                } elseif (strpos($fallbackPath, '/rooms/') === 0 && file_exists(public_path('rooms/' . $filename))) {
                    // Keep public path only if storage doesn't exist
                    $fallbackPath = '/rooms/' . $filename;
                }
                $mediaArray[] = [
                    'id' => 'public-' . $index,
                    'type' => 'image',
                    'path' => $fallbackPath,
                    'title' => $lab->getTranslated('title'),
                ];
            }
        }
        
        $completedAt = null;
        if ($lab->completed_at) {
            if (is_string($lab->completed_at)) {
                $completedAt = $lab->completed_at;
            } elseif (method_exists($lab->completed_at, 'toDateString')) {
                $completedAt = $lab->completed_at->toDateString();
            } else {
                $completedAt = (string) $lab->completed_at;
            }
        }
        
        return [
            'id' => $lab->id,
            'title' => $lab->getTranslated('title'),
            'slug' => $lab->slug,
            'platform' => $lab->platform,
            'room_url' => $lab->room_url,
            'completed_at' => $completedAt,
            'summary' => $lab->getTranslated('summary'),
            'media' => $mediaArray,
        ];
    })->toArray();
    
    // NO VIRTUAL ENTRIES - Only use real rooms/labs from database
    
    // Badges, Games, Simulations, and Programs - NO virtual entries, use empty arrays
    $badgesData = [];
    $gamesData = [];
    $simulationsData = [];
    $programsData = [];
    
    // Use hero section images if available, otherwise fallback to profile images
    $finalProfileImages = !empty($heroProfileImages) ? $heroProfileImages : $profileImages;
    
    // Ensure engagementSection is not null for view
    if (!$engagementSection) {
        $engagementSection = new \App\Models\EngagementSection();
    }
    
    // Get home page sections configuration
    $homePageSections = \App\Models\HomePageSection::with('navItem')
        ->where('enabled', true)
        ->orderBy('position')
        ->get();
    
    \Log::info('Home page: Loading home page sections', [
        'total_sections' => $homePageSections->count(),
        'section_ids' => $homePageSections->pluck('id')->toArray(),
        'section_details' => $homePageSections->map(function($s) {
            return [
                'id' => $s->id,
                'enabled' => $s->enabled,
                'nav_item_id' => $s->nav_item_id,
                'nav_item_label' => $s->navItem->label ?? 'N/A',
                'position' => $s->position
            ];
        })->toArray()
    ]);
    
    $homePageSections = $homePageSections
        ->map(function($section) {
            // Get NavLinks for this section based on selected_nav_link_ids
            // null means "show all", empty array means "none selected"
            $selectedNavLinkIds = $section->selected_nav_link_ids;
            $navLinks = [];
            
            // Build the query
            $navLinksQuery = NavLink::where('nav_item_id', $section->nav_item_id);
            
            // If selectedNavLinkIds is null, show all (no filter needed)
            // If it's an array (even if empty), use whereIn to filter
            if ($selectedNavLinkIds !== null && is_array($selectedNavLinkIds) && !empty($selectedNavLinkIds)) {
                // Only fetch the selected NavLinks
                $navLinksQuery->whereIn('id', $selectedNavLinkIds);
            } elseif ($selectedNavLinkIds === null) {
                // null means show all - no additional filter needed
                // The query already filters by nav_item_id
            } else {
                // Empty array means no subsections selected - return empty array
                // But wait - if selectedNavLinkIds is an empty array, we should still check if navItem exists
                // For now, let's continue with the query to see if there are any NavLinks
                // Actually, if it's an empty array (not null), it means user explicitly selected none
                // So return empty navLinks
                $navLinks = [];
                $currentLocale = app()->getLocale();
                return [
                    'id' => $section->id,
                    'nav_item_id' => $section->nav_item_id,
                    'nav_item_label' => $section->navItem->label ?? '',
                    'title' => $section->getTranslated('title', $currentLocale),
                    'subtitle' => $section->getTranslated('subtitle', $currentLocale),
                    'animation_style' => $section->animation_style,
                    'text_alignment' => $section->text_alignment,
                    'subsection_configurations' => $section->subsection_configurations ?? [],
                    'nav_links' => [],
                ];
            }
            
            // Fetch the NavLinks with all needed fields
            // IMPORTANT: Explicitly select all category fields to ensure animation_style and image_path are loaded
            try {
                // Debug: Log what we're querying
                \Log::info('Fetching NavLinks for section', [
                    'section_id' => $section->id,
                    'nav_item_id' => $section->nav_item_id,
                    'selected_nav_link_ids' => $selectedNavLinkIds,
                    'is_null' => $selectedNavLinkIds === null,
                    'is_array' => is_array($selectedNavLinkIds),
                    'array_count' => is_array($selectedNavLinkIds) ? count($selectedNavLinkIds) : 0,
                    'current_locale' => app()->getLocale(),
                ]);
                
                $navLinks = $navLinksQuery
                    ->with(['categories' => function($query) {
                    // Explicitly select all category fields to ensure they're loaded
                    $query->select('categories.*');
                }, 'categories.items' => function($query) {
                    // Load CategoryItems (the actual content items with slugs like slug-1, slug-2)
                    // Include linkedModel relationship for modal opening
                    $query->with('linkedModel')->orderBy('position');
                }])
                ->orderBy('position')
                ->get();
                
                \Log::info('NavLinks fetched', [
                    'section_id' => $section->id,
                    'nav_links_count' => $navLinks->count(),
                    'nav_link_ids' => $navLinks->pluck('id')->toArray(),
                    'nav_links_with_categories' => $navLinks->map(function($link) {
                        return [
                            'id' => $link->id,
                            'title' => $link->title,
                            'categories_count' => $link->categories->count(),
                            'category_ids' => $link->categories->pluck('id')->toArray()
                        ];
                    })->toArray()
                ]);
                
                $navLinks = $navLinks
                    ->map(function($link) {
                        // Handle issued_at date format
                        $issuedAt = null;
                        if ($link->issued_at) {
                            if (is_string($link->issued_at)) {
                                $issuedAt = $link->issued_at;
                            } elseif (method_exists($link->issued_at, 'toDateString')) {
                                $issuedAt = $link->issued_at->toDateString();
                            } else {
                                $issuedAt = (string) $link->issued_at;
                            }
                        }
                        
                        // Prepare media array from image_path
                        $mediaArray = [];
                        if ($link->image_path) {
                            $mediaArray[] = [
                                'id' => 'navlink-media-' . $link->id,
                                'type' => 'image',
                                'path' => '/storage/' . $link->image_path,
                                'title' => $link->title,
                            ];
                        }
                        
                        // Map categories to array format with CategoryItems
                        // Ensure we're using the current locale for translation
                        $currentLocale = app()->getLocale();
                        $categoriesArray = $link->categories->map(function($cat) use ($currentLocale) {
                            // Ensure name is always a string, not an object
                            $categoryName = $cat->getTranslated('name', $currentLocale);
                            if (is_array($categoryName)) {
                                $categoryName = $categoryName[$currentLocale] ?? $categoryName['en'] ?? $categoryName['ja'] ?? '';
                            }
                            if (!is_string($categoryName)) {
                                $categoryName = (string)($categoryName ?? '');
                            }
                            
                            return [
                                'id' => $cat->id,
                                'name' => $categoryName,
                                'slug' => $cat->slug,
                                'animation_style' => $cat->animation_style, // Include animation style for frontend
                                'image_path' => $cat->image_path,
                                'image_url' => $cat->image_path ? asset('storage/' . $cat->image_path) : null,
                                // Include CategoryItems (the actual content items with slugs like slug-1, slug-2)
                                'items' => $cat->items ? $cat->items->map(function($item) use ($currentLocale) {
                                    // Prepare linked_model data for modal opening
                                    $linkedModel = null;
                                    if ($item->linked_model_type && $item->linked_model_id && $item->linkedModel) {
                                        $linkedModel = [
                                            'type' => $item->model_type_name, // Returns 'book-page', 'code-summary', 'room', etc.
                                            'id' => $item->linked_model_id,
                                            'slug' => $item->linkedModel->slug ?? null
                                        ];
                                    }
                                    
                                    return [
                                        'id' => $item->id,
                                        'title' => $item->getTranslated('title', $currentLocale),
                                        'slug' => $item->slug,
                                        'image_path' => $item->image_path,
                                        'image_url' => $item->image_path ? asset('storage/' . $item->image_path) : null,
                                        'url' => $item->url,
                                        'summary' => $item->getTranslated('summary', $currentLocale),
                                        'download_url' => $item->download_url,
                                        'view_url' => $item->view_url,
                                        'visit_url' => $item->visit_url,
                                        'position' => $item->position,
                                        'linked_model' => $linkedModel // Include linked_model for modal opening
                                    ];
                                })->toArray() : []
                            ];
                        })->toArray();
                        
                        $currentLocaleForLink = app()->getLocale();
                        
                        // Ensure title is a string
                        $linkTitle = '';
                        if (is_string($link->title)) {
                            $linkTitle = $link->title;
                        } elseif (is_array($link->title)) {
                            $linkTitle = $link->title[$currentLocaleForLink] ?? $link->title['en'] ?? $link->title['ja'] ?? '';
                        } else {
                            $linkTitle = (string)($link->title ?? 'Untitled');
                        }
                        
                        return [
                            'id' => $link->id,
                            'title' => $linkTitle, // Always a string
                            'position' => $link->position,
                            'category_id' => $link->category_id, // Keep for backward compatibility
                            'categories' => $categoriesArray, // NEW: Multiple categories
                            'category' => $link->categories->first() ? [ // For backward compatibility
                                'id' => $link->categories->first()->id,
                                'name' => $link->categories->first()->getTranslated('name', $currentLocaleForLink),
                                'slug' => $link->categories->first()->slug,
                                'animation_style' => $link->categories->first()->animation_style,
                                'image_path' => $link->categories->first()->image_path,
                                'image_url' => $link->categories->first()->image_path ? asset('storage/' . $link->categories->first()->image_path) : null,
                            ] : null,
                            'issued_at' => $issuedAt,
                            'url' => $link->url,
                            'proof_url' => $link->proof_url,
                            'notes' => $link->notes,
                            'progress' => $link->progress,
                            'media' => $mediaArray,
                        ];
                    })
                    ->toArray();
            } catch (\Exception $e) {
                // Log error and return empty array
                \Log::error('Error fetching NavLinks for section ' . $section->id, [
                    'error' => $e->getMessage(),
                    'nav_item_id' => $section->nav_item_id,
                    'selected_nav_link_ids' => $selectedNavLinkIds
                ]);
                $navLinks = [];
            }
            
            $currentLocale = app()->getLocale();
            
            // Ensure nav_item_label is a string for frontend
            $navItemLabel = '';
            if ($section->navItem) {
                $labelTranslated = $section->navItem->getTranslated('label', $currentLocale);
                if (is_string($labelTranslated)) {
                    $navItemLabel = $labelTranslated;
                } elseif (is_array($labelTranslated)) {
                    $navItemLabel = $labelTranslated[$currentLocale] ?? $labelTranslated['en'] ?? $labelTranslated['ja'] ?? '';
                } elseif (is_array($section->navItem->label)) {
                    $navItemLabel = $section->navItem->label[$currentLocale] ?? $section->navItem->label['en'] ?? $section->navItem->label['ja'] ?? '';
                } else {
                    $navItemLabel = (string)($section->navItem->label ?? '');
                }
            }
            
            // Ensure title is a string for frontend
            // First try current locale, then fallback to other locale, then navItem label
            $sectionTitle = '';
            $titleRaw = $section->title;
            
            if (is_array($titleRaw)) {
                // Try current locale first
                $sectionTitle = $titleRaw[$currentLocale] ?? '';
                // If empty, try the other locale
                if (empty($sectionTitle)) {
                    $otherLocale = $currentLocale === 'en' ? 'ja' : 'en';
                    $sectionTitle = $titleRaw[$otherLocale] ?? '';
                }
            } else {
                // If not an array, try getTranslated
                $titleTranslated = $section->getTranslated('title', $currentLocale);
                if (is_string($titleTranslated) && !empty($titleTranslated)) {
                    $sectionTitle = $titleTranslated;
                } elseif (is_array($titleTranslated)) {
                    $sectionTitle = $titleTranslated[$currentLocale] ?? '';
                    if (empty($sectionTitle)) {
                        $otherLocale = $currentLocale === 'en' ? 'ja' : 'en';
                        $sectionTitle = $titleTranslated[$otherLocale] ?? '';
                    }
                }
            }
            
            // Final fallback to navItem label if title is still empty
            if (empty($sectionTitle) && $section->navItem) {
                $sectionTitle = $navItemLabel;
            }
            
            // Ensure subtitle is a string for frontend
            // First try current locale, then fallback to other locale
            $sectionSubtitle = '';
            $subtitleRaw = $section->subtitle;
            
            if (is_array($subtitleRaw)) {
                // Try current locale first
                $sectionSubtitle = $subtitleRaw[$currentLocale] ?? '';
                // If empty, try the other locale
                if (empty($sectionSubtitle)) {
                    $otherLocale = $currentLocale === 'en' ? 'ja' : 'en';
                    $sectionSubtitle = $subtitleRaw[$otherLocale] ?? '';
                }
            } else {
                // If not an array, try getTranslated
                $subtitleTranslated = $section->getTranslated('subtitle', $currentLocale);
                if (is_string($subtitleTranslated) && !empty($subtitleTranslated)) {
                    $sectionSubtitle = $subtitleTranslated;
                } elseif (is_array($subtitleTranslated)) {
                    $sectionSubtitle = $subtitleTranslated[$currentLocale] ?? '';
                    if (empty($sectionSubtitle)) {
                        $otherLocale = $currentLocale === 'en' ? 'ja' : 'en';
                        $sectionSubtitle = $subtitleTranslated[$otherLocale] ?? '';
                    }
                }
            }
            
            $sectionData = [
                'id' => $section->id,
                'nav_item_id' => $section->nav_item_id,
                'nav_item_label' => $navItemLabel, // Always a string
                'position' => $section->position,
                'text_alignment' => $section->text_alignment,
                'animation_style' => $section->animation_style ?? null,
                'title' => $sectionTitle, // Always a string
                'subtitle' => $sectionSubtitle, // Always a string
                'selected_nav_link_ids' => $selectedNavLinkIds,
                'nav_links' => $navLinks ?? [], // Ensure navLinks is always an array
                'subsection_configurations' => $section->subsection_configurations ?? [],
            ];
            
            \Log::info('Home page: Section data prepared', [
                'section_id' => $sectionData['id'],
                'nav_item_label' => $sectionData['nav_item_label'],
                'title' => $sectionData['title'],
                'nav_links_count' => count($sectionData['nav_links']),
                'nav_links_summary' => array_map(function($link) {
                    return [
                        'id' => $link['id'] ?? 'N/A',
                        'title' => $link['title'] ?? 'N/A',
                        'categories_count' => count($link['categories'] ?? [])
                    ];
                }, $sectionData['nav_links'])
            ]);
            
            return $sectionData;
        })
        ->values()
        ->toArray();
    
    // Get ongoing progress items from NavItems (TryHackMe, Udemy, etc.)
    // Each NavItem becomes a progress row, calculated from its NavLinks
    function deriveUnitFromLabel($label, $linkCount = 0) {
        // Ensure label is a string (handle arrays/objects)
        if (is_array($label)) {
            $label = $label['en'] ?? $label['ja'] ?? '';
        }
        if (!is_string($label)) {
            $label = (string)($label ?? '');
        }
        $labelLower = strtolower($label);
        if (strpos($labelLower, 'tryhackme') !== false || strpos($labelLower, 'thm') !== false) {
            return 'rooms';
        } elseif (strpos($labelLower, 'udemy') !== false) {
            // For Udemy, show "courses" if there are links, otherwise "hours"
            return $linkCount > 0 ? 'courses' : 'hours';
        } elseif (strpos($labelLower, 'book') !== false) {
            return 'pages';
        } elseif (strpos($labelLower, 'python') !== false) {
            return 'LoC';
        } elseif (strpos($labelLower, 'java') !== false) {
            return 'labs';
        }
        // Default to a generic unit based on context
        return $linkCount > 0 ? 'items' : 'items';
    }
    
    $progressItems = \App\Models\NavItem::with('links')
        ->where('visible', true)
        ->orderBy('position')
        ->get()
        ->map(function($navItem) {
            $links = $navItem->links;
            $totalLinks = $links->count();
            
            // Skip NavItems with no links
            if ($totalLinks === 0) {
                return null;
            }
            
            $completedLinks = $links->where('progress', 100)->count();
            $inProgressLinks = $links->where('progress', '>', 0)->where('progress', '<', 100)->count();
            
            // Calculate average progress across all links
            $avgProgress = $totalLinks > 0 ? round($links->avg('progress') ?? 0) : 0;
            
            // Use average progress as current value, 100 as goal (percentage-based)
            $currentValue = $avgProgress;
            $goalValue = 100;
            
            // Get translated label string (use English for unit derivation since function checks English terms)
            $currentLocale = app()->getLocale();
            $labelString = $navItem->getTranslated('label', 'en') ?: '';
            if (!is_string($labelString)) {
                // Fallback: if getTranslated returns array, extract English value
                $labelArray = is_array($navItem->label) ? $navItem->label : [];
                $labelString = $labelArray['en'] ?? $labelArray['ja'] ?? '';
            }
            
            // Determine unit based on NavItem label and number of links
            $unit = deriveUnitFromLabel($labelString, $totalLinks);
            
            // Build URL
            $linkUrl = null;
            if ($navItem->url) {
                $linkUrl = $navItem->url;
            } elseif ($navItem->route) {
                try {
                    $linkUrl = route($navItem->route);
                } catch (\Exception $e) {
                    // Route doesn't exist, use nav links index
                    $linkUrl = route('admin.nav.links.index', $navItem);
                }
            } else {
                $linkUrl = route('admin.nav.links.index', $navItem);
            }
            
            // Translate NavItem label based on locale for display
            $labelTranslations = [
                'en' => [
                    'books' => 'books',
                    'pages' => 'pages',
                    'Udemy' => 'Udemy',
                    'courses' => 'courses',
                    'tryhackme' => 'tryhackme',
                    'rooms' => 'rooms',
                ],
                'ja' => [
                    'books' => '書籍',
                    'pages' => 'ページ',
                    'Udemy' => 'Udemy',
                    'courses' => 'コース',
                    'tryhackme' => 'TryHackMe',
                    'rooms' => 'ルーム',
                ],
            ];
            
            // Get translated label for display
            $translatedLabel = $navItem->getTranslated('label', $currentLocale) ?: '';
            if (!is_string($translatedLabel)) {
                // Fallback: if getTranslated returns array, extract current locale value
                $labelArray = is_array($navItem->label) ? $navItem->label : [];
                $translatedLabel = $labelArray[$currentLocale] ?? $labelArray['en'] ?? $labelArray['ja'] ?? '';
            }
            
            // Apply custom translations if available
            $labelLower = strtolower($labelString);
            if (isset($labelTranslations[$currentLocale][$labelLower])) {
                $translatedLabel = $labelTranslations[$currentLocale][$labelLower];
            }
            
            return [
                'id' => 'nav_' . $navItem->id,
                'label' => $translatedLabel,
                'unit' => $unit,
                'value' => $currentValue,
                'goal' => $goalValue,
                'link' => $linkUrl,
                'eta' => null, // Could calculate based on progress rate if needed
                'trend' => null,
                'nav_item_id' => $navItem->id,
                'total_items' => $totalLinks,
                'completed_items' => $completedLinks,
                'in_progress_items' => $inProgressLinks,
            ];
        })
        ->filter(function($item) {
            return $item !== null;
        })
        ->values()
        ->toArray();
    
    // Fetch published blogs for blog section
    $blogs = \App\Models\Blog::where('is_published', true)
        ->with('media')
        ->orderBy('published_at', 'desc')
        ->orderBy('created_at', 'desc')
        ->limit(20) // Limit to 20 for carousel
        ->get();
    
    return view('home', compact('services','profile', 'profileImages', 'finalProfileImages', 'heroSection', 'engagementSection', 'engagementVideo', 'certificates', 'certificatesData', 'courses', 'coursesData', 'labs', 'roomsData', 'badgesData', 'gamesData', 'simulationsData', 'programsData', 'progressItems', 'homePageSections', 'blogs'));
})->name('home');
*/
Route::view('/about', 'pages.about')->name('about');
Route::view('/skills', 'pages.skills')->name('skills');
Route::get('/projects', function () {
    $query = Project::query();
    $activeTag = request('tag');
    if ($activeTag) {
        $query->whereHas('tags', function ($q) use ($activeTag) {
            $q->where('slug', $activeTag);
        });
    }
    $projects = $query->latest('completed_at')->latest()->paginate(12);
    return view('pages.projects', compact('projects', 'activeTag'));
})->name('projects');

// Route::get('/certificates', function () {
//     $query = Certificate::query();
//     $activeTag = request('tag');
//     if ($activeTag) {
//         $query->whereHas('tags', function ($q) use ($activeTag) {
//             $q->where('slug', $activeTag);
//         });
//     }
//     $certificates = $query->latest('issued_at')->latest()->paginate(12);
//     return view('pages.certificates', compact('certificates', 'activeTag'));
// })->name('certificates');

Route::get('/projects/{slug}', function (string $slug) {
    $project = Project::where('slug', $slug)->with(['media', 'tags'])->firstOrFail();
    return view('pages.project_show', compact('project'));
})->name('project.show');

Route::get('/blog/{slug}', function (string $slug) {
    $blog = \App\Models\Blog::where('slug', $slug)->where('is_published', true)->with(['media', 'tags'])->firstOrFail();
    return view('pages.blog_show', compact('blog'));
})->name('blog.show');
Route::view('/labs', 'pages.labs')->name('labs');
// Books route removed - content opens in modals directly
Route::get('/books', function () {
    // Redirect to home page - books content will open in modals
    return redirect()->route('home');
})->name('books');

// Public routes for content items - these open modals directly via JavaScript
// Removed public routes - content should open in modals, not navigate to pages
// If someone directly accesses these URLs, redirect to home
Route::get('/book-pages/{bookPage:slug}', function (\App\Models\BookPage $bookPage) {
    // Redirect to home - modal will be opened via JavaScript if needed
    return redirect()->route('home');
})->name('book-pages.show');

Route::get('/code-summaries/{codeSummary:slug}', function (\App\Models\CodeSummary $codeSummary) {
    return redirect()->route('home');
})->name('code-summaries.show');

Route::get('/rooms/{room:slug}', function (\App\Models\Room $room) {
    return redirect()->route('home');
})->name('rooms.show');

Route::view('/timeline', 'pages.timeline')->name('timeline');
Route::view('/contact', 'pages.contact')->name('contact');

// Legal pages
Route::get('/legal/terms', function () {
    return view('legal.terms');
})->name('legal.terms');

Route::get('/legal/privacy', function () {
    return view('legal.privacy');
})->name('legal.privacy');

// Removed /dashboard route - using /admin/dashboard instead
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Debug route to check authentication status (remove in production)
Route::get('/debug-auth', function () {
    return response()->json([
        'authenticated' => Auth::check(),
        'user_id' => Auth::id(),
        'user' => Auth::user() ? [
            'id' => Auth::user()->id,
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ] : null,
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
        'cookies' => request()->cookies->all(),
        'headers' => [
            'cookie' => request()->header('Cookie'),
            'user-agent' => request()->header('User-Agent'),
        ],
    ]);
})->name('debug.auth');

// Debug route WITH auth middleware to test if middleware works
Route::middleware('auth')->get('/debug-auth-protected', function () {
    return response()->json([
        'authenticated' => Auth::check(),
        'user_id' => Auth::id(),
        'user' => Auth::user() ? [
            'id' => Auth::user()->id,
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ] : null,
        'message' => 'This route requires authentication and you accessed it successfully!',
    ]);
})->name('debug.auth.protected');

// Admin routes (simple fallback until Filament)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tryhackme', function () {
        $nav = NavItem::whereRaw('LOWER(label) = ?', ['tryhackme'])->first();
        if ($nav) { return redirect()->route('admin.nav.links.index', $nav); }
        $labs = \App\Models\Lab::orderByDesc('completed_at')->paginate(10);
        return view('admin.tryhackme', compact('labs'));
    })->name('thm');
    Route::get('/udemy', function () {
        $nav = NavItem::whereRaw('LOWER(label) = ?', ['udemy'])->first();
        if ($nav) { return redirect()->route('admin.nav.links.index', $nav); }
        $certificates = \App\Models\Certificate::orderByDesc('issued_at')->paginate(10);
        return view('admin.udemy', compact('certificates'));
    })->name('udemy');
    Route::get('/reports', function () {
        $nav = NavItem::whereRaw('LOWER(label) = ?', ['reports'])->first();
        if ($nav) { return redirect()->route('admin.nav.links.index', $nav); }
        $entries = \App\Models\TimelineEntry::orderByDesc('occurred_at')->paginate(10);
        return view('admin.reports', compact('entries'));
    })->name('reports');
    Route::get('/tasks', function () {
        $nav = NavItem::whereRaw('LOWER(label) = ?', ['tasks'])->first();
        if ($nav) { return redirect()->route('admin.nav.links.index', $nav); }
        $projects = \App\Models\Project::orderByDesc('created_at')->paginate(10);
        return view('admin.tasks', compact('projects'));
    })->name('tasks');
    Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class);
    Route::resource('certificates', \App\Http\Controllers\Admin\CertificateController::class);
    Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class);
    Route::resource('blogs', \App\Http\Controllers\Admin\BlogController::class);
    Route::resource('testimonials', \App\Http\Controllers\Admin\TestimonialController::class);
    Route::get('linkedin', [\App\Http\Controllers\Admin\LinkedInController::class, 'index'])->name('linkedin.index');
    Route::post('linkedin/import', [\App\Http\Controllers\Admin\LinkedInController::class, 'import'])->name('linkedin.import');
    Route::get('linkedin/{blog}/format', [\App\Http\Controllers\Admin\LinkedInController::class, 'getLinkedInFormat'])->name('linkedin.format');
    Route::post('book-pages/ai-capture', [\App\Http\Controllers\Admin\BookPageController::class, 'aiCapture'])->name('book-pages.ai-capture');
    Route::resource('book-pages', \App\Http\Controllers\Admin\BookPageController::class);
    
    // Section management - Sections are CategoryItems, redirect to items management
    Route::get('categories/{category}/sections', function (\App\Models\Category $category) {
        $nav = $category->navLinksMany->first()->navItem ?? null;
        $link = $category->navLinksMany->first() ?? null;
        if ($nav && $link) {
            return redirect()->route('admin.nav.links.categories.items.index', [$nav, $link, $category]);
        }
        return redirect()->back();
    })->name('sections.index');
    
    // AJAX endpoint to create section (CategoryItem) quickly from book page form
    Route::post('sections/quick-create', function (Request $request) {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
        ]);
        
        $category = \App\Models\Category::findOrFail($data['category_id']);
        
        // Convert name to title and description to summary for CategoryItem
        $itemData = [
            'category_id' => $data['category_id'],
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'title' => $data['name'],
            'summary' => $data['description'] ?? null,
            'position' => $data['position'] ?? 0,
        ];
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $baseSlug = \Illuminate\Support\Str::slug($data['name']);
            $slug = $baseSlug;
            $counter = 1;
            
            while (\App\Models\CategoryItem::where('category_id', $category->id)
                ->where('slug', $slug)
                ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $itemData['slug'] = $slug;
        } else {
            $itemData['slug'] = $data['slug'];
        }
        
        $section = \App\Models\CategoryItem::create($itemData);
        $section->load('category');
        
        return response()->json([
            'success' => true,
            'section' => [
                'id' => $section->id,
                'name' => $section->getTranslated('title'), // Return as 'name' for backward compatibility
                'title' => $section->getTranslated('title'),
                'slug' => $section->slug,
                'category' => [
                    'id' => $section->category->id,
                    'name' => $section->category->getTranslated('name'),
                ]
            ]
        ]);
    })->name('sections.quick-create');
    
    // Section content management routes (attach/detach content items to sections)
    Route::post('sections/{section}/attach', [\App\Http\Controllers\Admin\NavLinkController::class, 'sectionAttach'])->name('sections.attach');
    Route::delete('sections/{section}/detach', [\App\Http\Controllers\Admin\NavLinkController::class, 'sectionDetach'])->name('sections.detach');
    
    Route::resource('code-summaries', \App\Http\Controllers\Admin\CodeSummaryController::class);
    Route::resource('rooms', \App\Http\Controllers\Admin\RoomController::class);
    Route::resource('tags', \App\Http\Controllers\Admin\TagController::class)->except(['show']);
    Route::resource('skills', \App\Http\Controllers\Admin\SkillController::class)->except(['show']);
    Route::resource('labs', \App\Http\Controllers\Admin\LabController::class);
    Route::resource('books', \App\Http\Controllers\Admin\BookController::class);
    Route::resource('timeline', \App\Http\Controllers\Admin\TimelineEntryController::class)->parameters(['timeline' => 'timelineEntry']);
    Route::resource('nav', \App\Http\Controllers\Admin\NavItemController::class)->parameters(['nav'=>'nav']);
    Route::prefix('nav/{nav}')->name('nav.')->group(function () {
        Route::resource('links', \App\Http\Controllers\Admin\NavLinkController::class)->parameters(['links'=>'link']);
        Route::prefix('links/{link}')->name('links.')->group(function () {
            Route::get('categories', [\App\Http\Controllers\Admin\NavLinkController::class, 'categoriesIndex'])->name('categories.index');
            Route::post('categories', [\App\Http\Controllers\Admin\NavLinkController::class, 'categoriesStore'])->name('categories.store');
            Route::post('categories/attach', [\App\Http\Controllers\Admin\NavLinkController::class, 'categoriesAttach'])->name('categories.attach');
            Route::put('categories/update-animation-style', [\App\Http\Controllers\Admin\NavLinkController::class, 'categoriesUpdateAnimationStyle'])->name('categories.update-animation-style');
            Route::delete('categories/{category}/detach', [\App\Http\Controllers\Admin\NavLinkController::class, 'categoriesDetach'])->name('categories.detach');
            Route::put('categories/{category}', [\App\Http\Controllers\Admin\NavLinkController::class, 'categoriesUpdate'])->name('categories.update');
            Route::delete('categories/{category}', [\App\Http\Controllers\Admin\NavLinkController::class, 'categoriesDestroy'])->name('categories.destroy');
            // Category Items routes
            Route::get('categories/{category}/items', [\App\Http\Controllers\Admin\NavLinkController::class, 'categoriesItemsIndex'])->name('categories.items.index');
            Route::post('categories/{category}/items', [\App\Http\Controllers\Admin\NavLinkController::class, 'categoriesItemsStore'])->name('categories.items.store');
            Route::put('categories/{category}/items/{item}', [\App\Http\Controllers\Admin\NavLinkController::class, 'categoriesItemsUpdate'])->name('categories.items.update');
            Route::delete('categories/{category}/items/{item}', [\App\Http\Controllers\Admin\NavLinkController::class, 'categoriesItemsDestroy'])->name('categories.items.destroy');
            
            // Content items management for a specific category item (section)
            Route::get('categories/{category}/items/{item}/content', [\App\Http\Controllers\Admin\NavLinkController::class, 'categoryItemContentIndex'])->name('categories.items.content.index');
        });
    });
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::get('hero', [\App\Http\Controllers\Admin\HeroSectionController::class, 'edit'])->name('hero.edit');
    Route::put('hero', [\App\Http\Controllers\Admin\HeroSectionController::class, 'update'])->name('hero.update');
    Route::post('hero/reset', [\App\Http\Controllers\Admin\HeroSectionController::class, 'reset'])->name('hero.reset');
    Route::get('engagement', [\App\Http\Controllers\Admin\EngagementSectionController::class, 'edit'])->name('engagement.edit');
    Route::put('engagement', [\App\Http\Controllers\Admin\EngagementSectionController::class, 'update'])->name('engagement.update');
    Route::post('engagement/reset', [\App\Http\Controllers\Admin\EngagementSectionController::class, 'reset'])->name('engagement.reset');
    Route::resource('ongoing-progress', \App\Http\Controllers\Admin\OngoingProgressController::class)->parameters(['ongoing-progress' => 'ongoingProgressItem']);
    Route::resource('home-page-sections', \App\Http\Controllers\Admin\HomePageSectionController::class)->parameters(['home-page-sections' => 'homePageSection']);
    Route::post('home-page-sections/{homePageSection}/toggle-enabled', [\App\Http\Controllers\Admin\HomePageSectionController::class, 'toggleEnabled'])->name('home-page-sections.toggle-enabled');
    Route::get('home-page-sections/nav-links/{navItemId}', [\App\Http\Controllers\Admin\HomePageSectionController::class, 'getNavLinks'])->name('home-page-sections.nav-links');
    Route::get('home-page-sections/nav-items/list', [\App\Http\Controllers\Admin\HomePageSectionController::class, 'getNavItems'])->name('home-page-sections.nav-items');
    Route::post('build', [\App\Http\Controllers\Admin\BuildController::class, 'build'])->name('build');
});

// User portfolio pages - MUST be LAST to avoid catching other routes
Route::get('/{username}', [\App\Http\Controllers\PortfolioController::class, 'show'])->name('portfolio.show');
