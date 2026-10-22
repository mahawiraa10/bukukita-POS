<template>
  <div class="space-y-4">
    <div class="flex flex-wrap items-center gap-3">
      <h2 class="text-lg font-semibold text-[#0f172a] uppercase tracking-[0.02em]">HISTORI EVENT</h2>
      <div class="flex-1"></div>
      <input v-model="search" @input="debouncedFetch" placeholder="Cari nama / lokasi..."
        class="bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-1.5 text-sm font-mono" />
      <select v-model="statusFilter" @change="fetchList" class="bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-1.5 text-sm font-mono">
        <option value="">Semua Status</option>
        <option value="AKTIF">AKTIF</option>
        <option value="NONAKTIF">SELESAI</option>
      </select>
    </div>

    <div class="bg-white border border-[#e2e8f0] rounded-[4px] shadow-[0_1px_2px_0_rgb(0_0_0/0.05)] overflow-x-auto">
      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[12px] font-semibold uppercase tracking-[0.05em]">
            <th class="p-3">ID</th>
            <th class="p-3">Nama Acara</th>
            <th class="p-3">Lokasi</th>
            <th class="p-3">Periode</th>
            <th class="p-3">Status</th>
            <th class="p-3 text-right">Omzet</th>
            <th class="p-3 text-right">Profit</th>
            <th class="p-3 text-right">Margin</th>
            <th class="p-3 text-right">Retur</th>
            <th class="p-3 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="e in events" :key="e.id_acara" class="border-b border-[#e2e8f0] hover:bg-slate-50">
            <td class="p-3 font-mono text-xs text-[#64748b]">{{ e.id_acara }}</td>
            <td class="p-3 font-medium">{{ e.nama_acara }}</td>
            <td class="p-3 text-xs text-[#64748b]">{{ e.lokasi }}</td>
            <td class="p-3 text-xs font-mono text-[#64748b]">{{ e.tanggal_mulai }} &rarr; {{ e.tanggal_selesai }}</td>
            <td class="p-3">
              <span v-if="e.status_acara === 'AKTIF'" class="text-[10px] font-bold uppercase bg-[#dcfce7] text-[#059669] rounded-[2px] px-1.5 py-0.5">AKTIF</span>
              <span v-else class="text-[10px] font-bold uppercase bg-[#f1f5f9] text-[#64748b] rounded-[2px] px-1.5 py-0.5">SELESAI</span>
            </td>
            <td class="p-3 text-right font-mono">{{ rupiah(e.omzet) }}</td>
            <td class="p-3 text-right font-mono" :class="e.profit >= 0 ? 'text-[#059669]' : 'text-[#ef4444]'">{{ rupiah(e.profit) }}</td>
            <td class="p-3 text-right font-mono text-[#64748b]">{{ e.margin }}%</td>
            <td class="p-3 text-right font-mono text-xs" :class="e.qty_retur > 0 ? 'text-[#ef4444]' : 'text-[#94a3b8]'">
              {{ e.qty_retur > 0 ? '-' + e.qty_retur + ' pcs' : '-' }}
            </td>
            <td class="p-3 text-right whitespace-nowrap">
              <button @click="openDetail(e.id_acara)" class="text-[#10b981] hover:underline text-xs font-semibold mr-3">DETAIL</button>
              <button @click="openCompare(e)" class="text-[#0f172a] hover:underline text-xs font-semibold">BANDINGKAN</button>
            </td>
          </tr>
          <tr v-if="events.length === 0">
            <td colspan="10" class="p-6 text-center text-[#64748b]">Belum ada event.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="detail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4" @click.self="detail = null">
      <div class="bg-white rounded-[4px] shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-5 py-3 border-b border-[#e2e8f0]">
          <h3 class="text-sm font-semibold uppercase tracking-[0.02em]">{{ detail.event.nama_acara }} — {{ detail.event.lokasi }}</h3>
          <button @click="detail = null" class="text-[#64748b] hover:text-[#0f172a] text-lg leading-none">&times;</button>
        </div>
        <div class="p-5 space-y-5">
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="border border-[#e2e8f0] rounded-[4px] p-3">
              <div class="text-[10px] font-bold uppercase text-[#64748b] tracking-wider">OMZET</div>
              <div class="text-base font-mono font-semibold mt-1">{{ rupiah(detail.kpi.omzet) }}</div>
            </div>
            <div class="border border-[#e2e8f0] rounded-[4px] p-3">
              <div class="text-[10px] font-bold uppercase text-[#64748b] tracking-wider">HPP</div>
              <div class="text-base font-mono mt-1 text-[#64748b]">{{ rupiah(detail.kpi.hpp) }}</div>
            </div>
            <div class="border border-[#e2e8f0] rounded-[4px] p-3">
              <div class="text-[10px] font-bold uppercase text-[#64748b] tracking-wider">PROFIT</div>
              <div class="text-base font-mono font-semibold mt-1" :class="detail.kpi.profit >= 0 ? 'text-[#059669]' : 'text-[#ef4444]'">{{ rupiah(detail.kpi.profit) }}</div>
            </div>
            <div class="border border-[#e2e8f0] rounded-[4px] p-3">
              <div class="text-[10px] font-bold uppercase text-[#64748b] tracking-wider">MARGIN</div>
              <div class="text-base font-mono font-semibold mt-1">{{ detail.kpi.margin }}%</div>
            </div>
          </div>

          <div class="flex flex-wrap gap-4 text-xs font-mono text-[#64748b]">
            <span>Terjual: <b class="text-[#0f172a]">{{ detail.kpi.qty_terjual }} pcs</b></span>
            <span>Retur: <b class="text-[#ef4444]">{{ detail.kpi.qty_retur }} pcs</b></span>
            <span>Nilai Retur: <b class="text-[#0f172a]">{{ rupiah(detail.kpi.nilai_retur) }}</b></span>
            <span>PO: <b class="text-[#0f172a]">{{ (detail.po_terkait || []).length }}</b></span>
          </div>

          <div>
            <div class="text-[10px] font-bold uppercase text-[#64748b] tracking-wider mb-2">RINCIAN PER PRODUK</div>
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] uppercase">
                  <th class="p-2">Produk</th>
                  <th class="p-2 text-right">Qty</th>
                  <th class="p-2 text-right">Omzet</th>
                  <th class="p-2 text-right">Profit</th>
                  <th class="p-2 text-right">Retur</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in detail.produk" :key="p.id_produk" class="border-b border-[#f1f5f9]">
                  <td class="p-2">{{ p.nama_produk }}</td>
                  <td class="p-2 text-right font-mono">{{ p.qty }}</td>
                  <td class="p-2 text-right font-mono">{{ rupiah(p.omzet) }}</td>
                  <td class="p-2 text-right font-mono" :class="p.profit >= 0 ? 'text-[#059669]' : 'text-[#ef4444]'">{{ rupiah(p.profit) }}</td>
                  <td class="p-2 text-right font-mono text-[#ef4444]">{{ p.qty_retur || '-' }}</td>
                </tr>
                <tr v-if="detail.produk.length === 0">
                  <td colspan="5" class="p-4 text-center text-[#64748b]">Belum ada penjualan di event ini.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div v-if="compare" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4" @click.self="compare = null">
      <div class="bg-white rounded-[4px] shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-5 py-3 border-b border-[#e2e8f0]">
          <h3 class="text-sm font-semibold uppercase tracking-[0.02em]">BANDINGKAN EVENT</h3>
          <button @click="compare = null" class="text-[#64748b] hover:text-[#0f172a] text-lg leading-none">&times;</button>
        </div>
        <div class="p-5 space-y-4">
          <div class="flex flex-wrap items-end gap-3">
            <div>
              <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Event Sebelumnya (Pembanding)</label>
              <select v-model="compareId" @change="loadCompareTarget()" class="bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-1.5 text-sm font-mono w-72">
                <option value="">-- Pilih event lain --</option>
                <option v-for="ev in otherEvents" :key="ev.id_acara" :value="ev.id_acara">{{ ev.id_acara }} — {{ ev.nama_acara }}</option>
              </select>
            </div>
          </div>

          <div v-if="compareTarget" class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
              <thead>
                <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[12px] font-semibold uppercase">
                  <th class="p-3">Metrik</th>
                  <th class="p-3">{{ compare.event.nama_acara }} <span class="font-normal text-[#94a3b8]">({{ compare.event.tanggal_mulai }}→{{ compare.event.tanggal_selesai }})</span></th>
                  <th class="p-3">{{ compareTarget.event.nama_acara }} <span class="font-normal text-[#94a3b8]">({{ compareTarget.event.tanggal_mulai }}→{{ compareTarget.event.tanggal_selesai }})</span></th>
                  <th class="p-3 text-right">Δ Selisih</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="m in compareRows" :key="m.label" class="border-b border-[#f1f5f9]">
                  <td class="p-3 text-[#64748b] text-xs font-semibold uppercase">{{ m.label }}</td>
                  <td class="p-3 font-mono">{{ m.a }}</td>
                  <td class="p-3 font-mono">{{ m.b }}</td>
                  <td class="p-3 text-right font-mono font-semibold" :class="m.deltaClass">{{ m.delta }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="p-4 text-center text-[#64748b] text-sm">Pilih event pembanding untuk melihat selisih.</div>
        </div>
      </div>
    </div>

    <div v-if="error" class="fixed bottom-4 right-4 px-4 py-3 rounded-[4px] text-sm text-white shadow-lg z-[60] bg-[#ef4444]">{{ error }}</div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return { events: [], search: '', statusFilter: '', detail: null, error: '', _deb: null, compare: null, compareId: '', compareTarget: null };
  },
  mounted() { this.fetchList(); },
  computed: {
    otherEvents() {
      return this.events.filter(e => e.id_acara !== this.compare?.event?.id_acara);
    },
    compareRows() {
      if (!this.compare || !this.compareTarget) return [];
      const k = this.compare.kpi, t = this.compareTarget.kpi;
      const pct = (a, b) => (b !== 0 ? Math.round((a - b) / Math.abs(b) * 1000) / 10 : null);
      const judul = [
        { label: 'Omzet',     a: k.omzet,     b: t.omzet,     fmt: 'rp' },
        { label: 'HPP',       a: k.hpp,       b: t.hpp,       fmt: 'rp' },
        { label: 'Profit',    a: k.profit,    b: t.profit,    fmt: 'rp' },
        { label: 'Margin',    a: k.margin,    b: t.margin,    fmt: 'pp' },
        { label: 'Qty Terjual', a: k.qty_terjual, b: t.qty_terjual, fmt: 'n' },
        { label: 'Qty Retur', a: k.qty_retur, b: t.qty_retur, fmt: 'n', lowerBetter: true },
        { label: 'Nilai Retur', a: k.nilai_retur, b: t.nilai_retur, fmt: 'rp', lowerBetter: true },
      ];
      return judul.map(m => {
        const diff = m.fmt === 'pp' ? Math.round((m.a - m.b) * 10) / 10 : m.a - m.b;
        const good = m.lowerBetter ? diff < 0 : diff > 0;
        const bad = m.lowerBetter ? diff > 0 : diff < 0;
        let delta;
        if (m.fmt === 'rp') delta = (diff >= 0 ? '+' : '') + this.rupiah(diff);
        else if (m.fmt === 'pp') delta = (diff >= 0 ? '+' : '') + diff + ' pp';
        else delta = (diff >= 0 ? '+' : '') + Math.round(diff) + ' pcs';
        if (pct(m.a, m.b) !== null) delta += ' (' + (pct(m.a, m.b) >= 0 ? '+' : '') + pct(m.a, m.b) + '%)';
        return { label: m.label, a: this.fmt(m.a, m.fmt), b: this.fmt(m.b, m.fmt), delta, deltaClass: diff === 0 ? 'text-[#64748b]' : (good ? 'text-[#059669]' : bad ? 'text-[#ef4444]' : 'text-[#64748b]') };
      });
    },
  },
  methods: {
    headers() { return { Authorization: 'Bearer ' + (localStorage.token || '') }; },
    rupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }); },
    fmt(v, kind) {
      if (kind === 'rp') return this.rupiah(v);
      if (kind === 'pp') return Number(v || 0) + '%';
      return Number(v || 0).toLocaleString('id-ID');
    },
    async openCompare(e) {
      this.compare = null;
      this.compareTarget = null;
      this.compareId = '';
      try {
        const res = await axios.get('/api/event-history/' + e.id_acara, { headers: this.headers() });
        this.compare = res.data;
      } catch (err) {
        this.flash('Gagal memuat data event.');
      }
    },
    async loadCompareTarget() {
      this.compareTarget = null;
      if (!this.compareId) return;
      try {
        const res = await axios.get('/api/event-history/' + this.compareId, { headers: this.headers() });
        this.compareTarget = res.data;
      } catch (err) {
        this.flash('Gagal memuat event pembanding.');
      }
    },
    debouncedFetch() {
      clearTimeout(this._deb);
      this._deb = setTimeout(() => this.fetchList(), 250);
    },
    async fetchList() {
      try {
        const res = await axios.get('/api/event-history', {
          headers: this.headers(),
          params: { search: this.search || undefined, status: this.statusFilter || undefined },
        });
        this.events = res.data.data ?? [];
      } catch (e) {
        this.flash(e.response?.status === 403 ? 'Akses ditolak. Hanya supervisor & owner.' : 'Gagal memuat histori.');
      }
    },
    async openDetail(id) {
      try {
        const res = await axios.get('/api/event-history/' + id, { headers: this.headers() });
        this.detail = res.data;
      } catch (e) {
        this.flash('Gagal memuat detail event.');
      }
    },
    flash(msg) {
      this.error = msg;
      setTimeout(() => (this.error = ''), 3000);
    },
  },
};
</script>
