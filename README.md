# iForm

iForm adalah aplikasi digitalisasi form inspeksi untuk menggantikan form kertas yang kompleks. Form asli diimpor dari Excel, diubah menjadi template digital, dikirim ke Android sebagai data terstruktur, lalu hasil input operator dikirim kembali ke web dalam format JSON.

## Tujuan Aplikasi

- Menggantikan form kertas yang berisi banyak pertanyaan, simbol, dan aturan pemeriksaan.
- Mempercepat pengisian form di lapangan melalui Android.
- Menjaga hubungan antara template asli, hasil input operator, dan file laporan.
- Menampilkan atau mengekspor hasil inspeksi dalam bentuk yang mendekati form asli.

## Alur Utama

1. Admin mengimpor template form dari file Excel.
2. Sistem mem-parsing file Excel menjadi schema template.
3. Android mengambil template berdasarkan mesin atau template aktif.
4. Operator mengisi form di Android.
5. Android mengirim hasil input ke web dalam JSON.
6. Web menyimpan hasil berdasarkan item template.
7. Hasil dapat dilihat di web dan diekspor kembali ke Excel.

## Arsitektur Singkat

- Web backend: Yii2 + MySQL
- Client lapangan: Android
- Format pertukaran data: JSON
- Sumber template awal: Excel
- Output hasil: Web view dan export Excel

## Domain Utama

Domain utama aplikasi adalah alur `FormTemplate` dan `FormResult`.

- `FormTemplate` menyimpan definisi template hasil impor.
- `FormResult` menyimpan header hasil inspeksi.
- `FormResultDetail` menyimpan jawaban per item template.

Alur `Checksheet*` masih ada di codebase sebagai referensi beberapa kemampuan output, tetapi bukan domain utama produk.

## Catatan Implementasi Saat Ini

- Submit API sudah divalidasi terhadap schema template.
- Jawaban disimpan memakai key item template yang stabil.
- Field tidak dikenal akan ditolak.
- Jika schema memiliki item wajib (`required=true`), submit wajib mengisi item tersebut.
- Schema template menyimpan metadata mapping Excel per item (`sheet`, `row`, `source_cell`) untuk membantu export template-aware.
- Saat parsing awal, sistem otomatis mencari sel kosong mulai kolom `E` lalu menyimpan target sel hasil (`excel.cell`).
- Admin dapat mengubah mapping (`sheet/row/cell`) dan status `required` langsung dari halaman preview template.
- Saat aktivasi template, sistem memvalidasi mapping (`excel.sheet`, `excel.row`, `excel.cell`) dan menolak aktivasi jika ada format salah atau target cell duplikat.
- Status validasi mapping bisa dicek via endpoint web: `/form-template/mapping-status?id={template_id}`.
- Export Excel akan mencoba memakai file template asli jika tersedia.
- Jika template lama belum punya mapping yang cukup, sistem memakai fallback export generik yang tetap mengikuti urutan schema.

## Setup Singkat

Project ini dijalankan dengan stack lokal seperti MAMP atau XAMPP.

1. Sesuaikan koneksi database di `config/db.php`.
2. Jalankan migration bila diperlukan.
3. Pastikan folder `runtime/` dan `web/assets/` bisa ditulis.
4. Akses aplikasi melalui web server lokal yang mengarah ke folder project ini.

## Dokumen Terkait

- `API_FORM_SUBMISSION.md`
- `FORM_SUBMISSION_SUMMARY.md`
- `API_TESTING.md`
- `RBAC_QUICKSTART.md`
