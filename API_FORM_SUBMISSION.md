# API Documentation - Form Submission dari Android

## Overview

API ini dipakai Android untuk mengirim hasil input operator ke web. Template form berasal dari schema template, dan jawaban yang dikirim harus memakai key item template yang valid. Hasil submission disimpan ke database dan dapat diekspor kembali ke Excel.

## Topologi Komunikasi Produksi

- Android -> WiFi lokal pabrik -> server iForm (LAN)
- Android mengambil template dan submit hasil melalui endpoint API di server lokal
- Untuk produksi lokal, gunakan IP/hostname server lokal (contoh `http://192.168.1.100/api`)
- Jangan gunakan `localhost` pada aplikasi Android

---

## Endpoints API

### 1. Submit Form Hasil Input Android

**Endpoint:** `POST /api/submit`

**Authentication:** Bearer Token (HTTP Authorization Header)

**Request Body:**

```json
{
  "template_id": 1,
  "operator": "john_doe",
  "mesin_id": "M001",
  "tanggal": "2026-02-14",
  "shift": "shift1",
  "answers": {
    "CHK-001": true,
    "CHK-002": "OK",
    "CHK-003": {
      "catatan": "Bearing masih normal"
    }
  }
}
```

**Required Fields:**

- `template_id` (integer) - ID form template
- `operator` (string) - Username operator
- `mesin_id` (string) - Nomor mesin
- `tanggal` (string) - Format: YYYY-MM-DD
- `shift` (string) - Nama shift (shift1, shift2, shift3)
- `answers` (object/array) - Jawaban form dengan key = `item_id` dari schema template, value = jawaban operator

**Success Response (200):**

```json
{
  "status": "ok",
  "message": "Form berhasil disimpan",
  "data": {
    "form_result_id": 123
  }
}
```

**Error Response (422):**

```json
{
  "status": "error",
  "message": "Field 'operator' harus diisi"
}
```

**Error Response (422) - Field tidak valid:**

```json
{
  "status": "error",
  "message": "Jawaban mengandung field yang tidak terdaftar pada template"
}
```

**Error Response (422) - Field wajib tidak lengkap:**

```json
{
  "status": "error",
  "message": "Field wajib belum diisi: CHK-001, CHK-004"
}
```

**Error Response (500):**

```json
{
  "status": "error",
  "message": "Gagal menyimpan form result: ..."
}
```

---

### 2. Export Form ke Excel (via API)

**Endpoint:** `GET /api/submit/export?id=123`

**Authentication:** Bearer Token

**Success Response:**

```json
{
  "status": "ok",
  "message": "Export berhasil",
  "data": {
    "file_path": "form_result_123_20260214120530.xlsx"
  }
}
```

Untuk download file, gunakan path ke `/runtime/export/{file_path}`

Jika file Excel sumber template tersedia di server, export akan mencoba memakai file asli tersebut sebagai dasar hasil export.

Respons template API juga dapat menyertakan `mapping_validation` untuk menunjukkan apakah mapping schema template sudah valid dipakai produksi.

---

## Cara Implementasi di Android

Catatan:

- Gunakan `item_id` dari schema template sebagai key pada object `answers`.
- Jangan kirim field bebas yang tidak ada di template.
- Simpan relasi antara komponen input Android dan `item_id` saat form dirender.
- Jika item schema bertanda `required=true`, Android wajib mengirim nilainya.

Catatan tambahan template:

- Saat template diparse dari Excel, item dapat otomatis ditandai `required=true` jika ada indikasi seperti `*`, `wajib`, `mandatory`, `must`, atau `harus` pada teks item/standar/cara.

### 1. Setup HTTP Client (Retrofit/OkHttp)

```kotlin
// Retrofit dengan Interceptor untuk Bearer Token
val client = OkHttpClient.Builder()
    .addInterceptor { chain ->
        val request = chain.request().newBuilder()
            .addHeader("Authorization", "Bearer YOUR_API_TOKEN")
            .build()
        chain.proceed(request)
    }
    .build()

val retrofit = Retrofit.Builder()
  .baseUrl("http://192.168.1.100")
    .client(client)
    .addConverterFactory(GsonConverterFactory.create())
    .build()
```

Catatan:

- Dengan deklarasi `@POST("/api/submit")`, endpoint final menjadi `http://192.168.1.100/api/submit`.

### 2. Define API Service

```kotlin
interface FormApiService {
    @POST("/api/submit")
    suspend fun submitForm(@Body formData: FormSubmission): ApiResponse<FormSubmissionResult>

    @GET("/api/submit/export")
    suspend fun exportForm(@Query("id") formId: Int): ApiResponse<ExportResult>
}

data class FormSubmission(
    val template_id: Int,
    val operator: String,
    val mesin_id: String,
    val tanggal: String,
    val shift: String,
    val answers: Map<String, Any>
)

data class FormSubmissionResult(
    val form_result_id: Int
)

data class ExportResult(
    val file_path: String
)

data class ApiResponse<T>(
    val status: String,
    val message: String,
    val data: T?
)
```

### 3. Submit Form

```kotlin
val service = retrofit.create(FormApiService::class.java)

val formData = FormSubmission(
    template_id = 1,
    operator = "operator1",
    mesin_id = "M001",
    tanggal = "2026-02-14",
    shift = "shift1",
    answers = mapOf(
    "CHK-001" to true,
    "CHK-002" to "OK",
    "CHK-003" to "Semua normal",
    "CHK-004" to listOf("base64_data_1", "base64_data_2")
    )
)

try {
    val response = service.submitForm(formData)
    if (response.status == "ok") {
        val formResultId = response.data?.form_result_id
        println("Form submitted with ID: $formResultId")
    } else {
        println("Error: ${response.message}")
    }
} catch (e: Exception) {
    println("Network error: ${e.message}")
}
```

---

## Web UI untuk Manage Results

### Akses Form Results

**URL:** `/form-result`

Fitur:

- 📋 Daftar semua form results
- 👁️ Lihat detail setiap form
- 📥 Download individual form as Excel
- 📥 Download semua forms sebagai Excel (multiple sheets)

---

## Database Schema

### Table: form_result

```
id (PK)
template_id (FK) → form_template.id
operator (string)
no_mesin (string)
tanggal (date)
shift (string)
created_at (timestamp)
updated_at (timestamp)
```

### Table: form_result_detail

```
id (PK)
form_result_id (FK) → form_result.id
field_name (string)
field_value (text/json)
```

---

## Contoh Response dengan Real Data

### Submit Request

```bash
curl -X POST http://localhost/iform/api/submit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your_token_here" \
  -d '{
    "template_id": 1,
    "operator": "operator1",
    "mesin_id": "M001",
    "tanggal": "2026-02-14",
    "shift": "shift1",
    "answers": {
      "nama_mesin": "Mesin Stamping 001",
      "status": "operasional",
      "suhu": "85",
      "tekanan": "120",
      "catatan": "Semua dalam kondisi normal"
    }
  }'
```

### Success Response

```json
{
  "status": "ok",
  "message": "Form berhasil disimpan",
  "data": {
    "form_result_id": 42
  }
}
```

---

## Error Handling

### Common Errors

| Status | Message                     | Cause                      |
| ------ | --------------------------- | -------------------------- |
| 401    | Token tidak valid           | Bearer token salah/expired |
| 422    | Field 'X' harus diisi       | Field wajib kosong         |
| 500    | Gagal menyimpan form result | Database error             |

### Android Error Handling

```kotlin
fun submitFormWithErrorHandling(formData: FormSubmission) {
    viewModelScope.launch {
        try {
            val response = apiService.submitForm(formData)

            when (response.status) {
                "ok" -> {
                    // Success
                    val formId = response.data?.form_result_id
                    showMessage("Form berhasil disimpan dengan ID: $formId")
                }
                "error" -> {
                    // API error
                    showError(response.message)
                }
            }
        } catch (e: HttpException) {
            when (e.code()) {
                401 -> showError("Token expired, silakan login ulang")
                422 -> showError("Ada field yang tidak valid")
                500 -> showError("Server error, coba lagi nanti")
                else -> showError("HTTP Error: ${e.code()}")
            }
        } catch (e: IOException) {
            showError("Tidak ada koneksi internet")
        } catch (e: Exception) {
            showError("Error: ${e.message}")
        }
    }
}
```

---

## Tips Implementasi

1. **Offline Support**: Simpan form ke local database dulu, sync ke server saat ada koneksi
2. **Large Objects**: Untuk foto/file, encode menjadi Base64 string
3. **Retry Logic**: Implementasikan retry dengan exponential backoff
4. **Validation**: Validasi data di Android sebelum submit ke server
5. **Progress Tracking**: Untuk form besar, track upload progress

---

## Testing API dengan Postman

1. Import request ke Postman
2. Set environment variable `api_token` = token Anda
3. Test endpoint dengan sample data
4. Verify response dengan status "ok"

---

**Last Updated:** 2026-02-14
**API Version:** 1.0
