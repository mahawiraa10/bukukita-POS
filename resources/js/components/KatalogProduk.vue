<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-[#0f172a] rounded-[4px] px-5 py-3">
      <h1 class="text-sm font-bold text-white uppercase tracking-[0.03em]">KATALOG PRODUK</h1>
      <p class="text-[11px] text-slate-400 mt-0.5">Daftar seluruh produk + hierarki 4 level. Buat PO di <a href="/operational/po" class="underline hover:text-white">Purchase Order</a>.</p>
    </div>

    <!-- Top Bar: Filter + Search -->
    <div class="flex justify-between items-center gap-4">
      <div class="flex items-center gap-2">
        <button @click="showFilterModal = true" class="flex items-center gap-2 px-4 py-2 bg-white border border-[#e2e8f0] rounded-[4px] text-xs font-semibold text-[#0f172a] hover:bg-[#f8fafc] transition-colors shadow-sm">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
          <span>FILTER</span>
        </button>
        <div class="flex items-center border border-[#e2e8f0] rounded-[4px] bg-white overflow-hidden w-full max-w-md shadow-sm">
          <div class="flex items-center px-3 text-[#94a3b8]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <input v-model="search" @keyup.enter="fetchProducts()" placeholder="Cari nama / SKU..." class="w-full py-2 text-sm text-[#0f172a] outline-none placeholder:text-[#94a3b8]" />
        </div>
      </div>
      <span class="text-xs font-mono text-[#64748b] shrink-0">Total: {{ products.length }} produk</span>
    </div>

    <!-- Grid Produk (gaya e-commerce) -->
    <div v-if="loading" class="py-16 text-center text-[#64748b] font-mono text-sm">Memuat data...</div>

    <div v-else-if="products.length === 0" class="bg-white border border-[#e2e8f0] rounded-[4px] py-16 text-center text-[#64748b] text-sm">
      Tidak ada produk ditemukan.
    </div>

    <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
      <button
        v-for="p in products" :key="p.id_produk"
        @click="openDetail(p)"
        class="group text-left bg-white border border-[#e2e8f0] rounded-[4px] overflow-hidden hover:border-[#10b981] hover:shadow-[0_4px_12px_rgb(0_0_0/0.06)] transition-all flex flex-col"
      >
        <!-- Cover -->
        <div class="relative w-full aspect-[3/4] bg-[#f8fafc] border-b border-[#e2e8f0] overflow-hidden">
          <img v-if="p.foto_produk" :src="p.foto_produk" :alt="p.nama_produk"
               class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-200"
               loading="lazy" @error="p.foto_produk = ''" />
          <div v-else class="w-full h-full flex items-center justify-center text-[#cbd5e1]">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          <!-- Badge stok -->
          <span v-if="p.stok <= 0" class="absolute top-2 left-2 px-2 py-0.5 rounded-[2px] text-[10px] font-bold uppercase bg-[#ef4444] text-white">Habis</span>
          <span v-else-if="p.stok <= (p.min_stock || 0)" class="absolute top-2 left-2 px-2 py-0.5 rounded-[2px] text-[10px] font-bold uppercase bg-[#f59e0b] text-white">Stok Tipis</span>
        </div>

        <!-- Info -->
        <div class="p-3 flex flex-col gap-1.5 flex-1">
          <p class="text-[10px] font-mono text-[#94a3b8] uppercase">{{ p.sku }}</p>
          <p class="text-sm text-[#0f172a] font-semibold leading-snug line-clamp-2 min-h-[2.5rem]">{{ p.nama_produk }}</p>
          <p class="text-[11px] text-[#64748b] truncate">{{ p.penerbit || '-' }}</p>
          <div class="mt-auto pt-2 flex items-end justify-between gap-2">
            <span class="font-mono text-sm font-bold text-[#10b981]">Rp {{ (p.harga_jual || 0).toLocaleString() }}</span>
            <span class="font-mono text-[11px] text-[#64748b]">stok {{ p.stok }}</span>
          </div>
        </div>
      </button>
    </div>

    <!-- Modal Filter Hierarki -->
    <Teleport to="body">
      <div v-if="showFilterModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showFilterModal = false">
        <div class="bg-white rounded-[4px] w-full max-w-md p-6 shadow-xl border border-[#e2e8f0] space-y-4">
          <div class="flex justify-between items-center pb-3 border-b border-[#e2e8f0]">
            <h3 class="text-sm font-bold text-[#0f172a] uppercase">FILTER KATALOG</h3>
            <button @click="showFilterModal = false" class="text-[#64748b] hover:text-black font-bold">✕</button>
          </div>

          <div class="space-y-3">
            <div class="flex items-center gap-2 pb-2">
              <input id="exactMatch" v-model="exact" type="checkbox" class="accent-[#10b981] w-4 h-4" />
              <label for="exactMatch" class="text-xs text-[#0f172a] font-semibold select-none">Exact match (tanpa fuzzy)</label>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">DIVISI</label>
              <input v-model="fDivisi" list="dl-divisi" @input="onPickDivisi" placeholder="Semua divisi" class="w-full bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-xs font-mono" />
              <datalist id="dl-divisi"><option v-for="d in divisions" :key="d.id_divisi" :value="d.nama_divisi" /></datalist>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">DEPARTEMEN</label>
              <input v-model="fDepartemen" list="dl-departemen" @input="onPickDepartemen" placeholder="Semua departemen" class="w-full bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-xs font-mono" />
              <datalist id="dl-departemen"><option v-for="d in deptOptions" :key="d.id_departemen" :value="d.nama_departemen" /></datalist>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">KELAS</label>
              <input v-model="fKelas" list="dl-kelas" @input="onPickKelas" placeholder="Semua kelas" class="w-full bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-xs font-mono" />
              <datalist id="dl-kelas"><option v-for="c in kelasOptions" :key="c.id_kelas" :value="c.nama_kelas" /></datalist>
            </div>

            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">SUB-KELAS</label>
              <input v-model="fSubkelas" list="dl-subkelas" @input="onPickSubkelas" placeholder="Semua sub-kelas" class="w-full bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-xs font-mono" />
              <datalist id="dl-subkelas"><option v-for="s in subkelasOptions" :key="s.id_subkelas" :value="s.nama_subkelas" /></datalist>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-[#e2e8f0]">
            <button @click="applyFilter" class="px-4 py-2 bg-[#10b981] text-white rounded-[2px] text-xs font-semibold hover:bg-[#059669]">TERAPKAN</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Detail Produk -->
    <Teleport to="body">
      <div v-if="detail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="detail = null">
        <div class="bg-white rounded-[4px] w-full max-w-2xl shadow-xl border border-[#e2e8f0] overflow-hidden">
          <div class="flex justify-between items-center px-6 py-4 border-b border-[#e2e8f0] bg-[#f8fafc]">
            <h3 class="text-sm font-bold text-[#0f172a] uppercase tracking-[0.03em]">DETAIL PRODUK</h3>
            <button @click="detail = null" class="text-[#64748b] hover:text-[#0f172a] text-lg leading-none font-bold">✕</button>
          </div>

          <div class="flex flex-col sm:flex-row gap-6 p-6">
            <!-- Cover besar -->
            <div class="w-full sm:w-48 shrink-0">
              <div class="w-full aspect-[3/4] bg-[#f8fafc] border border-[#e2e8f0] rounded-[2px] overflow-hidden">
                <img v-if="detail.foto_produk" :src="detail.foto_produk" :alt="detail.nama_produk" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-[#cbd5e1]">
                  <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
              </div>
            </div>

            <!-- Detail -->
            <div class="flex-1 min-w-0 space-y-3">
              <div>
                <p class="text-[11px] font-mono text-[#94a3b8] uppercase">{{ detail.sku }}</p>
                <h4 class="text-lg font-bold text-[#0f172a] leading-snug">{{ detail.nama_produk }}</h4>
                <p class="text-xs text-[#64748b] mt-1">{{ detail.penerbit || 'Tanpa penerbit' }}</p>
              </div>

              <p class="text-sm font-mono font-bold text-[#10b981] text-lg">Rp {{ (detail.harga_jual || 0).toLocaleString() }}</p>

              <dl class="grid grid-cols-[110px_1fr] gap-y-2 gap-x-3 text-xs border-t border-[#e2e8f0] pt-3">
                <dt class="text-[#64748b] font-semibold uppercase text-[10px] pt-0.5">ISBN</dt>
                <dd class="font-mono text-[#0f172a]">{{ detail.isbn || '-' }}</dd>

                <dt class="text-[#64748b] font-semibold uppercase text-[10px] pt-0.5">Stok</dt>
                <dd class="font-mono text-[#0f172a]">{{ detail.stok }} <span class="text-[#94a3b8]">(min {{ detail.min_stock }})</span></dd>

                <dt class="text-[#64748b] font-semibold uppercase text-[10px] pt-0.5">Hierarki</dt>
                <dd class="text-[#0f172a]">{{ hierarki(detail) }}</dd>

                <dt v-if="canSeeCost" class="text-[#64748b] font-semibold uppercase text-[10px] pt-0.5">Harga Beli</dt>
                <dd v-if="canSeeCost" class="font-mono text-[#0f172a]">Rp {{ (detail.harga_beli || 0).toLocaleString() }}</dd>

                <dt v-if="canSeeCost" class="text-[#64748b] font-semibold uppercase text-[10px] pt-0.5">HPP</dt>
                <dd v-if="canSeeCost" class="font-mono text-[#0f172a]">Rp {{ (detail.harga_pokok || 0).toLocaleString() }}</dd>

                <dt class="text-[#64748b] font-semibold uppercase text-[10px] pt-0.5 align-top">Deskripsi</dt>
                <dd class="text-[#0f172a]">{{ detail.deskripsi_produk || '-' }}</dd>
              </dl>
            </div>
          </div>

          <div class="flex justify-end px-6 py-4 border-t border-[#e2e8f0] bg-[#f8fafc]">
            <button @click="detail = null" class="px-5 py-2 border border-[#e2e8f0] rounded-[2px] text-xs font-semibold text-[#64748b] hover:bg-white">TUTUP</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      products: [], search: '', loading: false, detail: null,
      showFilterModal: false,
      exact: false,
      divisions: [], departments: [], classes: [], subclasses: [],
      fDivisi: '', fDepartemen: '', fKelas: '', fSubkelas: '',
      idDivisi: '', idDepartemen: '', idKelas: '', idSubkelas: '',
    };
  },
  computed: {
    canSeeCost() {
      const user = JSON.parse(localStorage.getItem('user') || '{}');
      const role = (user.role?.name || '').toLowerCase();
      return role === 'owner' || role === 'supervisor';
    },
    deptOptions() {
      return this.idDivisi ? this.departments.filter(d => d.id_divisi === this.idDivisi) : this.departments;
    },
    kelasOptions() {
      let list = this.classes;
      if (this.idDepartemen) list = list.filter(c => c.id_departemen === this.idDepartemen);
      return list;
    },
    subkelasOptions() {
      if (!this.idKelas) return this.subclasses;
      return this.subclasses.filter(s => s.id_kelas === this.idKelas);
    },
  },
  mounted() { this.fetchHierarki(); this.fetchProducts(); },
  methods: {
    headers() { return { Authorization: 'Bearer ' + (localStorage.token || '') }; },
    openDetail(p) { this.detail = p; },
    async fetchHierarki() {
      try {
        const [div, dep, cls, sub] = await Promise.all([
          axios.get('/api/divisions?per_page=200', { headers: this.headers() }),
          axios.get('/api/departments?per_page=200', { headers: this.headers() }),
          axios.get('/api/classes?per_page=200', { headers: this.headers() }),
          axios.get('/api/subclasses?per_page=500', { headers: this.headers() }),
        ]);
        const rows = r => r.data.data || r.data || [];
        this.divisions = rows(div); this.departments = rows(dep);
        this.classes = rows(cls); this.subclasses = rows(sub);
      } catch (e) { console.error(e); }
    },
    onPickDivisi() {
      const d = this.divisions.find(x => x.nama_divisi === this.fDivisi);
      this.idDivisi = d?.id_divisi || '';
      this.fDepartemen = ''; this.idDepartemen = '';
      this.fKelas = ''; this.idKelas = '';
      this.fSubkelas = ''; this.idSubkelas = '';
    },
    onPickDepartemen() {
      const d = this.deptOptions.find(x => x.nama_departemen === this.fDepartemen);
      this.idDepartemen = d?.id_departemen || '';
      if (d?.id_divisi) {
        this.idDivisi = d.id_divisi;
        const p = this.divisions.find(x => x.id_divisi === d.id_divisi);
        if (p) this.fDivisi = p.nama_divisi;
      }
      this.fKelas = ''; this.idKelas = '';
      this.fSubkelas = ''; this.idSubkelas = '';
    },
    onPickKelas() {
      const c = this.kelasOptions.find(x => x.nama_kelas === this.fKelas);
      this.idKelas = c?.id_kelas || '';
      if (c?.id_departemen) {
        this.idDepartemen = c.id_departemen;
        const p = this.departments.find(x => x.id_departemen === c.id_departemen);
        if (p) {
          this.fDepartemen = p.nama_departemen;
          if (p.id_divisi) {
            this.idDivisi = p.id_divisi;
            const g = this.divisions.find(x => x.id_divisi === p.id_divisi);
            if (g) this.fDivisi = g.nama_divisi;
          }
        }
      }
      this.fSubkelas = ''; this.idSubkelas = '';
    },
    onPickSubkelas() {
      const s = this.subkelasOptions.find(x => x.nama_subkelas === this.fSubkelas);
      this.idSubkelas = s?.id_subkelas || '';
      if (s?.id_kelas) {
        const c = this.classes.find(x => x.id_kelas === s.id_kelas);
        if (c) { this.fKelas = c.nama_kelas; this.onPickKelas(); this.idSubkelas = s.id_subkelas; this.fSubkelas = s.nama_subkelas; }
      }
    },
    applyFilter() { this.showFilterModal = false; this.fetchProducts(); },
    async fetchProducts() {
      this.loading = true;
      try {
        const res = await axios.get('/api/products', {
          headers: this.headers(),
          params: {
            per_page: 500,
            search: this.search.trim() || undefined,
            exact: this.exact ? 1 : undefined,
            id_subkelas: this.idSubkelas || undefined,
            id_kelas: this.idSubkelas ? undefined : (this.idKelas || undefined),
            id_departemen: (this.idSubkelas || this.idKelas) ? undefined : (this.idDepartemen || undefined),
            id_divisi: (this.idSubkelas || this.idKelas || this.idDepartemen) ? undefined : (this.idDivisi || undefined),
          },
        });
        this.products = res.data.data || res.data || [];
      } catch (e) { console.error(e); } finally { this.loading = false; }
    },
    hierarki(p) {
      const parts = [p.divisi?.nama_divisi, p.departemen?.nama_departemen, p.kelas?.nama_kelas, p.subkelas?.nama_subkelas].filter(Boolean);
      return parts.length ? parts.join(' › ') : (p.id_subkelas || '-');
    },
  },
};
</script>

<style scoped>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>