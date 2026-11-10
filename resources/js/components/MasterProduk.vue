<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-4 mb-4 flex justify-between items-center">
      <div>
        <span class="text-xs font-semibold text-[#10b981] uppercase tracking-wider block">SPESIFIKASI 2.3 &amp; 8.1 — MASTER PRODUK &amp; BULK INJECT</span>
        <h1 class="text-base font-bold text-[#0f172a]">MASTER PRODUK</h1>
        <p class="text-xs text-[#64748b]">Daftar produk yang sudah dimiliki toko. Bisa ditambah manual atau inject massal via CSV/Excel.</p>
      </div>
      <div class="flex gap-2">
        <button @click="showBulkModal = true" class="px-4 py-2 bg-[#0f172a] hover:bg-[#1e293b] text-white rounded-[2px] text-xs font-semibold transition-colors">📦 BULK INJECT CSV</button>
        <button @click="openCreateModal" class="px-4 py-2 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-xs font-semibold transition-colors">+ TAMBAH PRODUK</button>
      </div>
    </div>

    <!-- Filter + Tabel -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-6 shadow-[0_1px_2px_0_rgb(0_0_0/0.05)]">
      <h2 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em] mb-4">FILTER PRODUK</h2>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">PILIH DIVISI</label>
          <select v-model="selectedDivisi" @change="updateHierarki" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] focus:border-[#10b981] font-mono">
            <option value="">[ SEMUA DIVISI ]</option>
            <option v-for="d in divisiList" :key="d.id_divisi" :value="d.id_divisi">{{ d.nama_divisi }}</option>
          </select>
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">DEPARTEMEN</label>
          <input :value="selectedHierarchy.dept" disabled class="w-full bg-[#f8fafc] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#64748b] font-mono" />
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">KELAS</label>
          <input :value="selectedHierarchy.kelas" disabled class="w-full bg-[#f8fafc] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#64748b] font-mono" />
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">SUB-KELAS</label>
          <input :value="selectedHierarchy.subkelas" disabled class="w-full bg-[#f8fafc] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#64748b] font-mono" />
        </div>
      </div>
      <div class="flex justify-end mb-6">
        <button @click="loadProducts" class="px-6 py-2 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-sm font-semibold shadow-sm transition-colors">LOAD DATA</button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
          <thead>
            <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[12px] font-semibold uppercase tracking-[0.05em]">
              <th class="p-3">SKU</th>
              <th class="p-3">ISBN</th>
              <th class="p-3">NAMA PRODUK</th>
              <th class="p-3">PENULIS</th>
              <th class="p-3">PENERBIT</th>
              <th class="p-3">KATEGORI</th>
              <th class="p-3 text-right">HARGA JUAL</th>
              <th class="p-3 text-right">STOK</th>
              <th class="p-3 text-right">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#e2e8f0]">
            <tr v-if="loading"><td colspan="9" class="p-6 text-center text-[#64748b] font-mono">Memuat data...</td></tr>
            <tr v-for="(p, index) in products" :key="p.id_produk" :class="index % 2 === 0 ? 'bg-white' : 'bg-[#f8fafc]'" class="hover:bg-[#f1f5f9] transition-colors">
              <td class="p-3 font-mono text-xs text-[#64748b]">{{ p.sku }}</td>
              <td class="p-3 font-mono text-xs text-[#64748b]">{{ p.isbn || '-' }}</td>
              <td class="p-3 font-medium text-[#0f172a]">{{ p.nama_produk }}</td>
              <td class="p-3 text-[#64748b] font-mono text-xs">{{ p.deskripsi_produk || '-' }}</td>
              <td class="p-3 text-[#64748b] font-mono text-xs">{{ p.penerbit || '-' }}</td>
              <td class="p-3 text-[#64748b] font-mono text-xs">{{ p.kategori || '-' }}</td>
              <td class="p-3 text-right font-mono text-[#0f172a]">Rp {{ Number(p.harga_jual || 0).toLocaleString() }}</td>
              <td class="p-3 text-right font-mono text-[#64748b]">{{ p.stok || 0 }}</td>
              <td class="p-3 text-right">
                <button @click="openEditModal(p)" class="px-2.5 py-1 bg-[#0f172a] hover:bg-[#1e293b] text-white rounded-[2px] text-xs font-semibold transition-colors">
                  EDIT
                </button>
              </td>
            </tr>
            <tr v-if="!loading && products.length === 0">
              <td colspan="9" class="p-6 text-center text-[#64748b]">Belum ada produk.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Modal Bulk Inject -->
    <Teleport to="body">
    <div v-if="showBulkModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
      <div class="bg-white rounded-[4px] shadow-xl max-w-lg w-full p-6">
        <h3 class="text-sm font-bold text-[#0f172a] uppercase mb-4">BULK INJECT CSV</h3>
        <p class="text-xs text-[#64748b] mb-4">Pastikan format CSV sesuai kolom (sku, nama_produk, id_departemen, harga_jual, harga_beli, dll).</p>
        <input type="file" ref="csvFile" @change="handleFile" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
        <div class="flex justify-end gap-2 mt-6">
          <button @click="showBulkModal = false" class="px-4 py-2 border border-[#e2e8f0] rounded-[2px] text-xs font-semibold text-[#64748b]">BATAL</button>
          <button @click="submitBulk" class="px-4 py-2 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-xs font-semibold">PROCESS CSV</button>
        </div>
      </div>
    </div>
    </Teleport>
    <!-- Modal Edit / Create Produk (Owner / Supervisor Guard) -->
    <Teleport to="body">
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
      <div class="bg-white rounded-[4px] shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto relative">
        <h3 class="text-sm font-bold text-[#0f172a] uppercase mb-4">{{ isEditing ? 'EDIT PRODUK (MASTER)' : 'TAMBAH PRODUK BARU' }}</h3>
        <div class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">SKU *</label>
              <input v-model="editForm.sku" :disabled="isEditing" placeholder="SKU-001" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono disabled:bg-[#f8fafc]" />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">ISBN</label>
              <input v-model="editForm.isbn" placeholder="ISBN" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
            </div>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Nama Produk *</label>
            <input v-model="editForm.nama_produk" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
          </div>
          <div v-if="!isEditing" class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Departemen *</label>
              <select v-model="editForm.id_departemen" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-xs font-mono">
                <option value="">[ Pilih Dept ]</option>
                <option v-for="d in allDepartments" :key="d.id_departemen" :value="d.id_departemen">{{ d.nama_departemen }}</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Sub-Kelas</label>
              <select v-model="editForm.id_subkelas" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-xs font-mono">
                <option value="">[ Opsional ]</option>
                <option v-for="s in filteredSubclassesForForm" :key="s.id_subkelas" :value="s.id_subkelas">{{ s.nama_subkelas }}</option>
              </select>
            </div>
          </div>
          <div v-if="!isEditing" class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Pemasok Utama</label>
              <select v-model="editForm.id_pemasok" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-xs font-mono">
                <option value="">[ Pilih Vendor ]</option>
                <option v-for="v in vendorList" :key="v.id_pemasok" :value="v.id_pemasok">{{ v.nama_pemasok }}</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Stok Awal</label>
              <input v-model.number="editForm.stok" type="number" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Pengarang / Deskripsi</label>
              <input v-model="editForm.deskripsi_produk" placeholder="Pengarang/Ukuran" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Penerbit</label>
              <input v-model="editForm.penerbit" placeholder="Penerbit" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Harga Beli (Rp)</label>
              <input v-model.number="editForm.harga_beli" type="number" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Harga Jual (Rp) *</label>
              <input v-model.number="editForm.harga_jual" type="number" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
            </div>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Foto Produk (URL .webp)</label>
            <input v-model="editForm.foto_produk" placeholder="https://contoh.com/buku.webp" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
            <p class="text-[10px] text-[#94a3b8] mt-1">Wajib berakhiran .webp — format lain ditolak sistem.</p>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <button @click="showEditModal = false" class="px-4 py-2 border border-[#e2e8f0] rounded-[2px] text-xs font-semibold text-[#64748b]">BATAL</button>
          <button @click="submitEditProduct" class="px-4 py-2 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-xs font-semibold">
            {{ isEditing ? 'SIMPAN PERUBAHAN' : 'BUAT PRODUK' }}
          </button>
        </div>
      </div>
    </div>
    </Teleport>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  name: 'MasterProduk',
  data() { return { products: [], loading: false, selectedDivisi: '', divisiList: [], allClasses: [], allDepartments: [], allSubclasses: [], selectedHierarchy: {}, showEditModal: false, isEditing: false, showBulkModal: false, editForm: {}, bulkFile: null, bulkResult: null, vendorList: [] }; },
  computed: {
    // Sub-kelas disaring ikut departemen terpilih di form.
    filteredSubclassesForForm() {
      if (!this.editForm.id_departemen) return this.allSubclasses;
      return this.allSubclasses.filter(s => String(s.id_departemen) === String(this.editForm.id_departemen));
    },
  },
  mounted() { this.loadKelasDivisi(); this.loadVendors(); },
  methods: {
    openCreateModal() {
      const user = JSON.parse(localStorage.getItem('user') || '{}');
      const roleName = (user.role?.name || '').toLowerCase();
      if (roleName !== 'owner' && roleName !== 'supervisor') {
        alert('Akses ditolak: Hanya Owner dan Supervisor yang berhak menambah master produk.');
        return;
      }
      this.isEditing = false;
      this.editForm = {
        sku: '', nama_produk: '', isbn: '', deskripsi_produk: '', penerbit: '',
        id_departemen: '', id_subkelas: '', id_pemasok: '', stok: 0,
        harga_beli: 0, harga_jual: 0, foto_produk: '',
      };
      this.showEditModal = true;
    },
    async loadVendors() {
      try {
        const res = await axios.get('/api/vendors', { headers: { Authorization: `Bearer ${localStorage.token || ''}` } });
        this.vendorList = res.data.data || res.data || [];
      } catch (e) { console.error('Gagal load vendor', e); }
    },
    // Adapter kolom CSV (Indonesia friendly) -> payload API
    normRow(r) {
      const low = {};
      Object.keys(r).forEach(k => { low[String(k).trim().toLowerCase().replace(/\s+/g, '_')] = r[k]; });
      return {
        sku: String(low.sku ?? '').trim(),
        nama_produk: String(low.nama_produk ?? low.judul_buku ?? '').trim(),
        isbn: String(low.isbn ?? '').trim() || null,
        deskripsi_produk: String(low.penulis ?? '').trim() || null,
        penerbit: String(low.penerbit ?? '').trim() || null,
        id_departemen: String(low.id_departemen ?? 'DEP-00001').trim(),
        id_subkelas: String(low.id_subkelas ?? '').trim() || null,
        id_pemasok: String(low.id_pemasok ?? '').trim() || null,
        harga_beli: Number(low.harga_beli ?? 0),
        harga_jual: Number(low.harga_jual ?? 0),
        stok: Number(low.stok_awal ?? low.stok ?? 0),
        foto_produk: String(low.foto_produk ?? 'default.webp').trim(),
      };
    },
    handleFile(e) {
      this.bulkFile = e.target.files[0] || null;
    },
    async submitBulk() {
      if (!this.bulkFile) { alert('Pilih file CSV dulu.'); return; }
      try {
        const text = await this.bulkFile.text();
        const rows = this.parseCsv(text).map(r => this.normRow(r));
        if (!rows.length) { alert('CSV kosong / header tidak terbaca.'); return; }
        const res = await axios.post('/api/products/bulk', { rows }, {
          headers: { Authorization: `Bearer ${localStorage.token || ''}` },
        });
        const d = res.data;
        this.bulkResult = d;
        let msg = `Sukses: ${d.sukses}/${d.total} baris.`;
        if (d.gagal?.length) {
          msg += '\n\nGagal:\n' + d.gagal.map(g => `• Baris ${g.baris} (${g.sku}): ${g.alasan}`).join('\n');
        }
        alert(msg);
        if (d.sukses > 0) { this.showBulkModal = false; this.loadProducts(); }
      } catch (e) {
        alert(e.response?.data?.message || 'Gagal proses bulk inject.');
      }
    },
    // CSV parser minimal
    parseCsv(text) {
      const lines = text.split(String.fromCharCode(10)).filter(l => l.trim() !== '');
      if (lines.length < 2) return [];
      const split = (line) => {
        const out = []; let cur = ''; let q = false;
        for (let i = 0; i < line.length; i++) {
          const c = line[i];
          if (q) {
            if (c === '"' && line[i + 1] === '"') { cur += '"'; i++; }
            else if (c === '"') q = false;
            else cur += c;
          } else if (c === '"') q = true;
          else if (c === ',' || c === ';') { out.push(cur); cur = ''; }
          else cur += c;
        }
        out.push(cur);
        return out;
      };
      const head = split(lines[0]).map(h => h.trim());
      return lines.slice(1).map(l => {
        const cells = split(l);
        const o = {}; head.forEach((h, i) => { o[h] = (cells[i] ?? '').trim(); });
        return o;
      });
    },
    openEditModal(p) {
      const user = JSON.parse(localStorage.getItem('user') || '{}');
      const roleName = (user.role?.name || '').toLowerCase();
      if (roleName !== 'owner' && roleName !== 'supervisor') {
        alert('Akses ditolak: Hanya Owner dan Supervisor yang berhak mengedit master produk.');
        return;
      }
      this.isEditing = true;
      this.editForm = { ...p };
      this.showEditModal = true;
    },
    async submitEditProduct() {
      try {
        const token = localStorage.token || '';
        const hdr = { Authorization: `Bearer ${token}` };
        if (this.isEditing) {
          await axios.put(`/api/products/${this.editForm.id_produk}`, this.editForm, { headers: hdr });
          alert('Produk berhasil diperbarui.');
        } else {
          await axios.post('/api/products', this.editForm, { headers: hdr });
          alert('Produk berhasil ditambahkan.');
        }
        this.showEditModal = false;
        this.loadProducts();
      } catch (e) {
        alert(e.response?.data?.message || 'Gagal menyimpan produk.');
      }
    },
    async loadKelasDivisi() {
      try {
        const token = localStorage.token || '';
        const [kelasRes, divRes, deptRes, subRes] = await Promise.all([
          axios.get('/api/classes', { headers: { Authorization: `Bearer ${token}` } }),
          axios.get('/api/divisions', { headers: { Authorization: `Bearer ${token}` } }),
          axios.get('/api/departments', { headers: { Authorization: `Bearer ${token}` } }),
          axios.get('/api/subclasses', { headers: { Authorization: `Bearer ${token}` } })
        ]);
        this.allClasses = kelasRes.data.data || kelasRes.data || [];
        this.divisiList = divRes.data.data || divRes.data || [];
        this.allDepartments = deptRes.data.data || deptRes.data || [];
        this.allSubclasses = subRes.data.data || subRes.data || [];
        this.selectedHierarchy = { dept: '', kelas: '', subkelas: '' };
      } catch (e) { console.error('Gagal load filter', e); }
    },
    updateHierarki() {
      const divId = this.selectedDivisi;
      if (!divId) {
        this.selectedHierarchy = { dept: '', kelas: '', subkelas: '' };
        return;
      }
      const dept = this.allDepartments.find(d => d.id_divisi === divId);
      const deptId = dept ? dept.id_departemen : null;
      const cls = deptId ? this.allClasses.find(c => c.id_departemen === deptId) : null;
      const clsId = cls ? cls.id_kelas : null;
      const sub = clsId ? this.allSubclasses.find(s => s.id_kelas === clsId) : null;
      this.selectedHierarchy = {
        dept: dept ? `${dept.nama_departemen} (${dept.id_departemen})` : '-',
        kelas: cls ? `${cls.nama_kelas} (${cls.id_kelas})` : '-',
        subkelas: sub ? `${sub.nama_subkelas} (${sub.id_subkelas})` : '-'
      };
    },
    async loadProducts() {
      this.loading = true;
      try {
        const token = localStorage.token || '';
        let url = '/api/products';
        let params = {};
        if (this.selectedDivisi) params.id_divisi = this.selectedDivisi;
        const res = await axios.get(url, { params, headers: { Authorization: `Bearer ${token}` } });
        this.products = res.data.data || res.data || [];
        // Load vendor nama untuk setiap produk
        const vendorIds = [...new Set(this.products.map(p => p.id_pemasok).filter(Boolean))];
        if (vendorIds.length > 0) {
          const vRes = await axios.get('/api/vendors', { headers: { Authorization: `Bearer ${token}` } });
          const vMap = {};
          (vRes.data.data || vRes.data || []).forEach(v => { vMap[v.id_pemasok] = v.nama_pemasok; });
          this.products.forEach(p => { p.vendor_nama = vMap[p.id_pemasok] || ''; });
        }
      } catch (e) { console.error('Gagal load produk', e); } finally { this.loading = false; }
    }
  }
};
</script>
