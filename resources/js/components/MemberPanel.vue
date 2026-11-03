<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="bg-[#ffffff] border border-[#e2e8f0] rounded-[4px] p-4 flex justify-between items-center">
      <div>
        <span class="text-xs font-semibold text-[#10b981] uppercase tracking-wider block">SPESIFIKASI 6.2 &amp; 6.3 — MASTER MEMBER VIP</span>
        <h1 class="text-base font-bold text-[#0f172a]">MASTER MEMBER VIP</h1>
        <p class="text-xs text-[#64748b]">Daftar member, tier, poin, dan histori transaksi.</p>
      </div>
      <button @click="openCreate" class="px-4 py-2 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-xs font-semibold transition-colors">+ MEMBER BARU</button>
    </div>

    <!-- Search + filter -->
    <div class="flex flex-wrap items-center gap-2">
      <input v-model="cari" @keyup.enter="fetchMembers" placeholder="Cari nama / no HP / kota..."
        class="border border-[#e2e8f0] rounded-[2px] px-3 py-1.5 text-sm font-mono w-64 bg-white" />
      <select v-model="tier" @change="fetchMembers" class="border border-[#e2e8f0] rounded-[2px] px-3 py-1.5 text-xs font-mono bg-white">
        <option value="">SEMUA TIER</option>
        <option value="bronze">BRONZE</option>
        <option value="silver">SILVER</option>
        <option value="gold">GOLD</option>
      </select>
      <span class="text-xs font-mono text-[#64748b] ml-auto">Total: {{ total }} member</span>
    </div>

    <!-- Tabel -->
    <div class="bg-white border border-[#e2e8f0] rounded-[4px] overflow-hidden">
      <table class="w-full text-left border-collapse text-sm">
        <thead>
          <tr class="border-b border-[#e2e8f0] bg-[#f1f5f9] text-[#64748b] text-[11px] font-semibold uppercase tracking-[0.05em]">
            <th class="p-3">ID</th>
            <th class="p-3">Nama</th>
            <th class="p-3">No HP</th>
            <th class="p-3">Kota</th>
            <th class="p-3 text-center">Tier</th>
            <th class="p-3 text-right">Poin</th>
            <th class="p-3 text-right">Total Belanja</th>
            <th class="p-3 text-right">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in members" :key="m.id" class="border-b border-[#e2e8f0] hover:bg-[#f8fafc]">
            <td class="p-3 font-mono text-xs text-[#64748b]">{{ m.id_anggota || '-' }}</td>
            <td class="p-3 font-medium text-[#0f172a]">{{ m.name }}</td>
            <td class="p-3 font-mono text-xs">{{ m.phone }}</td>
            <td class="p-3 text-xs text-[#64748b]">{{ m.city || '-' }}</td>
            <td class="p-3 text-center">
              <span class="text-[10px] font-bold uppercase rounded-[2px] px-1.5 py-0.5" :class="tierClass(m.tier)">{{ m.tier || 'bronze' }}</span>
            </td>
            <td class="p-3 text-right font-mono">{{ m.points || 0 }}</td>
            <td class="p-3 text-right font-mono text-xs">Rp {{ Number(m.total_spent || 0).toLocaleString('id-ID') }}</td>
            <td class="p-3 text-right whitespace-nowrap">
              <button @click="openDetail(m)" class="text-[#10b981] hover:underline text-xs font-semibold">DETAIL</button>
              <button @click="openEdit(m)" class="ml-2 text-[#0f172a] hover:underline text-xs font-semibold">EDIT</button>
              <button @click="nonaktifkan(m)" class="ml-2 text-[#ef4444] hover:underline text-xs font-semibold">NONAKTIF</button>
            </td>
          </tr>
          <tr v-if="members.length === 0">
            <td colspan="8" class="p-6 text-center text-[#64748b] text-sm">Belum ada member.</td>
          </tr>
        </tbody>
      </table>
      <div class="flex justify-between items-center px-3 py-2 text-xs text-[#64748b] border-t border-[#e2e8f0]">
        <span>Halaman {{ page }} dari {{ lastPage }}</span>
        <div class="flex gap-2">
          <button :disabled="page <= 1" @click="page--; fetchMembers()" class="px-2 py-1 border border-[#e2e8f0] rounded-[2px] disabled:opacity-40">PREV</button>
          <button :disabled="page >= lastPage" @click="page++; fetchMembers()" class="px-2 py-1 border border-[#e2e8f0] rounded-[2px] disabled:opacity-40">NEXT</button>
        </div>
      </div>
    </div>

    <!-- Modal Create / Edit -->
    <Teleport to="body">
      <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-[4px] shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
          <h3 class="text-sm font-bold text-[#0f172a] uppercase mb-4">{{ editing ? 'EDIT MEMBER' : 'MEMBER BARU' }}</h3>
          <div class="space-y-3">
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Nama *</label>
              <input v-model="form.name" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">No HP *</label>
                <input v-model="form.phone" :disabled="editing" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono disabled:bg-[#f8fafc]" />
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Kota *</label>
                <input v-model="form.city" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Gender *</label>
                <select v-model="form.gender" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm bg-white">
                  <option value="male">PRIA</option>
                  <option value="female">WANITA</option>
                  <option value="other">LAINNYA</option>
                </select>
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Email</label>
                <input v-model="form.email" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
              </div>
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-[#64748b] uppercase mb-1">Tanggal Lahir</label>
              <input v-model="form.date_of_birth" type="date" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
            </div>
          </div>
          <p v-if="err" class="text-xs text-[#ef4444] mt-3">{{ err }}</p>
          <div class="flex justify-end gap-2 mt-6">
            <button @click="showForm = false" class="px-4 py-2 border border-[#e2e8f0] rounded-[2px] text-xs font-semibold text-[#64748b]">BATAL</button>
            <button @click="save" class="px-4 py-2 bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] text-xs font-semibold">SIMPAN</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Modal Detail + Histori + Poin -->
    <Teleport to="body">
      <div v-if="detail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-[4px] shadow-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-sm font-bold text-[#0f172a] uppercase">DETAIL MEMBER</h3>
              <p class="text-xs text-[#64748b] font-mono">{{ detail.member.id_anggota }}</p>
            </div>
            <button @click="detail = null" class="text-[#64748b] hover:text-black font-bold">✕</button>
          </div>

          <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-[#f8fafc] border border-[#e2e8f0] rounded-[2px] p-3">
              <p class="text-[10px] uppercase text-[#64748b] font-semibold mb-1">Tier</p>
              <p class="text-sm font-bold uppercase text-[#0f172a]">{{ detail.member.tier }}</p>
            </div>
            <div class="bg-[#f8fafc] border border-[#e2e8f0] rounded-[2px] p-3">
              <p class="text-[10px] uppercase text-[#64748b] font-semibold mb-1">Poin</p>
              <p class="text-sm font-bold font-mono text-[#10b981]">{{ detail.member.points || 0 }}</p>
            </div>
            <div class="bg-[#f8fafc] border border-[#e2e8f0] rounded-[2px] p-3">
              <p class="text-[10px] uppercase text-[#64748b] font-semibold mb-1">Total Belanja</p>
              <p class="text-sm font-bold font-mono">Rp {{ Number(detail.member.total_spent || 0).toLocaleString('id-ID') }}</p>
            </div>
          </div>

          <!-- Poin manual -->
          <div class="bg-white border border-[#e2e8f0] rounded-[2px] p-3 mb-4">
            <p class="text-[10px] uppercase text-[#64748b] font-semibold mb-2">Penyesuaian Poin Manual</p>
            <div class="flex gap-2">
              <select v-model="poinForm.jenis" class="border border-[#e2e8f0] rounded-[2px] px-2 py-1.5 text-xs font-mono bg-white">
                <option value="EARN">TAMBAH</option>
                <option value="REDEEM">KURANGI</option>
              </select>
              <input v-model.number="poinForm.poin" type="number" min="1" class="border border-[#e2e8f0] rounded-[2px] px-2 py-1.5 text-xs font-mono w-24" />
              <input v-model="poinForm.catatan" placeholder="Catatan (opsional)" class="flex-1 border border-[#e2e8f0] rounded-[2px] px-2 py-1.5 text-xs" />
              <button @click="applyPoin" class="px-3 py-1.5 bg-[#0f172a] hover:bg-[#1e293b] text-white rounded-[2px] text-xs font-semibold">TERAPKAN</button>
            </div>
            <p v-if="poinErr" class="text-xs text-[#ef4444] mt-2">{{ poinErr }}</p>
          </div>

          <!-- Histori -->
          <p class="text-[10px] uppercase text-[#64748b] font-semibold mb-2">Histori Transaksi Poin</p>
          <div class="border border-[#e2e8f0] rounded-[2px] overflow-hidden">
            <table class="w-full text-left text-xs">
              <thead>
                <tr class="bg-[#f1f5f9] text-[#64748b] uppercase font-semibold">
                  <th class="p-2">Tanggal</th>
                  <th class="p-2 text-right">Masuk</th>
                  <th class="p-2 text-right">Keluar</th>
                  <th class="p-2 text-right">Saldo</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(r, i) in detail.riwayat" :key="i" class="border-t border-[#e2e8f0]">
                  <td class="p-2 font-mono">{{ fmtDate(r.created_at) }}</td>
                  <td class="p-2 text-right font-mono text-[#10b981]">{{ r.poin_diperoleh || '-' }}</td>
                  <td class="p-2 text-right font-mono text-[#ef4444]">{{ r.poin_digunakan || '-' }}</td>
                  <td class="p-2 text-right font-mono">{{ r.saldo_poin }}</td>
                </tr>
                <tr v-if="!detail.riwayat || detail.riwayat.length === 0">
                  <td colspan="4" class="p-4 text-center text-[#64748b]">Belum ada histori poin.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  name: 'MemberPanel',
  data() {
    return {
      members: [], total: 0, page: 1, lastPage: 1,
      cari: '', tier: '',
      showForm: false, editing: false, form: {}, err: '',
      detail: null, poinForm: { jenis: 'EARN', poin: 1, catatan: '' }, poinErr: '',
    };
  },
  mounted() { this.fetchMembers(); },
  methods: {
    headers() { return { Authorization: `Bearer ${localStorage.token || ''}` }; },
    tierClass(t) {
      return { bronze: 'bg-[#f1f5f9] text-[#64748b]', silver: 'bg-[#e2e8f0] text-[#334155]', gold: 'bg-[#fef3c7] text-[#92400e]' }[t || 'bronze'];
    },
    fmtDate(d) { return d ? String(d).slice(0, 10) : '-'; },
    async fetchMembers() {
      try {
        const res = await axios.get('/api/members', {
          headers: this.headers(),
          params: { cari: this.cari || undefined, tier: this.tier || undefined, page: this.page },
        });
        this.members = res.data.data || [];
        this.total = res.data.total || 0;
        this.lastPage = res.data.last_page || 1;
      } catch (e) { console.error(e); }
    },
    openCreate() {
      this.editing = false; this.err = '';
      this.form = { name: '', phone: '', city: '', gender: 'male', email: '', date_of_birth: '' };
      this.showForm = true;
    },
    openEdit(m) {
      this.editing = true; this.err = '';
      this.form = { ...m };
      this.showForm = true;
    },
    async save() {
      this.err = '';
      try {
        if (this.editing) {
          await axios.put(`/api/members/${this.form.id_anggota || this.form.phone}`, this.form, { headers: this.headers() });
        } else {
          await axios.post('/api/members', this.form, { headers: this.headers() });
        }
        this.showForm = false;
        this.fetchMembers();
      } catch (e) {
        const errs = e.response?.data?.errors;
        this.err = errs ? Object.values(errs).flat().join(' ') : (e.response?.data?.message || 'Gagal simpan.');
      }
    },
    async nonaktifkan(m) {
      if (!confirm(`Nonaktifkan member ${m.name}? Data histori tetap tersimpan.`)) return;
      await axios.delete(`/api/members/${m.id_anggota || m.phone}`, { headers: this.headers() });
      this.fetchMembers();
    },
    async openDetail(m) {
      const res = await axios.get(`/api/members/${m.id_anggota || m.phone}`, { headers: this.headers() });
      this.detail = res.data;
      this.poinForm = { jenis: 'EARN', poin: 1, catatan: '' };
      this.poinErr = '';
    },
    async applyPoin() {
      this.poinErr = '';
      try {
        await axios.post(`/api/members/${this.detail.member.id_anggota}/points`, this.poinForm, { headers: this.headers() });
        this.openDetail(this.detail.member);
        this.fetchMembers();
      } catch (e) {
        this.poinErr = e.response?.data?.message || 'Gagal ubah poin.';
      }
    },
  },
};
</script>