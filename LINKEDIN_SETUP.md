# LinkedIn Integration Setup Guide

This guide will help you set up the LinkedIn integration for automatically syncing posts from LinkedIn to your blog.

## Prerequisites

1. A LinkedIn account
2. Access to LinkedIn Developer Portal
3. Your Laravel application running

## Step 1: Create a LinkedIn App

1. Go to [LinkedIn Developers](https://www.linkedin.com/developers/apps)
2. Click "Create app"
3. Fill in the required information:
   - App name: Your portfolio/blog name
   - Company: Your name or company
   - Privacy policy URL: Your website's privacy policy
   - App logo: Your logo
4. Click "Create app"

## Step 2: Request API Permissions

In your LinkedIn app settings, go to the "Auth" tab and request the following permissions:

- **r_liteprofile** - Read basic profile information
- **r_basicprofile** - Read full profile information  
- **r_emailaddress** - Read email address
- **w_member_social** - Write posts as the authenticated user
- **r_organization_social** - Read organization posts (if posting as organization)

## Step 3: Get OAuth Access Token

### Option A: Using OAuth 2.0 Flow (Recommended)

1. In your LinkedIn app, go to "Auth" tab
2. Add redirect URL: `http://yourdomain.com/admin/linkedin/callback`
3. Use the OAuth 2.0 authorization code flow:
   - Redirect user to: `https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id=YOUR_CLIENT_ID&redirect_uri=YOUR_REDIRECT_URI&state=STATE&scope=r_liteprofile%20r_emailaddress%20w_member_social`
   - Exchange the authorization code for an access token
   - Store the access token in your `.env` file

### Option B: Manual Token (For Testing)

1. Use LinkedIn's OAuth 2.0 Playground or Postman
2. Follow the OAuth flow to get an access token
3. Add it to your `.env` file

## Step 4: Configure Environment Variables

Add these to your `.env` file:

```env
LINKEDIN_CLIENT_ID=your_client_id_here
LINKEDIN_CLIENT_SECRET=your_client_secret_here
LINKEDIN_REDIRECT_URI=http://yourdomain.com/admin/linkedin/callback
LINKEDIN_ACCESS_TOKEN=your_access_token_here
```

## Step 5: Run Migrations

```bash
php artisan migrate
```

This will add the necessary LinkedIn fields to your blogs table.

## Step 6: Set Up Scheduled Tasks

The system is configured to automatically fetch LinkedIn posts daily at 6:00 AM.

Make sure your cron job is set up to run Laravel's scheduler:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Step 7: Test the Integration

1. Go to `/admin/linkedin` in your admin panel
2. Click "Sync Now" to manually fetch posts from LinkedIn
3. Check if posts are being synced to your blog

## Features

### Automatic Daily Sync
- Posts are automatically fetched from LinkedIn every day at 6:00 AM
- You can also manually trigger a sync from the admin panel

### Post to LinkedIn
- When creating/editing a blog post, you can post it directly to LinkedIn
- The post will include the blog title, excerpt, and link back to your blog

### Auto-Sync Settings
- Enable auto-sync for specific posts to keep them updated
- Posts will be updated when syncing from LinkedIn

## Troubleshooting

### "LinkedIn access token not configured" error
- Make sure `LINKEDIN_ACCESS_TOKEN` is set in your `.env` file
- Check that the token hasn't expired (LinkedIn tokens typically expire after 60 days)

### "Failed to authenticate with LinkedIn" error
- Your access token may have expired
- Regenerate a new access token from LinkedIn Developer Portal
- Make sure you have the correct permissions requested

### Posts not syncing
- Check your cron job is running: `php artisan schedule:run`
- Check Laravel logs: `storage/logs/laravel.log`
- Verify your access token has the correct permissions

### API Rate Limits
- LinkedIn has rate limits on API calls
- If you hit limits, wait a few minutes and try again
- Consider reducing the number of posts fetched per sync

## LinkedIn API Documentation

For more information, refer to:
- [LinkedIn API Documentation](https://docs.microsoft.com/en-us/linkedin/)
- [LinkedIn Share API](https://docs.microsoft.com/en-us/linkedin/consumer/integrations/self-serve/share-on-linkedin)
- [LinkedIn UGC Posts API](https://docs.microsoft.com/en-us/linkedin/marketing/integrations/community-management/shares/ugc-post-api)

## Notes

- LinkedIn access tokens typically expire after 60 days
- You may need to implement token refresh logic for production use
- Some LinkedIn API endpoints require specific partnership agreements
- The integration uses LinkedIn API v2





