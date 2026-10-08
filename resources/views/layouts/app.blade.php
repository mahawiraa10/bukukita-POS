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
        <nav class="hidden md:flex items-center gap-1 text-xs font-medium text-slate-600">
          <a href="/dashboard" class="px-3 py-1.5 rounded-sm hover:bg-slate-100 {{ request()->is('dashboard*') ? 'bg-slate-100 text-slate-900 font-semibold' : '' }}">Dashboard</a>
          
          <!-- Dropdown Data Master -->
          <div class="relative dropdown">
            <button class="px-3 py-1.5 rounded-sm hover:bg-slate-100 flex items-center gap-1">
              Data Master ▾
            </button>
            <div class="dropdown-menu hidden absolute left-0 mt-1 w-48 bg-white border border-slate-200 rounded-sm shadow-lg py-1 z-40">
              <a href="/company-setup" class="block px-4 py-2 hover:bg-slate-50 text-slate-700">Company Setup</a>
              <a href="/users-management" class="block px-4 py-2 hover:bg-slate-50 text-slate-700">User & Role</a>
              <a href="#" class="block px-4 py-2 hover:bg-slate-50 text-slate-700">Produk & Katalog</a>
              <a href="#" class="block px-4 py-2 hover:bg-slate-50 text-slate-700">Vendor / Pemasok</a>
              <a href="/master/hierarki" class="block px-4 py-2 hover:bg-slate-50 text-slate-700">Divisi & Departemen</a>
              <a href="#" class="block px-4 py-2 hover:bg-slate-50 text-slate-700">Member & CRM</a>
            </div>
          </div>

          <!-- Dropdown Report -->
          <div class="relative dropdown">
            <button class="px-3 py-1.5 rounded-sm hover:bg-slate-100 flex items-center gap-1">
              Laporan ▾
            </button>
            <div class="dropdown-menu hidden absolute left-0 mt-1 w-56 bg-white border border-slate-200 rounded-sm shadow-lg py-1 z-40 max-h-96 overflow-y-auto">
              <div class="px-4 py-1 text-[10px] font-bold uppercase text-slate-400 tracking-wider">Pembelian & Stok</div>
              <a href="#" class="block px-4 py-1.5 hover:bg-slate-50 text-slate-700">1. Vendor Receiving</a>
              <a href="#" class="block px-4 py-1.5 hover:bg-slate-50 text-slate-700">2. Internal Receiving</a>
              <a href="#" class="block px-4 py-1.5 hover:bg-slate-50 text-slate-700">3. Vendor Return</a>
              <div class="border-t border-slate-100 my-1"></div>
              <div class="px-4 py-1 text-[10px] font-bold uppercase text-slate-400 tracking-wider">Penjualan & Omzet</div>
              <a href="#" class="block px-4 py-1.5 hover:bg-slate-50 text-slate-700">4. Receipts Recap</a>
              <a href="#" class="block px-4 py-1.5 hover:bg-slate-50 text-slate-700">5. Receipts Detail</a>
              <a href="#" class="block px-4 py-1.5 hover:bg-slate-50 text-slate-700">6. Omzet Produk</a>
              <a href="#" class="block px-4 py-1.5 hover:bg-slate-50 text-slate-700">7. Omzet Departemen</a>
              <a href="#" class="block px-4 py-1.5 hover:bg-slate-50 text-slate-700">8. Omzet Kasir</a>
              <div class="border-t border-slate-100 my-1"></div>
              <div class="px-4 py-1 text-[10px] font-bold uppercase text-slate-400 tracking-wider">Member & Closing</div>
              <a href="#" class="block px-4 py-1.5 hover:bg-slate-50 text-slate-700">9. New Loyalty Member</a>
              <a href="#" class="block px-4 py-1.5 hover:bg-slate-50 text-slate-700">10. Member Riwayat</a>
              <a href="#" class="block px-4 py-1.5 hover:bg-slate-50 text-slate-700">11. Till & Shift Closing</a>
              <a href="#" class="block px-4 py-1.5 hover:bg-slate-50 text-slate-700">12. Event Closing</a>
            </div>
          </div>

          <a href="/pos" class="px-3 py-1.5 rounded-sm hover:bg-slate-100">POS Kasir</a>
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
      document.getElementById('userBadge').textContent = `${user.name} (${user.role?.name || 'Staff'})`;
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
</body>
</html>