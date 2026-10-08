<template>
  <div class="p-6 bg-slate-900 text-slate-100 min-h-screen">
    <div class="max-w-7xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-emerald-400">Hierarki Master Data</h1>
          <p class="text-sm text-slate-400">Kelola Divisi, Departemen, Kelas, dan Sub-Kelas secara hierarkis (Vue 3 Hybrid)</p>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="flex space-x-2 border-b border-slate-700 pb-3 mb-6">
        <button 
          v-for="tab in tabs" 
          :key="tab.key"
          @click="currentTab = tab.key"
          :class="['px-4 py-2 rounded-sm text-sm font-medium transition-colors', currentTab === tab.key ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700']"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Content Area -->
      <div class="bg-slate-800 border border-slate-700 rounded-sm p-4 shadow-sm">
        <div class="flex justify-between items-center mb-4">
          <span class="text-sm font-semibold uppercase text-slate-400">Daftar {{ currentLabel }}</span>
          <button @click="openModal()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-sm text-sm font-medium transition-colors">
            + Tambah {{ currentLabel }}
          </button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-sm">
            <thead>
              <tr class="border-b border-slate-700 text-slate-400 bg-slate-900/50">
                <th class="p-3">ID</th>
                <th class="p-3">Nama</th>
                <th class="p-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading"><td colspan="3" class="p-4 text-center text-slate-400">Memuat data...</td></tr>
              <tr v-else-if="items.length === 0"><td colspan="3" class="p-4 text-center text-slate-400">Belum ada data.</td></tr>
              <tr v-for="item in items" :key="item[pkField]" class="border-b border-slate-700/50 hover:bg-slate-700/30">
                <td class="p-3 font-mono text-slate-300">{{ item[pkField] }}</td>
                <td class="p-3 font-medium">{{ item[nameField] }}</td>
                <td class="p-3 text-right space-x-2">
                  <button @click="openModal(item)" class="text-xs bg-amber-600 hover:bg-amber-500 px-2.5 py-1 rounded-sm text-white">Edit</button>
                  <button @click="deleteItem(item[pkField])" class="text-xs bg-rose-600 hover:bg-rose-500 px-2.5 py-1 rounded-sm text-white">Hapus</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 z-50">
      <div class="bg-slate-800 border border-slate-700 rounded-sm w-full max-w-md p-6">
        <h3 class="text-lg font-bold text-emerald-400 mb-4">{{ editMode ? 'Edit' : 'Tambah' }} {{ currentLabel }}</h3>
        
        <form @submit.prevent="saveItem">
          <div class="mb-4">
            <label class="block text-xs uppercase text-slate-400 mb-1">Nama {{ currentLabel }}</label>
            <input v-model="form[nameField]" type="text" required class="w-full bg-slate-900 border border-slate-700 rounded-sm px-3 py-2 text-slate-100 focus:outline-none focus:border-emerald-500" />
          </div>

          <!-- Relational Parent Dropdown (jika bukan divisi) -->
          <div v-if="currentTab !== 'divisions'" class="mb-4">
            <label class="block text-xs uppercase text-slate-400 mb-1">Induk Terkait</label>
            <select v-model="form[parentKey]" required class="w-full bg-slate-900 border border-slate-700 rounded-sm px-3 py-2 text-slate-100 focus:outline-none focus:border-emerald-500">
              <option value="" disabled>Pilih Induk</option>
              <option v-for="p in parentList" :value="p[parentPk]">{{ p[parentNameKey] }}</option>
            </select>
          </div>

          <div class="flex justify-end space-x-2 mt-6">
            <button type="button" @click="showModal = false" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-sm text-sm">Batal</button>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-sm text-sm font-medium">Simpan</button>
          </div>
        </form>
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
      currentTab: 'divisions',
      tabs: [
        { key: 'divisions', label: 'Divisi' },
        { key: 'departments', label: 'Departemen' },
        { key: 'classes', label: 'Kelas' },
        { key: 'subclasses', label: 'Sub-Kelas' },
      ],
      items: [],
      loading: false,
      showModal: false,
      editMode: false,
      form: {},
      parentList: [],
    };
  },
  computed: {
    currentLabel() {
      const t = this.tabs.find(x => x.key === this.currentTab);
      return t ? t.label : '';
    },
    pkField() {
      return { divisions: 'id_divisi', departments: 'id_departemen', classes: 'id_kelas', subclasses: 'id_subkelas' }[this.currentTab];
    },
    nameField() {
      return { divisions: 'nama_divisi', departments: 'nama_departemen', classes: 'nama_kelas', subclasses: 'nama_subkelas' }[this.currentTab];
    },
    parentKey() {
      return { departments: 'id_divisi', classes: 'id_departemen', subclasses: 'id_kelas' }[this.currentTab];
    },
    parentPk() {
      return { departments: 'id_divisi', classes: 'id_departemen', subclasses: 'id_kelas' }[this.currentTab];
    },
    parentNameKey() {
      return { departments: 'nama_divisi', classes: 'nama_departemen', subclasses: 'nama_kelas' }[this.currentTab];
    },
  },
  watch: {
    currentTab() {
      this.fetchData();
      if (this.currentTab !== 'divisions') {
        this.fetchParents();
      }
    },
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    async fetchData() {
      this.loading = true;
      try {
        const token = localStorage.getItem('token') || '';
        const res = await axios.get(`/api/master/${this.currentTab}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.items = res.data.data || res.data;
      } catch (e) {
        alert('Gagal memuat data');
      } finally {
        this.loading = false;
      }
    },
    async fetchParents() {
      const parentMap = { departments: 'divisions', classes: 'departments', subclasses: 'classes' };
      const target = parentMap[this.currentTab];
      if (!target) return;
      try {
        const token = localStorage.getItem('token') || '';
        const res = await axios.get(`/api/master/${target}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.parentList = res.data.data || res.data;
      } catch (e) {
        console.error('Gagal memuat induk');
      }
    },
    openModal(item = null) {
      this.editMode = !!item;
      this.form = item ? { ...item } : { [this.nameField]: '', [this.parentKey]: '' };
      this.showModal = true;
      if (this.currentTab !== 'divisions') {
        this.fetchParents();
      }
    },
    async saveItem() {
      try {
        const token = localStorage.getItem('token') || '';
        const headers = { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' };
        if (this.editMode) {
          await axios.put(`/api/master/${this.currentTab}/${this.form[this.pkField]}`, this.form, { headers });
        } else {
          await axios.post(`/api/master/${this.currentTab}`, this.form, { headers });
        }
        this.showModal = false;
        this.fetchData();
      } catch (e) {
        alert(e.response?.data?.message || 'Gagal menyimpan data');
      }
    },
    async deleteItem(id) {
      if (!confirm('Yakin ingin menghapus data ini?')) return;
      try {
        const token = localStorage.getItem('token') || '';
        await axios.delete(`/api/master/${this.currentTab}/${id}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        this.fetchData();
      } catch (e) {
        alert(e.response?.data?.message || 'Gagal menghapus (terproteksi cascade)');
      }
    },
  },
};
</script>
