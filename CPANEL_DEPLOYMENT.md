# ClockWise - cPanel Deployment Guide (No Terminal Access)
## Step-by-Step FileZilla Upload Guide

---

## 📋 PREPARATION (On Your Computer)

### Step 1: Install Required Dependencies
Open PowerShell in project folder and run:
```
composer install --optimize-autoloader --no-dev
```

### Step 2: Prepare Files
The project is ready to upload. You'll need:
- All files from `clockWise` folder
- `.env.production` file (rename to `.env` on server)

---

## 🗄️ CPANEL SETUP

### Step 1: Create MySQL Database
1. Login to cPanel
2. Go to **MySQL® Databases**
3. Create New Database: `clockwise` (will become `username_clockwise`)
4. Create New User with strong password
5. Add User to Database with **ALL PRIVILEGES**
6. **Note down:**
   - Database name: `username_clockwise`
   - Username: `username_dbuser`
   - Password: `your_password`

### Step 2: Create Subdomain (Optional)
If deploying to subdomain:
1. Go to **Subdomains**
2. Create: `hrms.yourdomain.com`
3. Document Root: `/public_html/clockwise/public`

---

## 📁 FILEZILLA SETUP

### Step 1: Get FTP Credentials
In cPanel:
1. Go to **FTP Accounts**
2. Use existing cPanel account or create new FTP user
3. Note down:
   - Host: `ftp.yourdomain.com` or your server IP
   - Username: your cPanel username
   - Password: your cPanel password
   - Port: `21`

### Step 2: Configure FileZilla
1. Open FileZilla
2. Go to **File > Site Manager**
3. Click **New Site**
4. Fill in:
   - **Protocol:** FTP - File Transfer Protocol
   - **Host:** ftp.yourdomain.com
   - **Port:** 21
   - **Encryption:** Use explicit FTP over TLS if available
   - **Logon Type:** Normal
   - **User:** your_cpanel_username
   - **Password:** your_cpanel_password
5. Click **Connect**

---

## 📤 UPLOAD FILES

### Step 1: Navigate to Correct Folder
On the **Remote site** (right panel):
- Navigate to `/public_html/` 
- Create new folder: `clockwise`
- Enter the `clockwise` folder

### Step 2: Upload All Files
On the **Local site** (left panel):
1. Navigate to `C:\xampp\htdocs\clockWise`
2. Select ALL files and folders (Ctrl+A)
3. Right-click > **Upload**
4. Wait for upload to complete (may take 10-15 minutes)

### Step 3: Configure .env File
After upload:
1. On remote site, find `.env.production`
2. Right-click > **Rename** > change to `.env`
3. Right-click `.env` > **View/Edit**
4. Update these values:
```
APP_URL=https://yourdomain.com/clockwise/public

DB_DATABASE=username_clockwise
DB_USERNAME=username_dbuser
DB_PASSWORD=your_actual_password
```
5. Save and upload when prompted

---

## 🔒 SET FOLDER PERMISSIONS

In cPanel **File Manager** (easier than FileZilla):

1. Go to **File Manager**
2. Navigate to `/public_html/clockwise/`
3. Right-click `storage` folder > **Change Permissions**
   - Set to `775` or check all boxes
   - Check "Recurse into subdirectories"
4. Do the same for `bootstrap/cache` folder

---

## 🚀 RUN INSTALLER

### Step 1: Access Installer
Open browser and go to:
```
https://yourdomain.com/clockwise/public/install.php?token=clockwise2026
```

### Step 2: Run Installation
1. Check that all requirements pass ✅
2. Click **Run Installation**
3. Wait for migrations and seeding to complete
4. Note the login credentials

### Step 3: DELETE INSTALLER! ⚠️
**IMPORTANT:** After successful installation:
1. Go back to FileZilla or cPanel File Manager
2. Navigate to `/public_html/clockwise/public/`
3. **DELETE** `install.php` file

---

## 🌐 CONFIGURE URL (Remove /public)

### Option A: Using .htaccess (Recommended)
1. In cPanel File Manager, go to `/public_html/clockwise/`
2. Create new file: `.htaccess`
3. Add this content:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### Option B: Point Domain Directly
In cPanel:
1. Go to **Domains** or **Addon Domains**
2. Change Document Root to: `/public_html/clockwise/public`

---

## ✅ TEST YOUR INSTALLATION

1. Visit: `https://yourdomain.com/clockwise/` (or your domain)
2. Login with:
   - **Admin:** admin@clockwise.my / password123
   - **Employee:** ali@clockwise.my / password123

---

## 🔧 TROUBLESHOOTING

### Error 500 (Internal Server Error)
1. Check `.env` file has correct database credentials
2. Check `storage` and `bootstrap/cache` have write permissions (775)
3. Check PHP version is 8.1+ in cPanel > Select PHP Version

### Blank White Page
1. Enable error display temporarily in `.env`:
   ```
   APP_DEBUG=true
   ```
2. Check error, then set back to `false`

### Database Connection Error
1. Verify database credentials in cPanel
2. Make sure user is added to database with ALL privileges
3. Database host should be `localhost` for cPanel

### CSS/JS Not Loading
1. Check APP_URL in `.env` matches your actual URL
2. Clear browser cache
3. Try running storage link manually via installer

---

## 📧 CONFIGURE EMAIL (Optional)

For password reset emails, in cPanel:
1. Go to **Email Accounts**
2. Create: noreply@yourdomain.com
3. Update `.env`:
```
MAIL_HOST=mail.yourdomain.com
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=email_password
```

---

## 🎉 DONE!

Your ClockWise HRMS should now be running!

**Default Logins:**
- Admin: admin@clockwise.my / password123
- Employee: ali@clockwise.my / password123

**Remember to:**
- [ ] Delete `install.php` file
- [ ] Change default passwords
- [ ] Set APP_DEBUG=false in production
- [ ] Configure email for password resets
