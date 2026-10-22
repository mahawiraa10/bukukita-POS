<template>
  <div class="space-y-6">
    <!-- Header PO Form -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-6 shadow-[0_1px_2px_0_rgb(0_0_0/0.05)]">
      <h2 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em] mb-4">FORM PURCHASE ORDER (PO)</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">PILIH VENDOR</label>
          <select v-model="form.id_pemasok" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] focus:border-[#10b981]">
            <option value="" disabled>-- Pilih Vendor --</option>
            <option v-for="v in vendors" :key="v.id_pemasok" :value="v.id_pemasok">{{ v.nama_pemasok }} (Diskon: {{ v.diskon_pemasok }}%)</option>
          </select>
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">TIPE PO</label>
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
    </div>

    <!-- Multi-Item Produk -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-6 shadow-[0_1px_2px_0_rgb(0_0_0/0.05)]">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em]">DAFTAR ITEM PRODUK</h3>
        <div class="flex items-center gap-2">
          <a href="/api/export/purchase-orders" class="px-3 py-1.5 border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-[2px] text-xs font-semibold flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            EXPORT PO
          </a>
          <button @click="openProductModal()" class="px-3 py-1.5 border border-[#e2e8f0] bg-white hover:bg-slate-50 text-[#0f172a] rounded-[2px] text-xs font-semibold flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            PRODUK BARU
          </button>
          <button @click="addItemRow()" class="px-3 py-1.5 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-xs font-semibold flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            TAMBAH ITEM
          </button>
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
          <template v-for="(item, index) in form.items" :key="index">
            <!-- Baris ringkas item -->
            <tr class="border-b border-[#e2e8f0]">
              <td class="p-3">
                <!-- Dropdown produk existing (repeat order + konsistensi kapitalisasi untuk audit) -->
                <select
                  v-if="!item.is_new"
                  v-model="item.id_produk"
                  @change="updatePrice(item)"
                  class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-1.5 text-sm font-mono"
                >
                  <option value="" disabled>-- Pilih Produk --</option>
                  <option v-for="p in products" :key="p.id_produk" :value="p.id_produk">{{ p.nama_produk }} (Stok: {{ p.stok }})</option>
                </select>
                <div v-else class="flex items-center gap-2 px-1 py-1.5">
                  <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-[2px] px-1.5 py-0.5">BARU</span>
                  <span class="text-xs text-[#64748b] truncate">{{ item.new_product.nama_produk || 'Lengkapi di modal produk baru' }}</span>
                </div>
              </td>
              <td class="p-3">
                <input v-model.number="item.jumlah" type="number" min="1" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-2 py-1.5 text-sm font-mono" />
              </td>
              <td class="p-3">
                <input v-model.number="item.harga" @input="suggestJual(item)" type="number" min="0" placeholder="0" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-2 py-1.5 text-sm font-mono" />
              </td>
              <td class="p-3 text-right font-mono font-medium">
                Rp {{ ((item.jumlah || 0) * (item.harga || 0)).toLocaleString() }}
              </td>
              <td class="p-3 text-right">
                <button @click="removeItemRow(index)" class="text-[#ef4444] hover:underline text-xs font-semibold">HAPUS</button>
              </td>
            </tr>
          </template>
          <tr v-if="form.items.length === 0">
            <td colspan="5" class="p-6 text-center text-[#64748b]">Belum ada item ditambahkan.</td>
          </tr>
        </tbody>
      </table>

      <!-- Grand Total & Submit -->
      <div class="flex justify-between items-center pt-4 border-t border-[#e2e8f0]">
        <div>
          <span class="text-sm font-semibold text-[#64748b]">TOTAL ESTIMASI: </span>
          <span class="text-lg font-bold font-mono text-[#0f172a]">Rp {{ grandTotal.toLocaleString() }}</span>
        </div>
        <button @click="submitPo()" class="px-6 py-2.5 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-sm font-semibold shadow-sm">
          BUAT PURCHASE ORDER
        </button>
      </div>
    </div>

    <!-- Modal Produk Baru -->
    <div v-if="showProductModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4" @click.self="showProductModal = false">
      <div class="bg-white rounded-[4px] shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-5 py-3 border-b border-[#e2e8f0]">
          <h3 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em]">Produk Baru (Lahir dari PO)</h3>
          <button @click="showProductModal = false" class="text-[#64748b] hover:text-[#0f172a] text-lg leading-none">&times;</button>
        </div>
        <div class="p-5 space-y-3">
          <div>
            <label class="block text-[10px] font-bold text-[#64748b] uppercase tracking-wider mb-1">Nama Produk <span class="text-[#ef4444]">*</span></label>
            <input v-model="modalProduct.nama_produk" placeholder="cth: Seporsi Mie Ayam Sebelum Mati" class="w-full bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[10px] font-bold text-[#64748b] uppercase tracking-wider mb-1">Departemen <span class="text-[#ef4444]">*</span></label>
              <select v-model="modalProduct.id_departemen" class="w-full bg-white border border-[#e2e8f0] rounded-[2px] px-2 py-2 text-sm">
                <option value="" disabled>-- Pilih --</option>
                <option v-for="d in departments" :key="d.id_departemen" :value="d.id_departemen">{{ d.nama_departemen }}</option>
              </select>
            </div>
            <div>
              <label class="block text-[10px] font-bold text-[#64748b] uppercase tracking-wider mb-1">SubKelas</label>
              <select v-model="modalProduct.id_subkelas" class="w-full bg-white border border-[#e2e8f0] rounded-[2px] px-2 py-2 text-sm">
                <option value="">-- Opsional --</option>
                <option v-for="s in subclasses" :key="s.id_subkelas" :value="s.id_subkelas">{{ s.nama_subkelas }}</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[10px] font-bold text-[#64748b] uppercase tracking-wider mb-1">Harga Jual <span class="text-[#ef4444]">*</span> <span class="font-normal normal-case text-[#94a3b8]">(ke customer)</span></label>
              <input v-model.number="modalProduct.harga_jual" type="number" min="0" placeholder="0" class="w-full bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm font-mono" />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-[#64748b] uppercase tracking-wider mb-1">Penerbit</label>
              <input v-model="modalProduct.penerbit" placeholder="opsional" class="w-full bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm" />
            </div>
          </div>
          <div>
            <label class="block text-[10px] font-bold text-[#64748b] uppercase tracking-wider mb-1">SKU</label>
            <input v-model="modalProduct.sku" placeholder="opsional — dikosongkan = auto-generate" class="w-full bg-white border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm font-mono" />
          </div>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t border-[#e2e8f0]">
          <button @click="showProductModal = false" class="px-4 py-2 border border-[#e2e8f0] rounded-[2px] text-xs font-semibold text-[#64748b]">BATAL</button>
          <button @click="addNewProductFromModal()" class="px-4 py-2 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-xs font-semibold">TAMBAH KE PO</button>
        </div>
      </div>
    </div>

    <!-- DAFTAR PO: approve / cancel / lihat status -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-6 shadow-[0_1px_2px_0_rgb(0_0_0/0.05)]">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em]">DAFTAR PURCHASE ORDER</h2>
        <button @click="fetchPo()" class="px-3 py-1.5 border border-[#e2e8f0] rounded-[2px] text-xs font-semibold text-[#64748b] hover:bg-[#f1f5f9]">REFRESH</button>
      </div>
      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[12px] font-semibold uppercase tracking-[0.05em]">
            <th class="p-3">NO PO</th>
            <th class="p-3">VENDOR</th>
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
            <td class="p-3">
              <span :class="statusClass(po.status)" class="px-2 py-0.5 rounded-[2px] border text-[11px] font-semibold uppercase">{{ po.status }}</span>
            </td>
            <td class="p-3 font-mono text-xs text-[#64748b]">{{ progressText(po) }}</td>
            <td class="p-3 text-right whitespace-nowrap">
              <button v-if="po.status === 'draft'" @click="approvePo(po)" class="px-2 py-1 bg-[#10b981] hover:bg-[#059669] text-white text-[11px] font-semibold rounded-[2px] mr-2">APPROVE</button>
              <button v-if="['draft','approved','partial'].includes(po.status)" @click="cancelPo(po)" class="px-2 py-1 border border-[#ef4444] text-[#ef4444] hover:bg-red-50 text-[11px] font-semibold rounded-[2px] mr-2">BATAL</button>
              <a v-if="['approved','partial'].includes(po.status)" href="/operational/receiving" class="text-[#0f172a] hover:underline text-[11px] font-semibold">TERIMA &rarr;</a>
            </td>
          </tr>
          <tr v-if="poList.length === 0">
            <td colspan="7" class="p-6 text-center text-[#64748b]">Belum ada PO.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
data() {
  return {
    vendors: [],
    products: [],
    departments: [],
    subclasses: [],
    events: [],
    poList: [],
    showProductModal: false,
    modalProduct: { nama_produk: '', id_departemen: '', id_subkelas: '', sku: '', harga_jual: 0, penerbit: '' },
    form: {
      id_pemasok: '',
      id_acara: '',
      tipe_po: 'reguler',
      tanggal_po: new Date().toISOString().slice(0, 10),
      items: []
    }
  };
},
computed: {
  grandTotal() {
    return this.form.items.reduce((acc, item) => acc + (item.jumlah * item.harga), 0);
  }
},
mounted() {
  this.fetchData();
  this.fetchPo();
},
methods: {
  async fetchData() {
    const token = localStorage.token || '';
    const headers = { Authorization: `Bearer ${token}` };
    const [vRes, pRes, dRes, sRes, eRes] = await Promise.all([
      axios.get('/api/vendors', { headers }),
      axios.get('/api/products', { headers }),
      axios.get('/api/departments', { headers }),
      axios.get('/api/subclasses', { headers }),
      axios.get('/api/events', { headers }),
    ]);
    this.vendors = vRes.data.data || vRes.data || [];
    this.products = pRes.data.data || pRes.data || [];
    this.departments = dRes.data.data || dRes.data || [];
    this.subclasses = sRes.data.data || sRes.data || [];
    this.events = eRes.data.data || eRes.data || [];
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
  async fetchPo() {
    try {
      const token = localStorage.token || '';
      const res = await axios.get('/api/purchase-orders', { headers: { Authorization: 'Bearer ' + token }, params: { per_page: 100 } });
      this.poList = (res.data.data || res.data || []).filter(p => ['draft','approved','partial','received'].includes(p.status));
    } catch (e) {
      this.showAlert(e.response?.data?.message || 'Gagal memuat daftar PO', 'error');
    }
  },
  async approvePo(po) {
    if (!confirm('Approve PO ' + po.po_code + '? Setelah approve, PO bisa diterima barangnya.')) return;
    try {
      const token = localStorage.token || '';
      await axios.patch('/api/purchase-orders/' + po.id + '/approve', {}, { headers: { Authorization: 'Bearer ' + token } });
      this.showAlert('PO ' + po.po_code + ' di-approve.', 'success');
      await this.fetchPo();
    } catch (e) {
      this.showAlert(e.response?.data?.message || 'Gagal approve PO', 'error');
    }
  },
  async cancelPo(po) {
    if (!confirm('Batalkan PO ' + po.po_code + '?')) return;
    try {
      const token = localStorage.token || '';
      await axios.patch('/api/purchase-orders/' + po.id + '/cancel', {}, { headers: { Authorization: 'Bearer ' + token } });
      this.showAlert('PO ' + po.po_code + ' dibatalkan.', 'success');
      await this.fetchPo();
    } catch (e) {
      this.showAlert(e.response?.data?.message || 'Gagal batalkan PO', 'error');
    }
  },
  validateForm() {
    if (!this.form.id_pemasok) return 'Pilih vendor dulu!';
    if (this.form.tipe_po === 'konsinyasi' && !this.form.id_acara) return 'PO konsinyasi wajib bind event!';
    if (this.form.items.length === 0) return 'Tambahkan minimal 1 item produk!';
    for (const it of this.form.items) {
      if (it.is_new) {
        if (!it.new_product.nama_produk.trim()) return 'Nama produk baru wajib diisi.';
        if (!it.new_product.id_departemen) return 'Departemen produk baru wajib dipilih.';
      } else if (!it.id_produk) {
        return 'Masih ada item yang belum pilih produk.';
      }
    }
    return null;
  },
  addItemRow() {
    this.form.items.push({
      id_produk: '', jumlah: 1, harga: 0,
      is_new: false,
      new_product: { nama_produk: '', id_departemen: '', id_subkelas: '', sku: '', harga_jual: 0, penerbit: '' }
    });
  },
  removeItemRow(index) {
    this.form.items.splice(index, 1);
  },
  openProductModal() {
    this.modalProduct = { nama_produk: '', id_departemen: '', id_subkelas: '', sku: '', harga_jual: 0, penerbit: '' };
    this.showProductModal = true;
  },
  addNewProductFromModal() {
    if (!this.modalProduct.nama_produk.trim()) {
      this.showAlert('Nama produk wajib diisi.', 'error');
      return;
    }
    if (!this.modalProduct.id_departemen) {
      this.showAlert('Pilih departemen dulu.', 'error');
      return;
    }
    // Tambah sebagai item produk baru di form PO
    this.form.items.push({
      id_produk: '', jumlah: 1, harga: 0,
      is_new: true,
      new_product: { ...this.modalProduct, nama_produk: this.modalProduct.nama_produk.trim() }
    });
    this.showProductModal = false;
  },
  updatePrice(item, p) {
    const prod = p || this.products.find(x => x.id_produk === item.id_produk);
    if (prod) {
      item.harga = prod.harga_beli || prod.harga_jual || 0;
    }
  },
  suggestJual(item) {
    // Auto-suggest harga jual = harga beli x 1.4 (markup 40%), kalau field jual masih kosong/0.
    if (item.is_new && item.harga > 0 && !(item.new_product.harga_jual > 0)) {
      item.new_product.harga_jual = Math.round(item.harga * 1.4);
    }
  },
  async submitPo() {
    const err = this.validateForm();
    if (err) {
      this.showAlert(err, 'error');
      return;
    }
    // Map ke payload backend
    const payload = {
      id_pemasok: this.form.id_pemasok,
      tipe_po: this.form.tipe_po,
      id_acara: this.form.tipe_po === 'konsinyasi' ? this.form.id_acara : null,
      expected_date: this.form.tanggal_po,
      items: this.form.items.map(it => {
        const base = { qty: it.jumlah, unit_price: it.harga };
        if (it.is_new) {
          base.new_product = it.new_product;
        } else {
          base.id_produk = it.id_produk;
        }
        return base;
      })
    };
    // Validasi klien sudah di validateForm()
    try {
      const token = localStorage.token || '';
      await axios.post('/api/purchase-orders', payload, { headers: { Authorization: `Bearer ${token}` } });
      this.showAlert('Purchase Order berhasil dibuat!', 'success');
      setTimeout(() => window.location.reload(), 1000);
    } catch (e) {
      this.showAlert(e.response?.data?.message || 'Gagal membuat PO', 'error');
    }
  },
  showAlert(message, type = 'error', title = '') {
    this.alertModal = { show: true, type, message, title: title || (type === 'error' ? 'PERHATIAN' : 'BERHASIL') };
  }
}
};
</script>