# Nexus 4D

Nexus 4D is a web tool for JugendTeam members to track reviews, events, and participation. It includes admin and boss tools for managing users, events, and content.

## Features

- **Authentication**: Session-based with JWT tokens stored in localStorage
- **Dark Mode**: Toggle between light and dark themes
- **Role-based Access**: Admin, Boss, and Member roles
- **Guest Reviews**: Submit reviews without login
- **Event Management**: Create, manage, and vote on events
- **Review System**: Track feedback with star ratings
- **Dashboard**: Role-specific dashboards with stats

## Installation

### Docker Setup (Development)

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

### Default Credentials

- **Email**: admin@nexus4d.ch
- **Password**: admin123

## API Documentation

### Authentication

#### Login
```bash
POST /api/login.php
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

Response:
```json
{
  "success": true,
  "user": {"id": 1, "email": "user@example.com", "role": "admin"},
  "token": "abc123...",
  "expires_at": "2025-12-31 23:59:59"
}
```

#### Refresh Token
```bash
POST /api/refresh.php
Authorization: Bearer abc123...
```

### Endpoints

| Endpoint | Method | Role | Description |
|----------|--------|------|-------------|
| `/api/login.php` | POST | Guest | Login |
| `/api/logout.php` | POST | Auth | Logout |
| `/api/refresh.php` | POST | Auth | Refresh token |
| `/api/users.php` | GET | Admin/Boss | List users |
| `/api/users_create.php` | POST | Admin/Boss | Create user |
| `/api/users_update.php` | PUT | Admin/Boss | Update user |
| `/api/users_delete.php` | DELETE | Admin/Boss | Delete user |
| `/api/events.php` | GET | All | List events |
| `/api/events_create.php` | POST | Admin/Boss | Create event |
| `/api/events_update.php` | PUT | Admin/Boss | Update event |
| `/api/events_delete.php` | DELETE | Admin/Boss | Delete event |
| `/api/posts.php` | GET | All | List posts |
| `/api/posts_create.php` | POST | Admin/Boss | Create post |
| `/api/posts_update.php` | PUT | Admin/Boss | Update post |
| `/api/posts_delete.php` | DELETE | Admin/Boss | Delete post |
| `/api/reviews.php` | GET | Auth | Get my reviews |
| `/api/reviews_all.php` | GET | Admin/Boss | Get all reviews |
| `/api/reviews_create.php` | POST | Auth/Guest | Submit review |
| `/api/bars.php` | GET | Admin/Boss | List bars |
| `/api/bars_create.php` | POST | Admin/Boss | Create bar |
| `/api/stats_members.php` | GET | Admin/Boss | Member statistics |
| `/api/participation_my.php` | GET | Auth | My event votes |

## Project Structure

```
nexus-4d/
├── httpdocs/
│   ├── api/              # PHP API endpoints
│   ├── assets/
│   │   ├── css/         # styles.css
│   │   ├── js/          # main.js
│   │   └── images/
│   ├── index.html       # Main entry
│   └── .htaccess        # Apache config
├── initdb.d/
│   └── 01-schema.sql    # Database schema
├── docker-compose.yml   # Docker config
└── AGENTS.md            # Project documentation
```

## Development

### Adding New API Endpoints

1. Create PHP file in `httpdocs/api/`
2. Use existing endpoints as examples
3. Add proper authentication checks
4. Return JSON responses with consistent structure

### Database Changes

1. Update `initdb.d/01-schema.sql`
2. Restart Docker containers
3. Update API endpoints accordingly

## Testing

The application can be tested using:
- Browser: http://localhost:8080
- Database: Adminer at http://localhost:8081
- Email: Maildev at http://localhost:1080

## License

© 2025 ROKJA:4D
