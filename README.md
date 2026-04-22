# Wartil (ورتل) - Quranic E-Learning Platform 📖✨

![Laravel](https://img.shields.io/badge/Laravel-v11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Agora](https://img.shields.io/badge/Agora-WebRTC-099DFD?style=for-the-badge&logo=agora&logoColor=white)
![PayTabs](https://img.shields.io/badge/PayTabs-Payment-success?style=for-the-badge)

## Overview

**Wartil (ورتل)** is a Quranic learning platform that connects students with certified Quran teachers through live sessions, intelligent scheduling, secure package subscriptions, and gift-card based learning packages.

The platform supports:

- Live 1-on-1 Quran recitation sessions  
- Teacher scheduling and availability management  
- Secure payment and package subscriptions  
- Gift-card based session sharing  
- Cloud recording for session history  
- Real-time notifications and automated session handling

---

# Mobile App Screenshots

Upload your images inside:

```bash
docs/images/
```

Then replace the image paths below:

```html
<div align="center">
  <img src="docs/images/registration-screen.jpg" width="220"/>
  <img src="docs/images/home-screen.jpg" width="220"/>
  <img src="docs/images/session-screen.jpg" width="220"/>
  <img src="docs/images/profile-screen.jpg" width="220"/>
</div>
```

---

# Features

## For Students

- Smart teacher discovery
- Filter by specialization, gender and ratings
- Flexible session booking
- Purchase minute-based packages
- Send gift packages using claim codes
- View recorded sessions history

## For Teachers

- Manage availability
- Conduct live video/audio sessions
- Track earnings automatically
- Control cloud recording sessions

## Core Technical Features

- Real-time notifications using Pusher / Reverb
- Database locking with `lockForUpdate()`
- Double-booking prevention
- Automatic refund and missed-session handling via Cron jobs
- Secure minute deduction logic

---

# Tech Stack

## Backend

- Laravel 10/11
- PHP 8.x
- MySQL

## Real-Time

- Agora WebRTC
- Pusher / Laravel Reverb

## Storage

- Cloudflare R2 (S3 Compatible)

## Payments

- PayTabs

## Mobile

- Flutter *(Update if needed)*

---

# Project Structure

```bash
app/
routes/
database/
resources/
storage/
docs/
```

---

# Installation

## Prerequisites

- PHP >= 8.1
- Composer
- MySQL
- Node.js
- NPM

## Clone Repository

```bash
git clone https://github.com/yourusername/wartil-backend.git
cd wartil-backend
```

## Install Dependencies

```bash
composer install
npm install
```

## Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env`

```dotenv
APP_NAME=Wartil

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wartil_db
DB_USERNAME=root
DB_PASSWORD=

AGORA_APP_ID=your_agora_app_id
AGORA_APP_CERTIFICATE=your_certificate

PAYTABS_PROFILE_ID=your_profile_id
PAYTABS_SERVER_KEY=your_server_key

CLOUDFLARE_R2_PUBLIC_URL=https://your-r2-public-url.dev
```

## Run Database

```bash
php artisan migrate --seed
```

## Start Server

```bash
php artisan serve
```

## Start Queue Worker

```bash
php artisan queue:work
```

## Start Scheduler

```bash
php artisan schedule:work
```

---

# API Response Format

Example:

```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```

---

# Authentication

Protected using:

- Laravel Sanctum *(or Passport if used)*

Example:

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
```

---

# Security

- Role-based authorization
- Secure payment webhooks
- Privacy consent support
- Protected recording access
- Transaction-safe booking logic

---

# Documentation

API documentation can be found inside:

```bash
/docs
```

Or via Postman collection (add your link here).

---

# Deployment

Example production commands:

```bash
php artisan config:cache
php artisan route:cache
php artisan queue:restart
```

---

# License

This project is proprietary and confidential.

Unauthorized copying or distribution is prohibited.

---

## Author

Developed for the Wartil Community ❤️
