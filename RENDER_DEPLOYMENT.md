# 🚀 Render Deployment Guide for ClockWise

This guide walks you through deploying ClockWise HRMS on [Render.com](https://render.com).

---

## 📋 Prerequisites

- GitHub account with ClockWise repository
- Render.com account (free tier available)

---

## 🎯 Deployment Options

### Option 1: Blueprint (Recommended) - One-Click Deploy

1. **Push the Render files to GitHub**
   ```bash
   git add .
   git commit -m "Add Render deployment configuration"
   git push origin main
   ```

2. **Go to Render Dashboard**
   - Visit [dashboard.render.com](https://dashboard.render.com)
   - Click **"New"** → **"Blueprint"**

3. **Connect Repository**
   - Select your GitHub repository: `Syafiq276/ClockWise`
   - Render will detect `render.yaml` automatically

4. **Deploy**
   - Click **"Apply"**
   - Render will create:
     - ✅ Web Service (Laravel app)
     - ✅ PostgreSQL Database (free tier)

5. **Wait for deployment** (~5-10 minutes)

---

### Option 2: Manual Setup

#### Step 1: Create PostgreSQL Database

1. Go to Render Dashboard
2. Click **"New"** → **"PostgreSQL"**
3. Configure:
   - **Name:** `clockwise-db`
   - **Database:** `clockwise`
   - **User:** `clockwise`
   - **Region:** Singapore (closest to Malaysia)
   - **Plan:** Free
4. Click **"Create Database"**
5. Copy the **Internal Database URL**

#### Step 2: Create Web Service

1. Click **"New"** → **"Web Service"**
2. Connect your GitHub repository
3. Configure:
   - **Name:** `clockwise`
   - **Region:** Singapore
   - **Branch:** `main`
   - **Runtime:** Docker
   - **Dockerfile Path:** `./Dockerfile.render`
   - **Plan:** Free

#### Step 3: Add Environment Variables

Click **"Environment"** and add:

| Key | Value |
|-----|-------|
| `APP_NAME` | ClockWise |
| `APP_ENV` | production |
| `APP_DEBUG` | false |
| `APP_KEY` | *(click Generate)* |
| `APP_URL` | https://clockwise-xxxx.onrender.com |
| `DB_CONNECTION` | pgsql |
| `DATABASE_URL` | *(paste Internal Database URL)* |
| `LOG_CHANNEL` | stderr |
| `SESSION_DRIVER` | cookie |
| `CACHE_STORE` | file |

#### Step 4: Deploy

1. Click **"Create Web Service"**
2. Wait for build and deployment

---

## 🔧 Post-Deployment Setup

### 1. Run Database Migrations

Render runs migrations automatically on deploy. If needed manually:

1. Go to your Web Service
2. Click **"Shell"** tab
3. Run:
   ```bash
   php artisan migrate --force
   ```

### 2. Seed Database (Optional)

For sample data:
```bash
php artisan db:seed --force
```

### 3. Generate App Key (if not set)

```bash
php artisan key:generate --show
```
Copy and paste into `APP_KEY` environment variable.

---

## 🌐 Access Your Application

After deployment:

- **URL:** `https://clockwise-xxxx.onrender.com`
- **Admin Login:** `admin@clockwise.my` / `password123`
- **Employee Login:** `ali@clockwise.my` / `password123`

---

## ⚠️ Important Notes

### Free Tier Limitations

| Resource | Limit |
|----------|-------|
| Web Service | Spins down after 15 min inactivity |
| PostgreSQL | 1GB storage, 97 days retention |
| Bandwidth | 100GB/month |

### Cold Starts

Free tier services "sleep" after inactivity. First request may take 30-60 seconds.

**Solution:** Use [UptimeRobot](https://uptimerobot.com) to ping every 14 minutes.

### File Storage

Render uses ephemeral storage. Uploaded files (MC certificates) will be lost on redeploy.

**Solution:** Use cloud storage (AWS S3, Cloudinary) for persistent files.

---

## 🔄 Continuous Deployment

Render auto-deploys on every push to `main` branch:

```bash
git add .
git commit -m "Your changes"
git push origin main
# Render automatically rebuilds and deploys
```

---

## 🛠️ Troubleshooting

### Build Failed

**Check logs:**
1. Go to Web Service → **"Events"** tab
2. Click on failed deploy to see logs

**Common issues:**
- Missing PHP extensions → Check `Dockerfile.render`
- Composer errors → Run `composer install` locally first

### Database Connection Error

1. Verify `DATABASE_URL` is correct
2. Check PostgreSQL service is running
3. Ensure `DB_CONNECTION=pgsql` is set

### 500 Error

1. Check **"Logs"** tab for errors
2. Temporarily set `APP_DEBUG=true`
3. Check storage permissions

### Migrations Not Running

Run manually via Shell:
```bash
php artisan migrate --force
php artisan db:seed --force
```

---

## 📊 Monitoring

### Logs

- Go to Web Service → **"Logs"** tab
- Real-time application logs

### Metrics

- Go to Web Service → **"Metrics"** tab
- CPU, Memory, Request count

---

## 💰 Upgrading

For production use, consider upgrading:

| Plan | Price | Benefits |
|------|-------|----------|
| Starter | $7/mo | No sleep, custom domains |
| Standard | $25/mo | More CPU/RAM, SSD |

PostgreSQL:
| Plan | Price | Storage |
|------|-------|---------|
| Starter | $7/mo | 1GB |
| Standard | $20/mo | 10GB |

---

## 🔗 Useful Links

- [Render Dashboard](https://dashboard.render.com)
- [Render Docs](https://render.com/docs)
- [Laravel on Render](https://render.com/docs/deploy-php-laravel)
- [PostgreSQL on Render](https://render.com/docs/databases)

---

## 📝 Environment Variables Reference

```env
# Application
APP_NAME=ClockWise
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.onrender.com
APP_KEY=base64:generated-key-here

# Database (PostgreSQL)
DB_CONNECTION=pgsql
DATABASE_URL=postgres://user:pass@host:5432/dbname

# Logging
LOG_CHANNEL=stderr
LOG_LEVEL=error

# Session & Cache
SESSION_DRIVER=cookie
CACHE_STORE=file
QUEUE_CONNECTION=sync

# Optional: Mail (for password reset)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@clockwise.my
MAIL_FROM_NAME=ClockWise
```

---

<p align="center">
  <strong>Happy Deploying! 🚀</strong><br>
  ClockWise on Render
</p>
