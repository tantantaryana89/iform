╔═══════════════════════════════════════════════════════════╗
║ FACTORY DEPLOYMENT CHECKLIST ║
║ Printable Reference for Production Setup ║
╚═══════════════════════════════════════════════════════════╝

PHASE 1: SERVER SETUP (Day 1)
═══════════════════════════════════════════════════════════

Equipment Checklist:
☐ Server computer (Windows/Mac/Linux)
☐ WiFi router (isolated, no internet)
☐ Android phones (operator devices)
☐ Admin laptop (for management)
☐ Network cables/ethernet (backup)

Network Configuration:
☐ Factory WiFi network name: ******\_\_\_\_******
☐ Factory WiFi password: ******\_\_\_\_******
☐ Network subnet: 192.168.1.0/24
☐ Server static IP: 192.168.1.100
☐ IP address configured? ****\_\_\_\_**** - Windows: Settings > Network > Static IP - macOS: System Prefs > Network > IPv4 - Linux: /etc/netplan/00-installer-config.yaml

Firewall Rules:
☐ Firewall enabled
☐ Allow incoming: 192.168.1.0/24 port 80/443
☐ Allow incoming: 192.168.1.0/24 port 3306 (optional)
☐ Deny incoming: All other sources
☐ Deny outgoing: All (hardlock internet)
☐ Firewall rules verified? ****\_\_\_\_****

WiFi Router Settings:
☐ Router WAN/Internet: DISABLED (no gateway)
☐ DHCP enabled: Yes
☐ DHCP range: 192.168.1.50-200
☐ No internet connectivity: VERIFIED

═══════════════════════════════════════════════════════════

═══════════════════════════════════════════════════════════

Prerequisites:
☐ iForm code copied to: /Applications/MAMP/htdocs/iform

Initial Setup:
☐ cp .env.example .env
☐ Edit .env file: - Change MYSQL_ROOT_PASSWORD to: ******\_\_\_\_****** - Change MYSQL_PASSWORD to: ******\_\_\_\_****** - Set APP_URL to: http://192.168.1.100

Start Application:
☐ Choose: 2 (Production)
☐ Wait ~60 seconds for startup
☐ App accessible at: http://192.168.1.100

Test Application:
☐ Open browser: http://192.168.1.100
☐ Yii2 login page visible
☐ Can login with default credentials
☐ Database responding

═══════════════════════════════════════════════════════════

PHASE 3: USER & AUTH SETUP (Day 1)
═══════════════════════════════════════════════════════════

Create Admin User:
☐ First admin user created

Initialize RBAC:
☐ Admin roles created (operator, supervisor, admin)
☐ Permissions assigned
☐ Verify roles: mysql> SELECT \* FROM auth_item;

Create Database Backup:

═══════════════════════════════════════════════════════════

PHASE 4: ANDROID CONFIGURATION (Days 1-2)
═══════════════════════════════════════════════════════════

API Token Generation:
☐ Admin panel: http://192.168.1.100
☐ Menu: Admin > API Clients
☐ Add client for each Android phone:

     Device 1:
       Name: ________________
       IMEI: ________________
       Generated Token: ________________

     Device 2:
       Name: ________________
       IMEI: ________________
       Generated Token: ________________

     Device 3:
       Name: ________________
       IMEI: ________________
       Generated Token: ________________

Android App Configuration:
☐ Update BuildConfig.kt:
const val BASE_URL = "http://192.168.1.100"
☐ Store API token (encrypted):
EncryptedSharedPreferences.putString("api_token", TOKEN)
☐ Test app login
☐ Test form submission

═══════════════════════════════════════════════════════════

PHASE 5: TESTING (Day 2)
═══════════════════════════════════════════════════════════

Network Verification:
☐ Server IP is static: ping 192.168.1.100 (works)
☐ No internet access: ping 8.8.8.8 (should FAIL)
☐ No external: ping google.com (should FAIL)
☐ Firewall rules active: ufw status (shows rules)

Android Device Testing:
☐ Device 1 connects to WiFi
☐ Opens app: can login
☐ Submits test form
☐ Data appears in admin dashboard
☐ Device 1: no internet: ping 8.8.8.8 (FAIL)

☐ Device 2 connects to WiFi
☐ Opens app: can login
☐ Submits test form
☐ Data appears in admin dashboard

☐ Device 3 connects to WiFi
☐ Opens app: can login
☐ Submits test form
☐ Data appears in admin dashboard

Database Testing:
☐ Multiple devices see same templates
☐ Excel export works
☐ Backup/restore works

Offline Mode Testing:
☐ Disable WiFi on phone
☐ App shows offline notice
☐ Can still fill forms locally
☐ Enable WiFi
☐ Data automatically syncs
☐ Forms appear in admin dashboard

═══════════════════════════════════════════════════════════

PHASE 6: PRODUCTION VALIDATION (Day 2)
═══════════════════════════════════════════════════════════

Security Verification:
☐ No internet gateway on WiFi router
☐ Firewall blocking external access
☐ All devices registered (no unknown IPs)
☐ API tokens securely stored
☐ Network logs created & monitored
☐ Database backup exists

Performance Testing:
☐ Multiple concurrent submissions work
☐ Response time acceptable (< 2 seconds)
☐ No database errors

Documentation:
☐ Network diagram updated
☐ Access credentials documented (secure vault)
☐ Troubleshooting guide available
☐ Emergency contacts listed
☐ Runbook created for daily operations

Staff Training:
☐ Operators trained on app usage
☐ Supervisor trained on admin panel
☐ IT staff trained on troubleshooting
☐ Emergency procedures documented

═══════════════════════════════════════════════════════════

ONGOING MAINTENANCE (Daily/Weekly)
═══════════════════════════════════════════════════════════

Daily:
☐ Verify WiFi network status
☐ All devices connecting normally

Weekly:
☐ Backup database:
☐ Review network logs for anomalies
☐ Test form submissions across all devices

Monthly:
☐ Full system backup
☐ Review and update security policies
☐ Update documentation if changes made
☐ Performance review & optimization
☐ Staff retraining if needed

═══════════════════════════════════════════════════════════

TROUBLESHOOTING QUICK REFERENCE
═══════════════════════════════════════════════════════════

Problem: Android can't connect to server
Solution:
☐ Verify server IP is static
☐ Ping server from phone: ping 192.168.1.100
☐ Check WiFi network connection
☐ Verify firewall not blocking

Problem: Android has internet (shouldn't!)
Solution:
☐ Check router WAN gateway disabled
☐ Verify firewall blocking outgoing
☐ Check DNS not configured (use local only)

Problem: Data not saving to database
Solution:
☐ Check API token is correct

Problem: Server won't start
Solution:
☐ Check ports not in use: netstat -an | grep :80

═══════════════════════════════════════════════════════════

EMERGENCY PROCEDURES
═══════════════════════════════════════════════════════════

If Server Crashes:
☐ Wait 30 seconds

If Database Corrupted:
☐ Restore: mysql < backup_latest.sql
☐ Verify data exists

If Unauthorized Access Detected:
☐ Check network logs: tcpdump -i any port 80/443
☐ Check connected devices: arp -a
☐ Shutdown suspect device
☐ Review firewall logs
☐ Block unauthorized MAC address

═══════════════════════════════════════════════════════════

CONTACTS & ESCALATION
═══════════════════════════════════════════════════════════

IT Support:
Name: ******\_\_\_\_******
Phone: ******\_\_\_\_******
Email: ******\_\_\_\_******
Available: ******\_\_\_\_******

App Administrator:
Name: ******\_\_\_\_******
Phone: ******\_\_\_\_******
Email: ******\_\_\_\_******

Server Location:
Building: ******\_\_\_\_******
Room: ******\_\_\_\_******
Network Switch: ******\_\_\_\_******
Contact Person: ******\_\_\_\_******

═══════════════════════════════════════════════════════════

CRITICAL FILES LOCATIONS
═══════════════════════════════════════════════════════════

Documentation:
• XAMPP_QUICK_START.md - Quick start
• FACTORY_SETUP_QUICK_REF.md - This setup
• NETWORK_SECURITY.md - Network details
• XAMPP_QUICK_START.md - Detailed guide

Configuration:
• .env - Production configuration

Backups:
• /backups/backup\_\*.sql - Daily backups
• Location: ******\_\_\_\_******

Logs:
• Location: ******\_\_\_\_******

═══════════════════════════════════════════════════════════

SIGN-OFF
═══════════════════════════════════════════════════════════

Setup Completed By: **********\_\_**********
Date: **********\_\_**********
Verified By: **********\_\_**********
Date: **********\_\_**********

Notes:

---

---

---

PRODUCTION STATUS: ☐ READY FOR DEPLOYMENT

═══════════════════════════════════════════════════════════

Keep this checklist printed and available for:
• New staff training
• Troubleshooting reference
• Emergency procedures
• Monthly verification

Good luck! 🚀

═══════════════════════════════════════════════════════════
