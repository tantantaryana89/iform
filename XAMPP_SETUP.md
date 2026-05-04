# 🔧 XAMPP Setup Guide - Local Network Production

## Scenario Anda

```
Factory Network (Restricted)
        │
    WiFi Router (Isolated, No Internet)
        │
        ├─ Server Computer (XAMPP, Static IP)
        │   → 192.168.1.100
        │   → Port 80 (Apache)
        │   → MySQL included
        │
        ├─ Android Phone 1
        │   → Submit forms via API
        │
        ├─ Android Phone 2
        │   → Submit forms via API
        │
        └─ Admin Laptop
            → Manage via //192.168.1.100
```

---

## 1️⃣ XAMPP Installation

### macOS

```bash
# Download XAMPP from:
# https://www.apachefriends.org/download.html

# Install
# Drag to Applications folder

# Start XAMPP
cd /Applications/XAMPP/xamppfiles
sudo bin/apachectl  start
sudo bin/mysqld_safe &

# Or use: /Applications/XAMPP/manager-osx
```

### Linux

```bash
# Download from official site
# Or via package manager
sudo apt-get install xampp-linux-x64

# Start XAMPP
sudo /opt/lampp/lampp start

# Check status
sudo /opt/lampp/lampp status
```

### Windows

```bash
# Download XAMPP installer
# https://www.apachefriends.org/download.html

# Run installer
# Default location: C:\xampp

# Start via XAMPP Control Panel
# (or command line: C:\xampp\apache_start.bat)
```

---

## 2️⃣ Deploy Yii2 App to XAMPP

### Copy Project

```bash
# macOS/Linux
cp -r /Applications/MAMP/htdocs/iform /Applications/XAMPP/xamppfiles/htdocs/iform
# Or
sudo cp -r /path/to/iform /opt/lampp/htdocs/

# Windows
# Copy folder to: C:\xampp\htdocs\iform
```

### Set Permissions

```bash
# macOS/Linux
sudo chown -R daemon:daemon /Applications/XAMPP/xamppfiles/htdocs/iform
sudo chmod -R 755 /Applications/XAMPP/xamppfiles/htdocs/iform/runtime

# Or for Linux XAMPP
sudo chown -R nobody:nogroup /opt/lampp/htdocs/iform
sudo chmod -R 755 /opt/lampp/htdocs/iform/runtime
```

### Create Runtime Directories

```bash
mkdir -p /Applications/XAMPP/xamppfiles/htdocs/iform/runtime/{logs,cache,export}
# Or Windows: Create manually in htdocs/iform/runtime/
```

---

## 3️⃣ Configure Apache Virtual Host

### macOS/Linux: Edit Apache Config

```bash
# Edit httpd-vhosts.conf
sudo nano /Applications/XAMPP/xamppfiles/etc/httpd/conf.d/httpd-vhosts.conf
# Or Linux: /opt/lampp/etc/httpd/conf.d/httpd-vhosts.conf

# Add at bottom:
```

```apache
<VirtualHost *:80>
    ServerName iform.local
    ServerAlias 192.168.1.100
    DocumentRoot "/Applications/XAMPP/xamppfiles/htdocs/iform/web"
    # Or Linux: /opt/lampp/htdocs/iform/web

    <Directory "/Applications/XAMPP/xamppfiles/htdocs/iform/web">
        Options -Indexes +FollowSymLinks +MultiViews
        AllowOverride All
        Require all granted
    </Directory>

    # Yii2 URL rewriting
    <IfModule mod_rewrite.c>
        RewriteEngine on
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*) /index.php?/$1 [L]
    </IfModule>

    ErrorLog "/Applications/XAMPP/xamppfiles/logs/iform_error.log"
    CustomLog "/Applications/XAMPP/xamppfiles/logs/iform_access.log" common
</VirtualHost>
```

### Windows: Edit httpd-vhosts.conf

```
C:\xampp\apache\conf\extra\httpd-vhosts.conf

Add:
<VirtualHost *:80>
    ServerName iform.local
    ServerAlias 192.168.1.100
    DocumentRoot "C:\xampp\htdocs\iform\web"

    <Directory "C:\xampp\htdocs\iform\web">
        Options -Indexes +FollowSymLinks +MultiViews
        AllowOverride All
        Require all granted
    </Directory>

    <IfModule mod_rewrite.c>
        RewriteEngine on
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*) /index.php?/$1 [L]
    </IfModule>

    ErrorLog "C:\xampp\apache\logs\iform_error.log"
    CustomLog "C:\xampp\apache\logs\iform_access.log" common
</VirtualHost>
```

### Enable Modules

```bash
# macOS/Linux
sudo nano /Applications/XAMPP/xamppfiles/etc/httpd/httpd.conf
# Uncomment (or add):
# LoadModule rewrite_module modules/mod_rewrite.so
# LoadModule headers_module modules/mod_headers.so

# Windows: Edit C:\xampp\apache\conf\httpd.conf
```

### Restart Apache

```bash
# macOS
cd /Applications/XAMPP/xamppfiles
./bin/apachectl restart

# Linux
sudo /opt/lampp/lampp restart

# Windows
# Use XAMPP Control Panel: click "Restart" for Apache
```

---

## 4️⃣ Configure MySQL

### Create Database

```bash
# macOS/Linux
/Applications/XAMPP/bin/mysql -u root

# Or Linux
/opt/lampp/bin/mysql -u root

# Or Windows
# Open phpMyAdmin: http://localhost/phpmyadmin
```

### SQL Commands

```sql
-- Create database
CREATE DATABASE iform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'iform'@'localhost' IDENTIFIED BY 'strong_password_here';

-- Grant privileges
GRANT ALL PRIVILEGES ON iform.* TO 'iform'@'localhost';
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

### Import Database Schema

```bash
# From iForm migrations
/Applications/XAMPP/bin/mysql -u iform -p iform < /path/to/iform/schema.sql

# Or run migrations
cd /Applications/XAMPP/xamppfiles/htdocs/iform
php yii migrate
```

---

## 5️⃣ Configure Yii2 for XAMPP

### Update config/db.php

```php
<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=iform;charset=utf8mb4',
    'username' => 'iform',
    'password' => 'strong_password_here',  // dari setup di atas
    'charset' => 'utf8mb4',
    'tablePrefix' => '',
];
```

### Update web/index.php

```php
<?php
// macOS/Linux:
// Define path constants
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

// Production settings
error_reporting(0);
ini_set('display_errors', false);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
```

---

## 6️⃣ Install PHP Dependencies

### Run Composer

```bash
# Navigate ke project
cd /Applications/XAMPP/xamppfiles/htdocs/iform
# Or: cd /opt/lampp/htdocs/iform

# Install dependencies
composer install --no-dev --optimize-autoloader

# Or update
composer update --no-dev
```

### Check PHP Version

```bash
# XAMPP includes PHP, verify
/Applications/XAMPP/bin/php -v
# Or: /opt/lampp/bin/php -v
# Or Windows: C:\xampp\php\php.exe -v

# Show: PHP 7.4+ required
```

---

## 7️⃣ Setup Static Server IP


**macOS:**

```bash
System Preferences → Network → WiFi → Advanced → TCP/IP
Configure IPv4 → Manual
IP: 192.168.1.100
Subnet Mask: 255.255.255.0
Router: 192.168.1.1
DNS: 192.168.1.1 (local)
```

**Linux:**

```bash
# Edit /etc/netplan/00-installer-config.yaml
network:
  version: 2
  ethernets:
    eth0:
      dhcp4: no
      addresses: [192.168.1.100/24]
      routes:
        - to: 0.0.0.0/0
          via: 192.168.1.1
      nameservers:
        addresses: [192.168.1.1]

# Apply
sudo netplan apply
```

**Windows:**

```
Control Panel → Network & Internet → Network Connections
Right-click WiFi → Properties → IPv4 Properties
Set Static IP: 192.168.1.100
Subnet Mask: 255.255.255.0
Default Gateway: 192.168.1.1
DNS: 192.168.1.1
```

---

## 8️⃣ Configure Firewall

### macOS UFW (via brew)

```bash
# Install
brew install ufw

# Enable
sudo ufw enable

# Allow local network ONLY
sudo ufw default deny incoming
sudo ufw allow from 192.168.1.0/24 to any port 80
sudo ufw allow from 192.168.1.0/24 to any port 443
sudo ufw allow from 192.168.1.0/24 to any port 3306

# Status
sudo ufw status numbered
```

### Linux UFW

```bash
# Enable
sudo ufw enable

# Rules
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow WiFi network ONLY
sudo ufw allow from 192.168.1.0/24 to any port 80 comment 'HTTP from WiFi'
sudo ufw allow from 192.168.1.0/24 to any port 443 comment 'HTTPS from WiFi'
sudo ufw allow from 192.168.1.0/24 to any port 3306 comment 'MySQL from WiFi'

# Deny specific
sudo ufw deny 80
sudo ufw deny 443
sudo ufw deny 3306

# Status
sudo ufw status numbered
```

### Windows Firewall

```powershell
# Run as Admin

# Disable internet-facing rules
Set-NetFirewallRule -DisplayName "*World Wide Web*" -Enabled False

# Allow LOCAL network only
New-NetFirewallRule `
  -DisplayName "iForm API - WiFi Only" `
  -Direction Inbound `
  -Action Allow `
  -Protocol TCP `
  -LocalPort 80,443,3306 `
  -RemoteAddress 192.168.1.0/24

# Verify
Get-NetFirewallRule -DisplayName "*iForm*"
```

---

## 9️⃣ Test Installation

### Test Apache

```bash
# From server computer
curl http://localhost

# From another device on WiFi
curl http://192.168.1.100

# Should see Yii2 login page HTML
```

### Test MySQL

```bash
# Connect to MySQL
/Applications/XAMPP/bin/mysql -u iform -p iform

mysql> SELECT COUNT(*) FROM user;
mysql> SHOW TABLES;
mysql> EXIT;
```

### Test Yii2 Commands

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/iform

# Run migrations
php yii migrate

# Initialize RBAC
php yii rbac/init

# List users
php yii rbac/list-users

# Create admin
php yii rbac/assign admin_username admin
```

### Access from Browser

Open browser on SAME computer:

```
http://localhost/iform   (should work)
http://192.168.1.100     (should work)
```

From another device on WiFi:

```
http://192.168.1.100     (should work)
```

---

## 🔟 Configure Android App

### Update API URL

```kotlin
// BuildConfig.kt
object ApiConfig {
    const val BASE_URL = "http://192.168.1.100"  // Server static IP
    const val API_ENDPOINT = "$BASE_URL/api"
    const val TIMEOUT_SECONDS = 10L
    const val REQUIRE_INTERNET = false  // Offline capability
}
```

### Create API Tokens

Via admin panel:

```
http://192.168.1.100/admin
→ API Clients
→ Add new client
→ Store token securely
```

Or via MySQL:

```bash
/Applications/XAMPP/bin/mysql -u iform -p iform

INSERT INTO api_client (device_id, device_name, token, is_active, created_at)
VALUES ('IMEI_HERE', 'Operator 1', 'TOKEN_HERE', 1, UNIX_TIMESTAMP());
```

---

## Testing Network Setup

### Verify Isolation

```bash
# From server
ping 8.8.8.8             # Should FAIL (no internet)
ping 192.168.1.100       # Should work (self)
netstat -an | grep :80   # Port 80 must be open

# From Android phone (via adb)
adb shell
ping 192.168.1.100       # Should work
ping 8.8.8.8             # Should FAIL
netstat -an | grep 80    # See Apache

# Test API
adb shell
curl -H "Authorization: Bearer TOKEN" \
     http://192.168.1.100/api/checksheet/by-mesin?no_mesin=M001
```

---

## Monitoring & Logs

### Apache Logs

```bash
# Access logs
tail -f /Applications/XAMPP/xamppfiles/logs/iform_access.log

# Error logs
tail -f /Applications/XAMPP/xamppfiles/logs/iform_error.log

# Or Linux
tail -f /opt/lampp/logs/access_log
tail -f /opt/lampp/logs/error_log
```

### MySQL Logs

```bash
# macOS
tail -f /Applications/XAMPP/xamppfiles/data/mysql_error.log

# Linux
tail -f /opt/lampp/var/mysql/error.log

# Windows
C:\xampp\mysql\data\mysql_error.log
```

### Yii2 Application Logs

```bash
# Runtime logs
tail -f /Applications/XAMPP/xamppfiles/htdocs/iform/runtime/logs/app.log

# API log (if configured)
tail -f /Applications/XAMPP/xamppfiles/htdocs/iform/runtime/logs/api.log
```

---

## Troubleshooting

### Issue: Apache won't start

```bash
# Check port already in use
netstat -an | grep :80

# Kill process on port 80
sudo lsof -i :80
sudo kill -9 PID

# Check Apache config
/Applications/XAMPP/bin/apachectl configtest
# Should show: Syntax OK
```

### Issue: PHP extensions missing

```bash
# Check installed extensions
php -m | grep -i mysql
php -m | grep -i json
php -m | grep -i curl

# Enable if missing (edit php.ini)
/Applications/XAMPP/xamppfiles/etc/php.ini
# Uncomment: extension=php_mysql.dll
```

### Issue: Cannot write to runtime directory

```bash
# Fix permissions
sudo chmod -R 755 /Applications/XAMPP/xamppfiles/htdocs/iform/runtime
sudo chown -R daemon:daemon /Applications/XAMPP/xamppfiles/htdocs/iform
```

### Issue: Android can't reach server

```bash
# Verify server IP is static
ifconfig | grep 192.168.1.100

# Verify Apache listening
netstat -an | grep :80

# Verify firewall not blocking
sudo ufw status

# Test from Android
adb shell: ping 192.168.1.100
```

---

## Daily Startup

### Manual Startup (Recommended)

```bash
# 1. Start XAMPP
# macOS
/Applications/XAMPP/xamppfiles/bin/apachectl start
/Applications/XAMPP/xamppfiles/bin/mysqld_safe &

# 2. Verify running
ps aux | grep apache
ps aux | grep mysql

# 3. Test
curl http://192.168.1.100

# 4. Done!
```

### Or use XAMPP Control Panel

```bash
# macOS
/Applications/XAMPP/manager-osx

# Or Linux
sudo /opt/lampp/manager-linux-x64.run

# Or Windows
C:\xampp\xampp-control.exe
```

---

## Backup Strategy

### Daily Database Backup

```bash
#!/bin/bash
# Save as: /Applications/XAMPP/xamppfiles/htdocs/iform/backup.sh

BACKUP_DIR="/Applications/XAMPP/xamppfiles/htdocs/iform/backups"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

/Applications/XAMPP/bin/mysqldump -u iform -ppassword iform > \
    $BACKUP_DIR/iform_backup_$DATE.sql

echo "Backup created: $BACKUP_DIR/iform_backup_$DATE.sql"

# Cleanup old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
```

### Make executable and schedule

```bash
chmod +x /Applications/XAMPP/xamppfiles/htdocs/iform/backup.sh

# macOS: Add to crontab
crontab -e
# Add: 0 2 * * * /Applications/XAMPP/xamppfiles/htdocs/iform/backup.sh

# Linux: Same as above
```

---

## Production Checklist

### Before Going Live

- [ ] Server static IP configured (192.168.1.100)
- [ ] Apache virtual host configured
- [ ] MySQL database created & migrated
- [ ] Yii2 app accessible via http://192.168.1.100
- [ ] API tokens created for Android devices
- [ ] Firewall rules configured (allow ONLY WiFi)
- [ ] WiFi router WAN disabled (no internet)
- [ ] Network isolation verified (no external access)
- [ ] Logs are writable
- [ ] Backup script working
- [ ] Android apps can submit forms
- [ ] Data persists in database
- [ ] Admin can manage users & roles
- [ ] Offline mode tested

---


| ---------------- | ---------- | ----------------- |
| Installation     | ~5 minutes | ~5 minutes        |
| Setup            | Manual     | Automated         |
| Database         | Included   | Included          |
| Language         | PHP 7.4+   | PHP 7.4+          |
| Web Server       | Apache 2.4 | Apache 2.4        |
| Isolation        | File-based | Container-based   |
| Restart          | Manual     | Auto              |
| Performance      | Good       | Same              |
| Production Ready | With setup | Ready             |

---

## Next Steps

1. Install XAMPP
2. Copy iForm project
3. Configure Apache vhost
4. Create MySQL database
5. Set static IP
6. Configure firewall
7. Test locally
8. Deploy Android app
9. Test form submission
10. Monitor logs

---

_XAMPP Setup for Factory Network_
_March 1, 2026_
