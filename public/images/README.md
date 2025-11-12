# Default Profile Images Setup

## Where to Place Images

Place your default profile images in the following directory:
```
portfolio/public/images/
```

## Required Images

Add these three JPEG images to `portfolio/public/images/`:
- `pp1.jpg` - First profile picture
- `pp2.jpg` - Second profile picture  
- `pp3.jpg` - Third profile picture

These images will be used as default profile pictures in the demo template and will be tracked in git.

## Image Specifications

- **Format**: JPEG (.jpg)
- **Recommended Size**: 400x600px (3:4 aspect ratio)
- **Recommended File Size**: Under 500KB each for optimal loading

## After Adding Images

1. Run the seeder to create demo template data:
   ```bash
   php artisan db:seed --class=TemplateDemoSeeder
   ```

2. Or run all seeders:
   ```bash
   php artisan db:seed
   ```

## Demo Template Data

The seeder creates:
- Demo user: `demo@example.com` / password: `password`
- Username: `demo-user`
- Complete portfolio template with sample projects, certificates, courses, and sections

## Note

If you don't add the images, the template will use fallback images. However, adding these images ensures a consistent demo experience for all users.




