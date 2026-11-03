<template>
  <div>
    <!-- Step Header -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-4 mb-4 flex justify-between items-center">
      <div>
        <span class="text-xs font-semibold text-[#10b981] uppercase tracking-wider block">LANGKAH 1 DARI 4</span>
        <h1 class="text-base font-bold text-[#0f172a]">MASTER HIERARKI PRODUK</h1>
        <p class="text-xs text-[#64748b]">Tentukan struktur kategori barang (Divisi > Departemen > Kelas > Subkelas) sebelum mendaftar vendor.</p>
      </div>
      <a href="/master/vendor" class="px-4 py-2 bg-[#0f172a] hover:bg-[#1e293b] text-white rounded-[2px] text-xs font-semibold flex items-center gap-1 transition-colors">
        LANJUT: MASTER VENDOR &rarr;
      </a>
    </div>

    <!-- Form Input Terpadu -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-6 shadow-[0_1px_2px_0_rgb(0_0_0/0.05)] mb-6">
      <h2 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em] mb-4">INPUT DATA SUB-KELAS</h2>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
              <div class="relative">
                <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">DIVISI</label>
                <input v-model="form.divisi" @focus="show.divisi = true" @input="onDivisiInput" @blur="hideLater('divisi')" type="text" placeholder="Ketik nama divisi..." autocomplete="off" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] font-mono focus:border-[#10b981]" />
                <ul v-if="show.divisi && filteredDivisions.length" class="absolute z-20 mt-1 w-full max-h-48 overflow-auto bg-white border border-[#e2e8f0] rounded-[2px] shadow-lg">
                  <li v-for="d in filteredDivisions" :key="d.id_divisi" @mousedown.prevent="pickDivisi(d)" class="px-3 py-2 text-sm font-mono text-[#0f172a] hover:bg-[#ecfdf5] cursor-pointer">{{ d.nama_divisi }}</li>
                </ul>
              </div>
              <div class="relative">
                <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">DEPARTEMEN</label>
                <input v-model="form.departemen" @focus="show.departemen = true" @input="onDepartemenInput" @blur="hideLater('departemen')" type="text" placeholder="Ketik nama departemen..." autocomplete="off" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] font-mono focus:border-[#10b981]" />
                <ul v-if="show.departemen && filteredDepartments.length" class="absolute z-20 mt-1 w-full max-h-48 overflow-auto bg-white border border-[#e2e8f0] rounded-[2px] shadow-lg">
                  <li v-for="d in filteredDepartments" :key="d.id_departemen" @mousedown.prevent="pickDepartemen(d)" class="px-3 py-2 text-sm font-mono text-[#0f172a] hover:bg-[#ecfdf5] cursor-pointer">{{ d.nama_departemen }}</li>
                </ul>
              </div>
              <div class="relative">
                <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">KELAS</label>
                <input v-model="form.kelas" @focus="show.kelas = true" @input="onKelasInput" @blur="hideLater('kelas')" type="text" placeholder="Ketik nama kelas..." autocomplete="off" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] font-mono focus:border-[#10b981]" />
                <ul v-if="show.kelas && filteredClasses.length" class="absolute z-20 mt-1 w-full max-h-48 overflow-auto bg-white border border-[#e2e8f0] rounded-[2px] shadow-lg">
                  <li v-for="c in filteredClasses" :key="c.id_kelas" @mousedown.prevent="pickKelas(c)" class="px-3 py-2 text-sm font-mono text-[#0f172a] hover:bg-[#ecfdf5] cursor-pointer">{{ c.nama_kelas }}</li>
                </ul>
              </div>
              <div class="relative">
                <label class="block text-[12px] font-semibold text-[#64748b] uppercase tracking-[0.05em] mb-1">SUB-KELAS</label>
                <input v-model="form.subkelas" @focus="show.subkelas = true" @input="show.subkelas = true" @blur="hideLater('subkelas')" type="text" placeholder="Ketik nama sub-kelas..." autocomplete="off" class="w-full bg-[#ffffff] border border-[#e2e8f0] rounded-[2px] px-3 py-2 text-sm text-[#0f172a] focus:border-[#10b981] font-mono" />
                <ul v-if="show.subkelas && filteredSubclasses.length" class="absolute z-20 mt-1 w-full max-h-48 overflow-auto bg-white border border-[#e2e8f0] rounded-[2px] shadow-lg">
                  <li v-for="s in filteredSubclasses" :key="s.id_subkelas" @mousedown.prevent="pickSubkelas(s)" class="px-3 py-2 text-sm font-mono text-[#0f172a] hover:bg-[#ecfdf5] cursor-pointer">{{ s.nama_subkelas }}</li>
                </ul>
              </div>
            </div>

      <div class="flex justify-end">
        <button @click="saveHierarchy" class="px-5 py-2 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-sm font-semibold shadow-sm transition-colors">
          + TAMBAH DATA
        </button>
      </div>
    </div>

    <!-- Tabel Hasil -->
    <div class="mt-6 bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] shadow-[0_1px_2px_0_rgb(0_0_0/0.05)] overflow-hidden">
      <div class="px-6 py-4 border-b border-[#e2e8f0] bg-[#f1f5f9] flex justify-between items-center">
        <h2 class="text-sm font-semibold text-[#0f172a] uppercase tracking-[0.02em]">DAFTAR STRUKTUR HIERARKI</h2>
        <span class="text-xs font-mono text-[#64748b]">Total: {{ items.length }} baris</span>
      </div>

      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[12px] font-semibold uppercase tracking-[0.05em]">
            <th class="p-3">NO</th>
            <th class="p-3">DIVISI</th>
            <th class="p-3">DEPARTEMEN</th>
            <th class="p-3">KELAS</th>
            <th class="p-3">SUB-KELAS</th>
            <th class="p-3 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="p-6 text-center text-sm text-[#64748b] font-mono">Memuat data...</td>
          </tr>
          <tr v-else-if="items.length === 0">
            <td colspan="6" class="p-6 text-center text-sm text-[#64748b]">Belum ada data hierarki.</td>
          </tr>
          <tr v-for="(item, index) in items" :key="item.id_subkelas || index" class="border-b border-[#e2e8f0] hover:bg-[#f1f5f9] transition-colors">
            <td class="p-3 text-sm font-mono text-[#64748b] w-16">{{ index + 1 }}</td>
            <td class="p-3 text-sm font-medium text-[#0f172a] font-mono">{{ item.divisi }}</td>
            <td class="p-3 text-sm text-[#64748b] font-mono">{{ item.departemen || '-' }}</td>
            <td class="p-3 text-sm text-[#64748b] font-mono">{{ item.kelas || '-' }}</td>
            <td class="p-3 text-sm text-[#64748b] font-mono">{{ item.subkelas || '-' }}</td>
            <td class="p-3 text-right">
              <button @click="openDeleteModal(item)" class="px-2 py-1 bg-transparent hover:bg-[#fef2f2] text-[#ef4444] rounded-[2px] text-xs font-semibold transition-colors inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                HAPUS
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Custom Delete Modal -->
    <div v-if="deleteModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/50" @click="closeDeleteModal"></div>
      <div class="relative bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] shadow-lg max-w-sm w-full p-6">
        <div class="text-center">
          <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-[#fef2f2] flex items-center justify-center">
            <svg class="w-6 h-6 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <h3 class="text-base font-semibold text-[#0f172a] mb-2">Konfirmasi Hapus</h3>
          <p class="text-sm text-[#64748b] mb-6">Apakah Anda yakin ingin menghapus data ini?<br/>Tindakan ini tidak dapat dibatalkan.</p>
          <div class="flex gap-3 justify-center">
            <button @click="closeDeleteModal" class="px-4 py-2 border border-[#e2e8f0] text-[#64748b] rounded-[2px] text-sm font-semibold hover:bg-[#f1f5f9] transition-colors">
              BATAL
            </button>
            <button @click="confirmDelete" class="px-4 py-2 bg-[#ef4444] hover:bg-[#dc2626] text-white rounded-[2px] text-sm font-semibold shadow-sm transition-colors">
              HAPUS
            </button>
          </div>
        </div>
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
  name: 'MasterHierarki',
  data() {
    return {
      form: {
              divisi: '',
              departemen: '',
              kelas: '',
              subkelas: ''
            },
            // id parent terpilih dari suggestion (autocomplete cascading)
            selDivisi: '',
            selDepartemen: '',
            selKelas: '',
            show: { divisi: false, departemen: false, kelas: false, subkelas: false },
            divisions: [],
      departments: [],
      classes: [],
      subclasses: [],
      items: [],
      loading: false,
      deleteModal: {
        show: false,
        item: null
      },
      alertModal: {
        show: false,
        type: 'error',
        title: '',
        message: ''
      }
    };
  },
  computed: {
      // Autocomplete cascading — semua berbasis TEXT input (bukan select).
      // Parent terpilih disimpan di sel* (id), pencarian pakai substring nama.
      filteredDivisions() {
        const q = (this.form.divisi || '').toLowerCase().trim();
        if (!q) return this.divisions.slice(0, 50);
        return this.divisions.filter(d => (d.nama_divisi || '').toLowerCase().includes(q)).slice(0, 50);
      },
      filteredDepartments() {
        const q = (this.form.departemen || '').toLowerCase().trim();
        let list = this.departments;
        // Kalau divisi sudah dipilih dari dropdown, kunci ke divisi itu.
        if (this.selDivisi) list = list.filter(d => d.id_divisi === this.selDivisi);
        if (!q) return list.slice(0, 50);
        return list.filter(d => (d.nama_departemen || '').toLowerCase().includes(q)).slice(0, 50);
      },
      filteredClasses() {
        const q = (this.form.kelas || '').toLowerCase().trim();
        let list = this.classes;
        if (this.selDepartemen) list = list.filter(c => c.id_departemen === this.selDepartemen);
        if (!q) return list.slice(0, 50);
        return list.filter(c => (c.nama_kelas || '').toLowerCase().includes(q)).slice(0, 50);
      },
      filteredSubclasses() {
        const q = (this.form.subkelas || '').toLowerCase().trim();
        let list = this.subclasses;
        if (this.selKelas) list = list.filter(s => s.id_kelas === this.selKelas);
        if (!q) return list.slice(0, 50);
        return list.filter(s => (s.nama_subkelas || '').toLowerCase().includes(q)).slice(0, 50);
      },
    },
  mounted() {
    this.fetchData();
  },
  methods: {
      onDivisiInput() {
        this.selDivisi = '';
        this.form.departemen = ''; this.selDepartemen = '';
        this.form.kelas = ''; this.selKelas = '';
        this.show.divisi = true;
      },
      pickDivisi(d) {
        this.form.divisi = d.nama_divisi;
        this.selDivisi = d.id_divisi;
        this.show.divisi = false;
      },
      onDepartemenInput() {
        this.selDepartemen = '';
        this.form.kelas = ''; this.selKelas = '';
        this.show.departemen = true;
      },
      pickDepartemen(d) {
        this.form.departemen = d.nama_departemen;
        this.selDepartemen = d.id_departemen;
        if (d.id_divisi) {
          this.selDivisi = d.id_divisi;
          const div = this.divisions.find(x => x.id_divisi === d.id_divisi);
          if (div) this.form.divisi = div.nama_divisi;
        }
        this.show.departemen = false;
      },
      onKelasInput() {
        this.selKelas = '';
        this.show.kelas = true;
      },
      pickKelas(c) {
        this.form.kelas = c.nama_kelas;
        this.selKelas = c.id_kelas;
        if (c.id_departemen) {
          this.selDepartemen = c.id_departemen;
          const dept = this.departments.find(x => x.id_departemen === c.id_departemen);
          if (dept) {
            this.form.departemen = dept.nama_departemen;
            if (dept.id_divisi) {
              this.selDivisi = dept.id_divisi;
              const div = this.divisions.find(x => x.id_divisi === dept.id_divisi);
              if (div) this.form.divisi = div.nama_divisi;
            }
          }
        }
        this.show.kelas = false;
      },
      pickSubkelas(s) {
        this.form.subkelas = s.nama_subkelas;
        if (s.id_kelas) {
          const cls = this.classes.find(x => x.id_kelas === s.id_kelas);
          if (cls) this.pickKelas(cls);
        }
        this.show.subkelas = false;
      },
      hideLater(field) {
        setTimeout(() => { this.show[field] = false; }, 200);
      },
      async fetchData() {
      this.loading = true;
      try {
        const token = localStorage.getItem('token') || '';
        const headers = { Authorization: `Bearer ${token}` };
        
        const [divRes, depRes, clsRes, subRes] = await Promise.all([
          axios.get('/api/divisions', { headers }),
          axios.get('/api/departments', { headers }),
          axios.get('/api/classes', { headers }),
          axios.get('/api/subclasses', { headers })
        ]);

        this.divisions = divRes.data.data || divRes.data || [];
        this.departments = depRes.data.data || depRes.data || [];
        this.classes = clsRes.data.data || clsRes.data || [];
        this.subclasses = subRes.data.data || subRes.data || [];
        
        const divMap = {};
        for (const div of this.divisions) {
          divMap[div.id_divisi] = { nama: div.nama_divisi, deps: {} };
        }
        
        for (const dep of this.departments) {
          if (divMap[dep.id_divisi]) {
            divMap[dep.id_divisi].deps[dep.id_departemen] = { 
              nama: dep.nama_departemen, 
              kelas: {} 
            };
          }
        }
        
        for (const cls of this.classes) {
          for (const divId of Object.keys(divMap)) {
            if (divMap[divId].deps[cls.id_departemen]) {
              divMap[divId].deps[cls.id_departemen].kelas[cls.id_kelas] = {
                nama: cls.nama_kelas,
                subs: []
              };
            }
          }
        }
        
        for (const sub of this.subclasses) {
          for (const divId of Object.keys(divMap)) {
            for (const depId of Object.keys(divMap[divId].deps)) {
              if (divMap[divId].deps[depId].kelas[sub.id_kelas]) {
                divMap[divId].deps[depId].kelas[sub.id_kelas].subs.push({
                  id: sub.id_subkelas,
                  nama: sub.nama_subkelas
                });
              }
            }
          }
        }

        this.items = [];
        for (const divId of Object.keys(divMap)) {
          for (const depId of Object.keys(divMap[divId].deps)) {
            for (const clsId of Object.keys(divMap[divId].deps[depId].kelas)) {
              const clsData = divMap[divId].deps[depId].kelas[clsId];
              for (const sub of clsData.subs) {
                this.items.push({
                  id_subkelas: sub.id,
                  id_kelas: clsId,
                  id_departemen: depId,
                  id_divisi: divId,
                  divisi: divMap[divId].nama,
                  departemen: divMap[divId].deps[depId].nama,
                  kelas: clsData.nama,
                  subkelas: sub.nama
                });
              }
            }
          }
        }
      } catch (e) {
        console.error('Gagal memuat data hierarki', e);
      } finally {
        this.loading = false;
      }
    },
    async saveHierarchy() {
      if (!this.form.subkelas?.trim()) {
        this.showAlert('Sub-Kelas wajib diisi', 'error');
        return;
      }
      if (!this.form.divisi || !this.form.departemen || !this.form.kelas) {
        this.showAlert('Semua kolom hierarki wajib diisi', 'error');
        return;
      }

      const token = localStorage.getItem('token') || '';
      const headers = { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' };

      try {
        await axios.post('/api/subclasses', {
          nama_divisi: this.form.divisi,
          nama_departemen: this.form.departemen,
          nama_kelas: this.form.kelas,
          nama_subkelas: this.form.subkelas.trim()
        }, { headers });
        
        this.form.subkelas = '';
        await this.fetchData();
        this.showAlert('Hierarki berhasil ditambahkan!', 'success');
      } catch (e) {
        console.error('Gagal simpan hierarki', e);
        this.showAlert(e.response?.data?.message || 'Gagal menyimpan hierarki', 'error');
      }
    },
    openDeleteModal(item) {
      this.deleteModal = { show: true, item };
    },
    closeDeleteModal() {
      this.deleteModal = { show: false, item: null };
    },
    async confirmDelete() {
      const { item } = this.deleteModal;
      if (!item) return;

      const token = localStorage.getItem('token') || '';
      const headers = { Authorization: `Bearer ${token}` };

      try {
        await axios.delete(`/api/subclasses/${item.id_subkelas}`, { headers });
        this.closeDeleteModal();
        await this.fetchData();
        this.showAlert('Data berhasil dihapus', 'success');
      } catch (e) {
        console.error('Gagal hapus', e);
        const msg = e.response?.data?.message;
        if (msg?.includes('cascade') || msg?.includes('masih punya')) {
          this.showAlert('Tidak bisa dihapus: masih punya data di bawahnya. Hapus child dulu.', 'error');
        } else {
          this.showAlert(msg || 'Gagal menghapus data', 'error');
        }
      }
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
  },
};
</script>
