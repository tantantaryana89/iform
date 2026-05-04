# 🧪 API Testing Guide

## Android App Configuration

### Jalur Komunikasi Saat Ini

- Android -> WiFi lokal pabrik -> server iForm (LAN)
- Tidak perlu internet publik selama Android dan server berada di subnet lokal yang sama
- `localhost` hanya valid jika request dijalankan dari mesin server itu sendiri, bukan dari Android

### Production URL

```
Base URL (LAN): http://SERVER_LAN_IP/api
Contoh: http://192.168.1.100/api
```

### Development URL (Testing)

```
Base URL: http://YOUR_MACHINE_IP:8080/api
```

Catatan:

- Untuk testing dari Android fisik, selalu pakai IP LAN server/machine (contoh `192.168.x.x`).
- Jangan pakai `localhost` di aplikasi Android karena akan mengarah ke device Android itu sendiri.

Get your machine IP:

```bash
# macOS
ifconfig | grep "inet " | grep -v 127.0.0

# Linux
hostname -I

# Windows
ipconfig
```

---

## Testing dengan cURL (Terminal)

Catatan: contoh `localhost` di bawah ini untuk test dari terminal server lokal.
Jika test dari device lain di LAN, ganti host menjadi IP server, misalnya `http://192.168.1.100:8080/api/...`.

### 1. Submit Form Result

```bash
curl -X POST http://localhost:8080/api/submit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "template_id": 1,
    "operator": "john_doe",
    "mesin_id": "M001",
    "tanggal": "2026-03-01",
    "shift": "shift1",
    "answers": {
      "temperature": "95.5",
      "pressure": "100",
      "notes": "Normal operation"
    }
  }'
```

Response (Success):

```json
{
  "status": "ok",
  "message": "Form berhasil disimpan",
  "data": {
    "form_result_id": 123
  }
}
```

### 2. Export Form ke Excel

```bash
curl -X GET "http://localhost:8080/api/submit/export?id=123" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o form_result_123.xlsx
```

### 3. Get Checksheet by Mesin

```bash
curl -X GET "http://localhost:8080/api/checksheet/by-mesin?no_mesin=M001" \
  -H "Content-Type: application/json"
```

---

## Testing dengan Postman

### Setup

1. Download Postman: https://www.postman.com/downloads/
2. Create new Collection `iForm API`
3. Create Environment dengan variables:

```json
{
  "base_url": "http://localhost:8080/api",
  "token": "YOUR_API_TOKEN",
  "form_id": "123"
}
```

### HTTP Requests

**POST /submit**

```
URL: {{base_url}}/submit
Method: POST
Headers:
  - Content-Type: application/json
  - Authorization: Bearer {{token}}

Body (raw JSON):
{
  "template_id": 1,
  "operator": "john",
  "mesin_id": "M001",
  "tanggal": "2026-03-01",
  "shift": "shift1",
  "answers": {
    "field1": "value1",
    "field2": "value2"
  }
}
```

**GET /submit/export**

```
URL: {{base_url}}/submit/export?id={{form_id}}
Method: GET
Headers:
  - Authorization: Bearer {{token}}
```

---

## Android Studio Configuration

### ⚠️ IMPORTANT: IP Address Setup

For **factory network with static IP**:

```bash
# 1. Set server static IP (see NETWORK_SECURITY.md)
# 2. Update baseUrl dengan server IP (bukan localhost!)
# 3. Configure Android to ONLY allow local network
```

### HttpClient Setup (Kotlin/Java)

```kotlin
// Build.gradle
dependencies {
    implementation 'com.squareup.okhttp3:okhttp:4.9.0'
    implementation 'com.google.code.gson:gson:2.9.0'
}

// API Client
class ApiClient {
    private val client = OkHttpClient()
    private val baseUrl = "http://192.168.1.100/api"  // Static server IP!
    private val token = "YOUR_TOKEN"

    fun submitForm(data: FormData): String {
        val json = Gson().toJson(data)

        val body = json.toRequestBody("application/json".toMediaType())
        val request = Request.Builder()
            .url("$baseUrl/submit")
            .post(body)
            .header("Authorization", "Bearer $token")
            .header("Content-Type", "application/json")
            .build()

        return client.newCall(request).execute().body?.string() ?: ""
    }

    fun getChecksheet(mesinNo: String): String {
        val request = Request.Builder()
            .url("$baseUrl/checksheet/by-mesin?no_mesin=$mesinNo")
            .get()
            .header("Content-Type", "application/json")
            .build()

        return client.newCall(request).execute().body?.string() ?: ""
    }
}
```

### React Native Example

```javascript
// api.js
export const submitForm = async (formData) => {
  const response = await fetch("http://192.168.1.100:8080/api/submit", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${API_TOKEN}`,
    },
    body: JSON.stringify(formData),
  });

  return response.json();
};

export const getChecksheet = async (mesinId) => {
  const response = await fetch(
    `http://192.168.1.100:8080/api/checksheet/by-mesin?no_mesin=${mesinId}`,
    {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    },
  );

  return response.json();
};
```

---

## Getting API Token

### Create API Client di Admin Panel

1. Login sebagai admin
2. Go to: Site > Manage Clients
3. Create new client:
   - Name: `Mobile App v1`
   - Token: (auto-generated)
   - Is Active: Yes

Atau via command line:

```bash
# Connect ke MySQL

# Insert client
INSERT INTO api_client (name, token, is_active, created_at)
VALUES ('Mobile Dev', 'YOUR_SECRET_TOKEN', 1, UNIX_TIMESTAMP());
```

---

## Test Response Examples

### Success Response (200)

```json
{
  "status": "ok",
  "message": "Form berhasil disimpan",
  "data": {
    "form_result_id": 123
  }
}
```

### Validation Error (422)

```json
{
  "status": "error",
  "message": "Field 'operator' harus diisi"
}
```

### Authentication Error (401)

```json
{
  "status": "error",
  "message": "Token tidak valid"
}
```

### Server Error (500)

```json
{
  "status": "error",
  "message": "Internal server error"
}
```

---

## Debugging API Issues

### View API Logs

```bash
# Watch PHP logs

# Watch MySQL logs

# Connect to MySQL and check data

mysql> SELECT * FROM form_result;
mysql> SELECT * FROM form_result_detail WHERE form_result_id = 123;
```

### Common Issues

**1. Token Invalid**

- Check token di database
- Ensure `is_active = 1`

**2. Form submission fails**

- Check required fields
- Verify template_id exists
- Check database permissions

**3. Slow response**

---

## Performance Testing

### Load Testing dengan Apache Bench

```bash
# Single request
ab -n 10 -c 1 http://localhost:8080/api/checksheet/by-mesin?no_mesin=M001

# Concurrent requests
ab -n 100 -c 10 http://localhost:8080/api/checksheet/by-mesin?no_mesin=M001

# With JSON payload
ab -p form_data.json -T application/json -n 50 -c 5 http://localhost:8080/api/submit
```

### Monitoring Real-time

```bash
# CPU & Memory usage

# Check request latency
```

---

## SSL/HTTPS Setup (HTTPS UNTUK PRODUCTION!)

### Enable HTTPS

1. Get SSL certificate (Let's Encrypt)
2. Update Android app URL ke HTTPS

```bash
# Using Let's Encrypt with Certbot
certbot certonly --standalone -d yourdomain.com
```

---

## Security Headers untuk API

```bash
# Check security headers
curl -i http://localhost:8080/api/submit

# Should include:
# X-Content-Type-Options: nosniff
# X-Frame-Options: DENY
# X-XSS-Protection: 1; mode=block
```

---

Happy API testing! 🚀
