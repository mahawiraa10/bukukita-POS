<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS Kasir — BukuKita</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background: #0f172a; color: #f8fafc; -webkit-font-smoothing: antialiased; }
    .mono { font-family: 'JetBrains Mono', monospace; }
    input, select { transition: border-color .15s ease; }
    input:focus, select:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.12); }
  </style>
</head>
<body class="min-h-screen flex flex-col">

  <!-- Navbar POS -->
  <header class="bg-slate-900/80 backdrop-blur-sm border-b border-slate-800 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-6 h-14 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-6 h-6 rounded-sm bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">BK</div>
        <span class="font-bold tracking-tight text-slate-100">BukuKita <span class="text-emerald-400">POS</span></span>
      </div>
      <div class="flex items-center gap-4 text-xs">
        <span id="userBadge" class="mono text-slate-400">Memuat...</span>
        <a href="/dashboard" class="px-2.5 py-1.5 border border-slate-700 rounded-sm hover:bg-slate-800 text-slate-300">Dashboard</a>
        <button onclick="logout()" class="px-2.5 py-1.5 border border-slate-700 rounded-sm hover:bg-slate-800 text-slate-300">Keluar</button>
      </div>
    </div>
  </header>

  <!-- Main POS -->
  <main class="flex-1 p-6">
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">

      <!-- Kiri: Produk & Search -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-sm p-4">
          <div class="flex gap-4">
            <input type="text" id="product-search" placeholder="Cari produk (kode/nama/barcode)..." 
                   class="flex-1 bg-slate-800 border border-slate-700 rounded-sm px-4 py-2 text-slate-100 mono text-sm focus:outline-none focus:border-emerald-500" autofocus />
            <select id="category-filter" class="bg-slate-800 border border-slate-700 rounded-sm px-4 py-2 text-slate-100 text-sm focus:outline-none focus:border-emerald-500">
              <option value="">Semua Kategori</option>
            </select>
          </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-sm p-4 flex-1">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Daftar Produk</h3>
            <button onclick="loadProducts()" class="text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-1.5 rounded-sm mono">Refresh</button>
          </div>
          <div id="products-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 overflow-y-auto max-h-[calc(100vh-240px)] pb-4">
            <div class="col-span-full text-center text-slate-500 py-8 text-sm mono">Memuat produk...</div>
          </div>
        </div>
      </div>

      <!-- Kanan: Keranjang -->
      <div class="space-y-4">
        <div class="bg-slate-900 border border-slate-800 rounded-sm p-4 flex-1">
          <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Keranjang</h3>
          <div id="cart-items" class="space-y-2 mb-4 max-h-[40vh] overflow-y-auto">
            <p class="text-center text-slate-500 py-8 text-sm mono">Keranjang kosong</p>
          </div>
          <div class="border-t border-slate-700 pt-4 space-y-2">
            <div class="flex justify-between text-xs text-slate-400">
              <span>Subtotal</span>
              <span id="cart-subtotal" class="mono">Rp 0</span>
            </div>
            <div class="flex justify-between text-xs text-slate-400">
              <span>Diskon</span>
              <span id="cart-discount" class="mono">Rp 0</span>
            </div>
            <div class="flex justify-between text-lg font-bold border-t border-slate-700 pt-3">
              <span class="text-slate-100">Total</span>
              <span id="cart-total" class="mono text-emerald-400">Rp 0</span>
            </div>
            <button onclick="processPayment()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-3 rounded-sm font-medium transition-colors">
              Proses Pembayaran
            </button>
          </div>
        </div>
      </div>

    </div>
  </main>

  <script>
    const cart = [];
    let products = [];
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');

    // User badge
    if(user.name) {
      document.getElementById('userBadge').textContent = `${user.name} (${user.role?.name || 'Staff'})`;
    }

    const fmt = (n) => 'Rp ' + n.toLocaleString('id-ID');

    async function loadProducts() {
      try {
        const res = await fetch('/api/products', {
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
        });
        if(!res.ok) throw new Error('Gagal muat');
        const data = await res.json();
        products = data.data || data;
        renderProducts();
        renderCategories();
      } catch (e) {
        document.getElementById('products-grid').innerHTML = '<div class="col-span-full text-center text-rose-400 py-8 text-sm mono">Gagal memuat produk. Cek koneksi API.</div>';
      }
    }

    function renderProducts() {
      const q = document.getElementById('product-search').value.toLowerCase();
      const cat = document.getElementById('category-filter').value;
      
      const filtered = products.filter(p => {
        const matchSearch = p.kode_produk?.toLowerCase().includes(q) 
            || p.nama_produk?.toLowerCase().includes(q)
            || p.barcode?.toLowerCase().includes(q);
        const matchCat = !cat || String(p.id_kelas) === cat;
        return matchSearch && matchCat;
      });
      
      if (!filtered.length) {
        document.getElementById('products-grid').innerHTML = '<div class="col-span-full text-center text-slate-500 py-8 text-sm mono">Produk tidak ditemukan</div>';
        return;
      }
      
      document.getElementById('products-grid').innerHTML = filtered.map(p => `
        <button onclick="addToCart(${p.id_produk}, ${JSON.stringify(p.nama_produk)}, ${p.harga_jual}, ${p.stok || 0})"
            class="group p-3 bg-slate-800 border border-slate-700 rounded-sm hover:border-emerald-500 hover:bg-slate-700/50 transition-colors cursor-pointer text-left min-h-[100px]"
            ${(p.stok || 0) <= 0 ? 'disabled opacity-50 cursor-not-allowed' : ''}>
            <div class="text-[10px] text-slate-500 mono mb-1">${p.kode_produk || '-'}</div>
            <div class="font-medium text-xs truncate leading-tight">${p.nama_produk}</div>
            <div class="mt-2 text-emerald-400 mono text-xs">${fmt(p.harga_jual)}</div>
            <div class="mt-1 text-[10px] text-slate-500 mono">Stok: ${p.stok || 0}</div>
            ${(p.stok || 0) <= 0 ? '<div class="mt-1 text-[10px] text-rose-400 font-bold">HABIS</div>' : ''}
        </button>
      `).join('');
    }

    function renderCategories() {
      const cats = [...new Set(products.map(p => p.id_kelas).filter(Boolean))];
      document.getElementById('category-filter').innerHTML = '<option value="">Semua Kategori</option>' + cats.map(c => `<option value="${c}">${c}</option>`).join('');
    }

    function addToCart(id, name, price, stock) {
      const existing = cart.find(item => item.id === id);
      if (existing) {
        if (existing.qty >= stock) { alert('Stok tidak mencukupi'); return; }
        existing.qty++;
      } else {
        cart.push({ id, name, price, qty: 1, stock });
      }
      renderCart();
    }

    function updateQty(id, delta) {
      const item = cart.find(i => i.id === id);
      if (!item) return;
      const newQty = item.qty + delta;
      if (newQty <= 0) return removeFromCart(id);
      if (newQty > item.stock) { alert('Stok tidak mencukupi'); return; }
      item.qty = newQty;
      renderCart();
    }

    function removeFromCart(id) {
      const idx = cart.findIndex(i => i.id === id);
      if (idx > -1) cart.splice(idx, 1);
      renderCart();
    }

    function renderCart() {
      const itemsEl = document.getElementById('cart-items');
      if (!cart.length) {
        itemsEl.innerHTML = '<p class="text-center text-slate-500 py-8 text-sm mono">Keranjang kosong</p>';
        document.getElementById('cart-subtotal').textContent = fmt(0);
        document.getElementById('cart-discount').textContent = fmt(0);
        document.getElementById('cart-total').textContent = fmt(0);
        return;
      }
      
      const subtotal = cart.reduce((sum, i) => sum + i.price * i.qty, 0);
      
      itemsEl.innerHTML = cart.map(item => `
        <div class="flex items-center justify-between p-2 bg-slate-800 border border-slate-700 rounded-sm">
          <div class="flex-1 min-w-0 pr-3">
            <div class="font-medium text-xs truncate">${item.name}</div>
            <div class="text-[10px] text-slate-400 mono">${fmt(item.price)} x ${item.qty}</div>
          </div>
          <div class="flex items-center gap-2">
            <button onclick="updateQty('${item.id}', -1)" class="w-5 h-5 bg-slate-700 hover:bg-slate-600 rounded text-slate-100 text-[10px] flex items-center justify-center">−</button>
            <span class="mono text-xs w-6 text-center">${item.qty}</span>
            <button onclick="updateQty('${item.id}', 1)" class="w-5 h-5 bg-slate-700 hover:bg-slate-600 rounded text-slate-100 text-[10px] flex items-center justify-center">+</button>
            <button onclick="removeFromCart('${item.id}')" class="w-5 h-5 bg-rose-600/80 hover:bg-rose-500 rounded text-white text-[10px] flex items-center justify-center">✕</button>
          </div>
        </div>
      `).join('');
      
      document.getElementById('cart-subtotal').textContent = fmt(subtotal);
      document.getElementById('cart-discount').textContent = fmt(0);
      document.getElementById('cart-total').textContent = fmt(subtotal);
    }

    async function processPayment() {
      if (!cart.length) { alert('Keranjang kosong'); return; }
      const total = cart.reduce((sum, i) => sum + i.price * i.qty, 0);
      if (!confirm(`Proses pembayaran Rp ${total.toLocaleString('id-ID')}?`)) return;
      // TODO: Integrase dengan API /api/transactions
      alert('Fitur transaksi segera diintegrasikan. (Simulasi sukses)');
      cart.length = 0;
      renderCart();
    }

    function logout() {
      fetch('/api/logout', { method: 'POST', headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' } }).catch(()=>{});
      localStorage.clear();
      location.href = '/login';
    }

    // Event listeners
    document.getElementById('product-search').addEventListener('input', renderProducts);
    document.getElementById('category-filter').addEventListener('change', renderProducts);

    // Init
    loadProducts();
  </script>
</body>
</html>