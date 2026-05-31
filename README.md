# Nexus 4D

A web-based management system for JugendTeam members with review tracking, event management, and role-based access.

## Features

- 📊 **Dashboard**: Role-specific dashboards for Admin, Boss, and Member accounts
- 🌙 **Dark Mode**: Toggle between light and dark themes
- 📝 **Review System**: Track customer feedback with star ratings
- 📅 **Event Management**: Create, manage, and vote on events
- 👥 **User Management**: Full control over user accounts
- 🏪 **Bar Management**: Track Rümli bar locations
- 📈 **Statistics**: View member performance metrics
- 👤 **Guest Reviews**: Submit reviews without login

## Quick Start

### Prerequisites

- Docker Desktop installed
- Docker Compose available

### Installation

```bash
# Clone the repository (if not already done)
# cd nexus-4d

# Start Docker containers
docker-compose up -d

# Wait for database initialization
# Access the app at http://localhost:8080
```

### Default Credentials

- **Email**: `admin@nexus4d.ch`
- **Password**: `admin123`

## User Roles

### Admin
- Full access to all features
- Manage all users, events, posts, and reviews
- Can create other admins

### Boss
- Manage members
- Create and manage events, posts, bars
- View all reviews and member statistics

### Member
- View own reviews and statistics
- Vote on event participation
- View boss posts

### Guest
- Submit reviews without login
- Select member and event/bar
- Rate: Friendly, Professional, Overall

## API Documentation

All API endpoints are documented in `httpdocs/API.md`.

### Quick Examples

**Login:**
```bash
POST /api/login.php
{
  "email": "admin@nexus4d.ch",
  "password": "admin123"
}
```

**Get Events:**
```bash
GET /api/events.php
Authorization: Bearer <token>
```

**Submit Review:**
```bash
POST /api/reviews_create.php
{
  "target_user_id": 1,
  "rating_friendly": 5,
  "rating_professional": 4,
  "rating_overall": 5,
  "comment": "Great service!"
}
```

## Project Structure

```
nexus-4d/
├── httpdocs/
│   ├── api/              # PHP API endpoints
│   ├── assets/
│   │   ├── css/         # styles.css
│   │   ├── js/          # main.js
│   │   └── images/
│   ├── index.html       # Main entry point
│   └── README.md        # Setup instructions
├── initdb.d/
│   └── 01-schema.sql    # Database schema
├── docker-compose.yml   # Docker configuration
└── AGENTS.md            # Project documentation
```

## Development

### Adding New Features

1. Database: Update `initdb.d/01-schema.sql`
2. API: Create new endpoint in `httpdocs/api/`
3. Frontend: Update `httpdocs/assets/js/main.js`

### Theme Customization

Edit `httpdocs/assets/css/styles.css`:
- Light theme: Default colors
- Dark theme: `.dark-mode` class
- CSS variables in `:root`

### Database Changes

1. Update schema in `initdb.d/01-schema.sql`
2. Restart containers: `docker-compose down && docker-compose up -d`

## Configuration

### Environment Variables

Update `httpdocs/config/database.php` for production:
```php
$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'nexusData';
$user = getenv('DB_USER') ?: 'dev';
$pass = getenv('DB_PASS') ?: 'dev#123';
```

### Session Settings

Default session expiry: 6 hours
Modify in `httpdocs/api/auth.php`

## Troubleshooting

### Database Connection Failed

```bash
# Check container status
docker-compose ps

# View database logs
docker-compose logs db

# Restart containers
docker-compose down && docker-compose up -d
```

### Session Expired

- Refresh the page
- Clear localStorage: `localStorage.clear()` in browser console

### API Not Working

- Check browser console for errors
- Verify API endpoints exist in `httpdocs/api/`
- Ensure Docker containers are running

## Production Deployment

1. Update database credentials
2. Configure HTTPS
3. Update CORS settings
4. Set appropriate error reporting
5. Configure backup schedule

## Support

For issues or questions:
- Check the documentation in `httpdocs/README.md`
- Review API endpoints in `httpdocs/API.md`
- Contact development team

## License

© 2025 ROKJA:4D
Regionale Offene Kinder- und Jugendarbeit
Wünnewil-Flamatt | Schmitten | Ueberstorf
