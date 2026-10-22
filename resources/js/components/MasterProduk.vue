<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-4 mb-4 flex justify-between items-center">
      <div>
        <span class="text-xs font-semibold text-[#10b981] uppercase tracking-wider block">LANGKAH 3 DARI 4</span>
        <h1 class="text-base font-bold text-[#0f172a]">KATALOG PRODUK</h1>
        <p class="text-xs text-[#64748b]">Daftar produk aktif. Produk tercipta otomatis dari proses Penerimaan Barang (PO → Receiving).</p>
      </div>
      <a href="/master/vendor" class="px-4 py-2 bg-[#0f172a] hover:bg-[#1e293b] text-white rounded-[2px] text-xs font-semibold transition-colors">← VENDOR</a>
    </div>

    <!-- Filter + Tabel -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-6 shadow-[0_1px_2px_0_rgb(0_0_0/0.05)]">
      <h2 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em] mb-4">FILTER KATALOG</h2>
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
              <th class="p-3">NAMA PRODUK</th>
              <th class="p-3">KATEGORI</th>
              <th class="p-3">VENDOR ASAL</th>
              <th class="p-3 text-right">HARGA JUAL</th>
              <th class="p-3 text-right">STOK</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#e2e8f0]">
            <tr v-if="loading"><td colspan="6" class="p-6 text-center text-[#64748b] font-mono">Memuat data...</td></tr>
            <tr v-for="(p, index) in products" :key="p.id_produk" :class="index % 2 === 0 ? 'bg-white' : 'bg-[#f8fafc]'" class="hover:bg-[#f1f5f9] transition-colors">
              <td class="p-3 font-mono text-xs text-[#64748b]">{{ p.sku }}</td>
              <td class="p-3 font-medium text-[#0f172a]">{{ p.nama_produk }}</td>
              <td class="p-3 text-[#64748b] font-mono text-xs">{{ p.kategori || '-' }}</td>
              <td class="p-3 text-[#64748b] font-mono text-xs">{{ p.vendor_nama || 'BELUM ADA PO' }}</td>
              <td class="p-3 text-right font-mono text-[#0f172a]">Rp {{ Number(p.harga_jual || 0).toLocaleString() }}</td>
              <td class="p-3 text-right font-mono text-[#64748b]">{{ p.stok || 0 }}</td>
            </tr>
            <tr v-if="!loading && products.length === 0">
              <td colspan="6" class="p-6 text-center text-[#64748b]">Belum ada produk. Buat PO lalu terima barang di halaman Penerimaan.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  name: 'MasterProduk',
  data() { return { products: [], loading: false, selectedDivisi: '', divisiList: [], allClasses: [], allDepartments: [], allSubclasses: [], selectedHierarchy: {} }; },
  mounted() { this.loadKelasDivisi(); },
  methods: {
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
