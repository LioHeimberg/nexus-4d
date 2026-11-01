# Nexus 4D - Project Documentation

## Project Overview

**ROKJA:4D** (Regionale Offene Kinder- und Jugendarbeit) - A web-based youth work management system for Wünnewil-Flamatt, Schmitten, and Ueberstorf.

### Team
- **Kevin Zeh**: Stellenleiter (Jugendräume Wünnewil & Flamatt)
- **Raphael Kaufmann**: Jugendarbeiter (Schmitten)
- **Noë Scheidegger**: Neu im Team (seit August 2025)

---

## Technical Architecture

### Tech Stack
- **Backend**: PHP 8.5 (MariaDB/MySQL)
- **Frontend**: HTML5, CSS3, Vanilla JavaScript (ES6+ modules)
- **Authentication**: JWT tokens stored in localStorage
- **Server**: Apache with mod_rewrite
- **Dev Tools**: Docker Compose, Adminer

### Project Structure
```
nexus-4d/
├── httpdocs/              # Web server root
│   ├── api/               # PHP API endpoints
│   ├── assets/
│   │   ├── css/          # Stylesheets
│   │   ├── js/           # JavaScript modules
│   │   └── img/          # Images
│   └── config/           # Database configuration
├── initdb.d/             # Database initialization scripts
└── docker-compose.yml    # Development environment
```

---

## Account Roles & Permissions

| Feature | Admin | Boss | Member | Guest |
|---------|-------|------|--------|-------|
| Create/Delete Users | ✓ | ✗ | ✗ | ✗ |
| Manage Users | ✓ | ✓ | ✗ | ✗ |
| Create/Delete Events | ✓ | ✓ | ✗ | ✗ |
| Create/Delete Posts | ✓ | ✓ | ✗ | ✗ |
| Create/Delete Bars | ✓ | ✓ | ✗ | ✗ |
| View Member Stats | ✓ | ✓ | ✗ | ✗ |
| View All Reviews | ✓ | ✓ | ✗ | ✗ |
| Give Reviews | ✓ | ✓ | ✓ | ✓ |
| View Own Reviews | ✓ | ✓ | ✓ | ✓ |
| Vote on Events | ✓ | ✓ | ✓ | ✗ |

and more i've not documented here yet

### Role Descriptions

1. **ADMIN Account**
   - First account created in database
   - Full control over all system features
   - Can create/manage all other accounts
   - Access to all statistics and reviews

2. **BOSS Account** (Jugendteam)
   - Single team account for all adults
   - Create/edit/delete events, posts, bars
   - Manage JugendTeam member accounts
   - View member statistics and all reviews

3. **JUGENDTEAM Member Account**
   - Created by boss/admin accounts
   - For 7th-9th grade youth from Wünnewil-Flamatt, Schmitten, Ueberstorf
   - View own reviews from bosses and customers
   - See boss posts and vote on event participation
   - Give reviews at events or at Rümli (bar)

4. **GUEST Account**
   - No login required
   - Select JugendTeam member who served you
   - Select event or Rümli location
   - Rate service with 3 main criteria: friendly, professional, overall

---

## API Endpoints

### Authentication
- `POST /api/login.php` - User login
- `POST /api/logout.php` - User logout
- `GET /api/refresh.php` - Token refresh

### Users Management
- `GET /api/users.php` - List all users
- `POST /api/users_create.php` - Create new user
- `PUT /api/users_update.php` - Update user
- `DELETE /api/users_delete.php` - Delete user
- `GET /api/check_admin.php` - Check if admin exists

### Events
- `GET /api/events.php` - List events with participation stats
- `POST /api/events_create.php` - Create event
- `PUT /api/events_update.php` - Update event
- `DELETE /api/events_delete.php` - Delete event
- `POST /api/events.php` - Vote on event participation

### Posts
- `GET /api/posts.php` - List posts
- `POST /api/posts_create.php` - Create post
- `PUT /api/posts_update.php` - Update post
- `DELETE /api/posts_delete.php` - Delete post

### Reviews
- `GET /api/reviews.php?user_id=X` - Get reviews for specific user
- `GET /api/reviews_all.php` - Get all reviews
- `POST /api/reviews_create.php` - Create review
- `DELETE /api/reviews_delete.php` - Delete review

### Bars (Rümli)
- `GET /api/bars.php` - List bars
- `POST /api/bars_create.php` - Create bar

### Guest Features
- `GET /api/guest_members.php` - List JugendTeam members
- `GET /api/guest_events.php` - List upcoming events
- `GET /api/guest_bars.php` - List bars

---

## Database Schema

### Tables
- **users**: User accounts (id, email, password_hash, role, first_name, last_name, created_at)
- **sessions**: JWT token storage (token, user_id, expires_at)
- **events**: Event information (id, title, description, event_date, location, boss_id)
- **posts**: Team posts (id, title, content, published_at, author_id)
- **bars**: Rümli locations (id, name, location)
- **reviews**: Feedback system (id, target_user_id, reviewer_id, rating_friendly, rating_professional, rating_overall, comment, event_id, bar_id)
- **event_participation**: Event participation tracking (event_id, member_id, status: yes/maybe/no)

---

## Development Setup

### Docker Compose
```yaml
services:
  apache:    # PHP 8.5 Apache server
  db:        # MariaDB database
  adminer:   # Database management
```

### Environment Variables
- `ENV=dev` (development) or `ENV=prod` (production)
- Database config in `/httpdocs/config/database.php`

### Key Configuration
- API base URL: `/api`
- Session storage: localStorage (not PHP sessions)
- Theme support: Light/Dark mode

---

## Color Scheme & Design

### Primary Colors
- **R**: #2e63a8 (blue)
- **O**: #30a3db (light blue)
- **K**: #3eb074 (green)
- **J**: #ecd057 (yellow)
- **A**: #eb576b (red)
- **4D**: #000000 (black)

### Theme System
- Light mode (default)
- Dark mode (toggle via UI)
- CSS variables for easy customization

---

## Current Status

**Version**: 0.5.2 - ALPHA  
**Status**: Active development  
**Features**: Complete authentication, CRUD operations for users/events/posts/bars, review system, member statistics
**Last Update**: 01.11.2026