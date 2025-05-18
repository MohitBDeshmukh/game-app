# ==================

## INTERVIEW TASK

# ==================

# 🎮 Laravel Game App API

A Laravel 12 backend project for OTP-based user registration and game scoring with JWT authentication.

## 🚀 Features

-   OTP verification for mobile number (hardcoded to 1234)
-   User registration with phone, name, email, DOB
-   JWT-based authentication
-   Score submission (3/day limit, range 50–500)
-   Overall and weekly score + ranking

## ⚙️ Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/MohitBDeshmukh/game-app.git
cd game-app
```

# Install Dependencies(if required)

```bash
composer install
composer require tymon/jwt-auth
```

# Setup .env and Update database info in .env

```bash
cp .env.example .env
```

# Shared Database Script

game_app.sql

# Generate App Key & JWT Secret

```bash
php artisan key:generate
php artisan jwt:secret
```

# Run Migrations and Seeders

```bash
php artisan migrate
```

# Start Server

```bash
php artisan serve
```

# 🔐 Authentication (Use JWT token in header:)

Authorization: Bearer <your_token>

# 📮 Import Shared Postman Collection into Postamn

game-app.postman_collection.json
