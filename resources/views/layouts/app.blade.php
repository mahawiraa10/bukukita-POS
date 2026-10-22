<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'BukuKita POS')</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #0f172a; -webkit-font-smoothing: antialiased; }
    .mono { font-family: 'JetBrains Mono', monospace; }
    input, select, textarea { transition: border-color .15s ease; }
    input:focus, select:focus, textarea:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.12); }
    .dropdown-menu { display: none; }
    .dropdown.open .dropdown-menu { display: block; }
    .dropdown button { cursor: pointer; }
  </style>
  @stack('styles')
</head>
<body class="min-h-screen flex flex-col">

  <!-- Top Navbar -->
  <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-6 h-14 flex items-center justify-between">
      <div class="flex items-center gap-6">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 rounded-sm bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">BK</div>
          <span class="font-bold tracking-tight text-slate-900">BukuKita</span>
        </div>
        <nav class="hidden md:flex items-center gap-1 text-sm font-semibold text-slate-700">
          <a href="/dashboard" class="px-3 py-2 rounded border border-transparent hover:border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 {{ request()->is('dashboard*') ? 'bg-slate-100 text-slate-900 border-slate-200' : '' }}">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            DASHBOARD
          </a>
          
          <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>
          
          <a href="/master/hierarki" class="px-3 py-2 rounded border border-transparent hover:border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 {{ request()->is('master/hierarki*') ? 'bg-slate-100 text-slate-900 border-slate-200' : '' }}">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
            HIERARKI
          </a>
          
          <a href="/master/vendor" class="px-3 py-2 rounded border border-transparent hover:border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 {{ request()->is('master/vendor*') ? 'bg-slate-100 text-slate-900 border-slate-200' : '' }}">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            VENDOR
          </a>
          
          <a href="/master/produk" class="px-3 py-2 rounded border border-transparent hover:border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 {{ request()->is('master/produk*') ? 'bg-slate-100 text-slate-900 border-slate-200' : '' }}">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            PRODUK
          </a>

          <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>

          <a href="/operational/po" class="px-3 py-2 rounded border border-transparent hover:border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 {{ request()->is('operational/po*') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}">
            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
            PO
          </a>
          
          <a href="/operational/receiving" class="px-3 py-2 rounded border border-transparent hover:border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 {{ request()->is('operational/receiving*') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}">
            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            TERIMA
          </a>

          <a href="/operational/gudang" class="px-3 py-2 rounded border border-transparent hover:border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 {{ request()->is('operational/gudang*') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}">
            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            GUDANG
          </a>

          <a href="/operational/events" class="px-3 py-2 rounded border border-transparent hover:border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 {{ request()->is('operational/events*') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            HISTORI EVENT
          </a>
          <a href="/operational/event-manage" class="px-3 py-2 rounded border border-transparent hover:border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 {{ request()->is('operational/event-manage*') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            KELOLA EVENT
          </a>

          <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>

          <a href="/pos" class="px-3 py-2 rounded bg-emerald-500 text-white hover:bg-emerald-600 flex items-center gap-1.5 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            KASIR
          </a>

          <a href="/reports" class="px-3 py-2 rounded border border-transparent hover:border-slate-200 hover:bg-slate-50 flex items-center gap-1.5 {{ request()->is('reports*') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            LAPORAN
          </a>
        </nav>
      </div>
      <div class="flex items-center gap-4 text-xs">
        <span id="userBadge" class="mono text-slate-500">Memuat...</span>
        <button onclick="logout()" class="px-2.5 py-1.5 border border-slate-200 rounded-sm hover:bg-slate-50 text-slate-700">Keluar</button>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="flex-1 max-w-7xl w-full mx-auto p-6">
    @yield('content')
  </main>

  <footer class="border-t border-slate-200 py-4 text-center text-xs mono text-slate-400">
    PT. Buku Kita · Sistem POS & ERP v2 · UNINDRA KP 2026
  </footer>

  <script>
    const token = localStorage.getItem('token');
    if(!token && location.pathname !== '/login') {
      location.href = '/login';
    }
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if(user.name) {
      const cleanName = user.name.replace(/\s*\([^)]*\)/g, '').trim();
      const roleName = (user.role?.name || 'STAFF').toUpperCase();
      document.getElementById('userBadge').textContent = `${cleanName} (${roleName})`;
    }

    // Role-based redirect & navbar hiding on page load
    const role = (user.role?.name || '').toLowerCase();
    const pathname = location.pathname;

    // Redirects
    if (role === 'admin' && pathname === '/dashboard') {
      location.href = '/operational/po';
    } else if ((role === 'admin' || role === 'kasir') && pathname.startsWith('/operational/events')) {
      // Histori event khusus supervisor & owner
      location.href = role === 'kasir' ? '/pos' : '/operational/po';
    } else if (role === 'kasir' && pathname !== '/pos') {
      location.href = '/pos';
    }

    // Navbar hiding & link removal
    if (role === 'admin') {
      // Admin tidak boleh akses Dashboard, Laporan, Kasir, dan Histori Event
      document.querySelectorAll('nav a[href="/dashboard"], nav a[href="/pos"], nav a[href="/operational/events"], nav .dropdown').forEach(el => el.style.display = 'none');
    } else if (role === 'kasir') {
      document.querySelectorAll('nav a:not([href="/pos"])').forEach(el => {
        if (!el.closest('.dropdown')) el.style.display = 'none';
      });
      document.querySelectorAll('nav .dropdown').forEach(el => el.style.display = 'none');
    }

    // Dropdown toggle (click, bukan hover — lebih stabil)
    document.querySelectorAll('.dropdown > button').forEach(btn => {
      btn.addEventListener('click', e => {
        const dd = btn.parentElement;
        document.querySelectorAll('.dropdown.open').forEach(o => { if(o !== dd) o.classList.remove('open'); });
        dd.classList.toggle('open');
        e.stopPropagation();
      });
    });
    document.addEventListener('click', () => document.querySelectorAll('.dropdown.open').forEach(d => d.classList.remove('open')));

    async function logout() {
      try {
        await fetch('/api/logout', { method: 'POST', headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' } });
      } catch(e) {}
      localStorage.clear();
      location.href = '/login';
    }
  </script>
  @stack('scripts')
  @vite(['resources/js/app.js'])
</body>
</html>