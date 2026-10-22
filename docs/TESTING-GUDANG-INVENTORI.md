# 📦 BukuKita — Panduan Testing (Buat Pemula)

Panduan ini buat yang belum pernah nyentuh sistem sama sekali. Ikuti urutannya dari atas ke bawah — **jangan loncat**, karena tiap langkah butuh data dari langkah sebelumnya.

---

## Bagian 0 — Persiapan

### 0.1 Nyalakan server
1. Buka **Laragon** → klik **Start All**.
2. Pastikan `http://bukukita.test` bisa dibuka di browser.

### 0.2 Akun yang tersedia
Semua akun pakai password sama: **`password`**

| Email | Nama | Bisa apa aja? |
|---|---|---|
| `admin@bukukita.test` | Andi | Input hierarki, vendor, produk, **bikin PO**, **terima barang** |
| `supervisor@bukukita.test` | Doni | **Approve PO**, laporan, histori event |
| `owner@bukukita.test` | Wira | Semua di atas + kasir |
| `kasir@bukukita.test` | Caca | Jualan kasir doang |

> 💡 **Kenapa 4 akun?** Sistem nyimulasi toko beneran: admin = staf gudang yang mesen barang, supervisor = atasan yang nyetujuin, kasir = yang jualan di depan. Tiap akun punya batasan biar gak salah pencet.

### 0.3 Peta menu
Setelah login, menu di atas (kiri ke kanan sesuai alur kerja):

```
HIERARKI → MASTER VENDOR → MASTER PRODUK → PO → TERIMA → GUDANG → KELOLA EVENT → HISTORY EVENT → KASIR → LAPORAN
```

Menu tertentu cuma muncul buat akun tertentu. Kalau gak keliatan, coba login pakai akun lain sesuai tabel.

---

## Bagian 1 — Login pertama (pakai ADMIN)

> ⚠️ **Penting!** PO di-*input* pakai **admin**, lalu di-*approve* pakai **supervisor**. Admin **gak punya wewenang approve** — ini disengaja, namanya *dual control* (kaya toko beneran: yang mesen gak boleh nyetujuin sendiri).

1. Buka `http://bukukita.test` → otomatis ke halaman login.
2. Isi `admin@bukukita.test` / `password`.
3. Klik **LOGIN**.
4. ✅ Berhasil = masuk **DASHBOARD**, nama "Andi" muncul di pojok.

---

## Bagian 2 — Hirarki Produk (4 level) — *masih ADMIN*

Hirarki = cara sistem nge-grup buku. Ibarat rak toko: **Divisi** (rak besar) → **Departemen** (seksi) → **Kelas** (kategori) → **Sub-Kelas** (sub-kategori).

> ⚠️ Harus urut dari level 1. Level 2 butuh level 1, level 3 butuh level 2, dst. Gak bisa dibalik.

1. Klik menu **HIERARKI**.
2. Wizard 4 langkah. Mulai **LANGKAH 1 — Divisi**:
   - Ketik nama divisi, contoh: `Fiksi`
   - Klik **SIMPAN** / **+ TAMBAH**
   - ✅ Nama muncul di tabel bawah.
3. **LANGKAH 2 — Departemen**:
   - Ketik nama, contoh: `Novel`
   - **Pilih Divisi**: `Fiksi` (dari dropdown)
   - Simpan.
4. **LANGKAH 3 — Kelas**:
   - Nama: `Romance`
   - **Pilih Departemen**: `Novel`
   - Simpan.
5. **LANGKAH 4 — Sub-Kelas**:
   - Nama: `Teen Romance`
   - **Pilih Kelas**: `Romance`
   - Simpan.

✅ **Cek berhasil:** tiap level punya minimal 1 baris di tabelnya.

> 💡 Kalau mau bikin lebih dari 1 cabang, ulangi langkah 2–5. Contoh: Divisi `Fiksi` → Dept `Novel` → Kelas `Romance` **dan** Kelas `Fantasi`.

❌ **Kalau dropdown kosong** = level di atasnya belum dibikin. Balik ke langkah sebelumnya.

---

## Bagian 3 — Vendor — *masih ADMIN*

Vendor = supplier/penerbit yang nyuplai buku.

1. Klik menu **MASTER VENDOR**.
2. Klik tombol **+ VENDOR BARU**.
3. Isi form:
   - **Nama Vendor**: `Gramedia Pustaka`
   - **Kontak**: `08123456789`
   - **Diskon (%)**: `10`
   - **Divisi**: pilih `Fiksi` ← **wajib, gak boleh kosong**
4. Klik **SIMPAN**.

✅ **Cek berhasil:** vendor muncul di tabel, kolom **DIVISI** terisi `Fiksi`.

❌ **"Divisi wajib dipilih!"** → dropdown divisi kosong berarti Bagian 2 belum selesai.

---

## Bagian 4 — Produk — *masih ADMIN*

1. Klik menu **MASTER PRODUK**.
2. Klik **LOAD DATA** lalu **+ PRODUK BARU**.
3. Isi form:
   - **Nama Produk**: `Laskar Pelangi`
   - **SKU**: `BK-001`
   - **Divisi**: `Fiksi` → **Departemen**: `Novel` → **Kelas**: `Romance` → **Sub-Kelas**: `Teen Romance` (pilih berurutan, dropdown berikutnya baru keisi setelah yang sebelumnya dipilih)
   - **Vendor Asal**: `Gramedia Pustaka`
   - **Harga Beli**: `45000`
   - **Harga Jual**: `65000`
   - **Diskon (%)**: `0`
4. Klik **SIMPAN**.

✅ **Cek berhasil:** produk muncul di tabel dengan **Stok = 0**.

> 💡 Stok sengaja 0. Stok baru naik di **Bagian 7 (Terima Barang)**. Ini disengaja biar semua stok punya jejak asal-usul — gak ada angka yang tiba-tiba muncul.

Ulangi buat bikin produk ke-2 dan ke-3 (biar nanti PO-nya berasa banyak). Contoh: `BK-002 Bumi Manusia`, `BK-003 Perahu Kertas`.

---

## Bagian 5 — Bikin PO — *masih ADMIN* ⭐

PO (Purchase Order) = surat pesanan ke vendor.

1. Klik menu **PO**.
2. Di form **FORM PURCHASE ORDER**:
   - **Vendor**: pilih `Gramedia Pustaka`
   - **Tipe PO**: `Reguler` (beli putus) — *pilih `Konsinyasi / Bind Event` cuma kalau barangnya titipan/bazar*
   - **Tanggal PO**: hari ini
3. Klik **TAMBAH KE PO**:
   - **Produk**: `Laskar Pelangi`
   - **Qty**: `10`
   - Klik **TAMBAH KE PO** di modal
   - ✅ Baris muncul di tabel bawah form
4. Ulangi **TAMBAH KE PO** buat produk lain:
   - `Bumi Manusia` — Qty `5`
5. Cek tabel udah ada 2 baris, lalu klik **BUAT PURCHASE ORDER**.

✅ **Cek berhasil:** di **DAFTAR PURCHASE ORDER** muncul baris baru:
- Nomor: `PO-202610-0001` (format `PO-YYYYMM-XXXX`)
- Status: **DRAFT** (abu-abu)
- Tombol **APPROVE** muncul di kolom AKSI

❌ **Tombol BUAT gak jalan** → pastikan minimal 1 baris produk udah ditambah dan qty > 0.

---

## Bagian 6 — Approve PO — *GANTI LOGIN ke SUPERVISOR* ⭐⭐

> ⚠️ **Ini langkah yang paling sering bikin bingung.** Admin yang bikin PO **gak bisa** approve PO-nya sendiri. Harus login sebagai supervisor. Kalau masih login admin, tombol APPROVE **gak akan muncul**.

1. Klik **LOGOUT** (pojok kanan atas).
2. Login: `supervisor@bukukita.test` / `password`.
3. Buka menu **PO**.
4. Scroll ke **DAFTAR PURCHASE ORDER**.
5. Cari baris status **DRAFT** → klik tombol **APPROVE** di kolom AKSI.
6. Muncul konfirmasi "Approve PO ...?" → klik **OK**.

✅ **Cek berhasil:** status berubah dari **DRAFT** (abu-abu) jadi **APPROVED** (biru), dan tombol APPROVE hilang.

> 💡 Kenapa harus ganti akun? Biar ada kontrol: orang yang mesen barang beda dengan orang yang nyetujuin pengeluaran. Sama kaya di kantor beneran.

---

## Bagian 7 — Terima Barang — *GANTI LOGIN balik ke ADMIN* ⭐⭐⭐

Ini langkah paling penting: **stok cuma naik di sini**.

1. **LOGOUT** dari supervisor.
2. Login: `admin@bukukita.test` / `password`.
3. Klik menu **TERIMA**.
4. Isi 3 kolom wajib (semua harus keisi, gak boleh kosong):
   - **NO SURAT JALAN**: `SJ-0001`
   - **NO FAKTUR**: `INV-0001`
   - **PENERIMA**: `Andi`
5. **Pilih PO**: dropdown cuma nampilin PO status `APPROVED` atau `PARTIAL`. Pilih `PO-202610-0001`.
6. ✅ Otomatis muncul tabel produk + qty sisa (`Laskar Pelangi` = 10, `Bumi Manusia` = 5).
7. **Skenario A — terima semua:**
   - Biarin qty apa adanya
   - Klik **SIMPAN PENERIMAAN & UPDATE STOK**
   - ✅ Alert hijau "Penerimaan berhasil!", status PO jadi **RECEIVED** (hijau)
8. **Skenario B — terima sebagian (partial):**
   - Ubah **Qty Diterima** `Laskar Pelangi` dari `10` jadi `4`
   - Klik **SIMPAN PENERIMAAN & UPDATE STOK**
   - ✅ Status PO jadi **PARTIAL** dengan label `4 / 10 pcs`
   - Ulangi langkah 3–7 dengan **No Surat Jalan baru** (`SJ-0002`, faktur `INV-0002`), qty `6` → status jadi **RECEIVED**, stok total 10

✅ **Cek stok naik:** buka menu **MASTER PRODUK**, lihat `Laskar Pelangi` → **Stok = 10** (atau 4 kalau baru partial).

❌ **"Qty diterima melebihi pesanan (12>10)"** → input lebih gede dari sisa. Kecilkan.
❌ **"Produk yang sama muncul lebih dari sekali"** → 2 baris produk sama di 1 surat jalan. Gabung jadi 1 baris.
❌ **Dropdown PO kosong** → PO belum di-approve. Balik ke Bagian 6.
❌ **"PO berstatus draft, tidak bisa diterima"** → approve dulu di Bagian 6.

---

## Bagian 8 — Event (opsional, buat konsinyasi/bazar)

Lewatin kalau cuma mau tes alur reguler.

1. Login **admin**.
2. Klik menu **KELOLA EVENT**.
3. Klik **+ EVENT BARU**:
   - **Nama Event**: `Bazar Desember`
   - **Tanggal Mulai**: `2026-12-01`
   - **Tanggal Selesai**: `2026-12-07`
   - **Lokasi**: `Mall Pondok Gede`
   - **Diskon (%)**: `15`
4. Klik **SIMPAN**.
5. ✅ Status event = **AKTIF**.

**Nutup event:**
- Klik **TUTUP EVENT** → konfirmasi **YA**
- ✅ Status jadi **SELESAI**, sisa stok konsinyasi otomatis dibikin bon retur (RTV)
- ⚠️ **Tutup event gak bisa dibatalin.** Jangan asal pencet.

---

## Bagian 9 — Jualan di Kasir — *GANTI LOGIN ke KASIR*

1. **LOGOUT** dari admin.
2. Login: `kasir@bukukita.test` / `password`.
3. Klik menu **KASIR**.
4. Cari & klik produk `Laskar Pelangi` → masuk keranjang.
5. Klik **BAYAR**, pilih metode, isi nominal, konfirmasi.
6. ✅ Struk muncul, stok otomatis turun.

✅ **Cek stok turun:** login admin → **MASTER PRODUK** → stok `Laskar Pelangi` sekarang `9` (kalau tadi jual 1).

> 💡 **HPP dikunci saat jual.** Kalau nanti harga beli produk naik, laporan penjualan lama **gak berubah**. Ini biar laporan lama gak bisa diakalin.

---

## Bagian 10 — Cek Kartu Stok

1. Login **admin**.
2. Klik menu **GUDANG**.
3. Cari produk `Laskar Pelangi`.
4. Klik **KARTU STOK**.

✅ **Yang harus keliatan** (urutan dari atas):
```
PENERIMAAN   +10   saldo 10
PENJUALAN     -1   saldo  9
```

> 💡 Kartu stok itu buku besar. Tiap gerakan stok wajib ada catatannya dan **gak bisa dihapus**. Ini yang bikin audit trail gak bolong.

---

## Bagian 11 — Laporan

1. Login **owner** atau **supervisor** (admin gak punya akses laporan).
2. Klik menu **LAPORAN**.
3. Ada 12 tab laporan. Coba yang utama:
   - **Vendor Receiving** — barang masuk per vendor
   - **Omzet Produk** — produk paling laku
   - **Event Closing** — hasil per event
4. Set **periode** (dari–sampai) → klik **TERAPKAN**.
5. Klik **EXPORT CSV** → file ke-download, buka pakai Excel.

❌ **Laporan kosong** → periodenya belum nyakup tanggal transaksi. Lebarin tanggalnya.

---

## Bagian 12 — Bandingkan Event

Butuh minimal **2 event** yang udah punya transaksi.

1. Login **supervisor** atau **owner**.
2. Klik menu **HISTORY EVENT**.
3. Klik **BANDINGKAN** di salah satu event.
4. Pilih event pembanding di dropdown.
5. ✅ Muncul tabel perbandingan dengan kolom **Δ (selisih)**.

**Arti warna:**
- 🟢 Hijau = membaik
- 🔴 Merah = memburuk
- ⚪ Abu-abu = sama

> 💡 Retur warnanya dibalik — retur **turun** itu bagus (hijau).

❌ **Klik HISTORY EVENT balik ke dashboard** → lu login pakai admin/kasir. Ganti ke supervisor/owner.

---

## Troubleshooting Cepat

| Gejala | Sebab | Fix |
|---|---|---|
| Dropdown divisi kosong | Hirarki belum dibikin | Bagian 2 |
| Dropdown vendor kosong | Belum bikin vendor | Bagian 3 |
| Tombol APPROVE gak muncul | Login admin, bukan supervisor | Bagian 6 |
| Gak bisa terima barang | PO masih DRAFT | Approve dulu (Bagian 6) |
| "Qty melebihi pesanan" | Input > sisa | Kecilkan qty |
| "Produk dobel 1 SJ" | 2 baris produk sama | Gabung 1 baris |
| Stok tetap 0 | Belum terima barang | Bagian 7 |
| Laporan kosong | Periode salah | Lebarkan tanggal |
| HISTORY EVENT balik dashboard | Role gak punya akses | Login supervisor/owner |
| Menu gak muncul | Role gak punya permission | Cek tabel akun (0.2) |

---

## Aturan Besi (jangan dilanggar)

1. **Urutan wajib:** Hirarki → Vendor → Produk → PO → Approve → Terima. Gak bisa dibalik.
2. **Stok naik cuma lewat Terima**, stok turun cuma lewat Kasir. Gak ada jalur lain.
3. **PO wajib approved** sebelum bisa diterima.
4. **1 surat jalan = 1 produk 1 baris.** Produk sama gak boleh dobel.
5. **Tutup event gak bisa dibatalin.**
6. **HPP dikunci saat transaksi.** Laporan lama gak berubah walau harga naik.
7. **Tiap mutasi stok wajib tercatat** di kartu stok, gak bisa dihapus.
8. **Yang bikin PO gak boleh approve PO sendiri** (admin ≠ supervisor).

---

## Reset Database (kalau mau tes ulang dari nol)

Kalau data udah kacau dan mau mulai bersih lagi:

```bash
cd C:/laragon/www/BukuKita
php artisan migrate:fresh --seed
```

> ⚠️ Ini **hapus semua data** dan bikin ulang 4 akun default. Semua transaksi hilang. Jangan jalanin kalau ada data yang masih butuh.

Setelah reset, mulai lagi dari **Bagian 1**.

---

## Cek Cepat (buat yang males baca panjang)

1. Login `admin@bukukita.test` / `password`
2. Bikin Divisi → Departemen → Kelas → Sub-Kelas
3. Bikin 1 vendor (pilih divisi)
4. Bikin 1 produk (stok 0)
5. Bikin PO + 1 produk qty 10 → status DRAFT
6. Logout, login `supervisor@bukukita.test` / `password`
7. Approve PO → status APPROVED
8. Logout, login `admin@bukukita.test` / `password`
9. Menu TERIMA → isi SJ/Faktur/Penerima → pilih PO → Simpan
10. Cek MASTER PRODUK → stok jadi 10 ✅

Kalau 10 langkah ini lolos, alur inti aman.
