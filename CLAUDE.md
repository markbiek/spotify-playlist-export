# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application that allows users to export their Spotify playlists to JSON files. The application uses Laravel Breeze for authentication and the `jwilsson/spotify-web-api-php` library for Spotify API integration.

## Development Commands

### Setup and Development
- `composer run dev` - Starts the full development environment (server, queue, logs, and Vite)
- `php artisan serve` - Start the Laravel development server
- `php artisan queue:listen --tries=1` - Start the queue worker
- `php artisan pail --timeout=0` - Start the log viewer
- `npm run dev` - Start Vite development server for frontend assets
- `npm run build` - Build production assets with Vite

### Database
- `php artisan migrate` - Run database migrations
- `php artisan migrate:fresh --seed` - Fresh migrate and seed database

### Testing
- `vendor/bin/phpunit` - Run PHPUnit tests
- `php artisan test` - Run Laravel tests

### Code Quality
- `./vendor/bin/pint` - Run Laravel Pint code formatter (follows WordPress phpcs standards)

### Docker Development
- `./vendor/bin/sail up` - Start Docker containers using Laravel Sail
- `./vendor/bin/sail down` - Stop Docker containers
- `./vendor/bin/sail artisan` - Run artisan commands in container
- `./vendor/bin/sail composer` - Run composer commands in container

## Architecture

### Core Components

**Models:**
- `SpotifyPlaylistExport` - Tracks export jobs, handles cleanup of exported files on deletion
- `User` - Standard Laravel user model with authentication

**Controllers (Single-action pattern):**
- `SpotifyController` - Handles OAuth flow with Spotify API
- `PlaylistExportController` - Creates new export jobs
- `DeleteExportController` - Deletes export records and associated files
- `DashboardController` - Shows user's exports and progress

**Jobs:**
- `ExportPlaylistsJob` - Queued job that fetches playlists from Spotify API and saves to JSON files

### Spotify Integration

The application uses OAuth 2.0 flow with these scopes:
- `playlist-read-private`
- `playlist-read-collaborative`
- `user-read-private`

Access tokens are stored in the session and used by the export job to fetch playlist data.

### File Storage

Exported playlists are saved to `storage/app/exports/{user_id}-{timestamp}/` with filenames formatted as `{owner_id}-{playlist_name}.json`. Each export creates a unique folder containing all the user's playlists as individual JSON files.

### Queue System

The application uses Laravel's queue system to process exports asynchronously. The `ExportPlaylistsJob` handles pagination through the Spotify API to fetch all playlists and their tracks.

## Frontend

- Uses Vite for asset compilation
- Tailwind CSS for styling
- Alpine.js for interactive components
- Blade templates for server-side rendering

## Development Standards

- Follow WordPress phpcs coding standards
- Use single-action controllers where possible
- Maintain `<?php` opening tags in all PHP files
- Use proper PHPDoc comments following the existing pattern