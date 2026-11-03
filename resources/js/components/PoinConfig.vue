<template>
  <div class="bg-white border border-[#e2e8f0] rounded-[4px] p-6 max-w-lg">
    <h3 class="text-sm font-bold text-[#0f172a] uppercase mb-4">SETUP KONVERSI POIN & TIER</h3>
    <div class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Setiap Belanja (Rp)</label>
          <input v-model.number="form.nilai_transaksi" type="number" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
        </div>
        <div>
          <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Dapat Poin</label>
          <input v-model.number="form.nilai_poin" type="number" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Min. Belanja Silver (Rp)</label>
          <input v-model.number="form.tier_silver_min" type="number" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
        </div>
        <div>
          <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Diskon Silver (%)</label>
          <input v-model.number="form.diskon_silver" type="number" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Min. Belanja Gold (Rp)</label>
          <input v-model.number="form.tier_gold_min" type="number" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
        </div>
        <div>
          <label class="block text-[10px] font-semibold text-[#64748b] uppercase mb-1">Diskon Gold (%)</label>
          <input v-model.number="form.diskon_gold" type="number" class="w-full border border-[#e2e8f0] rounded-[2px] p-2 text-sm font-mono" />
        </div>
      </div>
      <button @click="save" class="w-full bg-[#10b981] hover:bg-[#059669] text-white rounded-[2px] py-2 text-sm font-semibold transition-colors">SIMPAN KONFIGURASI</button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() { return { form: { nilai_transaksi: 10000, nilai_poin: 1, tier_silver_min: 500000, tier_gold_min: 2000000, diskon_silver: 5, diskon_gold: 10 } } },
  mounted() { this.fetch(); },
  methods: {
    async fetch() {
      const res = await axios.get('/api/loyalty-rules', { headers: { Authorization: `Bearer ${localStorage.token || ''}` } });
      if (res.data) this.form = res.data;
    },
    async save() {
      await axios.put('/api/loyalty-rules', this.form, { headers: { Authorization: `Bearer ${localStorage.token || ''}` } });
      alert('Poin tersetel.');
    }
  }
}
</script>