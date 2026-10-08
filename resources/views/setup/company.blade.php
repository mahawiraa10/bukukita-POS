@extends('layouts.app')

@section('title', 'Company Setup — BukuKita')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <div>
    <h1 class="text-lg font-bold text-slate-900 tracking-tight">Company Setup</h1>
    <p class="text-xs text-slate-500">Informasi profil toko yang tercetak pada struk kasir dan laporan.</p>
  </div>

  <div class="bg-white border border-slate-200 rounded-sm p-6">
    <form id="companyForm" class="space-y-4">
      <div>
        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-600 mb-1">Nama Perusahaan / Toko</label>
        <input type="text" id="nama_perusahaan" required class="w-full bg-white border border-slate-200 rounded-sm px-3 py-2 text-sm text-slate-900">
      </div>

      <div>
        <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-600 mb-1">Alamat Lengkap</label>
        <textarea id="alamat_perusahaan" rows="3" required class="w-full bg-white border border-slate-200 rounded-sm px-3 py-2 text-sm text-slate-900"></textarea>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-600 mb-1">Email</label>
          <input type="email" id="email" class="w-full bg-white border border-slate-200 rounded-sm px-3 py-2 text-sm mono text-slate-900">
        </div>
        <div>
          <label class="block text-[10px] font-semibold uppercase tracking-wider text-slate-600 mb-1">No. Telepon</label>
          <input type="text" id="nomor_telepon" class="w-full bg-white border border-slate-200 rounded-sm px-3 py-2 text-sm mono text-slate-900">
        </div>
      </div>

      <div id="alertMsg" class="hidden rounded-sm bg-emerald-50 border border-emerald-200 px-3 py-2 text-xs text-emerald-700"></div>

      <div class="flex justify-end pt-2">
        <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white rounded-sm px-4 py-2 text-xs font-semibold uppercase tracking-wider">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
async function loadCompany() {
  const r = await fetch('/api/company', { headers: { 'Authorization': `Bearer ${token}` } });
  const data = await r.json();
  const c = data.data || data;
  if(c && c.nama_perusahaan) {
    document.getElementById('nama_perusahaan').value = c.nama_perusahaan || '';
    document.getElementById('alamat_perusahaan').value = c.alamat_perusahaan || '';
    document.getElementById('email').value = c.email || '';
    document.getElementById('nomor_telepon').value = c.nomor_telepon || '';
  }
}
loadCompany();

document.getElementById('companyForm').onsubmit = async e => {
  e.preventDefault();
  const body = {
    nama_perusahaan: document.getElementById('nama_perusahaan').value,
    alamat_perusahaan: document.getElementById('alamat_perusahaan').value,
    email: document.getElementById('email').value,
    nomor_telepon: document.getElementById('nomor_telepon').value,
  };
  const r = await fetch('/api/company', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
    body: JSON.stringify(body)
  });
  if(r.ok) {
    const al = document.getElementById('alertMsg');
    al.textContent = 'Company setup berhasil disimpan.';
    al.classList.remove('hidden');
    setTimeout(() => al.classList.add('hidden'), 3000);
  }
};
</script>
@endpush