# TibetTune — Laravel Backend API

> Production-ready Laravel backend powering TibetTune, a Tibetan music streaming platform.

**PHP 8.2+ · Laravel 12 · MySQL · REST API · Sanctum · Stripe · RevenueCat · DigitalOcean**

## 🚀 Key Features

- User authentication and authorization
- Music catalog management
- Albums and playlists
- Search and music discovery
- Most-played and most-liked content
- Artist profiles and artist claims
- Song dedications
- User libraries
- Audio streaming with HTTP byte-range support
- Premium subscriptions
- Stripe and RevenueCat integration
- Webhooks
- Admin dashboard
- Analytics and moderation

TibetTune API:

Admin Dashboard:
[TibetTune Admin Dashboard](https://api.tibettune.com/admin)

The production admin dashboard is authentication-protected. Demo credentials can be provided.

Mobile Application:
Available on the Apple App Store.

## 🛠 Technical Highlights

- Designed RESTful APIs using Laravel controllers, services, models and API resources.
- Implemented authentication and authorization using Laravel Sanctum.
- Integrated Stripe and RevenueCat for subscription management and webhook processing.
- Implemented HTTP byte-range audio streaming for reliable mobile playback.
- Designed MySQL database schemas and relationships using Laravel migrations and Eloquent ORM.
- Built and deployed the production backend using Linux, Nginx, PHP-FPM, MySQL and DigitalOcean.
- Implemented admin analytics for plays, likes, users and recent activity.
- Diagnosed and resolved production API, database, caching and audio-streaming issues.

## 🏗 Architecture

```text
Flutter Mobile App
        │
        ▼
   Laravel REST API
        │
 ┌──────┼───────────────────────┐
 │      │       │       │       │
Auth  Music   Artists  Social  Premium
 │      │       │       │       │
 └──────┴───────┴───────┴───────┘
                │
              MySQL
                │
        DigitalOcean / Nginx

The backend provides the API layer for the TibetTune mobile application,
while the admin dashboard is used for content management, moderation and
analytics.

## 📁 Project Structure

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Resources/
├── Models/
└── Services/

database/
├── migrations/
├── seeders/
└── factories/

routes/
└── api.php

resources/
└── views/






