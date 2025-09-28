# API Token Authentication Guide

## Overview

The Brandless CMS backend now uses token-based authentication for all public-facing endpoints. This provides better security and usage tracking for API access.

## Protected Endpoints

The following endpoints now require an API token:

### Content Endpoints
- `GET /api/content/posts` - List all posts
- `GET /api/content/posts/featured` - Get featured posts
- `GET /api/content/posts/{slug}` - Get specific post
- `GET /api/content/pages` - List all pages
- `GET /api/content/pages/{slug}` - Get specific page
- `GET /api/content/categories` - List all categories
- `GET /api/content/categories/{slug}` - Get specific category
- `GET /api/content/categories/{slug}/posts` - Get posts in category
- `GET /api/content/tags` - List all tags
- `GET /api/content/tags/{slug}` - Get specific tag
- `GET /api/content/tags/{slug}/posts` - Get posts with tag

### Menu Endpoints
- `GET /api/menus/` - List all menus
- `GET /api/menus/{location}` - Get menu by location

### Media Endpoints
- `GET /api/media/` - List all media
- `GET /api/media/{id}` - Get specific media item

### Settings Endpoints
- `GET /api/settings/` - Get public settings
- `GET /api/settings/theme` - Get theme settings
- `GET /api/settings/{group}` - Get settings by group

## Authentication Methods

### Using X-API-Token Header
```bash
curl -H "X-API-Token: your-api-token-here" \
     https://your-domain.com/api/content/posts
```

### Using Authorization Header
```bash
curl -H "Authorization: Bearer your-api-token-here" \
     https://your-domain.com/api/content/posts
```

## Token Management

### For Regular Users

#### List Your Tokens
```bash
GET /api/tokens
Authorization: Bearer your-sanctum-token
```

#### Create a New Token
```bash
POST /api/tokens
Authorization: Bearer your-sanctum-token
Content-Type: application/json

{
    "name": "My API Token",
    "abilities": ["read", "write"],
    "expires_at": "2025-12-31",
    "description": "Token for my mobile app"
}
```

#### Update a Token
```bash
PUT /api/tokens/{token-id}
Authorization: Bearer your-sanctum-token
Content-Type: application/json

{
    "name": "Updated Token Name",
    "is_active": false
}
```

#### Revoke a Token
```bash
DELETE /api/tokens/{token-id}
Authorization: Bearer your-sanctum-token
```

### For Administrators

#### List All Tokens
```bash
GET /api/admin/tokens/all
Authorization: Bearer your-admin-sanctum-token
```

#### Create Public Token
```bash
POST /api/admin/tokens/public
Authorization: Bearer your-admin-sanctum-token
Content-Type: application/json

{
    "name": "Public API Access",
    "abilities": ["read"],
    "expires_at": "2025-12-31",
    "description": "Public token for frontend applications"
}
```

## Token Abilities

- `read` - Can read content (GET requests)
- `write` - Can create/update content (POST/PUT requests)
- `delete` - Can delete content (DELETE requests)
- `admin` - Full administrative access (admin only)

## Security Best Practices

1. **Store tokens securely** - Never expose tokens in client-side code
2. **Use HTTPS** - Always use HTTPS in production
3. **Set expiration dates** - Tokens should have reasonable expiration dates
4. **Monitor usage** - Check token usage regularly
5. **Revoke unused tokens** - Remove tokens that are no longer needed
6. **Use minimal abilities** - Only grant the minimum required permissions

## Error Responses

### Missing Token
```json
{
    "error": "API token required",
    "message": "Please provide a valid API token in X-API-Token header or Authorization header"
}
```

### Invalid Token
```json
{
    "error": "Invalid API token",
    "message": "The provided API token is invalid or has been revoked"
}
```

### Expired Token
```json
{
    "error": "API token expired",
    "message": "The provided API token has expired"
}
```

## Migration from Unprotected Endpoints

If you were previously using the public endpoints without authentication:

1. **Get an API token** - Contact your administrator or create one through the user dashboard
2. **Update your requests** - Add the `X-API-Token` header to all requests
3. **Test your integration** - Verify all endpoints work with the new authentication

## Development and Testing

For development, you can run the seeder to create sample tokens:

```bash
php artisan db:seed --class=ApiTokenSeeder
```

This will create:
- A public read-only token
- Personal tokens for all existing users

**Important**: Save the generated tokens immediately as they won't be shown again!
