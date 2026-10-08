@extends('layouts.app')
@section('title', 'Hierarki Departemen — BukuKita')
@section('content')
<div class="flex justify-between items-center">
  <div>
    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Hierarki Departemen</h1>
    <p class="text-xs text-slate-500">Pilih item di kolom kiri untuk memfilter kategori di kolom kanan. Gunakan (+) untuk menambah data.</p>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
  <!-- 1. Divisi -->
  <div class="bg-white border border-slate-200 rounded-sm flex flex-col h-[600px]">
    <div class="p-3 border-b border-slate-100 flex justify-between items-center bg-slate-50">
      <span class="text-[10px] font-bold uppercase tracking-widest text-slate-600">1. Divisi</span>
      <button onclick="openAddForm('divisions')" class="px-2 py-0.5 bg-emerald-500 text-white rounded text-xs font-bold hover:bg-emerald-600">+ Tambah</button>
    </div>
    <div class="flex-1 overflow-y-auto p-2 space-y-1" id="divList">
      <p class="text-xs text-slate-400 p-2 italic">Memuat...</p>
    </div>
  </div>

  <!-- 2. Departemen -->
  <div class="bg-white border border-slate-200 rounded-sm flex flex-col h-[600px]">
    <div class="p-3 border-b border-slate-100 flex justify-between items-center bg-slate-50">
      <span class="text-[10px] font-bold uppercase tracking-widest text-slate-600">2. Departemen</span>
      <button id="btnAddDept" onclick="openAddForm('departments')" class="px-2 py-0.5 bg-emerald-500 text-white rounded text-xs font-bold hover:bg-emerald-600 hidden">+ Tambah</button>
    </div>
    <div class="flex-1 overflow-y-auto p-2 space-y-1" id="deptList">
      <p class="text-xs text-slate-400 p-2 italic">Pilih divisi dulu</p>
    </div>
  </div>

  <!-- 3. Kelas -->
  <div class="bg-white border border-slate-200 rounded-sm flex flex-col h-[600px]">
    <div class="p-3 border-b border-slate-100 flex justify-between items-center bg-slate-50">
      <span class="text-[10px] font-bold uppercase tracking-widest text-slate-600">3. Kelas</span>
      <button id="btnAddClass" onclick="openAddForm('classes')" class="px-2 py-0.5 bg-emerald-500 text-white rounded text-xs font-bold hover:bg-emerald-600 hidden">+ Tambah</button>
    </div>
    <div class="flex-1 overflow-y-auto p-2 space-y-1" id="classList">
      <p class="text-xs text-slate-400 p-2 italic">Pilih departemen dulu</p>
    </div>
  </div>

  <!-- 4. SubKelas -->
  <div class="bg-white border border-slate-200 rounded-sm flex flex-col h-[600px]">
    <div class="p-3 border-b border-slate-100 flex justify-between items-center bg-slate-50">
      <span class="text-[10px] font-bold uppercase tracking-widest text-slate-600">4. SubKelas</span>
      <button id="btnAddSub" onclick="openAddForm('subclasses')" class="px-2 py-0.5 bg-emerald-500 text-white rounded text-xs font-bold hover:bg-emerald-600 hidden">+ Tambah</button>
    </div>
    <div class="flex-1 overflow-y-auto p-2 space-y-1" id="subList">
      <p class="text-xs text-slate-400 p-2 italic">Pilih kelas dulu</p>
    </div>
  </div>
</div>
@endsection

<!-- Modal Form (Noirish Design) -->
<div id="modalForm" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
  <div class="bg-white border border-slate-200 rounded-sm shadow-2xl w-full max-w-sm overflow-hidden">
    <div class="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
      <h3 id="modalTitle" class="text-sm font-bold text-slate-800 uppercase tracking-tight">Tambah Data</h3>
      <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">✕</button>
    </div>
    <form id="activeForm" onsubmit="handleFormSubmit(event)" class="p-4 space-y-4">
      <input type="hidden" id="formKey">
      <input type="hidden" id="formId">
      <input type="hidden" id="formAction">
      
      <div>
        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Nama Item</label>
        <input type="text" id="formInputName" required autofocus
          class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-sm focus:border-emerald-500 transition-all">
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 rounded-sm">Batal</button>
        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-500 hover:bg-emerald-600 rounded-sm shadow-sm">Simpan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
let selDiv = null, selDept = null, selClass = null;
const H = { Authorization: `Bearer ${token}`, 'Accept': 'application/json', 'Content-Type': 'application/json' };

async function fetchAPI(endpoint, method = 'GET', body = null) {
  const opt = { method, headers: H };
  if (body) opt.body = JSON.stringify(body);
  const r = await fetch(`/api/${endpoint}`, opt);
  return r.json();
}

async function loadDivisions() {
  const res = await fetchAPI('divisions');
  const list = res.data || [];
  const el = document.getElementById('divList');
  if (!list.length) { el.innerHTML = '<p class="text-xs text-slate-400 p-2 italic">Belum ada data divisi.</p>'; return; }
  el.innerHTML = list.map(d => `
    <div onclick="selectDiv('${d.id_divisi}')" class="group flex items-center justify-between px-3 py-2 text-xs rounded-sm cursor-pointer border ${selDiv === d.id_divisi ? 'bg-emerald-50 border-emerald-200 text-emerald-900 font-semibold' : 'hover:bg-slate-50 border-transparent text-slate-700'}">
      <span id="div-name-${d.id_divisi}">${d.nama_divisi} <span class="text-[10px] text-slate-400 mono">(${d.id_divisi})</span></span>
      <div class="space-x-1 opacity-0 group-hover:opacity-100">
        <button onclick="event.stopPropagation(); openEditForm('divisions', '${d.id_divisi}', '${d.nama_divisi}')" class="text-blue-600 hover:underline">Edit</button>
        <button onclick="event.stopPropagation(); deleteItem('divisions', '${d.id_divisi}')" class="text-red-600 hover:underline">Hapus</button>
      </div>
    </div>
  `).join('');
}

async function selectDiv(id) {
  selDiv = id; selDept = null; selClass = null;
  document.getElementById('btnAddDept').classList.remove('hidden');
  document.getElementById('btnAddClass').classList.add('hidden');
  document.getElementById('btnAddSub').classList.add('hidden');
  document.getElementById('classList').innerHTML = '<p class="text-xs text-slate-400 p-2 italic">Pilih departemen dulu</p>';
  document.getElementById('subList').innerHTML = '<p class="text-xs text-slate-400 p-2 italic">Pilih kelas dulu</p>';
  loadDivisions();
  
  const res = await fetchAPI('departments');
  const all = res.data || [];
  const list = all.filter(x => x.id_divisi === id);
  const el = document.getElementById('deptList');
  if (!list.length) { el.innerHTML = '<p class="text-xs text-slate-400 p-2 italic">Belum ada departemen.</p>'; return; }
  el.innerHTML = list.map(d => `
    <div onclick="selectDept('${d.id_departemen}')" class="group flex items-center justify-between px-3 py-2 text-xs rounded-sm cursor-pointer border ${selDept === d.id_departemen ? 'bg-emerald-50 border-emerald-200 text-emerald-900 font-semibold' : 'hover:bg-slate-50 border-transparent text-slate-700'}">
      <span>${d.nama_departemen} <span class="text-[10px] text-slate-400 mono">(${d.id_departemen})</span></span>
      <div class="space-x-1 opacity-0 group-hover:opacity-100">
        <button onclick="event.stopPropagation(); openEditForm('departments', '${d.id_departemen}', '${d.nama_departemen}')" class="text-blue-600 hover:underline">Edit</button>
        <button onclick="event.stopPropagation(); deleteItem('departments', '${d.id_departemen}')" class="text-red-600 hover:underline">Hapus</button>
      </div>
    </div>
  `).join('');
}

async function selectDept(id) {
  selDept = id; selClass = null;
  document.getElementById('btnAddClass').classList.remove('hidden');
  document.getElementById('btnAddSub').classList.add('hidden');
  document.getElementById('subList').innerHTML = '<p class="text-xs text-slate-400 p-2 italic">Pilih kelas dulu</p>';
  selectDiv(selDiv);
  
  const res = await fetchAPI('classes');
  const all = res.data || [];
  const list = all.filter(x => x.id_departemen === id);
  const el = document.getElementById('classList');
  if (!list.length) { el.innerHTML = '<p class="text-xs text-slate-400 p-2 italic">Belum ada kelas.</p>'; return; }
  el.innerHTML = list.map(d => `
    <div onclick="selectClass('${d.id_kelas}')" class="group flex items-center justify-between px-3 py-2 text-xs rounded-sm cursor-pointer border ${selClass === d.id_kelas ? 'bg-emerald-50 border-emerald-200 text-emerald-900 font-semibold' : 'hover:bg-slate-50 border-transparent text-slate-700'}">
      <span>${d.nama_kelas} <span class="text-[10px] text-slate-400 mono">(${d.id_kelas})</span></span>
      <div class="space-x-1 opacity-0 group-hover:opacity-100">
        <button onclick="event.stopPropagation(); openEditForm('classes', '${d.id_kelas}', '${d.nama_kelas}')" class="text-blue-600 hover:underline">Edit</button>
        <button onclick="event.stopPropagation(); deleteItem('classes', '${d.id_kelas}')" class="text-red-600 hover:underline">Hapus</button>
      </div>
    </div>
  `).join('');
}

async function selectClass(id) {
  selClass = id;
  document.getElementById('btnAddSub').classList.remove('hidden');
  selectDept(selDept);
  
  const res = await fetchAPI('subclasses');
  const all = res.data || [];
  const list = all.filter(x => x.id_kelas === id);
  const el = document.getElementById('subList');
  if (!list.length) { el.innerHTML = '<p class="text-xs text-slate-400 p-2 italic">Belum ada subkelas.</p>'; return; }
  el.innerHTML = list.map(d => `
    <div class="group flex items-center justify-between px-3 py-2 text-xs rounded-sm border hover:bg-slate-50 border-transparent text-slate-700">
      <span>${d.nama_subkelas} <span class="text-[10px] text-slate-400 mono">(${d.id_subkelas})</span></span>
      <div class="space-x-1 opacity-0 group-hover:opacity-100">
        <button onclick="openEditForm('subclasses', '${d.id_subkelas}', '${d.nama_subkelas}')" class="text-blue-600 hover:underline">Edit</button>
        <button onclick="deleteItem('subclasses', '${d.id_subkelas}')" class="text-red-600 hover:underline">Hapus</button>
      </div>
    </div>
  `).join('');
}

// Modal Handlers
function openAddForm(key) {
  document.getElementById('modalTitle').textContent = `Tambah ${key.slice(0, -1)}`;
  document.getElementById('formKey').value = key;
  document.getElementById('formAction').value = 'ADD';
  document.getElementById('formInputName').value = '';
  document.getElementById('modalForm').classList.remove('hidden');
  document.getElementById('formInputName').focus();
}

function openEditForm(key, id, oldName) {
  document.getElementById('modalTitle').textContent = `Ubah ${key.slice(0, -1)}`;
  document.getElementById('formKey').value = key;
  document.getElementById('formId').value = id;
  document.getElementById('formAction').value = 'EDIT';
  document.getElementById('formInputName').value = oldName;
  document.getElementById('modalForm').classList.remove('hidden');
  document.getElementById('formInputName').focus();
}

function closeModal() {
  document.getElementById('modalForm').classList.add('hidden');
}

async function handleFormSubmit(e) {
  e.preventDefault();
  const key = document.getElementById('formKey').value;
  const action = document.getElementById('formAction').value;
  const name = document.getElementById('formInputName').value;
  
  let body = {};
  if (key === 'divisions') body = { nama_divisi: name };
  if (key === 'departments') body = { nama_departemen: name, id_divisi: selDiv };
  if (key === 'classes') body = { nama_kelas: name, id_departemen: selDept };
  if (key === 'subclasses') body = { nama_subkelas: name, id_kelas: selClass };
  
  if (action === 'ADD') {
    await fetchAPI(key, 'POST', body);
  } else {
    const id = document.getElementById('formId').value;
    await fetchAPI(`${key}/${id}`, 'PUT', body);
  }
  
  closeModal();
  refreshCurrent(key);
}

async function deleteItem(key, id) {
  if (!confirm('Hapus data ini?')) return;
  const res = await fetchAPI(`${key}/${id}`, 'DELETE');
  if (res.message && res.message.includes('Ditolak')) {
    alert(res.message);
  } else {
    refreshCurrent(key);
  }
}

function refreshCurrent(key) {
  if (key === 'divisions') loadDivisions();
  if (key === 'departments') selectDiv(selDiv);
  if (key === 'classes') selectDept(selDept);
  if (key === 'subclasses') selectClass(selClass);
}

loadDivisions();
</script>
@endpush
