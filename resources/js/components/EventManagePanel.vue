<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em]">MANAJEMEN EVENT</h2>
        <p class="text-xs text-[#64748b] mt-0.5">Kelola event bazar. Event AKTIF bisa ditutup (frozen + bon retur).</p>
      </div>
      <div class="flex items-center gap-2">
        <input v-model="search" @input="debouncedFetch" placeholder="Cari event..." class="border p-2 text-sm w-56" />
        <button @click="openModal()" class="px-3 py-2 bg-[#10b981] text-white text-xs font-semibold rounded-[2px] hover:bg-[#059669]">+ EVENT BARU</button>
      </div>
    </div>

    <table class="w-full text-left border-collapse text-sm bg-white">
      <thead>
        <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[12px] font-semibold uppercase">
          <th class="p-3">ID</th>
          <th class="p-3">NAMA</th>
          <th class="p-3">TANGGAL</th>
          <th class="p-3">LOKASI</th>
          <th class="p-3">DISKON</th>
          <th class="p-3">STATUS</th>
          <th class="p-3 text-right">AKSI</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="e in events" :key="e.id_acara" class="border-b border-[#e2e8f0] hover:bg-[#f1f5f9]">
          <td class="p-3 font-mono text-xs">{{ e.id_acara }}</td>
          <td class="p-3">{{ e.nama_acara }}</td>
          <td class="p-3 font-mono text-xs text-[#64748b]">{{ e.tanggal_mulai }} &rarr; {{ e.tanggal_selesai }}</td>
          <td class="p-3 text-[#64748b]">{{ e.lokasi }}</td>
          <td class="p-3 font-mono text-xs">{{ e.jenis_diskon === 'PERSEN' ? e.nilai_diskon + '%' : 'Rp ' + Number(e.nilai_diskon || 0).toLocaleString() }}</td>
          <td class="p-3">
            <span :class="e.status_acara === 'AKTIF' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'" class="px-2 py-0.5 rounded-[2px] border text-[11px] font-semibold">{{ e.status_acara }}</span>
          </td>
          <td class="p-3 text-right whitespace-nowrap">
            <button @click="openModal(e)" class="text-[#64748b] hover:text-[#0f172a] text-xs font-semibold mr-3">EDIT</button>
            <button v-if="e.status_acara === 'AKTIF'" @click="askClose(e)" class="px-2 py-1 bg-[#0f172a] text-white text-[11px] font-semibold rounded-[2px] hover:bg-[#1e293b]">TUTUP EVENT</button>
            <span v-else class="text-[11px] text-slate-400 font-mono">frozen</span>
          </td>
        </tr>
        <tr v-if="!loading && events.length === 0">
          <td colspan="7" class="p-6 text-center text-[#64748b]">Belum ada event.</td>
        </tr>
      </tbody>
    </table>

    <!-- Modal form -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4" @click.self="showModal = false">
      <div class="bg-white rounded-[4px] shadow-xl w-full max-w-lg">
        <div class="px-5 py-3 border-b border-[#e2e8f0]">
          <h3 class="text-sm font-semibold text-[#0f172a] uppercase">{{ editMode ? 'EDIT EVENT' : 'EVENT BARU' }}</h3>
        </div>
        <div class="px-5 py-4 space-y-3">
          <div>
            <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Nama Event *</label>
            <input v-model="form.nama_acara" type="text" class="w-full border p-2 text-sm" placeholder="Bazar Buku Desember" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Tanggal Mulai *</label>
              <input v-model="form.tanggal_mulai" type="date" class="w-full border p-2 text-sm font-mono" />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Tanggal Selesai *</label>
              <input v-model="form.tanggal_selesai" type="date" class="w-full border p-2 text-sm font-mono" />
            </div>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Lokasi *</label>
            <input v-model="form.lokasi" type="text" class="w-full border p-2 text-sm" placeholder="Mall Kelapa Gading Atrium" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Jenis Diskon</label>
              <select v-model="form.jenis_diskon" class="w-full border p-2 text-sm">
                <option value="PERSEN">PERSEN (%)</option>
                <option value="NILAI">NILAI (Rp)</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Nilai Diskon</label>
              <input v-model.number="form.nilai_diskon" type="number" min="0" class="w-full border p-2 text-sm font-mono" />
            </div>
          </div>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t border-[#e2e8f0]">
          <button @click="showModal = false" class="px-4 py-2 border text-xs font-semibold rounded-[2px]">BATAL</button>
          <button @click="save()" :disabled="saving" class="px-4 py-2 bg-[#10b981] text-white text-xs font-semibold rounded-[2px] disabled:opacity-50">{{ saving ? 'MENYIMPAN...' : 'SIMPAN' }}</button>
        </div>
      </div>
    </div>

    <!-- Modal konfirmasi closing -->
    <div v-if="closeTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4">
      <div class="bg-white rounded-[4px] shadow-xl w-full max-w-md">
        <div class="px-5 py-3 border-b border-[#e2e8f0]">
          <h3 class="text-sm font-semibold text-[#0f172a] uppercase">TUTUP EVENT?</h3>
        </div>
        <div class="px-5 py-4 text-sm text-[#0f172a] space-y-2">
          <p>Event <b>{{ closeTarget.nama_acara }}</b> ({{ closeTarget.id_acara }}) akan di-<b>freeze</b>.</p>
          <p class="text-xs text-[#64748b]">Sisa stok konsinyasi otomatis diretur ke vendor + bon retur dibuat. Tindakan ini tidak bisa dibatalkan.</p>
        </div>
        <div class="flex justify-end gap-2 px-5 py-3 border-t border-[#e2e8f0]">
          <button @click="closeTarget = null" class="px-4 py-2 border text-xs font-semibold rounded-[2px]">BATAL</button>
          <button @click="doClose()" :disabled="saving" class="px-4 py-2 bg-[#0f172a] text-white text-xs font-semibold rounded-[2px] disabled:opacity-50">{{ saving ? 'MEMPROSES...' : 'YA, TUTUP' }}</button>
        </div>
      </div>
    </div>

    <!-- Alert -->
    <div v-if="alertModal.show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4" @click.self="alertModal.show = false">
      <div class="bg-white rounded-[4px] shadow-xl w-full max-w-sm">
        <div class="px-5 py-3 border-b border-[#e2e8f0]">
          <h3 class="text-sm font-semibold uppercase" :class="alertModal.type === 'error' ? 'text-[#ef4444]' : 'text-[#059669]'">{{ alertModal.title }}</h3>
        </div>
        <div class="px-5 py-4 text-sm text-[#0f172a] whitespace-pre-line">{{ alertModal.message }}</div>
        <div class="flex justify-end px-5 py-3 border-t border-[#e2e8f0]">
          <button @click="alertModal.show = false" class="px-4 py-2 bg-[#0f172a] text-white rounded-[2px] text-xs font-semibold">OK</button>
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
      events: [],
      search: '',
      loading: false,
      saving: false,
      showModal: false,
      editMode: false,
      closeTarget: null,
      form: {},
      alertModal: { show: false, type: 'error', title: '', message: '' },
      timer: null,
    };
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    h() {
      return { Authorization: 'Bearer ' + (localStorage.token || '') };
    },
    debouncedFetch() {
      clearTimeout(this.timer);
      this.timer = setTimeout(() => this.fetchData(), 300);
    },
    async fetchData() {
      this.loading = true;
      try {
        const res = await axios.get('/api/events', { headers: this.h(), params: { per_page: 100, search: this.search } });
        const body = res.data;
        this.events = (body.data || body || []).filter(e =>
          !this.search || e.nama_acara.toLowerCase().includes(this.search.toLowerCase()) || e.id_acara.toLowerCase().includes(this.search.toLowerCase())
        );
      } catch (e) {
        this.alert('Gagal memuat event: ' + (e.response?.data?.message || e.message));
      } finally {
        this.loading = false;
      }
    },
    openModal(e = null) {
      this.editMode = !!e;
      this.form = e
        ? { ...e }
        : { nama_acara: '', tanggal_mulai: new Date().toISOString().slice(0, 10), tanggal_selesai: new Date().toISOString().slice(0, 10), lokasi: '', jenis_diskon: 'PERSEN', nilai_diskon: 0 };
      this.showModal = true;
    },
    async save() {
      if (!this.form.nama_acara?.trim()) return this.alert('Nama event wajib diisi.');
      if (!this.form.tanggal_mulai || !this.form.tanggal_selesai) return this.alert('Tanggal mulai & selesai wajib diisi.');
      if (this.form.tanggal_selesai < this.form.tanggal_mulai) return this.alert('Tanggal selesai tidak boleh sebelum tanggal mulai.');
      if (!this.form.lokasi?.trim()) return this.alert('Lokasi wajib diisi.');
      this.saving = true;
      try {
        const payload = {
          nama_acara: this.form.nama_acara,
          tanggal_mulai: this.form.tanggal_mulai,
          tanggal_selesai: this.form.tanggal_selesai,
          lokasi: this.form.lokasi,
          jenis_diskon: this.form.jenis_diskon,
          nilai_diskon: this.form.nilai_diskon || 0,
        };
        if (this.editMode) {
          await axios.put('/api/events/' + this.form.id_acara, payload, { headers: this.h() });
        } else {
          await axios.post('/api/events', payload, { headers: this.h() });
        }
        this.showModal = false;
        await this.fetchData();
        this.alert(this.editMode ? 'Event diperbarui.' : 'Event dibuat.', 'success', 'BERHASIL');
      } catch (e) {
        const errs = e.response?.data?.errors;
        this.alert(errs ? Object.values(errs).flat().join('\n') : (e.response?.data?.message || 'Gagal menyimpan event.'));
      } finally {
        this.saving = false;
      }
    },
    askClose(e) {
      this.closeTarget = e;
    },
    async doClose() {
      this.saving = true;
      try {
        const res = await axios.post('/api/events/' + this.closeTarget.id_acara + '/close', {}, { headers: this.h() });
        const bon = res.data?.bon_return;
        this.closeTarget = null;
        await this.fetchData();
        this.alert('Event ditutup.' + (bon ? '\nBon retur: ' + (bon.id_retur || bon.id || JSON.stringify(bon)) : ''), 'success', 'BERHASIL');
      } catch (e) {
        this.alert(e.response?.data?.message || 'Gagal menutup event.');
      } finally {
        this.saving = false;
      }
    },
    alert(message, type = 'error', title = '') {
      this.alertModal = { show: true, type, message, title: title || (type === 'error' ? 'PERHATIAN' : 'BERHASIL') };
    },
  },
};
</script>
