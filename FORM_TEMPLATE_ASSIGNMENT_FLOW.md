# Alur Penjodohan Form Template & Mesin (Refactored)

## Ide Bisnis yang Tepat

Template form adalah **blueprint generic** (tidak terikat mesin spesifik saat dibuat).  
Mesin adalah **aset pabrik** yang butuh form untuk operasional.  
Relasi keduanya (`machine_template`) adalah **assignment operasional** yang bisa berubah.

**Keuntungan:**

- Satu template bisa dipakai banyak mesin (lebih efisien)
- Template tetap viable meski mesin berubah konfigurasi
- Admin punya fleksibilitas untuk reassign atau scale up mesin baru

---

## Alur Kerja Aplikasi (Setelah Refactor)

### 1. **Admin membuat Template Form** (halaman `/form-template/create`)

- Upload file Excel (struktur, bukan hasil)
- Berikan nama template
- **TIDAK ada pemilihan mesin** di sini
- Status otomatis: `draft`
- **Tujuan:** Membuat blueprint form yang generic

### 2. **Admin validasi & aktifkan Template** (preview: `/form-template/preview-form`)

- Lihat struktur items dari Excel
- Edit mapping (sheet, cell untuk setiap item)
- Tandai field yang wajib
- Klik **Aktifkan** → status jadi `active`
- **Validasi hanya pada mapping Excel**, bukan pada mesin
- **Tujuan:** Memastikan template siap diproduksi

### 3. **Admin assign Template ke Mesin** (halaman `/daftar-mesin/create` atau `/daftar-mesin/update`)

- Dropdown template hanya menampilkan template yang `active` + `valid`
- Pilih template yang sesuai untuk mesin itu
- Simpan → tercatat di tabel `machine_template`
- **Tujuan:** Menghubungkan blueprint form dengan aset mesin nyata

### 4. **Android request form** (API `/api/template/index?no_mesin=MC-001`)

- API cari record di `machine_template` dengan `no_mesin`
- Ambil `template_id` dari sana
- Load schema dari `form_template` (harus `active`)
- **Return:** template JSON siap untuk form Android
- **Source of truth:** `machine_template`, bukan `form_template.mesin_id`

### 5. **Android submit form** (API `/api/submit/index`)

- Kirim `template_id`, `mesin_id`, answers
- API validasi:
  - Template ada dan aktif?
  - Mapping Excel valid?
  - Mesin punya mapping ke template ini?
  - Answers sesuai schema?
- Simpan ke `form_result` + `form_result_detail`
- **Validation logic:** Sama seperti template read

---

## Perubahan Database & Code

### Model: `FormTemplate` (`models/FormTemplate.php`)

- ✅ Hapus requirement `mesin_id` → template bisa dibuat tanpa mesin
- ✅ Tetap ada field `mesin_id` di DB (backward compatibility), tapi tidak dipakai secara aktif
- ✅ Relasi fokus pada schema items, bukan mesin

### Controller: `FormTemplateController` (`controllers/FormTemplateController.php`)

- ✅ `actionCreate()` → Tidak lagi pass `$mesinList` ke view
- ✅ `actionActivate()` → Tidak lagi deactivate template lain berdasarkan `mesin_id`
  - Satu template bisa `active` untuk banyak mesin
  - Tidak ada "nonaktifkan template lain" logic

### View: Template Create (`views/form-template/create.php`)

- ✅ Hapus dropdown mesin
- ✅ Fokus pada: Nama template + File Excel

### View: Template Preview (`views/form-template/preview-form.php`)

- ✅ Tambah alert/info yang menjelaskan alur
- ✅ Terbatas pada mapping validation, bukan machine assignment

### View: Template Index (`views/form-template/index.php`)

- ✅ Tambah kolom "Mesin Terpakai" → show how many machines use this template
- ✅ Kolom ini mengambil count dari `machine_template` tabel

### View: Machine Form (`views/daftar-mesin/_form.php`)

- ✅ Update dropdown template → hanya ada pilihan `active` + `valid`
- ✅ Tampilkan warning jika tidak ada template siap
- ✅ Ini adalah tempat **pairing sesungguhnya** terjadi

---

## Tabel Database & Relasi

```
form_template (template blueprint)
├─ id (PK)
├─ name
├─ status (draft|active|archived)
├─ schema_json (items mapping)
└─ mesin_id (legacy, tidak dipakai aktif lagi)

daftar_mesin (aset mesin)
├─ id (PK)
├─ no_mesin
├─ nama_mesin
└─ ... other fields ...

machine_template (assignment operasional) ⭐ SOURCE OF TRUTH UNTUK RUNTIME
├─ id (PK)
├─ no_mesin (FK → daftar_mesin.no_mesin)
├─ template_id (FK → form_template.id)
└─ created_at, updated_at
```

**Relasi yang dipakai API:**

- API cari `machine_template` by `no_mesin` ✅
- Ambil `template_id` dari sana ✅
- Load `form_template.schema_json` ✅

**Relasi yang TIDAK dipakai API:**

- `form_template.mesin_id` ❌ (legacy, bisa diabaikan)

---

## Migration Path (Jika Ada Data Lama)

Jika database sudah punya `form_template.mesin_id` dari sebelumnya:

1. Data tetap ada (backward compat)
2. Tidak dihapus, tapi juga tidak dibaca saat activate
3. Android hanya lihat `machine_template`
4. Di masa depan bisa dihapus column jika tidak perlu

---

## Checklist Admin Jika Mau Deploy

- [ ] Upload template form baru (Excel file)
- [ ] Lihat preview & validasi mapping
- [ ] Aktifkan template
- [ ] Assign template ke mesin di halaman Daftar Mesin
- [ ] **Jangan** di create template pilih mesin (tidak ada di form anymore)
- [ ] Test: Cek bahwa Android bisa ambil dan submit form lewat API
