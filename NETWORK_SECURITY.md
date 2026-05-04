# 🔐 Network Security Guide - Local Network Setup

## 📍 Scenario Anda

```
Factory Network (Restricted)
        ↓
WiFi Router (Isolated VLAN)
        │   └─ IP: Static (192.168.x.x)
        │       Port: 80/443
        │
        ├─ Android Device 1 (Operator)
        │   └─ IP: DHCP (192.168.x.y)
        │       Connect to: http://SERVER_IP/api/submit
        │
        ├─ Android Device 2 (Operator)
        │   └─ Connect to: http://SERVER_IP/api/submit
        │
        └─ Admin Laptop
            └─ Can manage via http://SERVER_IP
```

---

## 🎯 Network Requirements

### ✅ Harus Bisa Terjadi

```
Android Phone → (WiFi) → Server:80/443 → Database:3306
Android Phone dapat submit form
Admin dapat access dashboard
Devices dapat lihat list checksheet
```

### ❌ Harus TIDAK Bisa Terjadi

```
❌ Android akses internet eksternal
❌ Server terhubung ke internet
❌ Data keluar dari network pabrik
❌ Outsider akses dari luar WiFi
❌ Non-registered devices connect
```

---

## 1️⃣ Setup Server Statis IP

### macOS Setup

```bash
# System Preferences → Network → WiFi → Advanced → TCP/IP
# Pilih: Configure IPv4 → Manually

IP Address:        192.168.1.100
Subnet Mask:       255.255.255.0
Router:            192.168.1.1
DNS Server:        192.168.1.1 (local) atau 8.8.8.8
```

Atau via command line:

```bash
# Check current network
ifconfig | grep -A 5 "en0:"

# Set static IP (macOS)
sudo ifconfig en0 inet 192.168.1.100 netmask 255.255.255.0
```

### Linux Setup

Edit `/etc/netplan/00-installer-config.yaml`:

```yaml
network:
  version: 2
  ethernets:
    eth0:
      dhcp4: no
      addresses:
        - 192.168.1.100/24
      routes:
        - to: 0.0.0.0/0
          via: 192.168.1.1
      nameservers:
        addresses: [192.168.1.1]
```

Apply:

```bash
sudo netplan apply
```

---


### Network Architecture

```yaml

networks:
    driver: bridge
    ipam:
      config:
        - subnet: 172.20.0.0/16
          gateway: 172.20.0.1
```

Devices HANYA bisa akses via:

- External Port: 80/443 (dari host)
- Internal Network: 172.20.0.x (isolated)

### Verification

```bash

# Should see:
# Gateway: 172.20.0.1
# Subnet: 172.20.0.0/16
# Isolated from other containers
```

---

## 3️⃣ Firewall Rules (Ketat)

### macOS Firewall Setup

```bash
# Enable firewall
sudo defaults write /Library/Preferences/com.apple.alf globalstate -int 1

# Allow ONLY aplikasi tertentu
sudo /usr/libexec/ApplicationFirewall/socketfilterfw --setallowsigned on


sudo /usr/libexec/ApplicationFirewall/socketfilterfw --add /usr/sbin/httpd
```

### Linux UFW Firewall

```bash
# Enable firewall
sudo ufw enable

# Allow HANYA WiFi network
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow dari WiFi subnet ONLY
sudo ufw allow from 192.168.1.0/24 to any port 80 comment 'HTTP from WiFi'
sudo ufw allow from 192.168.1.0/24 to any port 443 comment 'HTTPS from WiFi'

# Allow SSH (admin) - local only
sudo ufw allow from 192.168.1.0/24 to any port 22 comment 'SSH from WiFi'

# Deny everything else
# sudo ufw deny from any port 80
# sudo ufw deny from any port 443

# Verify rules
sudo ufw status numbered
```

### Windows Firewall (via PowerShell)

```powershell
# Run as Administrator

# Disable internet-facing rules
Set-NetFirewallRule -DisplayName "World Wide Web Services*" -Enabled False

# Allow ONLY WiFi network
New-NetFirewallRule `
  -DisplayName "iForm API - WiFi Only" `
  -Direction Inbound `
  -Action Allow `
  -Protocol TCP `
  -LocalPort 80,443 `
  -RemoteAddress 192.168.1.0/24

# Verify
Get-NetFirewallRule -DisplayName "iForm*"
```

---

## 4️⃣ Network Isolation (VLAN)

### WiFi Router Configuration

**Untuk security maksimal, setup 2 WiFi networks:**

```
SSID: Factory-Production
├─ IP Range: 192.168.1.0/24
├─ Devices: Android phones, server
├─ Firewall: Internal only
├─ Internet: BLOCKED
└─ Guest: DISABLED

SSID: Admin-Management
├─ IP Range: 192.168.2.0/24
├─ Devices: Admin laptop
├─ Firewall: Can access internet atau local
└─ Separate network
```

**Router Settings (Generic):**

1. Login ke router admin panel
2. WiFi Settings → Advanced → VLAN
3. Create 2 SSIDs dengan different VLAN
4. Firewall → Inter-VLAN blocking
5. Disable WAN untuk Production VLAN

---

## 5️⃣ Android App Configuration

Update URL untuk local network:

### AndroidManifest.xml Cleartext Traffic

```xml
<?xml version="1.0" encoding="utf-8"?>
<manifest>
    <domain-config cleartextTrafficPermitted="true">
        <!-- Allow HTTP ONLY untuk local network -->
        <domain includeSubdomains="true">192.168.1.100</domain>
        <domain includeSubdomains="true">192.168.1.*</domain>
        <!-- Block internet -->
        <domain includeSubdomains="false">*.com</domain>
        <domain includeSubdomains="false">*.net</domain>
    </domain-config>
</manifest>
```

### API Configuration (Kotlin)

```kotlin
// BuildConfig untuk prod
object ApiConfig {
    // OFFLINE / LOCAL NETWORK ONLY
    const val BASE_URL = "http://192.168.1.100"  // Server IP
    const val API_ENDPOINT = "$BASE_URL/api"
    const val TIMEOUT_SECONDS = 10L

    // Disable internet connectivity check
    const val REQUIRE_INTERNET = false

    // Use local cache pada offline
    const val CACHE_DIR = "/sdcard/iform_cache"
    const val DB_NAME = "iform_local.db"
}

// Network client config
val httpClient = OkHttpClient.Builder()
    .connectTimeout(10, TimeUnit.SECONDS)
    .readTimeout(10, TimeUnit.SECONDS)
    .writeTimeout(10, TimeUnit.SECONDS)
    .cache(Cache(cacheDir, 10 * 1024 * 1024))  // 10MB cache
    .addNetworkInterceptor { chain ->
        // Log semua network calls untuk audit
        val request = chain.request()
        Timber.d("API Call: ${request.url}")
        chain.proceed(request)
    }
    .build()
```

---

## 6️⃣ DNS Configuration

### Local DNS Setup

Untuk avoid hardcoding IP, setup local DNS:

**Option 1: Router DHCP DNS**

Router akan automatic assign DNS yang point ke server.

**Option 2: Hosts File Lokal di Setiap Device**

Android (root required):

```bash
adb shell
su
echo "192.168.1.100 iform.local" >> /etc/hosts
```

Atau add di router DHCP options.

**Option 3: Local DNS Server (dnsmasq)**

```bash
# Install
sudo apt-get install dnsmasq

# Edit /etc/dnsmasq.conf
echo "address=/iform.local/192.168.1.100" >> /etc/dnsmasq.conf
echo "address=/api.iform.local/192.168.1.100" >> /etc/dnsmasq.conf

# Restart
sudo systemctl restart dnsmasq
```

---

## 7️⃣ API Token & Authentication

### Secure Token Management

```php
// app/models/ApiClient.php

class ApiClient extends ActiveRecord
{
    public static function createToken($deviceId, $deviceName)
    {
        // Token unique per device
        $token = Yii::$app->security->generateRandomString(32);

        $client = new self();
        $client->device_id = $deviceId;      // IMEI atau unique ID
        $client->device_name = $deviceName;  // "Operator Room 1 Phone"
        $client->token = $token;
        $client->ip_address = Yii::$app->request->userIP;
        $client->mac_address = $this->getMacAddress();
        $client->is_active = 1;
        $client->created_at = time();
        $client->expires_at = time() + (365 * 24 * 60 * 60);  // 1 tahun

        $client->save();

        return $token;
    }
}
```

### Token Storage di Android

```kotlin
// Store in encrypted SharedPreferences
val encryptedSharedPreferences = EncryptedSharedPreferences.create(
    context,
    "iform_tokens",
    MasterKey.Builder(context).setKeyScheme(MasterKey.KeyScheme.AES256_GCM).build(),
    EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
    EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
)

// Save token securely
encryptedSharedPreferences.edit().putString("api_token", token).apply()

// Use token
val token = encryptedSharedPreferences.getString("api_token", null)
```

---

## 8️⃣ Monitoring & Logging

### Network Traffic Monitoring

```bash
# Monitor connections
netstat -an | grep :80
netstat -an | grep :443
netstat -an | grep :3306

# Log semua incoming connections
sudo tcpdump -i en0 -n 'tcp port 80 or tcp port 443'

# Check DNS queries (jika pakai local DNS)
ngrep -d any 'iform.local'
```

### Server-Side Request Logging

```php
// config/web.php - Add request logging

'log' => [
    'targets' => [
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning', 'info'],
            'logFile' => '@runtime/logs/network.log',
            'categories' => ['api.*'],
        ],
    ],
],
```

Log format:

```
2026-03-01 10:15:23 [api.submit] POST /api/submit from 192.168.1.50 - Device: Operator1-Phone
2026-03-01 10:15:24 [api.submit] Form#123 submitted - Data saved
2026-03-01 10:16:00 [api.checksheet] GET /api/checksheet/by-mesin?no_mesin=M001 - Success
```

---

## 9️⃣ Offline Mode (Fallback)

Jika WiFi disconnect, app tetap bisa:

```kotlin
// SQLite database untuk offline storage
class OfflineDatabase {
    fun savePendingSubmissions(formData: FormData) {
        // Store locally
        db.formDao().insertPending(formData)
    }

    fun syncWhenOnline() {
        // Sync semua pending data ke server
        if (isNetworkAvailable()) {
            val pendingForms = db.formDao().getPendingForms()
            pendingForms.forEach { form ->
                submitToServer(form)
                    .onSuccess {
                        db.formDao().markAsSynced(form.id)
                    }
                    .onError {
                        // Retry later
                    }
            }
        }
    }
}

// Detect network changes
class NetworkMonitor(context: Context) {
    fun observeNetworkStatus() {
        connectivityManager
            .registerNetworkCallback(networkRequest) { network ->
                if (isOnline()) {
                    // Sync immediately
                    offlineDb.syncWhenOnline()
                }
            }
    }
}
```

---

## 🔟 Security Checklist

- [ ] Server IP address static (SET)
- [ ] Firewall rules configured (ALLOW ONLY WiFi)
- [ ] VLAN isolated jika ada (PRODUCTION VLAN ONLY)
- [ ] DNS local setup atau hosts file
- [ ] API tokens unique per device (GENERATED)
- [ ] Token expires configured
- [ ] Network logging enabled
- [ ] Offline mode capability added
- [ ] SSL certificate for future (HTTPS ready)
- [ ] Device MAC address logging
- [ ] Regular network audit logs review

---

## 🔧 Configuration Examples

### Development Network

```
Server Static IP:     192.168.1.100
WiFi Network:         192.168.0.0/24
Firebase:             DISABLED
Internet Access:      NO
External Ports:       BLOCKED
Device Limit:         Unlimited (development)
Token Expiry:         Never
Logging:              Verbose
```

### Production Network (Pabrik)

```
Server Static IP:     192.168.1.100
WiFi Network:         VPN Isolated
Firebase:             DISABLED
Internet Access:      NO (Hard block)
External Ports:       Firewall restricted
Device Limit:         Max registered devices
Token Expiry:         30 days
Logging:              Audit trail
MAC Whitelist:        Enabled
IP Whitelist:         Enabled
```

---

## 📊 Network Diagram Anda

```
┌─────────────────────────────────────────────────────┐
│          Factory  Network (Isolated)                │
│                                                     │
│  ┌───────────────────────────────────────────────┐  │
│  │       WiFi Router (Local)                     │  │
│  │  No internet gateway                          │  │
│  │  192.168.1.0/24                              │  │
│  └─────────────────────────────────────────────┬─┘  │
│            ├─────────────────────────────────────  │
│            │                                        │
│    ┌───────┴──────┐    ┌────────────┐             │
│    │              │    │            │             │
│    ▼              ▼    ▼            ▼             │
│  Server        Android  Android   Admin          │
│  (Prod)        Phone1   Phone2    Laptop         │
│  192.168.1.100 DHCP     DHCP      DHCP           │
│  :80/:443      :random  :random   :random        │
│                                                   │
│                                                   │
│                                                   │
│  ❌ NO INTERNET ❌ NO EXTERNAL ACCESS            │
│  ❌ NO CLOUD ❌ NO EXTERNAL CONNECTIVITY          │
│                                                   │
└─────────────────────────────────────────────────────┘
```

---

## 🎯 Recommended Setup

```
1. Server Computer
   ├─ Static IP: 192.168.1.100
   ├─ Port 80/443 exposed to WiFi only
   └─ Firewall: Block all except 192.168.1.0/24

2. WiFi Network
   ├─ SSID: Factory-Production
   ├─ No internet gateway
   ├─ DHCP: 192.168.1.50-200
   └─ Firewall: Inter-VLAN blocking

3. Android Devices
   ├─ Server URL: http://192.168.1.100/api
   ├─ API token: Unique per device
   ├─ Offline mode: SQLite cache
   └─ Auto-retry: On network reconnect

4. Admin Laptop
   ├─ Server URL: http://192.168.1.100:8080
   ├─ Password protected
   ├─ Session limited
   └─ Access logs: Yes
```

---

## 📈 Network Monitoring

```bash
# Real-time monitoring
watch -n 1 'netstat -an | grep ":80\|:443\|:3306" | wc -l'

# Connection logs
tcpdump -i any -w /tmp/traffic.pcap port 80 or port 443

# Check who's connected
arp -a | grep 192.168.1


# Application metrics
curl http://192.168.1.100/metrics
```

---

## 🎓 Testing Network Security

```bash
# Test firewall rules
curl -I http://192.168.1.100/api/submit  # ✅ Should work
curl -I http://8.8.8.8/api/submit        # ❌ Should NOT work
curl -I http://google.com                 # ❌ Should NOT work

# Test Android device connection
adb shell
ping 192.168.1.100      # ✅ Should respond
ping 8.8.8.8            # ❌ Should NOT respond
ping google.com          # ❌ Should NOT respond

# Verify isolated network
```

---

## ⚠️ Common Issues

### Issue: Android dapat akses internet

**Solution:** Block WiFi gateway

```bash
# Router: Set WAN port ke DISABLED
# atau remove default gateway
sudo route delete default
```

### Issue: Server can reach internet

**Solution:** Block outgoing traffic

```bash
# Firewall rule
sudo ufw deny out to any  # Block all outgoing
sudo ufw allow out 192.168.1.0/24  # Allow only local network
```

### Issue: Android phone tidak bisa submit

**Debug:**

```bash
# Check dari Android
adb shell
netstat -an | grep 192.168.1.100

```

---

## 📚 References

- UFW Documentation: https://help.ubuntu.com/community/UFW
- macOS Firewall: https://support.apple.com/en-us/HT201642
- Android Network Security: https://developer.android.com/training/articles/security-config

---

_Last updated: March 1, 2026_
_Factory Network Security Configuration for iForm_
