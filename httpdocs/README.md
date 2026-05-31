# Nexus 4D Setup Instructions

## Prerequisites
- Docker Desktop installed
- Docker Compose available

## Quick Start

1. **Start the Docker containers:**
   ```bash
   docker-compose up -d
   ```

2. **Wait for database initialization:**
   The database will be initialized with the schema from `initdb.d/01-schema.sql`
   
3. **Access the application:**
   - Main app: http://localhost:8080
   - Adminer (database): http://localhost:8081
   - Maildev: http://localhost:1080

4. **Default login credentials:**
   - Email: `admin@nexus4d.ch`
   - Password: `admin123`

## Database Schema

The database includes the following tables:
- `users` - User accounts (admin, boss, member)
- `sessions` - Session tokens
- `events` - Event management
- `posts` - Posts published by bosses
- `bars` - Rümli bar locations
- `reviews` - Customer reviews
- `event_participation` - Event voting

## API Endpoints

All API endpoints are located in `/httpdocs/api/`:

### Authentication
- `POST /api/login.php` - Login (returns token)
- `POST /api/logout.php` - Logout
- `POST /api/refresh.php` - Refresh token

### Users (Admin/Boss only)
- `GET /api/users.php` - List all users
- `POST /api/users_create.php` - Create user
- `PUT /api/users_update.php?id=X` - Update user
- `DELETE /api/users_delete.php?id=X` - Delete user

### Events (Admin/Boss)
- `GET /api/events.php` - List events
- `POST /api/events_create.php` - Create event
- `PUT /api/events_update.php?id=X` - Update event
- `DELETE /api/events_delete.php?id=X` - Delete event

### Posts (Admin/Boss)
- `GET /api/posts.php` - List posts
- `POST /api/posts_create.php` - Create post
- `PUT /api/posts_update.php?id=X` - Update post
- `DELETE /api/posts_delete.php?id=X` - Delete post

### Reviews (All roles)
- `GET /api/reviews.php` - Get my reviews
- `GET /api/reviews_all.php` - Get all reviews (Admin/Boss)
- `POST /api/reviews_create.php` - Submit review

### Bars (Admin/Boss)
- `GET /api/bars.php` - List bars
- `POST /api/bars_create.php` - Create bar

### Member Stats (Admin/Boss)
- `GET /api/stats_members.php` - Member statistics

### Guest Review Endpoints
- `GET /api/guest_events.php` - List events for guest
- `GET /api/guest_bars.php` - List bars for guest
- `GET /api/guest_members.php` - List members for guest
- `POST /api/reviews_create.php` - Submit guest review

## Theme

The application supports light and dark mode:
- Default: Light mode
- Toggle: Click the sun/moon icon in the login page
- Preference: Saved to localStorage

## User Roles

### Admin
- Full access to all features
- Can manage all users, events, posts, and reviews
- Can create other admins

### Boss
- Manage members
- Create and manage events, posts, bars
- View all reviews and member stats

### Member
- View own reviews
- Vote on event participation
- View boss posts

### Guest
- Submit reviews without login
- Select member and event/bar
- Rate: Friendly, Professional, Overall

## Development

The application uses:
- PHP 8.5
- MariaDB
- Plain HTML/CSS/JS (no framework)
- LocalStorage for session management

## File Structure

```
httpdocs/
├── api/              # PHP API endpoints
├── assets/
│   ├── css/         # styles.css
│   ├── js/          # main.js
│   └── images/
├── index.html        # Main entry point
└── .htaccess         # Apache config

initdb.d/
└── 01-schema.sql     # Database schema

docker-compose.yml    # Docker configuration
```

## Troubleshooting

### Database connection failed
- Ensure Docker containers are running: `docker-compose ps`
- Check database logs: `docker-compose logs db`

### Session expired
- Refresh the page
- If persistent, clear localStorage in browser

### API errors
- Check browser console for details
- Ensure API endpoints exist in `/httpdocs/api/`

## Production Deployment

1. Update database credentials in `/httpdocs/config/database.php`
2. Set proper file permissions
3. Configure HTTPS
4. Update CORS settings if needed
5. Set appropriate PHP error reporting

## Support

For issues or questions, contact the development team.
