<template>
  <div>
    <!-- Step Header -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-4 mb-4 flex justify-between items-center">
      <div class="flex gap-2">
        <a href="/master/hierarki" class="px-3 py-1.5 bg-[#f1f5f9] hover:bg-[#e2e8f0] text-[#0f172a] rounded-[2px] text-xs font-semibold transition-colors">&larr; HIERARKI</a>
      </div>
      <div class="text-center">
        <span class="text-xs font-semibold text-[#10b981] uppercase tracking-wider block">LANGKAH 2 DARI 4</span>
        <h1 class="text-base font-bold text-[#0f172a]">MASTER VENDOR & PEMASOK</h1>
      </div>
      <a href="/master/produk" class="px-4 py-2 bg-[#0f172a] hover:bg-[#1e293b] text-white rounded-[2px] text-xs font-semibold flex items-center gap-1 transition-colors">
        LANJUT: MASTER PRODUK &rarr;
      </a>
    </div>

    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-6 shadow-[0_1px_2px_0_rgb(0_0_0/0.05)] mb-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em]">MASTER VENDOR</h2>
        <button @click="openModal()" class="px-4 py-2 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-sm font-semibold shadow-sm">+ TAMBAH VENDOR</button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
          <thead>
            <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[12px] font-semibold uppercase tracking-[0.05em]">
              <th class="p-3">ID</th>
              <th class="p-3">NAMA</th>
              <th class="p-3">KONTAK</th>
              <th class="p-3">DIVISI</th>
              <th class="p-3 text-right">DISKON</th>
              <th class="p-3 text-right">AKSI</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="5" class="p-6 text-center text-[#64748b]">Memuat...</td></tr>
            <tr v-for="v in vendors" :key="v.id_pemasok" class="border-b border-[#e2e8f0] hover:bg-[#f1f5f9]">
              <td class="p-3 font-mono text-xs text-[#64748b]">{{ v.id_pemasok }}</td>
              <td class="p-3 font-medium text-[#0f172a]">{{ v.nama_pemasok }}</td>
              <td class="p-3 text-[#64748b]">{{ v.nomor_telepon_pemasok }}</td>
              <td class="p-3 text-[#64748b] text-xs">{{ divisiName(v.id_divisi) }}</td>
              <td class="p-3 text-right text-[#0f172a] font-mono">{{ v.diskon_pemasok }}%</td>
              <td class="p-3 text-right">
                <button @click="openModal(v)" class="text-[#10b981] hover:bg-[#ecfdf5] p-2 rounded-[2px] transition-colors inline-flex items-center gap-1 mr-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  EDIT
                </button>
                <button type="button" @click.stop.prevent="deleteVendor(v.id_pemasok)" class="text-[#ef4444] hover:bg-[#fef2f2] p-2 rounded-[2px] transition-colors inline-flex items-center gap-1 cursor-pointer">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  HAPUS
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex justify-between items-center mt-6">
        <span class="text-xs text-[#64748b]">Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
        <div class="flex gap-2">
          <button @click="fetchData(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 bg-gray-100 rounded-[2px] text-xs disabled:opacity-50">PREV</button>
          <button @click="fetchData(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 bg-gray-100 rounded-[2px] text-xs disabled:opacity-50">NEXT</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-[4px] w-96 shadow-xl">
        <h3 class="text-sm font-bold uppercase mb-4">{{ editMode ? 'EDIT VENDOR' : 'TAMBAH VENDOR' }}</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Nama Vendor</label>
            <input v-model="form.nama_pemasok" placeholder="Nama Vendor" class="w-full border p-2 text-sm" />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Alamat Lengkap</label>
            <input v-model="form.alamat_lengkap" placeholder="Alamat Lengkap" class="w-full border p-2 text-sm" />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Kontak / No Telepon</label>
            <input v-model="form.nomor_telepon_pemasok" placeholder="Kontak / No Telepon" class="w-full border p-2 text-sm" />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Diskon (%)</label>
            <input v-model="form.diskon_pemasok" type="number" placeholder="Diskon %" class="w-full border p-2 text-sm" />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Divisi *</label>
            <select v-model="form.id_divisi" class="w-full border p-2 text-sm">
              <option value="" disabled>-- Pilih Divisi --</option>
              <option v-for="d in divisions" :key="d.id_divisi" :value="d.id_divisi">{{ d.nama_divisi }}</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <button @click="showModal = false" class="px-4 py-2 bg-gray-200 rounded-[2px] text-sm">BATAL</button>
          <button @click="saveVendor()" class="px-4 py-2 bg-[#10b981] text-white rounded-[2px] text-sm font-semibold">SIMPAN</button>
        </div>
      </div>
    </div>

    <!-- Custom Delete Modal -->
    <div v-if="deleteModal.show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-[4px] w-96 shadow-xl">
        <h3 class="text-sm font-bold uppercase mb-4">Konfirmasi Hapus</h3>
        <p class="text-sm text-[#64748b] mb-6">Apakah Anda yakin ingin menghapus vendor <strong>{{ deleteModal.item?.nama_pemasok }}</strong>?</p>
        <div class="flex justify-end gap-2">
          <button @click="closeDeleteModal" class="px-4 py-2 bg-gray-200 rounded-[2px] text-sm">BATAL</button>
          <button @click="confirmDelete" class="px-4 py-2 bg-[#ef4444] text-white rounded-[2px] text-sm font-semibold">HAPUS</button>
        </div>
      </div>
    </div>

    <!-- Custom Success Modal -->
    <div v-if="successModal.show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-[4px] w-96 shadow-xl text-center">
        <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-[#ecfdf5] flex items-center justify-center">
          <svg class="w-6 h-6 text-[#10b981]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <h3 class="text-sm font-bold uppercase mb-2">BERHASIL</h3>
        <p class="text-sm text-[#64748b] mb-6">{{ successModal.message }}</p>
        <button @click="successModal.show = false" class="w-full px-4 py-2 bg-[#10b981] text-white rounded-[2px] text-sm font-semibold">OK</button>
      </div>
    </div>
    <!-- Custom Alert Modal (Bapak-bapak Friendly) -->
    <div v-if="alertModal.show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/50" @click="closeAlert"></div>
      <div class="relative bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] shadow-lg max-w-sm w-full p-6 text-center">
        <div class="w-12 h-12 mx-auto mb-4 rounded-full flex items-center justify-center" :class="alertModal.type === 'error' ? 'bg-[#fef2f2]' : 'bg-[#f0fdf4]'">
          <svg v-if="alertModal.type === 'error'" class="w-6 h-6 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <svg v-else class="w-6 h-6 text-[#10b981]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <h3 class="text-base font-semibold text-[#0f172a] mb-2">{{ alertModal.title }}</h3>
        <p class="text-sm text-[#64748b] mb-6 whitespace-pre-line">{{ alertModal.message }}</p>
        <button @click="closeAlert" class="w-full py-2 bg-[#0f172a] hover:bg-[#1e293b] text-white rounded-[2px] text-sm font-semibold transition-colors uppercase">
          OKE, MENGERTI
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() { return { vendors: [], divisions: [], loading: false, showModal: false, editMode: false, form: {}, deleteModal: { show: false, item: null }, pagination: { current_page: 1, last_page: 1, total: 0 }, successModal: { show: false, message: '' }, alertModal: { show: false, type: 'error', title: '', message: '' } }; },
  mounted() { this.fetchData(); this.fetchDivisions(); },
  methods: {
    async fetchDivisions() {
      try {
        const res = await axios.get('/api/divisions', { headers: { Authorization: `Bearer ${localStorage.token}` } });
        this.divisions = res.data.data || res.data || [];
      } catch (e) { console.error(e); }
    },
    divisiName(id) {
      if (!id) return '-';
      return this.divisions.find(d => d.id_divisi === id)?.nama_divisi || id;
    },
    async fetchData(page = 1) {
      this.loading = true;
      try {
        const token = localStorage.token;
        const res = await axios.get(`/api/vendors?page=${page}&per_page=5`, { headers: { Authorization: `Bearer ${token}` } });
        this.vendors = res.data.data || res.data;
        this.pagination = {
          current_page: res.data.current_page || 1,
          last_page: res.data.last_page || 1,
          total: res.data.total || this.vendors.length
        };
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
    openModal(v = null) {
      this.editMode = !!v;
      this.form = v ? { ...v } : { nama_pemasok: '', nomor_telepon_pemasok: '', diskon_pemasok: 0, id_divisi: this.divisions[0]?.id_divisi || '' };
      this.showModal = true;
    },
    async saveVendor() {
      if (!this.form.nama_pemasok?.trim()) {
        this.showAlert('Nama vendor/pemasok wajib diisi!', 'error');
        return;
      }
      if (!this.form.id_divisi) {
        this.showAlert('Divisi wajib dipilih!', 'error');
        return;
      }
      try {
        const token = localStorage.token;
        const config = { headers: { Authorization: `Bearer ${token}` } };
        if (this.editMode) {
          await axios.put(`/api/vendors/${this.form.id_pemasok}`, this.form, config);
          this.showModal = false;
          this.fetchData();
          this.successModal = { show: true, message: 'Vendor berhasil diupdate!' };
        } else {
          await axios.post('/api/vendors', this.form, config);
          this.showModal = false;
          this.fetchData();
          this.successModal = { show: true, message: 'Vendor baru berhasil ditambahkan!' };
        }
      } catch (e) { this.showAlert(e.response?.data?.message || 'Gagal menyimpan data vendor', 'error'); }
    },
    async deleteVendor(id) {
      if (!id) {
        console.warn('deleteVendor: id is empty');
        this.showAlert('ID vendor tidak valid', 'error');
        return;
      }
      this.deleteModal.item = this.vendors.find(v => v.id_pemasok === id);
      if (!this.deleteModal.item) {
        console.warn('deleteVendor: vendor not found with id:', id);
        console.table(this.vendors.map(v => ({ id: v.id_pemasok, nama: v.nama_pemasok })));
        this.showAlert('Vendor tidak ditemukan', 'error');
        return;
      }
      this.deleteModal.show = true;
    },
    closeDeleteModal() {
      this.deleteModal = { show: false, item: null };
    },
    async confirmDelete() {
      const { item } = this.deleteModal;
      if (!item) return;
      const token = localStorage.token;
      try {
        await axios.delete(`/api/vendors/${item.id_pemasok}`, { headers: { Authorization: `Bearer ${token}` } });
        this.closeDeleteModal();
        this.fetchData();
      } catch (e) { this.showAlert(e.response?.data?.message || 'Gagal menghapus vendor', 'error'); }
    },
    showAlert(message, type = 'error', title = '') {
      this.alertModal = {
        show: true,
        type: type,
        title: title || (type === 'error' ? 'PERHATIAN' : 'BERHASIL'),
        message: message
      };
    },
    closeAlert() {
      this.alertModal.show = false;
    }
  }
};
</script>