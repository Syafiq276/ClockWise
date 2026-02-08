# ✈️ Fly.io Deployment Guide for ClockWise

This guide walks you through deploying ClockWise to **Fly.io**, which offers a generous free tier and crucially, **SSH access** to your running server.

---

## 📋 Prerequisites

1.  **Fly.io Account**: [Sign up here](https://fly.io/app/sign-up) (No credit card needed for free tier usually, but sometimes required for verification).
2.  **flyctl installed**: The Fly.io command-line tool.

### 🛠️ Step 1: Install flyctl

**Windows (PowerShell):**
```powershell
pwsh -Command "iwr https://fly.io/install.ps1 -useb | iex"
```

**Mac/Linux:**
```bash
curl -L https://fly.io/install.sh | sh
```

After installing, restart your terminal and log in:
```bash
fly auth login
```

---

## 🚀 Step 2: Initialize App

Run this command in your project folder:

```bash
fly launch
```

Detailed prompts flow:
1.  **Choose an app name**: (e.g., `clockwise-hrms`)
2.  **Choose a region**: Select one close to you (e.g., `sin` for Singapore).
3.  **Would you like to set up a Postgresql database now?**: Type **Y**.
    *   Select **Development** config (Free).
4.  **Would you like to set up an Upstash Redis database now?**: Type **N** (we can use file cache for now).
5.  **Would you like to set up a .dockerignore file?**: Type **Y**.
6.  **Would you like to deploy now?**: Type **N** (we need to tweak config first).

---

## ⚙️ Step 3: Configure `fly.toml`

The `fly launch` command created a `fly.toml` file. We need to ensure it handles the web server correctly. It should look roughly like this (we'll update the env vars):

```toml
[env]
  APP_ENV = "production"
  APP_DEBUG = "false"
  LOG_CHANNEL = "stderr"
  LOG_LEVEL = "info"
  DB_CONNECTION = "pgsql"
  SESSION_DRIVER = "cookie"
  CACHE_STORE = "file"

[http_service]
  internal_port = 8080
  force_https = true
  auto_stop_machines = true
  auto_start_machines = true
```

---

## 🔐 Step 4: Set Secrets

Run these commands to set your sensitive data encrypted on Fly:

```bash
# Generate a new app key
fly secrets set APP_KEY=base64:$(openssl rand -base64 32)

# If using external DB (Neon), set connection details here
# fly secrets set DB_HOST=... DB_PASSWORD=...
# BUT since we used 'fly launch' to create a db, DATABASE_URL is already set automatically!
```

---

## 🚀 Step 5: Deploy

```bash
fly deploy
```

This will build your Docker image and push it to Fly.io.

---

## 🛠️ Step 6: Post-Deployment Setup (The Important Part!)

Once deployed, run migrations:

```bash
fly ssh console -C "php /var/www/html/artisan migrate --force"
```

### 🆘 Resetting Your Admin Password (Securely)

This is the power of Fly.io. You can log directly into the server:

1.  **Open Console:**
    ```bash
    fly ssh console
    ```

2.  **Reset Password via Tinker:**
    ```bash
    php artisan tinker
    ```
    Then run:
    ```php
    $user = \App\Models\User::where('email', 'admin@clockwise.my')->first();
    $user->password = Hash::make('NewPassword123!');
    $user->save();
    exit
    ```

---

## 🐞 Troubleshooting

**Logs:**
```bash
fly logs
```

**Status:**
```bash
fly status
```
