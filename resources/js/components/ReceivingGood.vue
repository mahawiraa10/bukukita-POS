<template>
  <div class="space-y-6">
    <!-- Header Form SJ/Invoice -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-6 shadow-[0_1px_2px_0_rgb(0_0_0/0.05)]">
      <h2 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em] mb-4">PENERIMAAN BARANG</h2>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">NO SURAT JALAN</label>
          <input v-model="form.nomor_surat_jalan" type="text" placeholder="No. SJ..." class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] focus:border-[#10b981] font-mono" />
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">NO INVOICE</label>
          <input v-model="form.nomor_faktur" type="text" placeholder="No. Invoice..." class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] focus:border-[#10b981] font-mono" />
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">TANGGAL</label>
          <input v-model="form.tanggal" type="date" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] font-mono" />
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">PENERIMA *</label>
          <input v-model="form.penerima" type="text" placeholder="Nama penerima" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] focus:border-[#10b981]" />
        </div>
        <div>
          <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">PILIH VENDOR</label>
          <select v-model="form.id_pemasok" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] focus:border-[#10b981] font-mono">
            <option value="" disabled>-- Pilih Vendor --</option>
            <option v-for="v in vendors" :key="v.id_pemasok" :value="v.id_pemasok">{{ v.nama_pemasok }}</option>
          </select>
        </div>
      </div>
    </div>

    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-6 shadow-[0_1px_2px_0_rgb(0_0_0/0.05)] mb-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em]">REFERENSI PURCHASE ORDER (OPTIONAL)</h3>
        <select v-model="form.id_pesanan" @change="loadPoItems()" class="bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-1.5 text-xs font-mono text-[#0f172a] focus:border-[#10b981]">
          <option value="">-- Tanpa PO (Direct) --</option>
          <option v-for="po in pos" :key="po.po_code" :value="po.po_code">{{ po.po_code }} ({{ po.vendor?.nama_pemasok }})</option>
        </select>
      </div>
      
      <div class="flex justify-between items-center mb-4 pt-4 border-t border-[#e2e8f0]">
        <h3 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em]">ITEM PRODUK DITERIMA</h3>
        <button @click="addItemRow()" class="px-3 py-1.5 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-xs font-semibold flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          TAMBAH PRODUK
        </button>
      </div>

      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[12px] font-semibold uppercase tracking-[0.05em]">
            <th class="p-3">PRODUK</th>
            <th class="p-3 w-32">QTY DITERIMA</th>
            <th class="p-3 w-40">HARGA BELI</th>
            <th class="p-3 w-32">DISKON %</th>
            <th class="p-3">SUBTOTAL</th>
            <th class="p-3 w-16 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in form.items" :key="index" class="border-b border-[#e2e8f0] hover:bg-[#f1f5f9]">
            <td class="p-3">
              <select v-model="item.id_produk" @change="updateProduk(item)" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm font-mono">
                <option value="" disabled>-- Pilih Produk --</option>
                <option v-for="p in products" :key="p.id_produk" :value="p.id_produk">{{ p.nama_produk }} (Stok: {{ p.stok }})</option>
              </select>
            </td>
            <td class="p-3">
              <input v-model.number="item.jumlah_diterima" type="number" min="1" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-1.5 text-sm font-mono" />
            </td>
            <td class="p-3">
              <input v-model.number="item.harga_beli" type="number" min="0" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-1.5 text-sm font-mono" />
            </td>
            <td class="p-3">
              <input v-model.number="item.diskon_pemasok" type="number" min="0" max="100" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-1.5 text-sm font-mono" />
            </td>
            <td class="p-3 font-medium font-mono">Rp {{ (item.jumlah_diterima * item.harga_beli).toLocaleString() }}</td>
            <td class="p-3 text-right">
              <button @click="removeItemRow(index)" class="text-[#ef4444] hover:underline text-xs font-semibold">HAPUS</button>
            </td>
          </tr>
          <tr v-if="form.items.length === 0">
            <td colspan="6" class="p-6 text-center text-[#64748b]">Belum ada item ditambahkan.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Tombol Finalize -->
    <div>
      <button @click="finishReceive()" class="px-6 py-2.5 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-sm font-semibold shadow-sm">
        SIMPAN PENERIMAAN & UPDATE STOK
      </button>
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
      pos: [],
      poItems: [],
      poInfo: null,
      saving: false,
      form: {
        nomor_surat_jalan: '',
        nomor_faktur: '',
        id_pemasok: '',
        id_pesanan: '',
        penerima: '',
        tanggal: new Date().toISOString().slice(0, 10),
        items: []
      },
      alertModal: { show: false, type: 'error', title: '', message: '' }
    };
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    headers() {
      return { Authorization: 'Bearer ' + (localStorage.token || '') };
    },
    async fetchData() {
      const [vRes, pRes, poRes] = await Promise.all([
        axios.get('/api/vendors', { headers: this.headers() }),
        axios.get('/api/products', { headers: this.headers(), params: { per_page: 500 } }),
        axios.get('/api/purchase-orders', { headers: this.headers(), params: { per_page: 200 } }),
      ]);
      this.vendors = vRes.data.data || vRes.data || [];
      this.products = pRes.data.data || pRes.data || [];
      const allPo = poRes.data.data || poRes.data || [];
      this.pos = allPo.filter(p => ['approved', 'partial'].includes(p.status));
    },
    addItemRow() {
      this.form.items.push({ id_produk: '', jumlah_diterima: 1, harga_beli: 0, diskon_pemasok: 0 });
    },
    removeItemRow(index) {
      this.form.items.splice(index, 1);
    },
    productOptions(item) {
      if (!this.form.id_pesanan) return this.products;
      return this.poItems.map(pi => ({ id_produk: pi.id_produk, nama_produk: pi.product?.nama_produk || pi.id_produk }));
    },
    sisaPo(idProduk) {
      if (!this.form.id_pesanan || !idProduk) return '-';
      const pi = this.poItems.find(x => x.id_produk === idProduk);
      if (!pi) return '-';
      return Math.max(0, (pi.qty || 0) - (pi.received_qty || 0));
    },
    updateProduk(item) {
      const p = this.products.find(x => x.id_produk === item.id_produk);
      if (p) {
        item.harga_beli = p.harga_beli || p.harga_jual || 0;
        item.diskon_pemasok = p.diskon_pemasok || 0;
      }
      if (this.form.id_pesanan) {
        const sisa = this.sisaPo(item.id_produk);
        if (typeof sisa === 'number' && sisa > 0) item.jumlah_diterima = sisa;
      }
    },
    async loadPoItems() {
      this.poItems = [];
      this.poInfo = null;
      if (!this.form.id_pesanan) {
        this.form.items = [];
        return;
      }
      const po = this.pos.find(p => p.po_code === this.form.id_pesanan);
      const res = await axios.get('/api/purchase-orders/' + po.id, { headers: this.headers() });
      const detail = res.data.data || res.data;
      this.poInfo = detail;
      this.poItems = detail.items || [];
      this.form.id_pemasok = detail.id_pemasok || this.form.id_pemasok;
      this.form.items = this.poItems
        .filter(pi => (pi.qty - pi.received_qty) > 0)
        .map(pi => ({
          id_produk: pi.id_produk,
          jumlah_diterima: pi.qty - pi.received_qty,
          harga_beli: pi.unit_price || 0,
          diskon_pemasok: 0,
        }));
    },
    async finishReceive() {
      if (!this.form.id_pemasok) return this.showAlert('Pilih vendor dulu!');
      if (!this.form.nomor_surat_jalan || !this.form.nomor_faktur) return this.showAlert('No Surat Jalan & No Faktur wajib diisi!');
      if (!this.form.penerima.trim()) return this.showAlert('Nama penerima wajib diisi!');
      if (this.form.items.length === 0) return this.showAlert('Tambahkan produk dulu!');
      if (this.form.items.some(i => !i.id_produk)) return this.showAlert('Ada baris yang belum pilih produk.');

      this.saving = true;
      try {
        await axios.post('/api/receiving', {
          id_pesanan: this.form.id_pesanan || null,
          nomor_surat_jalan: this.form.nomor_surat_jalan,
          nomor_faktur: this.form.nomor_faktur,
          id_pemasok: this.form.id_pemasok,
          penerima: this.form.penerima,
          items: this.form.items.map(i => ({
            id_produk: i.id_produk,
            jumlah_diterima: i.jumlah_diterima,
            harga_beli: i.harga_beli,
            diskon_pemasok: i.diskon_pemasok || 0,
          })),
        }, { headers: this.headers() });
        this.showAlert('Penerimaan berhasil! Stok otomatis terupdate.', 'success');
        setTimeout(() => window.location.reload(), 1500);
      } catch (e) {
        this.showAlert(e.response?.data?.message || 'Gagal finalisasi penerimaan');
      } finally {
        this.saving = false;
      }
    },
    showAlert(message, type = 'error', title = '') {
      this.alertModal = { show: true, type, message, title: title || (type === 'error' ? 'PERHATIAN' : 'BERHASIL') };
    },
    closeAlert() {
      this.alertModal.show = false;
    }
  }
};
</script>