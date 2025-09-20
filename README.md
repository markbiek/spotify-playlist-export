# Spotify Playlist Export

A Laravel application that allows users to export their Spotify playlists to JSON and CSV files. Users authenticate with their Spotify account and can download `.zip` file containing all playlists in simple CSV format and the full playlist details in JSON.

## Features

- **Spotify OAuth Integration**: Secure authentication using Spotify's OAuth 2.0 flow
- **Playlist Export**: Export all user playlists (private and collaborative) to JSON files
- **User Management**: User registration, authentication, and admin approval system
- **Asynchronous Processing**: Queue-based playlist export to handle large collections
- **File Management**: Automatic cleanup of exported files when exports are deleted

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- SQLite (default) or MySQL/PostgreSQL
- Spotify Developer Application
- Docker (for local development with Laravel Sail)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd spotify-playlist-export
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure Spotify API**
   - Create a Spotify app at [Spotify Developer Dashboard](https://developer.spotify.com/dashboard)
   - Add your redirect URI (e.g., `http://localhost:8000/spotify/callback`)
   - Update your `.env` file:
   ```env
   SPOTIFY_CLIENT_ID=your_client_id
   SPOTIFY_CLIENT_SECRET=your_client_secret
   SPOTIFY_REDIRECT_URI=http://localhost:8000/spotify/callback
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

## Development

### Start the development environment
```bash
./vendor/bin/sail up
```
This starts the local Docker setup to run the application locally.

### Individual services
```bash
sail artisan queue:listen       # Queue worker for exports
sail artisan pail --timeout=0   # Log viewer
npm run dev                    # Vite development server
```

### Testing
```bash
sail artisan test
# or
vendor/bin/phpunit
```

## How It Works

1. **Authentication**: Users register and authenticate with the application
2. **Spotify OAuth**: Users connect their Spotify account via OAuth 2.0
3. **Export Creation**: Users can create export jobs for their playlists
4. **Background Processing**: A queued job fetches all playlists and tracks from Spotify
5. **File Generation**: Each playlist is saved as a separate JSON file
6. **Download**: Users can download their exported playlists

## API Scopes

The application requests these Spotify scopes:
- `playlist-read-private`: Access to user's private playlists
- `playlist-read-collaborative`: Access to collaborative playlists
- `user-read-private`: Basic user profile information

## File Structure

Exported playlists are organized as:
```
storage/app/exports/{user_id}-{timestamp}/
├── {owner_id}-{playlist_name}.json
├── {owner_id}-{playlist_name}.json
└── ...
```

Each JSON file contains complete playlist information including tracks, artists, albums, and metadata.

## Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Blade templates, Tailwind CSS, Alpine.js
- **Database**: SQLite (default), MySQL/PostgreSQL supported
- **Queue**: Database driver (Redis/SQS supported)
- **Build**: Vite
- **Spotify Integration**: jwilsson/spotify-web-api-php
