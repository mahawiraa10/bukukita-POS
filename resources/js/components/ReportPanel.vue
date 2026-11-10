<template>
  <div class="space-y-4">
    <div class="flex items-end justify-between flex-wrap gap-3">
      <div>
        <h2 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em]">{{ meta.judul || 'LAPORAN' }}</h2>
        <p class="text-xs text-[#64748b] mt-0.5 font-mono">Periode {{ dari }} s/d {{ sampai }}</p>
      </div>
      <div class="flex items-end gap-2">
        <div>
          <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Dari</label>
          <input v-model="dari" type="date" class="border p-1.5 text-xs font-mono" />
        </div>
        <div>
          <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Sampai</label>
          <input v-model="sampai" type="date" class="border p-1.5 text-xs font-mono" />
        </div>
        <button @click="fetchData()" class="px-3 py-1.5 bg-[#0f172a] text-white text-xs font-semibold rounded-[2px] hover:bg-[#1e293b]">TERAPKAN</button>
        <button @click="exportCsv()" class="px-3 py-1.5 bg-[#10b981] text-white text-xs font-semibold rounded-[2px] hover:bg-[#059669]">EXPORT CSV</button>
      </div>
    </div>

    <div class="flex flex-wrap gap-1.5">
      <a v-for="r in reportList" :key="r.type" :href="'?type=' + r.type"
         :class="r.type === type ? 'bg-[#0f172a] text-white border-[#0f172a]' : 'bg-white text-[#64748b] border-[#e2e8f0] hover:border-[#0f172a]'"
         class="px-2.5 py-1 border rounded-[2px] text-[11px] font-semibold">{{ r.label }}</a>
    </div>

    <div v-if="rekap.length" class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <div v-for="k in rekap" :key="k.label" class="bg-white border border-[#e2e8f0] rounded-[4px] p-3">
        <div class="text-[10px] font-semibold text-[#64748b] uppercase">{{ k.label }}</div>
        <div class="text-base font-semibold text-[#0f172a] font-mono mt-0.5">{{ k.value }}</div>
      </div>
    </div>

    <div v-if="loading" class="p-6 text-center text-sm text-[#64748b]">Memuat...</div>
    <div v-else-if="error" class="p-4 bg-red-50 border border-red-200 rounded-[4px] text-sm text-[#ef4444]">{{ error }}</div>
    <div v-else class="overflow-x-auto bg-white border border-[#e2e8f0] rounded-[4px]">
      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[12px] font-semibold uppercase">
            <th v-for="(h, i) in header" :key="i" class="p-3 whitespace-nowrap">{{ h }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, ri) in rows" :key="ri" class="border-b border-[#e2e8f0] hover:bg-[#f1f5f9]">
            <td v-for="(cell, ci) in row" :key="ci" class="p-3 font-mono text-xs" :class="ci === 0 ? 'text-[#0f172a] font-semibold' : 'text-[#64748b]'">{{ cell }}</td>
          </tr>
          <tr v-if="rows.length === 0">
            <td :colspan="header.length || 1" class="p-6 text-center text-[#64748b]">Tidak ada data pada periode ini.</td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-if="meta.dicetak" class="text-[10px] text-[#94a3b8] font-mono">Dicetak: {{ meta.dicetak }}</p>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    const q = new URLSearchParams(window.location.search);
    return {
      type: q.get('type') || 'vendor-receiving',
      dari: q.get('dari') || new Date().toISOString().slice(0, 10),
      sampai: q.get('sampai') || new Date().toISOString().slice(0, 10),
      meta: {},
      rekap: [],
      header: [],
      rows: [],
      loading: false,
      error: '',
      reportList: [
        { type: 'vendor-receiving', label: '1. Vendor Receiving' },
        { type: 'internal-receiving', label: '2. Internal Receiving' },
        { type: 'vendor-return', label: '3. Vendor Return' },
        { type: 'event-return', label: '3b. Retur Sisa Event' },
        { type: 'receipts-recap', label: '4. Receipts Recap' },
        { type: 'receipts-detail', label: '5. Receipts Detail' },
        { type: 'omzet-produk', label: '6. Omzet Produk' },
        { type: 'omzet-departemen', label: '7. Omzet Departemen' },
        { type: 'omzet-kasir', label: '8. Omzet Kasir' },
        { type: 'member-baru', label: '9. New Loyalty Member' },
        { type: 'member-riwayat', label: '10. Member Riwayat' },
        { type: 'till-closing', label: '11. Till & Shift Closing' },
        { type: 'event-closing', label: '12. Event Closing' },
        { type: 'purchase-orders', label: '13. Laporan PO' },
      ],
    };
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    h() {
      return { Authorization: 'Bearer ' + (localStorage.token || '') };
    },
    params() {
      return { dari: this.dari, sampai: this.sampai };
    },
    async fetchData() {
      this.loading = true;
      this.error = '';
      try {
        const res = await axios.get('/api/reports/' + this.type, { headers: this.h(), params: this.params() });
        const d = res.data || {};
        this.meta = d.metadata || {};
        this.rekap = d.rekapitulasi || [];
        this.header = d.header || [];
        this.rows = d.rows || [];
      } catch (e) {
        this.error = e.response?.data?.message || 'Gagal memuat laporan.';
        this.rows = [];
        this.header = [];
        this.rekap = [];
      } finally {
        this.loading = false;
      }
    },
    async exportCsv() {
      try {
        const res = await axios.get('/api/reports/' + this.type, {
          headers: this.h(),
          params: { ...this.params(), export: 'csv' },
          responseType: 'blob',
        });
        const url = URL.createObjectURL(new Blob([res.data], { type: 'text/csv' }));
        const a = document.createElement('a');
        a.href = url;
        a.download = this.type + '-' + this.dari + '-' + this.sampai + '.csv';
        a.click();
        URL.revokeObjectURL(url);
      } catch (e) {
        this.error = 'Gagal export CSV.';
      }
    },
  },
};
</script>
