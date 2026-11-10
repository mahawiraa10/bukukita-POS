# Security Audit Report - BukuKita POS

Target: `C:/laragon/www/BukuKita`  
Stack: Laravel 12, MySQL 8, Vue 3  
Date: 2026-11-10

---

## 1. Executive Summary
Audit repository BukuKita menemukan beberapa potensi risiko keamanan yang memerlukan mitigasi (Mass Assignment, string concatenation pada `DB::raw`, dan validasi input). Konfigurasi lingkungan `.env` sudah aman dari commit git.

---

## 2. Findings & Vulnerabilities

### A. Mass Assignment Risk (Medium)
- **Files:** `app/Models/*.php` (hampir seluruh model menggunakan `protected $guarded = [];`).
- **Risk:** Jika controller menggunakan `$request->all()` langsung pada `Model::create()` atau `update()` tanpa validasi ketat, user dapat memanipulasi field terlarang (misal: `role`, `is_admin`, `balance`).
- **Mitigasi:** Gunakan `$request->validated()` dari FormRequest atau `only([...])` eksplisit.

### B. Potensi SQL Injection via String Concatenation dalam `DB::raw` (Low/Medium)
- **File:** `app/Http/Controllers/Operational/PosSaleController.php` (baris 160-162)
- **Code:** 
  ```php
  DB::raw('total_spent + ' . $final)
  ```
- **Risk:** Meskipun `$final` berasal dari kalkulasi backend, penggabungan string langsung ke dalam `DB::raw` melanggar best practice ORM dan rentan jika sumber data berubah.
- **Mitigasi:** Gunakan method bawaan Eloquent `increment('total_spent', $final)` atau parameter binding.

### C. Environment Configuration (.env) (Secure)
- File `.env` dan `.env.production` terdaftar di `.gitignore`. Tidak ada secret key yang bocor di commit history utama.

---

## 3. Recommendations
1. Ganti `$guarded = []` dengan `$fillable = [...]` pada model yang menangani input user langsung, atau pastikan FormRequest ketat.
2. Ubah `DB::raw('col + ' . $val)` menjadi `$model->increment('col', $val)`.
3. Lakukan pengujian otorisasi endpoint API (`routes/api.php`) untuk memastikan role cashier/admin terisolasi dengan benar.
