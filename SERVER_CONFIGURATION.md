# Server Configuration for Large File Uploads

This guide helps you configure your server to allow uploading very high quality images (50MB+ per image, multiple images).

## Problem: 413 Request Entity Too Large

When uploading large images, you may encounter:
- **413 Request Entity Too Large** (nginx error)
- **PHP upload_max_filesize exceeded** (PHP error)

## Solution: Configure Nginx and PHP

### 1. Nginx Configuration

Edit your nginx site configuration file (usually located at `/etc/nginx/sites-available/your-site` or `/etc/nginx/nginx.conf`):

```nginx
server {
    # ... other configuration ...
    
    # Increase client max body size to allow large uploads
    # This allows up to 500MB total request size (for multiple images)
    client_max_body_size 500M;
    
    # Increase timeouts for large uploads
    client_body_timeout 300s;
    client_header_timeout 300s;
    
    # Increase buffer sizes
    client_body_buffer_size 128k;
    
    # ... rest of configuration ...
}
```

**After editing, test and reload nginx:**
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 2. PHP Configuration (php.ini)

Edit your PHP configuration file. Location depends on your setup:
- **PHP-FPM**: `/etc/php/8.x/fpm/php.ini` (replace 8.x with your PHP version)
- **PHP CLI**: `/etc/php/8.x/cli/php.ini`

Find and update these values:

```ini
; Maximum allowed size for uploaded files (per file)
upload_max_filesize = 50M

; Maximum size of POST data (total request size)
; Should be larger than upload_max_filesize to allow multiple files
post_max_size = 500M

; Maximum execution time (in seconds)
max_execution_time = 300

; Maximum input time (in seconds)
max_input_time = 300

; Memory limit
memory_limit = 256M
```

**After editing, restart PHP-FPM:**
```bash
sudo systemctl restart php8.x-fpm
# Replace 8.x with your PHP version
```

### 3. PHP-FPM Configuration (if using PHP-FPM)

Edit `/etc/php/8.x/fpm/php.ini` and also check `/etc/php/8.x/fpm/pool.d/www.conf`:

```ini
; In www.conf, ensure these are set:
request_terminate_timeout = 300
```

**Restart PHP-FPM:**
```bash
sudo systemctl restart php8.x-fpm
```

### 4. Verify Configuration

After making changes, verify the settings:

```bash
# Check PHP configuration
php -i | grep upload_max_filesize
php -i | grep post_max_size

# Check nginx configuration
sudo nginx -T | grep client_max_body_size
```

### 5. Laravel Application Settings

The Laravel application has been configured to accept:
- **50MB per image** (`max:51200` in validation)
- **Multiple images** (array validation)

No additional Laravel configuration needed.

## Quick Reference

| Setting | Recommended Value | Purpose |
|---------|-------------------|---------|
| `client_max_body_size` (nginx) | 500M | Total request size limit |
| `upload_max_filesize` (PHP) | 50M | Maximum size per uploaded file |
| `post_max_size` (PHP) | 500M | Maximum POST data size |
| `max_execution_time` (PHP) | 300 | Script execution timeout |
| `max_input_time` (PHP) | 300 | Input parsing timeout |
| `memory_limit` (PHP) | 256M | PHP memory limit |

## Troubleshooting

### Still getting 413 errors?
1. Check nginx error logs: `sudo tail -f /var/log/nginx/error.log`
2. Verify nginx config: `sudo nginx -t`
3. Reload nginx: `sudo systemctl reload nginx`
4. Check PHP-FPM logs: `sudo tail -f /var/log/php8.x-fpm.log`

### Still getting PHP upload errors?
1. Verify PHP settings: `php -i | grep -E "(upload_max_filesize|post_max_size)"`
2. Restart PHP-FPM: `sudo systemctl restart php8.x-fpm`
3. Check Laravel logs: `tail -f storage/logs/laravel.log`

### Images not uploading after configuration?
1. Clear Laravel cache: `php artisan config:clear`
2. Clear browser cache
3. Check file permissions: `storage/app/public` should be writable

## Notes

- **Security**: Allowing large uploads can be a security risk. Ensure you have proper validation and virus scanning in place.
- **Performance**: Large uploads may take time. Consider implementing progress indicators in the frontend.
- **Storage**: Ensure your server has enough disk space for large image files.
- **Backup**: Always backup your configuration files before making changes.

