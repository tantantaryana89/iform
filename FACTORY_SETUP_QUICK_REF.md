⚡ QUICK REFERENCE - Factory Network Configuration

═══════════════════════════════════════════════════════════════

📍 YOUR SETUP

Server Computer (Windows/Mac/Linux)
└─ Static IP: 192.168.1.100 (or your factory subnet)
└─ Port: 80 (HTTP) - connected to factory WiFi

Factory WiFi Network
├─ SSID: Factory-Production
├─ Subnet: 192.168.1.0/24 (or your subnet)
└─ NO INTERNET (hardwired/isolated)

Android Phones (Operator Devices)
├─ Connected to: Factory WiFi
├─ Server URL: http://192.168.1.100/api
└─ API Token: Unique per device (admin assigns)

═══════════════════════════════════════════════════════════════

🔧 SETUP CHECKLIST (In Order)

1. SET STATIC IP FOR SERVER
   ──────────────────────────────
   ☐ Windows: Settings > Network > Static IP
   ☐ macOS: System Prefs > Network > Static IP
   ☐ Linux: /etc/netplan/00-installer-config.yaml

   Target IP: 192.168.1.100 (or factory subnet)
   Verify: $ ping 192.168.1.100

   See: NETWORK_SECURITY.md § Setup Server Statis IP

2. CONFIGURE FIREWALL (Allow ONLY WiFi network)
   ──────────────────────────────
   ☐ Block all incoming except 192.168.1.0/24
   ☐ Block all outgoing (no internet!)

   Windows: Windows Defender Firewall (Inbound Rules)
   macOS: System Prefs > Security > Firewall
   Linux: UFW (sudo ufw default deny incoming)

   See: NETWORK_SECURITY.md § Firewall Rules

   ──────────────────────────────
   ☐ $ cd /Applications/MAMP/htdocs/iform
   ☐ $ cp .env.example .env

   See: XAMPP_QUICK_START.md § Step-by-step

4. ADD API TOKEN FOR ANDROID DEVICES
   ──────────────────────────────
   ☐ Login as admin
   ☐ Go to: Admin > API Clients
   ☐ Add each Android device with unique token
   ☐ Assign device IMEI or name
   ☐ Copy token to Android app config

   See: NETWORK_SECURITY.md § API Token Management

5. CONFIGURE ANDROID APP
   ──────────────────────────────
   ☐ Update BuildConfig.kt:
   const val BASE_URL = "http://192.168.1.100"
   ☐ Store API token in secure SharedPreferences
   ☐ Disable internet connectivity requirements
   ☐ Add offline mode (SQLite cache)

   See: NETWORK_SECURITY.md § Android App Configuration

6. TEST CONNECTION FROM FACTORY WIFI
   ──────────────────────────────
   ☐ Connect Android phone to Factory WiFi
   ☐ Open app and try to login
   ☐ Submit test form
   ☐ Check if data appears in admin dashboard

   See: API_TESTING.md § Testing

7. VERIFY NO INTERNET ACCESS
   ──────────────────────────────
   ☐ From server: ping 8.8.8.8 (should FAIL)
   ☐ From Android: ping google.com (should FAIL)
   ☐ Firewall blocking? Check settings

   See: NETWORK_SECURITY.md § Testing Network Security

═══════════════════════════════════════════════════════════════

🚀 QUICK START FOR FACTORY DEPLOYMENT

Step 1 - Set Server Static IP
$ sudo ifconfig en0 inet 192.168.1.100

Choose: 2 (Production)

Step 3 - Create Admin User

Step 4 - Add API Client
mysql> INSERT INTO api_client (device_id, device_name, token, is_active)
VALUES ('DEVICE_IMEI', 'Operator Room 1', 'TOKEN_HERE', 1);

Step 5 - Test from Android Phone
$ adb shell
$ ping 192.168.1.100 (should work)
$ ping 8.8.8.8 (should FAIL)

Step 6 - Verify Firewall
$ sudo ufw status (should show rules)

═══════════════════════════════════════════════════════════════

📊 EXPECTED NETWORK

┌─ FACTORY NETWORK (Isolated) ──────────────────┐
│ │
│ Server 192.168.1.100 ─┬─ Android Phone 1 │
│ Port 80/443 │ │
│ ├─ Android Phone 2 │
│ │ (Submit forms) │
│ │ │
│ └─ Admin Laptop │
│ (Manage app) │
│ │
│ ❌ NO INTERNET ❌ NO EXTERNAL ACCESS │
│ ❌ NO CLOUD CONNECTIVITY │
│ │
└───────────────────────────────────────────────┘

═══════════════════════════════════════════════════════════════

🔐 SECURITY ESSENTIALS

✅ Static Server IP
└─ Android app connects to: http://192.168.1.100/api

✅ Firewall Rules
└─ Allow ONLY: 192.168.1.0/24
└─ Deny: Everything else

✅ No Internet Gateway
└─ WiFi router has NO WAN/internet connection
└─ Hard-isolated from outside

✅ API Token Authentication
└─ Each device gets unique token
└─ Token stored in encrypted SharedPreferences

✅ Device Whitelisting (Optional)
└─ MAC address filtering in firewall
└─ IMEI registration in app

✅ Network Logging
└─ Check who connects: netstat -an
└─ Monitor for unauthorized access: tcpdump

═══════════════════════════════════════════════════════════════

📚 DETAILED DOCUMENTATION

For complete setup and troubleshooting, see:

1. NETWORK_SECURITY.md
   ├─ Setup Server Static IP
   ├─ Configure Firewall
   ├─ Android App Configuration
   ├─ API Token Management
   ├─ Offline Mode (fallback)
   ├─ Network Monitoring
   └─ Security Checklist

2. API_TESTING.md
   ├─ Testing from Android
   ├─ cURL examples
   ├─ Postman setup
   └─ Troubleshooting

3. XAMPP_QUICK_START.md
   └─ 3-minute quick start

4. XAMPP_QUICK_START.md
   └─ Detailed setup guide

═══════════════════════════════════════════════════════════════

⚠️ COMMON ISSUES & FIXES

ISSUE: Android can't reach server (http://192.168.1.100)
FIX: • Check server IP is static
• Verify Android on same WiFi network
• Check firewall not blocking port 80
• Restart app after IP change

ISSUE: Android can access internet (not allowed!)
FIX: • Disable WiFi router WAN/gateway
• Check firewall rules
• See NETWORK_SECURITY.md § Testing Network Security

ISSUE: Server can reach external internet (leak!)
FIX: • Block outgoing traffic: sudo ufw deny out to any
• Add exception for local: sudo ufw allow out 192.168.1.0/24
• Check router firewall settings

ISSUE: Data not syncing between devices
• Verify both phones connected same WiFi
• Check API token is correct
• See API_TESTING.md for debug steps

ISSUE: Can't login to admin panel
• See RBAC_QUICKSTART.md for setup

═══════════════════════════════════════════════════════════════

✅ FINAL VERIFICATION

After setup, run these checks:

☐ curl http://192.168.1.100 (app responds?)
☐ netstat -an | grep :80 (port 80 open?)
☐ sudo ufw status (firewall rules OK?)
☐ adb connect PHONE_IP (Android connects?)
☐ App login works (credentials correct?)
☐ Form submission works (database saves?)
☐ No internet access (verified blocked?)

═══════════════════════════════════════════════════════════════

📞 NEED HELP?

1. Check NETWORK_SECURITY.md for detailed instructions
3. Test connectivity: ping, curl, netstat
4. Check firewall: sudo ufw status
5. Read troubleshooting section

═══════════════════════════════════════════════════════════════

Ready? Let's go!


Then read: NETWORK_SECURITY.md

═══════════════════════════════════════════════════════════════

Last updated: March 1, 2026
Factory Network Configuration Reference
