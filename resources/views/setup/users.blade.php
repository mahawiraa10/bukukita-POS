@extends('layouts.app')

@section('title', 'User Management — BukuKita')

@section('content')
<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">User Management</h1>
      <p class="text-xs text-slate-500">Kelola pengguna sistem dan hak akses mereka.</p>
    </div>
    <button class="bg-emerald-500 hover:bg-emerald-600 text-white rounded-sm px-3 py-1.5 text-xs font-semibold uppercase tracking-wider">
      Tambah User
    </button>
  </div>

  <div class="bg-white border border-slate-200 rounded-sm overflow-hidden">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200">
          <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">Nama</th>
          <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">Email</th>
          <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">Role</th>
          <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500 text-center">Status</th>
          <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody id="userTable" class="text-sm">
        <tr>
          <td colspan="5" class="px-4 py-8 text-center text-slate-400 mono italic">Memuat data pengguna...</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
async function loadUsers() {
  try {
    const r = await fetch('/api/users', { headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' } });
    const data = await r.json();
    const users = data.data || data;
    const tbody = document.getElementById('userTable');
    tbody.innerHTML = '';
    
    users.forEach(u => {
      tbody.innerHTML += `
        <tr class="border-b border-slate-100 hover:bg-slate-50/50">
          <td class="px-4 py-3 font-medium text-slate-900">${u.name}</td>
          <td class="px-4 py-3 text-slate-500 mono">${u.email}</td>
          <td class="px-4 py-3">
            <span class="px-2 py-0.5 rounded-sm bg-slate-100 border border-slate-200 text-[10px] font-bold uppercase text-slate-600 tracking-tight">
              ${u.role?.name || 'Staff'}
            </span>
          </td>
          <td class="px-4 py-3 text-center">
            <span class="w-2 h-2 inline-block rounded-full ${u.is_active ? 'bg-emerald-500' : 'bg-slate-300'}"></span>
          </td>
          <td class="px-4 py-3 text-right">
            <button class="text-slate-400 hover:text-emerald-600 text-xs font-semibold">Edit</button>
          </td>
        </tr>
      `;
    });
  } catch(e) {
    console.error(e);
  }
}
loadUsers();
</script>
@endpush