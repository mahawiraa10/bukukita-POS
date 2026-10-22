@extends('layouts.app')

@section('title', 'User Management — BukuKita')

@section('content')
<!-- Custom Toast -->
<div id="status-toast" class="hidden fixed bottom-5 right-5 bg-slate-900 text-white px-6 py-3 rounded-sm shadow-xl z-[9999] text-xs font-bold uppercase tracking-wider">
  <span id="status-message"></span>
</div>

<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-lg font-bold text-slate-900 tracking-tight">User Management</h1>
      <p class="text-xs text-slate-500">Kelola pengguna sistem dan hak akses mereka.</p>
    </div>
    <button onclick="openCreateModal()" class="bg-emerald-500 hover:bg-emerald-600 text-white rounded-sm px-3 py-1.5 text-xs font-semibold uppercase tracking-wider">
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
        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400 mono italic">Memuat data...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Create/Edit -->
<div id="user-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm">
  <div class="bg-white border border-slate-200 rounded-sm p-6 w-96 shadow-2xl">
    <h3 id="modal-title" class="text-base font-bold text-slate-900 mb-4">Tambah User</h3>
    <form id="user-form" onsubmit="saveUser(event)">
      <input type="hidden" id="user-id">
      <div class="space-y-3">
        <div>
          <label class="text-xs font-semibold text-slate-600 uppercase">Nama Lengkap</label>
          <input type="text" id="input-name" required class="w-full border border-slate-200 rounded-sm px-3 py-2 text-sm mt-1 focus:border-emerald-500 focus:outline-none">
        </div>
        <div>
          <label class="text-xs font-semibold text-slate-600 uppercase">Email</label>
          <input type="email" id="input-email" required class="w-full border border-slate-200 rounded-sm px-3 py-2 text-sm mt-1 focus:border-emerald-500 focus:outline-none">
        </div>
        <div>
          <label class="text-xs font-semibold text-slate-600 uppercase">Password</label>
          <input type="password" id="input-password" class="w-full border border-slate-200 rounded-sm px-3 py-2 text-sm mt-1 focus:border-emerald-500 focus:outline-none">
          <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika tidak diubah</p>
        </div>
        <div>
          <label class="text-xs font-semibold text-slate-600 uppercase">Role</label>
          <select id="input-role" required class="w-full border border-slate-200 rounded-sm px-3 py-2 text-sm mt-1 focus:border-emerald-500 focus:outline-none">
            <option value="">-- Pilih Role --</option>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <input type="checkbox" id="input-active" checked class="rounded border-slate-300">
          <label class="text-sm text-slate-700">Aktif</label>
        </div>
      </div>
      <div class="mt-5 flex gap-3">
        <button type="button" onclick="closeModal()" class="flex-1 border border-slate-200 hover:bg-slate-50 text-slate-600 py-2 rounded-sm text-sm">Batal</button>
        <button type="submit" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-sm text-sm font-semibold">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
let roles = [];

async function loadUsers() {
  try {
    const r = await fetch('/api/users', { headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' } });
    const data = await r.json();
    const users = data.data || data;
    renderUsers(users);
  } catch(e) { console.error(e); }
}

async function loadRoles() {
  try {
    const r = await fetch('/api/roles', { headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' } });
    const data = await r.json();
    roles = data.data || data;
    const sel = document.getElementById('input-role');
    sel.innerHTML = '<option value="">-- Pilih Role --</option>' + roles.map(r => `<option value="${r.id}">${r.name}</option>`).join('');
  } catch(e) {}
}

function renderUsers(users) {
  const tbody = document.getElementById('userTable');
  if (!users.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada user</td></tr>';
    return;
  }
  tbody.innerHTML = users.map(u => `
    <tr class="border-b border-slate-100 hover:bg-slate-50/50">
      <td class="px-4 py-3 font-medium text-slate-900">${u.name}</td>
      <td class="px-4 py-3 text-slate-500 mono text-xs">${u.email}</td>
      <td class="px-4 py-3">
        <span class="px-2 py-0.5 rounded-sm bg-slate-100 border border-slate-200 text-[10px] font-bold uppercase text-slate-600 tracking-tight">${u.role?.name || '-'}</span>
      </td>
      <td class="px-4 py-3 text-center">
        <span class="w-2 h-2 inline-block rounded-full ${u.is_active ? 'bg-emerald-500' : 'bg-slate-300'}"></span>
      </td>
      <td class="px-4 py-3 text-right">
        <button onclick="openEditModal(${u.id})" class="text-emerald-600 hover:text-emerald-700 text-xs font-semibold mr-3" title="Edit">
            <svg class="w-3.5 h-3.5 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit
          </button>
        
        <div class="relative delete-dropdown">
          <button onclick="openDeleteConfirm(${u.id}, '${u.name.replace(/'/g, "\'")}')" class="text-rose-500 hover:text-rose-600 text-xs font-semibold flex items-center gap-1" type="button">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Hapus
          </button>
          <div id="delete-confirm-${u.id}" class="delete-confirm hidden absolute right-0 top-full mt-1 z-[60] bg-white border border-slate-200 rounded-sm shadow-xl p-4 w-64">
            <p class="text-xs font-bold text-slate-900 mb-1">Konfirmasi Hapus</p>
            <p class="text-[10px] text-slate-500 mb-3 leading-tight">Yakin hapus akun <b>${u.name}</b> secara permanen?</p>
            <div class="flex gap-2">
              <button onclick="doDelete(${u.id})" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold py-1.5 rounded-sm uppercase tracking-wider">Ya, Hapus</button>
              <button onclick="closeDeleteConfirm(${u.id})" class="flex-1 border border-slate-200 text-slate-600 hover:bg-slate-50 text-[10px] font-bold py-1.5 rounded-sm uppercase tracking-wider">Batal</button>
            </div>
          </div>
        </div>
      </td>
    </tr>
  `).join('');
}

function openCreateModal() {
  document.getElementById('modal-title').textContent = 'Tambah User';
  document.getElementById('user-form').reset();
  document.getElementById('user-id').value = '';
  document.getElementById('input-active').checked = true;
  document.getElementById('user-modal').classList.remove('hidden');
  document.getElementById('input-name').focus();
}

function openEditModal(userId) {
  fetch(`/api/users/${userId}`, { headers: { 'Authorization': `Bearer ${token}` } })
    .then(r => r.json())
    .then(u => {
      document.getElementById('modal-title').textContent = 'Edit User';
      document.getElementById('user-id').value = u.id;
      document.getElementById('input-name').value = u.name;
      document.getElementById('input-email').value = u.email;
      document.getElementById('input-password').value = '';
      document.getElementById('input-role').value = u.role?.id || '';
      document.getElementById('input-active').checked = u.is_active;
      document.getElementById('user-modal').classList.remove('hidden');
    });
}

function closeModal() {
  document.getElementById('user-modal').classList.add('hidden');
}

async function saveUser(e) {
  e.preventDefault();
  const id = document.getElementById('user-id').value;
  const pwInput = document.getElementById('input-password');
  const pwValue = pwInput.value.trim();
  const data = {
    name: document.getElementById('input-name').value,
    email: document.getElementById('input-email').value,
    role_id: document.getElementById('input-role').value,
    is_active: document.getElementById('input-active').checked
  };
  // Kirim password hanya kalau ada yang diisi (minimal 1 karakter)
  if (pwValue) { data.password = pwValue; }

  try {
    const url = id ? `/api/users/${id}` : '/api/users';
    const method = id ? 'PUT' : 'POST';
    const r = await fetch(url, {
      method,
      headers: { 
        'Authorization': `Bearer ${token}`, 
        'Content-Type': 'application/json',
        'Accept': 'application/json' 
      },
      body: JSON.stringify(data)
    });
    const result = await r.json();
    if (r.ok) {
      closeModal();
      loadUsers();
      showStatus(id ? 'User berhasil diupdate!' : 'User berhasil ditambahkan!');
    } else {
      showStatus(result.message || 'Gagal menyimpan');
    }
  } catch(e) {
    showStatus('Error: ' + e.message);
  }
}

// Hapus - pakai konfirmasi dari tombol onclick di tabel
async function deleteUser(userId, userName) {
  // Konfirmasi dilakukan dari HTML onclick, fungsi ini hanya eksekusi fetch
  try {
    const r = await fetch(`/api/users/${userId}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    const result = await r.json();
    if (r.ok) {
      loadUsers();
      // sukses, bisa ditambah notifikasi singkat
    } else {
      console.error(result.message || 'Gagal menghapus');
    }
  } catch(e) {
    console.error('Error:', e.message);
  }
}

// Init
loadUsers();
loadRoles();

// Custom toast notification (ganti alert browser)
function showStatus(msg) {
  const toast = document.getElementById('status-toast');
  if (!toast) return;
  document.getElementById('status-message').textContent = msg;
  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), 3000);
}

function openDeleteConfirm(userId, userName) {
  // Tutup semua dropdown confirm lain
  document.querySelectorAll('.delete-confirm').forEach(d => d.classList.add('hidden'));
  const el = document.getElementById('delete-confirm-' + userId);
  if (el) el.classList.toggle('hidden');
}

function closeDeleteConfirm(userId) {
  const el = document.getElementById('delete-confirm-' + userId);
  if (el) el.classList.add('hidden');
}

async function doDelete(userId) {
  closeDeleteConfirm(userId);
  try {
    const r = await fetch('/api/users/' + userId, {
      method: 'DELETE',
      headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
    });
    const result = await r.json();
    if (r.ok) {
      showStatus('Akun berhasil dihapus');
      loadUsers();
    } else {
      showStatus(result.message || 'Gagal menghapus');
    }
  } catch(e) {
    showStatus('Error: ' + e.message);
  }
}

// Tutup dropdown confirm saat klik di luar
document.addEventListener('click', function(e) {
  document.querySelectorAll('.delete-dropdown').forEach(dd => {
    if (!dd.contains(e.target)) {
      dd.querySelectorAll('.delete-confirm').forEach(c => c.classList.add('hidden'));
    }
  });
});
</script>
@endpush