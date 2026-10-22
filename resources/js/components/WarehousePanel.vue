<template>
  <div class="space-y-4">
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
  mounted() { this.fetchStock(); },
  methods: {
    headers() { return { Authorization: `Bearer ${localStorage.token || ''}` }; },
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
