# BukuKita POS & ERP

Sistem Point of Sale (Kasir) dan ERP untuk manajemen toko buku.

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Vue 3 + Tailwind CSS v4 + Vite |
| Database | MySQL 8 |
| Auth | Laravel Sanctum |

## Prasyarat

Instal tool ini dulu di komputer lu sebelum mulai:

| Tool | Kenapa Butuh | Link Download |
|------|-------------|---------------|
| Laragon | Server lokal (PHP + MySQL + Apache) | https://laragon.org/download/ |
| Composer | Install PHP packages Laravel | https://getcomposer.org/ |
| Node.js | Compile asset Vue.js & Tailwind | https://nodejs.org/ |


## Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/mahawiraa10/bukukita-POS.git
cd bukukita-POS
```

### 2. Copy Environment File
```bash
cp .env.example .env
```

### 3. Install Dependencies
```bash
composer install
npm install
```

### 4. Konfigurasi Database
1. Buka phpMyAdmin (`http://localhost/phpmyadmin`)
2. Buat database baru (misal: `bukukita_db`)
3. Edit file `.env`, ubah nilai `DB_DATABASE`:
   ```
   DB_DATABASE=bukukita_db
   ```

### 5. Jalankan Migrasi
```bash
php artisan migrate --seed
```
Perintah di atas akan membuat tabel-tabel database dan mengisi data role serta user awal.

### 6. Setup Aplikasi
```bash
php artisan key:generate
php artisan storage:link
```

### 7. Jalankan Server
Buka dua terminal terpisah:

**Terminal 1 - Backend API:**
```bash
php artisan serve
```

**Terminal 2 - Frontend Assets:**
```bash
npm run dev
```

### 8. Akses Aplikasi
Buka browser ke: http://localhost:8000

> **Catatan:** Untuk pengembangan Vue.js, gunakan `npm run dev` di terminal terpisah dan akses di `http://localhost:5173`.

## Akun Testing

| Role | Email | Password |
|------|-------|----------|
| Owner | owner@bukukita.test | password |
| Supervisor | supervisor@bukukita.test | password |
| Admin | admin@bukukita.test | password |
| Kasir | kasir@bukukita.test | password |

## Struktur Folder

```
bukukita-POS/
├── app/
│   ├── Http/Controllers/   # Logika backend
│   ├── Models/             # Model database (Eloquent)
│   └── Services/           # Business logic
├── database/
│   ├── migrations/         # Skema database
│   └── seeders/            # Data awal
├── resources/
│   ├── css/app.css         # Tailwind CSS
│   ├── js/
│   │   ├── app.js          # Vue 3 entry point
│   │   └── App.vue         # Root Vue component
│   └── views/              # Tampilan Blade (fallback)
└── routes/
    ├── api.php             # Endpoint API
    └── web.php             # Rute halaman web
```

---

Proyek KP PT. Buku Kita · 2026
