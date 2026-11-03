<template>
  <div class="space-y-4">
    <!-- Tab switcher -->
    <div class="flex gap-2 border-b border-[#e2e8f0]">
      <button @click="currentTab = 'STOK'" :class="currentTab === 'STOK' ? 'border-[#0f172a] text-[#0f172a]' : 'border-transparent text-[#64748b] hover:text-[#0f172a]'" class="px-4 py-2 text-xs font-semibold uppercase border-b-2 transition-colors">Stok Master</button>
      <button @click="currentTab = 'RETUR'" :class="currentTab === 'RETUR' ? 'border-[#0f172a] text-[#0f172a]' : 'border-transparent text-[#64748b] hover:text-[#0f172a]'" class="px-4 py-2 text-xs font-semibold uppercase border-b-2 transition-colors">Retur Vendor</button>
    </div>

    <div v-if="currentTab === 'STOK'">
      <!-- Header: search + filter rendah -->
      <div class="flex flex-wrap items-center gap-3">
        <h2 class="text-lg font-semibold text-[#0f172a] uppercase tracking-[0.02em]">GUDANG & INVENTORI</h2>
      <div class="flex-1"></div>
      <input v-model="search" @input="debouncedFetch" placeholder="Cari nama / SKU..."
        class="bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-1.5 text-sm font-mono" />
      <label class="flex items-center gap-1 text-xs text-[#64748b]">
        <input type="checkbox" v-model="onlyLow" @change="fetchStock" /> Stok rendah
      </label>
    </div>

    <!-- Tabel master stok -->
    <div class="bg-white border border-[#e2e8f0] rounded-[4px] shadow-[0_1px_2px_0_rgb(0_0_0/0.05)] overflow-hidden">
      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[12px] font-semibold uppercase tracking-[0.05em]">
            <th class="p-3">SKU</th>
            <th class="p-3">Produk</th>
            <th class="p-3">Pemasok</th>
            <th class="p-3 text-right">Stok</th>
            <th class="p-3 text-right">Min</th>
            <th class="p-3">Status</th>
            <th class="p-3 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in products" :key="p.id_produk" class="border-b border-[#e2e8f0]">
            <td class="p-3 font-mono text-xs text-[#64748b]">{{ p.sku }}</td>
            <td class="p-3">{{ p.nama_produk }}</td>
            <td class="p-3 text-xs text-[#64748b]">{{ p.id_pemasok || '-' }}</td>
            <td class="p-3 text-right font-mono font-medium" :class="p.stok < p.min_stock ? 'text-[#ef4444]' : ''">{{ p.stok }}</td>
            <td class="p-3 text-right font-mono text-[#64748b]">{{ p.min_stock }}</td>
            <td class="p-3">
              <span v-if="p.stok < p.min_stock" class="text-[10px] font-bold uppercase bg-[#fee2e2] text-[#ef4444] rounded-[2px] px-1.5 py-0.5">RENDAH</span>
              <span v-else class="text-[10px] font-bold uppercase bg-[#dcfce7] text-[#059669] rounded-[2px] px-1.5 py-0.5">AMAN</span>
            </td>
            <td class="p-3 text-right">
              <button @click="openKartu(p)" class="text-[#10b981] hover:underline text-xs font-semibold">KARTU STOK</button>
              <button @click="openAdjust(p)" class="ml-2 text-[#0f172a] hover:underline text-xs font-semibold">OPNAME</button>
            </td>
          </tr>
          <tr v-if="products.length === 0">
            <td colspan="7" class="p-6 text-center text-[#64748b]">Stok kosong / tidak ditemukan.</td>
          </tr>
        </tbody>
      </table>
      <div class="flex justify-between items-center px-3 py-2 text-xs text-[#64748b]">
        <span>Halaman {{ page }} dari {{ lastPage }}</span>
        <div class="flex gap-2">
          <button :disabled="page <= 1" @click="page--; fetchStock()" class="px-2 py-1 border border-[#e2e8f0] rounded-[2px] disabled:opacity-40">PREV</button>
          <button :disabled="page >= lastPage" @click="page++; fetchStock()" class="px-2 py-1 border border-[#e2e8f0] rounded-[2px] disabled:opacity-40">NEXT</button>
        </div>
      </div>
    </div>
    </div>

    <!-- Tab Retur -->
    <div v-if="currentTab === 'RETUR'" class="space-y-4">
      <div class="bg-white border border-[#e2e8f0] rounded-[4px] p-6 max-w-2xl mx-auto">
        <h3 class="text-sm font-bold text-[#0f172a] uppercase mb-4">PENGEMBALIAN BARANG KE PEMASOK</h3>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Pemasok *</label>
              <select v-model="returForm.id_pemasok" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm bg-white font-mono">
                <option value="">[ Pilih Vendor ]</option>
                <option v-for="v in vendors" :key="v.id_pemasok" :value="v.id_pemasok">{{ v.nama_pemasok }}</option>
              </select>
            </div>
            <div>
              <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Cari Produk *</label>
              <input v-model="returSearch" @input="searchReturProduk" placeholder="Ketik nama/SKU..." class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
              <div v-if="returResults.length" class="absolute z-10 bg-white border border-[#e2e8f0] rounded-[2px] shadow-lg mt-1 w-[300px] max-h-48 overflow-y-auto">
                <button v-for="rp in returResults" :key="rp.id_produk" @click="selectReturProduk(rp)" class="w-full text-left px-3 py-2 text-xs hover:bg-[#f8fafc] border-b border-[#f1f5f9]">
                  <p class="font-bold text-[#0f172a]">{{ rp.nama_produk }}</p>
                  <p class="text-[10px] text-[#64748b]">{{ rp.sku }} — Stok: {{ rp.stok }}</p>
                </button>
              </div>
            </div>
          </div>

          <div v-if="selectedReturProduk" class="bg-[#f8fafc] border border-[#e2e8f0] rounded-[2px] p-3 flex justify-between items-center">
            <div>
              <p class="text-[10px] uppercase font-bold text-[#64748b]">Produk Terpilih</p>
              <p class="text-xs font-semibold text-[#0f172a]">{{ selectedReturProduk.nama_produk }} ({{ selectedReturProduk.sku }})</p>
            </div>
            <div class="text-right">
              <p class="text-[10px] uppercase font-bold text-[#64748b]">Stok Tersedia</p>
              <p class="text-xs font-mono font-bold text-[#10b981]">{{ selectedReturProduk.stok }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Jumlah Retur *</label>
              <input v-model.number="returForm.jumlah_dikembalikan" type="number" min="1" :max="selectedReturProduk ? selectedReturProduk.stok : undefined" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
            </div>
            <div>
              <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Alasan *</label>
              <input v-model="returForm.alasan" placeholder="cth: Barang rusak / Event selesai" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm" />
            </div>
          </div>

          <button @click="submitRetur" class="w-full bg-[#ef4444] hover:bg-[#dc2626] text-white rounded-[2px] py-2 text-sm font-semibold transition-colors mt-2">
            PROSES PENGEMBALIAN BARANG
          </button>
        </div>
      </div>

      <!-- Histori Retur -->
      <div class="bg-white border border-[#e2e8f0] rounded-[4px] overflow-hidden">
        <div class="px-5 py-3 border-b border-[#e2e8f0] bg-[#f8fafc]">
          <h4 class="text-[11px] font-bold text-[#64748b] uppercase tracking-wider">Histori Retur Terakhir</h4>
        </div>
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="border-b border-[#e2e8f0] text-[#64748b] uppercase font-semibold">
              <th class="p-3">Tanggal</th>
              <th class="p-3">ID Retur</th>
              <th class="p-3">Vendor</th>
              <th class="p-3">Produk</th>
              <th class="p-3 text-right">Qty</th>
              <th class="p-3">Alasan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in historyRetur" :key="r.id_pengembalian" class="border-b border-[#f1f5f9]">
              <td class="p-3 font-mono">{{ r.tanggal_pengembalian }}</td>
              <td class="p-3 font-mono text-[#10b981]">{{ r.id_pengembalian }}</td>
              <td class="p-3">{{ r.id_pemasok }}</td>
              <td class="p-3">{{ r.nama_produk }}</td>
              <td class="p-3 text-right font-mono font-bold text-[#ef4444]">-{{ r.jumlah_dikembalikan }}</td>
              <td class="p-3 text-[#64748b]">{{ r.alasan }}</td>
            </tr>
            <tr v-if="historyRetur.length === 0">
              <td colspan="6" class="p-6 text-center text-[#64748b]">Belum ada histori retur.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Kartu Stok -->
    <div v-if="kartu" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4" @click.self="kartu = null">
      <div class="bg-white rounded-[4px] shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-5 py-3 border-b border-[#e2e8f0]">
          <h3 class="text-sm font-semibold uppercase tracking-[0.02em]">Kartu Stok — {{ kartu.produk.nama_produk }}</h3>
          <button @click="kartu = null" class="text-[#64748b] hover:text-[#0f172a] text-lg leading-none">&times;</button>
        </div>
        <div class="p-5">
          <div class="flex gap-4 text-xs text-[#64748b] mb-3 font-mono">
            <span>SKU: {{ kartu.produk.sku }}</span>
            <span>Stok Saat Ini: <b class="text-[#0f172a]">{{ kartu.produk.stok }}</b></span>
            <span>Min: {{ kartu.produk.min_stock }}</span>
          </div>
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] uppercase">
                <th class="p-2">Tanggal</th>
                <th class="p-2">Jenis</th>
                <th class="p-2 text-right">Delta</th>
                <th class="p-2 text-right">Saldo</th>
                <th class="p-2">Ref</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in kartu.mutasi" :key="m.id_perubahan_stok" class="border-b border-[#f1f5f9]">
                <td class="p-2 font-mono">{{ m.tanggal_perubahan }}</td>
                <td class="p-2">
                  <span :class="jenisColor(m.jenis_perubahan)">{{ m.jenis_perubahan }}</span>
                </td>
                <td class="p-2 text-right font-mono" :class="m.jumlah_perubahan >= 0 ? 'text-[#059669]' : 'text-[#ef4444]'">
                  {{ m.jumlah_perubahan >= 0 ? '+' : '' }}{{ m.jumlah_perubahan }}
                </td>
                <td class="p-2 text-right font-mono">{{ m.saldo_setelah }}</td>
                <td class="p-2 font-mono text-[#94a3b8]">{{ m.ref_type }}{{ m.ref_id ? ':' + m.ref_id : '' }}</td>
              </tr>
              <tr v-if="kartu.mutasi.length === 0">
                <td colspan="5" class="p-4 text-center text-[#64748b]">Belum ada mutasi.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Opname -->
    <div v-if="adjust" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4" @click.self="adjust = null">
      <div class="bg-white rounded-[4px] shadow-xl w-full max-w-sm">
        <div class="flex justify-between items-center px-5 py-3 border-b border-[#e2e8f0]">
          <h3 class="text-sm font-semibold uppercase tracking-[0.02em]">Stock Opname — {{ adjust.nama_produk }}</h3>
          <button @click="adjust = null" class="text-[#64748b] hover:text-[#0f172a] text-lg leading-none">&times;</button>
        </div>
        <div class="p-5 space-y-3">
          <div class="text-xs text-[#64748b]">Stok sistem: <b class="text-[#0f172a] font-mono">{{ adjust.stok }}</b></div>
          <div>
            <label class="block text-[10px] font-bold text-[#64748b] uppercase tracking-wider mb-1">Stok Fisik</label>
            <input v-model.number="adjust.fisik" type="number" min="0" class="w-full bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm font-mono" />
          </div>
          <div v-if="adjust.fisik !== null && adjust.fisik !== adjust.stok" class="text-xs font-mono"
            :class="adjust.fisik - adjust.stok >= 0 ? 'text-[#059669]' : 'text-[#ef4444]'">
            Delta: {{ adjust.fisik - adjust.stok >= 0 ? '+' : '' }}{{ adjust.fisik - adjust.stok }}
          </div>
          <div>
            <label class="block text-[10px] font-bold text-[#64748b] uppercase tracking-wider mb-1">Catatan</label>
            <input v-model="adjust.catatan" placeholder="cth: hasil hitung fisik 19/10" class="w-full bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm" />
          </div>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t border-[#e2e8f0]">
          <button @click="adjust = null" class="px-4 py-2 border border-[#e2e8f0] rounded-[2px] text-xs font-semibold text-[#64748b]">BATAL</button>
          <button @click="submitAdjust()" class="px-4 py-2 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-xs font-semibold">SESUAIKAN</button>
        </div>
      </div>
    </div>

    <div v-if="alert.show" :class="['fixed bottom-4 right-4 px-4 py-3 rounded-[4px] text-sm text-white shadow-lg z-[60]', alert.type === 'error' ? 'bg-[#ef4444]' : 'bg-[#0f172a]']">
      {{ alert.msg }}
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return {
      products: [],
      currentTab: 'STOK',
      vendors: [],
      returForm: { id_pemasok: '', id_produk: '', jumlah_dikembalikan: 1, alasan: '' },
      returSearch: '',
      returResults: [],
      selectedReturProduk: null,
      historyRetur: [],
      _returDeb: null,
      search: '',
      onlyLow: false,
      page: 1,
      lastPage: 1,
      kartu: null,
      adjust: null,
      alert: { show: false, msg: '', type: 'info' },
      _deb: null,
    };
  },
  mounted() { this.fetchStock(); this.loadVendors(); this.fetchHistoryRetur(); },
  methods: {
    headers() { return { Authorization: `Bearer ${localStorage.token || ''}` }; },
    async loadVendors() {
      try {
        const res = await axios.get('/api/vendors', { headers: this.headers() });
        this.vendors = res.data.data ?? res.data ?? [];
      } catch (e) { console.error(e); }
    },
    async fetchHistoryRetur() {
      try {
        const res = await axios.get('/api/vendor-returns', { headers: this.headers(), params: { per_page: 10 } });
        this.historyRetur = res.data.data ?? res.data ?? [];
      } catch (e) { console.error(e); }
    },
    searchReturProduk() {
      clearTimeout(this._returDeb);
      this._returDeb = setTimeout(async () => {
        if (!this.returSearch.trim()) { this.returResults = []; return; }
        try {
          const res = await axios.get('/api/products', { headers: this.headers(), params: { search: this.returSearch, per_page: 10 } });
          const d = res.data.data ?? res.data;
          this.returResults = d.data ?? d ?? [];
        } catch (e) { this.returResults = []; }
      }, 250);
    },
    selectReturProduk(p) {
      this.selectedReturProduk = p;
      this.returForm.id_produk = p.id_produk;
      this.returSearch = `${p.nama_produk} (${p.sku})`;
      this.returResults = [];
    },
    async submitRetur() {
      if (!this.returForm.id_pemasok) return this.showAlert('Pilih pemasok dulu.', 'error');
      if (!this.returForm.id_produk) return this.showAlert('Pilih produk dulu.', 'error');
      if (!this.returForm.alasan) return this.showAlert('Alasan wajib diisi.', 'error');
      if (this.selectedReturProduk && this.returForm.jumlah_dikembalikan > this.selectedReturProduk.stok) {
        return this.showAlert('Jumlah retur melebihi stok tersedia.', 'error');
      }
      try {
        await axios.post('/api/vendor-returns', this.returForm, { headers: this.headers() });
        this.showAlert('Retur vendor tercatat, stok dipotong.');
        this.returForm = { id_pemasok: '', id_produk: '', jumlah_dikembalikan: 1, alasan: '' };
        this.selectedReturProduk = null;
        this.returSearch = '';
        this.fetchStock();
        this.fetchHistoryRetur();
      } catch (e) {
        this.showAlert(e.response?.data?.message || 'Gagal proses retur.', 'error');
      }
    },
    showAlert(msg, type = 'info') {
      this.alert = { show: true, msg, type };
      setTimeout(() => (this.alert.show = false), 2500);
    },
    debouncedFetch() {
      clearTimeout(this._deb);
      this._deb = setTimeout(() => { this.page = 1; this.fetchStock(); }, 250);
    },
    async fetchStock() {
      const res = await axios.get('/api/warehouse', {
        headers: this.headers(),
        params: { search: this.search || undefined, low: this.onlyLow ? '1' : undefined, page: this.page, per_page: 20 },
      });
      const d = res.data.data ?? res.data;
      this.products = d.data ?? d;
      this.lastPage = d.last_page ?? 1;
    },
    async openKartu(p) {
      const res = await axios.get(`/api/warehouse/${p.id_produk}/kartu-stok`, { headers: this.headers() });
      this.kartu = res.data;
    },
    openAdjust(p) {
      this.adjust = { id_produk: p.id_produk, nama_produk: p.nama_produk, stok: p.stok, fisik: p.stok, catatan: '' };
    },
    async submitAdjust() {
      if (this.adjust.fisik === null || this.adjust.fisik === this.adjust.stok) {
        this.showAlert('Stok fisik sama dengan sistem, tidak ada perubahan.', 'error');
        return;
      }
      try {
        await axios.post('/api/warehouse/adjust', {
          id_produk: this.adjust.id_produk,
          stok_fisik: this.adjust.fisik,
          catatan: this.adjust.catatan,
        }, { headers: this.headers() });
        this.showAlert('Stok disesuaikan (PENYESUAIAN).');
        this.adjust = null;
        this.fetchStock();
      } catch (e) {
        this.showAlert(e.response?.data?.message || 'Gagal opname.', 'error');
      }
    },
    jenisColor(j) {
      const map = {
        PENERIMAAN: 'text-[#059669]', PENJUALAN: 'text-[#ef4444]',
        PENGEMBALIAN_PEMASOK: 'text-[#ef4444]', PENGEMBALIAN_GUDANG: 'text-[#059669]',
        LOAD_EVENT: 'text-[#ef4444]', PENYESUAIAN: 'text-[#0f172a]', REFUND: 'text-[#0f172a]',
      };
      return map[j] || 'text-[#64748b]';
    },
  },
};
</script>
