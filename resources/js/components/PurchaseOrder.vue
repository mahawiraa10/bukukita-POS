<template>
  <div class="space-y-6">
    <div class="bg-[#0f172a] rounded-[4px] px-5 py-3">
      <h1 class="text-sm font-bold text-white uppercase tracking-[0.03em]">PURCHASE ORDER</h1>
      <p class="text-[11px] text-slate-400 mt-0.5">Form PO ke vendor · daftar, approve, batalkan, export.</p>
    </div>

    <!-- FORM PO -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-6 shadow-[0_1px_2px_0_rgb(0_0_0/0.05)]">
      <h2 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em] mb-4">BUAT PURCHASE ORDER</h2>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">PILIH VENDOR <span class="text-[#ef4444]">*</span></label>
          <select v-model="form.id_pemasok" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] focus:border-[#10b981]">
            <option value="" disabled>-- Pilih Vendor --</option>
            <option v-for="v in vendors" :key="v.id_pemasok" :value="v.id_pemasok">{{ v.nama_pemasok }} (Diskon: {{ v.diskon_pemasok }}%)</option>
          </select>
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">TIPE PO <span class="text-[#ef4444]">*</span></label>
          <select v-model="form.tipe_po" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] focus:border-[#10b981]">
            <option value="reguler">Reguler (Beli Putus)</option>
            <option value="konsinyasi">Konsinyasi / Bind Event</option>
          </select>
        </div>
        <div v-if="form.tipe_po === 'konsinyasi'">
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">BIND EVENT <span class="text-[#ef4444]">*</span></label>
          <select v-model="form.id_acara" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] focus:border-[#10b981]">
            <option value="" disabled>-- Pilih Acara --</option>
            <option v-for="e in events" :key="e.id_acara" :value="e.id_acara">{{ e.nama_acara }} ({{ e.tanggal_mulai }})</option>
          </select>
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">TANGGAL PO</label>
          <input v-model="form.tanggal_po" type="date" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] font-mono" />
        </div>
      </div>

      <p v-if="form.tipe_po === 'konsinyasi'" class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-[2px] px-3 py-2">
        PO konsinyasi: sisa barang yang tidak terjual otomatis diretur ke vendor saat event ditutup.
      </p>
    </div>

    <!-- MULTI-ITEM -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-6 shadow-[0_1px_2px_0_rgb(0_0_0/0.05)]">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em]">DAFTAR ITEM PRODUK</h3>
        <div class="flex items-center gap-2">
          <button @click="addItemRow()" class="px-3 py-1.5 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-xs font-semibold">TAMBAH ITEM</button>
        </div>
      </div>

      <table class="w-full text-left border-collapse text-sm mb-4">
        <thead>
          <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[12px] font-semibold uppercase tracking-[0.05em]">
            <th class="p-3">PRODUK</th>
            <th class="p-3 w-24">QTY</th>
            <th class="p-3 w-40">HARGA BELI</th>
            <th class="p-3 w-40 text-right">SUBTOTAL</th>
            <th class="p-3 w-16 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in form.items" :key="index" class="border-b border-[#e2e8f0]">
            <td class="p-3">
              <select v-model="item.id_produk" @change="updatePrice(item)" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-1.5 text-sm font-mono">
                <option value="" disabled>-- Pilih Produk --</option>
                <option v-for="p in vendorProducts" :key="p.id_produk" :value="p.id_produk">{{ p.nama_produk }} (Stok: {{ p.stok }})</option>
              </select>
            </td>
            <td class="p-3"><input v-model.number="item.jumlah" type="number" min="1" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-2 py-1.5 text-sm font-mono" /></td>
            <td class="p-3"><input v-model.number="item.harga" type="number" min="0" placeholder="0" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-2 py-1.5 text-sm font-mono" /></td>
            <td class="p-3 text-right font-mono font-medium">Rp {{ ((item.jumlah || 0) * (item.harga || 0)).toLocaleString() }}</td>
            <td class="p-3 text-right"><button @click="removeItemRow(index)" class="text-[#ef4444] hover:underline text-xs font-semibold">HAPUS</button></td>
          </tr>
          <tr v-if="form.items.length === 0">
            <td colspan="5" class="p-6 text-center text-[#64748b]">Belum ada item ditambahkan.</td>
          </tr>
        </tbody>
      </table>

      <div class="flex justify-between items-center pt-4 border-t border-[#e2e8f0]">
        <div>
          <span class="text-sm font-semibold text-[#64748b]">TOTAL ESTIMASI: </span>
          <span class="text-lg font-bold font-mono text-[#0f172a]">Rp {{ grandTotal.toLocaleString() }}</span>
        </div>
        <button v-if="can('manage_po')" @click="submitPo()" :disabled="saving" class="px-6 py-2.5 bg-[#10b981] hover:bg-[#059669] disabled:opacity-60 text-white rounded-[2px] text-sm font-semibold shadow-sm">
          {{ saving ? 'MENYIMPAN...' : 'BUAT PURCHASE ORDER' }}
        </button>
        <span v-else class="text-xs text-[#64748b]">Role Anda tidak memiliki akses membuat PO.</span>
      </div>
    </div>

    <!-- DAFTAR PO -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-6 shadow-[0_1px_2px_0_rgb(0_0_0/0.05)]">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em]">DAFTAR PURCHASE ORDER</h2>
        <div class="flex items-center gap-2">
          <button v-if="can('manage_po')" @click="exportPo()" class="px-3 py-1.5 border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-[2px] text-xs font-semibold">EXPORT PO</button>
          <button @click="fetchPo()" class="px-3 py-1.5 border border-[#e2e8f0] rounded-[2px] text-xs font-semibold text-[#64748b] hover:bg-[#f1f5f9]">REFRESH</button>
        </div>
      </div>

      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[12px] font-semibold uppercase tracking-[0.05em]">
            <th class="p-3">NO PO</th>
            <th class="p-3">KODE VENDOR</th>
            <th class="p-3">TIPE</th>
            <th class="p-3">EVENT</th>
            <th class="p-3">STATUS</th>
            <th class="p-3">PROGRES TERIMA</th>
            <th class="p-3 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="po in poList" :key="po.id" class="border-b border-[#e2e8f0] hover:bg-[#f1f5f9]">
            <td class="p-3 font-mono text-xs font-semibold text-[#0f172a]">{{ po.po_code }}</td>
            <td class="p-3 text-[#64748b]">{{ po.vendor?.nama_pemasok || po.id_pemasok }}</td>
            <td class="p-3 text-[#64748b] text-xs uppercase">{{ po.tipe_po }}</td>
            <td class="p-3 font-mono text-xs text-[#64748b]">{{ po.id_acara || '-' }}</td>
            <td class="p-3"><span :class="statusClass(po.status)" class="px-2 py-0.5 rounded-[2px] border text-[11px] font-semibold uppercase">{{ po.status }}</span></td>
            <td class="p-3 font-mono text-xs text-[#64748b]">{{ progressText(po) }}</td>
            <td class="p-3 text-right whitespace-nowrap">
              <button v-if="po.status === 'draft' && can('approve_po')" @click="approvePo(po)" class="px-2 py-1 bg-[#10b981] hover:bg-[#059669] text-white text-[11px] font-semibold rounded-[2px] mr-2">APPROVE</button>
              <button v-if="['draft','approved','partial'].includes(po.status) && can('manage_po')" @click="cancelPo(po)" class="px-2 py-1 border border-[#ef4444] text-[#ef4444] hover:bg-red-50 text-[11px] font-semibold rounded-[2px] mr-2">BATAL</button>
              <a v-if="can('manage_po') && ['approved','partial'].includes(po.status)" href="/operational/receiving" class="text-[#0f172a] hover:underline text-[11px] font-semibold">TERIMA &rarr;</a>
            </td>
          </tr>
          <tr v-if="poList.length === 0">
            <td colspan="7" class="p-6 text-center text-[#64748b]">Belum ada PO.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ALERT -->
    <div v-if="alert.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4" @click.self="alert.show = false">
      <div class="bg-white rounded-[4px] shadow-xl w-full max-w-sm">
        <div class="px-5 py-3 border-b border-[#e2e8f0]">
          <h3 :class="alert.type === 'error' ? 'text-[#ef4444]' : 'text-[#10b981]'" class="text-sm font-bold uppercase tracking-wider">{{ alert.title }}</h3>
        </div>
        <p class="px-5 py-4 text-sm text-[#0f172a] whitespace-pre-line">{{ alert.message }}</p>
        <div class="flex justify-end px-5 py-3 border-t border-[#e2e8f0]">
          <button @click="alert.show = false" class="px-4 py-2 bg-[#0f172a] text-white rounded-[2px] text-xs font-semibold">OK</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      vendors: [], products: [], departments: [], subclasses: [], events: [],
      poList: [],
      saving: false,
      showProductModal: false,
      modalProduct: { nama_produk: '', id_departemen: '', id_subkelas: '', sku: '', penerbit: '' },
      alert: { show: false, type: 'error', title: '', message: '' },
      form: { id_pemasok: '', id_acara: '', tipe_po: 'reguler', payment_method: 'CASH', tanggal_po: new Date().toISOString().slice(0, 10), items: [] },
    };
  },
  computed: {
    vendorProducts() {
      if (!this.form.id_pemasok) return [];
      return this.products.filter(p => p.id_pemasok === this.form.id_pemasok);
    },
    grandTotal() {
      return this.form.items.reduce((acc, i) => acc + (i.jumlah || 0) * (i.harga || 0), 0);
    },
  },
  mounted() {
    this.fetchData();
    this.fetchPo();
  },
  methods: {
    headers() {
      return { Authorization: 'Bearer ' + (localStorage.token || '') };
    },
    can(perm) {
      try {
        const u = JSON.parse(localStorage.user || '{}');
        return (u.role?.permissions || []).some(p => p.name === perm);
      } catch { return false; }
    },
    async fetchData() {
      try {
        const [vRes, pRes, dRes, sRes, eRes] = await Promise.all([
          axios.get('/api/vendors', { headers: this.headers() }),
          axios.get('/api/products', { headers: this.headers(), params: { per_page: 500 } }),
          axios.get('/api/departments', { headers: this.headers() }),
          axios.get('/api/subclasses', { headers: this.headers() }),
          axios.get('/api/events', { headers: this.headers(), params: { per_page: 100 } }),
        ]);
        this.vendors = vRes.data.data || vRes.data || [];
        this.products = pRes.data.data || pRes.data || [];
        this.departments = dRes.data.data || dRes.data || [];
        this.subclasses = sRes.data.data || sRes.data || [];
        this.events = (eRes.data.data || eRes.data || []).filter(e => e.status_acara !== 'NONAKTIF');
      } catch (e) {
        this.showAlert(e.response?.data?.message || 'Gagal memuat data master');
      }
    },
    async fetchPo() {
      try {
        const res = await axios.get('/api/purchase-orders', { headers: this.headers(), params: { per_page: 100 } });
        this.poList = (res.data.data || res.data || []).filter(p => ['draft', 'approved', 'partial', 'received'].includes(p.status));
      } catch (e) {
        this.showAlert(e.response?.data?.message || 'Gagal memuat daftar PO');
      }
    },
    addItemRow() {
      this.form.items.push({ id_produk: '', jumlah: 1, harga: 0 });
    },
    removeItemRow(i) { this.form.items.splice(i, 1); },
    updatePrice(item) {
      const p = this.products.find(x => x.id_produk === item.id_produk);
      if (p) item.harga = p.harga_beli || p.harga_jual || 0;
    },
    validateForm() {
      if (!this.form.id_pemasok) return 'Pilih vendor dulu!';
      if (this.form.tipe_po === 'konsinyasi' && !this.form.id_acara) return 'PO konsinyasi wajib bind event!';
      if (this.form.items.length === 0) return 'Tambahkan minimal 1 item produk!';
      for (const it of this.form.items) {
        if (!it.id_produk) return 'Masih ada item yang belum pilih produk.';
      }
      return null;
    },
    async submitPo() {
      const err = this.validateForm();
      if (err) return this.showAlert(err);
      this.saving = true;
      try {
        await axios.post('/api/purchase-orders', {
          id_pemasok: this.form.id_pemasok,
          tipe_po: this.form.tipe_po,
          payment_method: this.form.payment_method,
          id_acara: this.form.tipe_po === 'konsinyasi' ? this.form.id_acara : null,
          expected_date: this.form.tanggal_po,
          items: this.form.items.map(it => ({
            qty: it.jumlah,
            unit_price: it.harga,
            id_produk: it.id_produk
          })),
        }, { headers: this.headers() });
        this.showAlert('Purchase Order berhasil dibuat!', 'success');
        this.form.items = [];
        this.form.id_pemasok = '';
        this.form.id_acara = '';
        await this.fetchPo();
      } catch (e) {
        this.showAlert(e.response?.data?.message || 'Gagal membuat PO');
      } finally {
        this.saving = false;
      }
    },
    async exportPo() {
      try {
        const res = await axios.get('/api/export/purchase-orders', { headers: this.headers(), responseType: 'blob' });
        const url = URL.createObjectURL(res.data);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'purchase-orders-' + new Date().toISOString().slice(0, 10) + '.csv';
        a.click();
        URL.revokeObjectURL(url);
      } catch (e) {
        this.showAlert(e.response?.data?.message || 'Gagal export PO');
      }
    },
    async approvePo(po) {
      if (!confirm('Approve PO ' + po.po_code + '?')) return;
      try {
        await axios.patch('/api/purchase-orders/' + po.id + '/approve', {}, { headers: this.headers() });
        this.showAlert('PO ' + po.po_code + ' di-approve.', 'success');
        await this.fetchPo();
      } catch (e) {
        this.showAlert(e.response?.data?.message || 'Gagal approve PO');
      }
    },
    async cancelPo(po) {
      if (!confirm('Batalkan PO ' + po.po_code + '?')) return;
      try {
        await axios.patch('/api/purchase-orders/' + po.id + '/cancel', {}, { headers: this.headers() });
        this.showAlert('PO ' + po.po_code + ' dibatalkan.', 'success');
        await this.fetchPo();
      } catch (e) {
        this.showAlert(e.response?.data?.message || 'Gagal batalkan PO');
      }
    },
    statusClass(status) {
      return {
        draft: 'bg-slate-100 text-slate-500 border-slate-200',
        approved: 'bg-blue-50 text-blue-700 border-blue-200',
        partial: 'bg-amber-50 text-amber-700 border-amber-200',
        received: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        cancelled: 'bg-red-50 text-red-600 border-red-200',
      }[status] || 'bg-slate-100 text-slate-500 border-slate-200';
    },
    progressText(po) {
      const items = po.items || [];
      if (!items.length) return '-';
      const total = items.reduce((s, i) => s + (i.qty || 0), 0);
      const recv = items.reduce((s, i) => s + (i.received_qty || 0), 0);
      return recv + ' / ' + total + ' pcs';
    },
    showAlert(message, type = 'error') {
      this.alert = { show: true, type, message, title: type === 'error' ? 'PERHATIAN' : 'BERHASIL' };
    },
  },
};
</script>
