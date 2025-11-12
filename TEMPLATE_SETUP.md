# Template Setup Guide

## Overview

This portfolio application includes a complete demo template that showcases all features. The template data is created via a database seeder.

## Setup Steps

### 1. Add Default Profile Images

Place these three JPEG images in `portfolio/public/images/`:
- `pp1.jpg` - First profile picture
- `pp2.jpg` - Second profile picture  
- `pp3.jpg` - Third profile picture

**Image Specifications:**
- Format: JPEG (.jpg)
- Recommended Size: 400x600px (3:4 aspect ratio)
- File Size: Under 500KB each

These images will be used as rotating profile pictures in the hero section.

### 2. Run Database Migrations

```bash
php artisan migrate
```

### 3. Seed Demo Template Data

Run the seeder to create the complete demo template:

```bash
php artisan db:seed --class=TemplateDemoSeeder
```

Or run all seeders:

```bash
php artisan db:seed
```

### 4. Access the Demo

- **Landing Page**: `http://localhost:8000/`
- **Demo Portfolio**: `http://localhost:8000/demo-user`
- **Admin Login**: `demo@example.com` / `password`

## What Gets Created

The seeder creates:

1. **Demo User**
   - Email: `demo@example.com`
   - Password: `password`
   - Username: `demo-user`

2. **Complete Portfolio Template**
   - Hero section with profile images
   - Engagement section
   - Home page sections (Projects, Certificates)
   - Sample projects with categories
   - Sample certificates
   - Sample courses
   - Navigation items and links
   - Tags and categories

3. **All Data is User-Scoped**
   - Everything is linked to the demo user
   - Shows how multi-user portfolios work

## Important Notes

⚠️ **Warning**: The seeder **truncates** (deletes) all existing data before creating the demo template. 

If you want to keep existing data:
1. Comment out the truncate section in `TemplateDemoSeeder.php`
2. Or create a backup before running the seeder

## File Locations

- **Seeder**: `database/seeders/TemplateDemoSeeder.php`
- **Images Directory**: `public/images/`
- **Image README**: `public/images/README.md`

## Customization

After seeding, you can:
1. Log in as the demo user
2. Edit any content through the admin panel
3. Replace demo images with your own
4. Add more projects, certificates, and courses

## Troubleshooting

**Images not showing?**
- Make sure `pp1.jpg`, `pp2.jpg`, `pp3.jpg` are in `public/images/`
- Check file permissions
- Clear cache: `php artisan cache:clear`

**Seeder fails?**
- Make sure migrations are run: `php artisan migrate`
- Check database connection
- Review error messages in `storage/logs/laravel.log`



