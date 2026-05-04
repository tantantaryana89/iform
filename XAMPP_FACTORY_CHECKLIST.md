🎯 FACTORY SETUP - XAMPP CHECKLIST

═══════════════════════════════════════════════════════════════════
XAMPP VERSION - Printable Setup Checklist
═══════════════════════════════════════════════════════════════════

LOCATION: [Write location] ************\_\_\_************
DATE: [Write date] ************\_\_\_************
PERSON: [Write name] ************\_\_\_************

═══════════════════════════════════════════════════════════════════

PHASE 1: XAMPP INSTALLATION
═══════════════════════════════════════════════════════════════════

☐ 1.1 Check XAMPP version
Current: ******\_\_\_\_******
Need: 8.0+

☐ 1.2 Verify Apache installed
Command: $ /Applications/XAMPP/bin/httpd -v
Result: ******\_\_\_\_******

☐ 1.3 Verify MySQL installed
Command: $ /Applications/XAMPP/bin/mysql --version
Result: ******\_\_\_\_******

☐ 1.4 Verify PHP version
Command: $ /Applications/XAMPP/init.sh && php -v
Result: PHP 7.4+ ✅

☐ 1.5 Verify required PHP extensions
Extensions needed:
☐ json
☐ pdo_mysql
☐ curl
☐ gd
☐ intl

      Command: php -m | grep -E 'json|pdo_mysql|curl'
      Result: ________________

═══════════════════════════════════════════════════════════════════

PHASE 2: PROJECT DEPLOYMENT
═══════════════════════════════════════════════════════════════════

☐ 2.1 Copy iForm to htdocs
Path: /Applications/XAMPP/xamppfiles/htdocs/iform
Status: ☐ Done

☐ 2.2 Verify file permissions
Commands:
$ ls -la /Applications/XAMPP/xamppfiles/htdocs/iform

      Check for (Owner/Group):
      ☐ iform/ owned by _www or www-data
      ☐ runtime/ writable
      ☐ web/ readable


☐ 2.3 Create runtime directory
Command: $ mkdir -p /Applications/XAMPP/xamppfiles/htdocs/iform/runtime
Status: ☐ Done

☐ 2.4 Fix permissions
Commands:
$ chmod -R 755 /Applications/XAMPP/xamppfiles/htdocs/iform
$ chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/iform/runtime
$ chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/iform/web/assets
Status: ☐ Done

═══════════════════════════════════════════════════════════════════

PHASE 3: DATABASE SETUP
═══════════════════════════════════════════════════════════════════

☐ 3.1 Start MySQL
Command: /Applications/XAMPP/manager-osx
Status: MySQL ☐ Running

☐ 3.2 Connect to MySQL
Command: $ /Applications/XAMPP/bin/mysql -u root
Result: mysql> **\_\_\_\_**

☐ 3.3 Create database
Command: CREATE DATABASE iform_factory CHARACTER SET utf8mb4;
Status: ☐ Created

☐ 3.4 Create database user
Commands:
CREATE USER 'iform_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON iform_factory.\* TO 'iform_user'@'localhost';
FLUSH PRIVILEGES;
Status: ☐ Created

☐ 3.5 Verify database
Command: $ /Applications/XAMPP/bin/mysql -u iform_user -p iform_factory
Password: [you just created]
Result: mysql> **\_\_\_\_**

═══════════════════════════════════════════════════════════════════

PHASE 4: APACHE CONFIGURATION
═══════════════════════════════════════════════════════════════════

☐ 4.1 Edit Apache vhost config
File: /Applications/XAMPP/etc/httpd-vhosts.conf

☐ 4.2 Add vhost entry (at end of file)

      <VirtualHost *:80>
          ServerName iform.local
          DocumentRoot "/Applications/XAMPP/xamppfiles/htdocs/iform/web"

          <Directory "/Applications/XAMPP/xamppfiles/htdocs/iform/web">
              AllowOverride All
              Require all granted

              RewriteEngine on
              RewriteCond %{REQUEST_FILENAME} !-f
              RewriteCond %{REQUEST_FILENAME} !-d
              RewriteRule ^(.*) /index.php?/$1 [L]
          </Directory>

          <IfModule mod_headers.c>
              Header set Access-Control-Allow-Origin "*"
              Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
              Header set Access-Control-Allow-Headers "Content-Type, Authorization"
          </IfModule>

          ErrorLog "/Applications/XAMPP/logs/iform_error.log"
          CustomLog "/Applications/XAMPP/logs/iform_access.log" combined
      </VirtualHost>

      Status: ☐ Added


☐ 4.3 Update localhost vhost (add to existing <VirtualHost \*:80>)

      Also add server address:
      ServerAlias 192.168.1.100

      Status: ☐ Updated


☐ 4.4 Enable mod_rewrite
Command: $ /Applications/XAMPP/bin/apachectl modules | grep rewrite
Result: rewrite_module (shared) **\_\_\_\_**

      If NOT enabled, edit: /Applications/XAMPP/etc/httpd.conf
      Find and uncomment: LoadModule rewrite_module modules/mod_rewrite.so

      Status: ☐ Enabled


☐ 4.5 Test Apache config
Command: $ /Applications/XAMPP/bin/apachectl configtest
Result: Syntax OK **\_\_\_\_**

═══════════════════════════════════════════════════════════════════

PHASE 5: YII2 CONFIGURATION
═══════════════════════════════════════════════════════════════════

☐ 5.1 Edit web config
File: /Applications/XAMPP/xamppfiles/htdocs/iform/config/web.php

      Check/Update:
      ☐ 'dsn' => 'mysql:host=127.0.0.1;dbname=iform_factory'
      ☐ 'user' => 'iform_user'
      ☐ 'password' => 'your_secure_password'
      ☐ 'charset' => 'utf8mb4'

      Status: ☐ Done


☐ 5.2 Edit params config
File: /Applications/XAMPP/xamppfiles/htdocs/iform/config/params.php

      Set:
      ☐ 'apiBaseUrl' => 'http://192.168.1.100'
      ☐ 'appName' => 'iForm Factory'

      Status: ☐ Done

═══════════════════════════════════════════════════════════════════

PHASE 6: DEPENDENCIES & MIGRATIONS
═══════════════════════════════════════════════════════════════════

☐ 6.1 Install Composer dependencies
Command: $ cd /Applications/XAMPP/xamppfiles/htdocs/iform
$ php composer install --no-dev

      Result: ________
      Status: ☐ Done


☐ 6.2 Run database migrations
Command: $ php yii migrate

      Output should show:
      ✓ m231128_000001_create_user_table
      ✓ m241121_000001_create_form_template_table
      ✓ m250214_000000_create_rbac_tables
      ✓ m250214_000001_add_template_id_to_form_result

      Status: ☐ All migrated


☐ 6.3 Initialize RBAC
Command: $ php yii rbac/init

      Output: RBAC tables initialized
      Status: ☐ Done


☐ 6.4 Create admin user
Command: $ php yii user/create-admin

      Username: admin
      Password: [set secure password] ________________
      Status: ☐ Created

═══════════════════════════════════════════════════════════════════

PHASE 7: STATIC IP CONFIGURATION
═══════════════════════════════════════════════════════════════════

☐ 7.1 Get current network info
Command: $ ifconfig | grep -A 5 en0

      Current IP: ________________
      Subnet: ________________
      Gateway: ________________

☐ 7.2 Set static IP (macOS)
For macOS Sierra and later:

      System Preferences → Network → Advanced...

      TCP/IP tab:
      ☐ IPv4 Address: 192.168.1.100
      ☐ Subnet Mask: 255.255.255.0
      ☐ Router: [Your WiFi router IP]
      ☐ DNS Servers: [Your router IP]

      Status: ☐ Set


☐ 7.3 Verify static IP assignment
Command: $ ifconfig | grep "inet "

      Result: inet 192.168.1.100 ________
      Status: ☐ Verified


☐ 7.4 Restart Apache
Command: $ /Applications/XAMPP/manager-osx (Stop/Start Apache)
Status: ☐ Restarted

☐ 7.5 Test access by IP
Command: $ curl http://192.168.1.100

      Result should show HTML (login page) ________
      Status: ☐ Working

═══════════════════════════════════════════════════════════════════

PHASE 8: FIREWALL CONFIGURATION
═══════════════════════════════════════════════════════════════════

☐ 8.1 Check firewall status
Command: $ sudo /usr/libexec/ApplicationFirewall/socketfilterfw -getglobalstate

      Status: ☐ On / ☐ Off


☐ 8.2 Install UFW (Uncomplicated Firewall)
Command: $ brew install ufw
Status: ☐ Installed

      OR use macOS built-in firewall:
      System Prefs → Security & Privacy → Firewall
      ☐ Enabled


☐ 8.3 Configure firewall rules (UFW)
Commands:
$ sudo ufw enable
$ sudo ufw reset
$ sudo ufw default deny incoming
$ sudo ufw default allow outgoing

      Allow API access (HTTP/HTTPS):
      $ sudo ufw allow from 192.168.1.0/24 to any port 80
      $ sudo ufw allow from 192.168.1.0/24 to any port 443

      Allow MySQL (optional, for admin):
      $ sudo ufw allow from 192.168.1.0/24 to any port 3306

      Status: ☐ Configured


☐ 8.4 Verify firewall rules
Command: $ sudo ufw status verbose

      Should show:
      ✓ 80/tcp from 192.168.1.0/24
      ✓ 443/tcp from 192.168.1.0/24

      Status: ☐ Verified


☐ 8.5 Block all other traffic
Commands:
$ sudo ufw deny 80/tcp
$ sudo ufw deny 443/tcp
$ sudo ufw deny 3306/tcp

      Status: ☐ Blocked


☐ 8.6 Verify NO internet access
Command: $ ping 8.8.8.8
Expected: ❌ No response (timeout)
Result: **\_\_\_\_**
Status: ☐ Verified - No internet!

═══════════════════════════════════════════════════════════════════

PHASE 9: TESTING & VERIFICATION
═══════════════════════════════════════════════════════════════════

☐ 9.1 Access web interface (from any device on WiFi)
URL: http://192.168.1.100
Expected: Yii2 login page
Status: ☐ Accessible

☐ 9.2 Login with admin credentials
Username: admin
Password: [what you set]
Expected: Dashboard loads
Status: ☐ Works

☐ 9.3 Check form templates
Menu: Admin → Form Templates
Expected: List of templates
Status: ☐ Visible

☐ 9.4 Test API access (with token)
GET: http://192.168.1.100/api/checksheet/list
Header: Authorization: Bearer [your_api_token]
Expected: JSON response with list
Status: ☐ Works

☐ 9.5 Check database connectivity
Command: $ php yii db/query "SELECT COUNT(\*) FROM user"
Expected: Count of users
Result: **\_\_\_\_**
Status: ☐ Works

☐ 9.6 Verify logs directory
Command: $ tail -f /Applications/XAMPP/xamppfiles/htdocs/iform/runtime/logs/app.log
Expected: Log entries
Status: ☐ Working

☐ 9.7 Test WiFi access from another device
Device: [Tablet/Phone]
IP: [Device IP]
URL: http://192.168.1.100
Expected: Login page loads
Status: ☐ Works

☐ 9.8 Verify offline operation capability
Disable WiFi on device
Open previously cached form
Expected: Can fill form
Status: ☐ Works

═══════════════════════════════════════════════════════════════════

PHASE 10: ANDROID APP CONFIGURATION
═══════════════════════════════════════════════════════════════════

For each Android device:

Device 1:
☐ 10.1 Device name: ******\_\_\_\_******
☐ 10.2 Device IP: ******\_\_\_\_******
☐ 10.3 Set BASE_URL: http://192.168.1.100
☐ 10.4 Create API token
☐ 10.5 Store token in SharedPreferences
☐ 10.6 Test form download
☐ 10.7 Test form submission
☐ 10.8 Verify in admin panel
Status: ☐ Configured

Device 2:
☐ 10.1 Device name: ******\_\_\_\_******
☐ 10.2 Device IP: ******\_\_\_\_******
☐ 10.3 Set BASE_URL: http://192.168.1.100
☐ 10.4 Create API token
☐ 10.5 Store token in SharedPreferences
☐ 10.6 Test form download
☐ 10.7 Test form submission
☐ 10.8 Verify in admin panel
Status: ☐ Configured

Device 3:
☐ 10.1 Device name: ******\_\_\_\_******
☐ 10.2 Device IP: ******\_\_\_\_******
☐ 10.3 Set BASE_URL: http://192.168.1.100
☐ 10.4 Create API token
☐ 10.5 Store token in SharedPreferences
☐ 10.6 Test form download
☐ 10.7 Test form submission
☐ 10.8 Verify in admin panel
Status: ☐ Configured

═══════════════════════════════════════════════════════════════════

PHASE 11: PRODUCTION HARDENING
═══════════════════════════════════════════════════════════════════

☐ 11.1 Disable debug mode
File: /Applications/XAMPP/xamppfiles/htdocs/iform/web/index.php

      Change:
      defined('YII_DEBUG') or define('YII_DEBUG', false);
      defined('YII_ENV') or define('YII_ENV', 'prod');

      Status: ☐ Done


☐ 11.2 Set secure passwords
MySQL root: [Secure with > 16 chars] ******\_\_\_\_******
MySQL iform_user: [Secure with > 16 chars] ******\_\_\_\_******
Admin user: [Secure] ******\_\_\_\_******
Status: ☐ Changed

☐ 11.3 Disable directory listing
File: /Applications/XAMPP/etc/httpd.conf

      Add: Options -Indexes

      Status: ☐ Disabled


☐ 11.4 Enable .htaccess restrictions
Ensure htaccess restrictions prevent:
☐ Direct access to config/
☐ Direct access to runtime/
☐ Direct access to vendor/

      Status: ☐ Restricted


☐ 11.5 Backup initial database
Command: $ /Applications/XAMPP/bin/mysqldump -u root -p iform_factory > iform_factory_backup.sql
Status: ☐ Backed up

☐ 11.6 Setup log rotation
File: /Applications/XAMPP/logs/
Plan: Daily backup of iform_error.log
Status: ☐ Configured

☐ 11.7 Disable remote MySQL access
File: /Applications/XAMPP/etc/my.cnf

      Add: skip-external-locking
      Add: skip-name-resolve
      Add: bind-address = 127.0.0.1

      Status: ☐ Restricted

═══════════════════════════════════════════════════════════════════

PHASE 12: DAILY STARTUP PROCEDURE
═══════════════════════════════════════════════════════════════════

Create startup script: startup.sh

#!/bin/bash
echo "Starting XAMPP for iForm Factory..."
echo "======================================="

# Start XAMPP

echo "1. Starting XAMPP Manager..."
open /Applications/XAMPP/manager-osx

# Wait for services

echo "2. Waiting 10 seconds for services..."
sleep 10

# Verify Apache

echo "3. Checking Apache..."
curl -s http://192.168.1.100 | grep -q "login" && echo "✓ Apache OK" || echo "✗ Apache Failed"

# Verify MySQL

echo "4. Checking MySQL..."
/Applications/XAMPP/bin/mysql -u root -e "SELECT 1" > /dev/null 2>&1 && echo "✓ MySQL OK" || echo "✗ MySQL Failed"

# Display URL

echo ""
echo "======================================="
echo "iForm is ready at:"
echo "✓ http://192.168.1.100"
echo "✓ http://iform.local (if DNS configured)"
echo "======================================="

☐ 12.1 Save startup script
Path: /Applications/XAMPP/
Status: ☐ Saved

☐ 12.2 Make executable
Command: $ chmod +x /Applications/XAMPP/startup.sh
Status: ☐ Executable

☐ 12.3 Create daily checklist
Every morning before factory:
☐ Run startup.sh
☐ Verify Apache running
☐ Verify MySQL running
☐ Test http://192.168.1.100 access
☐ Check firewall still active

      Status: ☐ Procedure in place

═══════════════════════════════════════════════════════════════════

PHASE 13: EMERGENCY PROCEDURES
═══════════════════════════════════════════════════════════════════

If Apache stops:
$ /Applications/XAMPP/bin/apachectl restart

If MySQL stops:
$ /Applications/XAMPP/manager-osx (GUI to restart)

If web is slow:
$ tail -f /Applications/XAMPP/logs/iform_error.log
Check for PHP errors

If database corrupted:
$ /Applications/XAMPP/bin/mysqldump -u root -p iform_factory > backup.sql
$ mysql -u root -p iform_factory < /path/to/recent/backup.sql

If can't login:
Reset password:
$ php yii user/change-password admin new_password

Escalation contacts:
☐ Technical lead: ******\_\_\_\_******
☐ IT support: ******\_\_\_\_******
☐ Factory manager: ******\_\_\_\_******

═══════════════════════════════════════════════════════════════════

FINAL CHECKLIST
═══════════════════════════════════════════════════════════════════

Overall Status:

Phase 1 (Installation): ☐ ✓ Complete
Phase 2 (Deployment): ☐ ✓ Complete
Phase 3 (Database): ☐ ✓ Complete
Phase 4 (Apache): ☐ ✓ Complete
Phase 5 (Yii2): ☐ ✓ Complete
Phase 6 (Dependencies): ☐ ✓ Complete
Phase 7 (Static IP): ☐ ✓ Complete
Phase 8 (Firewall): ☐ ✓ Complete
Phase 9 (Testing): ☐ ✓ Complete
Phase 10 (Android): ☐ ✓ Complete
Phase 11 (Security): ☐ ✓ Complete
Phase 12 (Startup): ☐ ✓ Complete
Phase 13 (Emergency): ☐ ✓ Complete

═══════════════════════════════════════════════════════════════════

SIGN OFF
═══════════════════════════════════════════════════════════════════

Setup completed by: ******\_\_\_\_****** Date: ****\_\_****

Verified by: ******\_\_\_\_****** Date: ****\_\_****

Factory manager approval: ******\_\_\_\_****** Date: ****\_\_****

═══════════════════════════════════════════════════════════════════

NOTES & ISSUES

Problem 1:
Description: ******************\_\_\_\_******************
Solution: ******************\_\_\_\_******************
Status: ☐ Resolved / ☐ Pending

Problem 2:
Description: ******************\_\_\_\_******************
Solution: ******************\_\_\_\_******************
Status: ☐ Resolved / ☐ Pending

Additional Notes:

---

---

═══════════════════════════════════════════════════════════════════

QUICK REFERENCE

Web Access: http://192.168.1.100
Admin user: admin
Database: iform_factory (user: iform_user)
XAMPP path: /Applications/XAMPP
iForm path: /Applications/XAMPP/xamppfiles/htdocs/iform
Apache config: /Applications/XAMPP/etc/httpd-vhosts.conf
MySQL command: /Applications/XAMPP/bin/mysql
PHP command: php (from terminal)
Logs: /Applications/XAMPP/xamppfiles/htdocs/iform/runtime/logs/
Backups: Keep in: ******\_\_\_\_******

═══════════════════════════════════════════════════════════════════

PDF Print Tips:
✓ Print double-sided to save paper
✓ Use pen to check off items
✓ Keep this checklist with server computer
✓ Update notes section with findings
✓ Sign and file for compliance

═══════════════════════════════════════════════════════════════════
