<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'slug',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Generate a unique username from name
     */
    public static function generateUsername(string $name): string
    {
        $baseSlug = \Illuminate\Support\Str::slug($name);
        $username = $baseSlug;
        $counter = 1;

        while (static::where('username', $username)->exists()) {
            $username = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Generate a unique slug from name
     */
    public static function generateSlug(string $name): string
    {
        return static::generateUsername($name); // Same logic for now
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->username)) {
                $user->username = static::generateUsername($user->name);
            }
            if (empty($user->slug)) {
                $user->slug = static::generateSlug($user->name);
            }
        });
    }

    // Relationships
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function navItems()
    {
        return $this->hasMany(NavItem::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function heroSection()
    {
        return $this->hasOne(HeroSection::class);
    }

    public function engagementSection()
    {
        return $this->hasOne(EngagementSection::class);
    }

    public function homePageSections()
    {
        return $this->hasMany(HomePageSection::class);
    }
}
