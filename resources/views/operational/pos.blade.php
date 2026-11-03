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
        <span id="userBadge" class="mono text-slate-400">Memuat...</span><button id="btnCloseShift" onclick="openCloseShiftModal()" class="hidden px-2.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-sm text-[10px] font-bold">TUTUP SHIFT</button>
        <button onclick="openHistory()" class="px-2.5 py-1.5 border border-slate-700 rounded-sm hover:bg-slate-800 text-slate-300">Histori</button><a href="/dashboard" class="px-2.5 py-1.5 border border-slate-700 rounded-sm hover:bg-slate-800 text-slate-300">Dashboard</a>
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
      const cleanName = user.name.replace(/\s*\([^)]*\)/g, '').trim();
      const roleName = (user.role?.name || 'STAFF').toUpperCase();
      document.getElementById('userBadge').textContent = `${cleanName} (${roleName})`;
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
      
      // Buka modal custom
      document.getElementById('modal-total').textContent = fmt(total);
      document.getElementById('modal-bayar').value = total;
      document.getElementById('modal-kembalian').textContent = fmt(0);
      document.getElementById('payment-modal').classList.remove('hidden');
      document.getElementById('modal-bayar').focus();
    }

    function togglePayInput() {
      const m = document.getElementById('modal-metode').value;
      const wBayar = document.getElementById('wrapper-bayar');
      const wRef = document.getElementById('wrapper-ref');
      const total = cart.reduce((sum, i) => sum + i.price * i.qty, 0);
      
      if (m === 'DEBIT') {
        wBayar.classList.add('hidden');
        wRef.classList.remove('hidden');
        document.getElementById('modal-bayar').value = total;
        document.getElementById('modal-kembalian').textContent = fmt(0);
      } else {
        wBayar.classList.remove('hidden');
        wRef.classList.add('hidden');
        document.getElementById('modal-bayar').value = total;
        calculateChange();
      }
    }

    function calculateChange() {
      const total = cart.reduce((sum, i) => sum + i.price * i.qty, 0);
      const bayar = parseFloat(document.getElementById('modal-bayar').value) || 0;
      const kembalian = bayar - total;
      document.getElementById('modal-kembalian').textContent = fmt(kembalian >= 0 ? kembalian : 0);
    }

    function closeModal() {
      document.getElementById('payment-modal').classList.add('hidden');
    }

    async function confirmPayment() {
      const total = cart.reduce((sum, i) => sum + i.price * i.qty, 0);
      const metode = document.getElementById('modal-metode').value;
      const bayar = parseFloat(document.getElementById('modal-bayar').value) || 0;
      const ref = document.getElementById('modal-ref').value;

      if (metode === 'TUNAI' && bayar < total) {
        alert('Uang bayar kurang dari total belanja!');
        return;
      }

      const payload = {
        items: cart.map(i => ({ id_produk: i.id, qty: i.qty })),
        metode_bayar: metode,
        uang_diterima: metode === 'TUNAI' ? bayar : total,
        no_referensi_bank: metode === 'DEBIT' ? ref : null
      };

      try {
        const res = await fetch('/api/pos/sales', {
          method: 'POST',
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const d = await res.json();
        if (!res.ok) throw new Error(d.message || 'Gagal simpan transaksi');
        
        alert(`Transaksi Berhasil!\nMetode: ${metode}\nInvoice: ${d.sale.invoice_code}\nKembalian: ${fmt(metode === 'TUNAI' ? bayar - total : 0)}`);
        cart.length = 0;
        renderCart();
        closeModal();
        loadProducts();
      } catch (e) { alert(e.message); }
    }

    function logout() {
      fetch('/api/logout', { method: 'POST', headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' } }).catch(()=>{});
      localStorage.clear();
      location.href = '/login';
    }

    // Event listeners
    document.getElementById('product-search').addEventListener('input', renderProducts);
    document.getElementById('category-filter').addEventListener('change', renderProducts);


    let activeSale = null;

    async function openHistory() {
      document.getElementById('history-modal').classList.remove('hidden');
      const res = await fetch('/api/sales', { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } });
      const d = await res.json();
      const list = d.data || d || [];
      document.getElementById('history-list').innerHTML = list.length ? list.map(sale => `
        <div class="p-3 border-b border-slate-700 flex justify-between items-center hover:bg-slate-800 cursor-pointer" onclick="showRefundModal('${sale.invoice_code}')">
          <div><div class="font-mono text-sm">${sale.invoice_code}</div><div class="text-[10px] text-slate-400">${(sale.sale_date || sale.created_at || '').slice(0, 16)}</div></div>
          <div class="font-bold text-emerald-400">${fmt(parseFloat(sale.final_amount))}</div>
        </div>
      `).join('') : '<p class="text-center text-slate-500 py-8">Belum ada transaksi.</p>';
    }

    async function showRefundModal(invoice) {
      if (!['owner', 'supervisor'].includes(user.role?.name)) { alert('Hanya Supervisor/Owner yang dapat melakukan refund.'); return; }
      const res = await fetch('/api/sales/' + encodeURIComponent(invoice), { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } });
      if (!res.ok) { alert('Nota tidak ditemukan'); return; }
      const d = await res.json();
      activeSale = invoice;
      document.getElementById('refund-invoice').textContent = invoice;
      document.getElementById('refund-items').innerHTML = (d.data ? d.data : d).items.map(it => `
        <label class="flex items-center justify-between gap-2 text-xs text-slate-300 bg-slate-800 px-2 py-1.5 rounded-sm cursor-pointer">
          <span class="flex items-center gap-2 min-w-0">
            <input type="checkbox" class="refund-chk accent-rose-500" value="${it.id_produk}" data-qty="${it.qty}">
            <span class="truncate">${(it.product && it.product.nama_produk) || it.nama_produk || it.id_produk}</span>
          </span>
          <span class="mono text-[10px] text-slate-400">x${it.qty}</span>
        </label>
      `).join('');
      document.getElementById('refund-pin').value = '';
      document.getElementById('history-modal').classList.add('hidden');
      document.getElementById('refund-modal').classList.remove('hidden');
      document.getElementById('refund-pin').focus();
    }

    async function submitRefund() {
      const pin = document.getElementById('refund-pin').value;
      if (!pin) { alert('Masukkan PIN supervisor.'); return; }
      const items = [...document.querySelectorAll('.refund-chk:checked')].map(c => ({ id_produk: c.value, qty: parseInt(c.dataset.qty) }));
      if (!items.length) { alert('Pilih minimal satu item untuk direfund.'); return; }
      try {
        const res = await fetch('/api/refunds', {
          method: 'POST',
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_transaksi_referensi: activeSale, items, sumber_pengembalian: 'TUNAI', pin_supervisor: pin })
        });
        const d = await res.json();
        if (!res.ok) throw new Error(d.message || 'Refund gagal');
        alert('Refund berhasil. Nilai: ' + fmt(d.nilai_refund));
        document.getElementById('refund-modal').classList.add('hidden');
        loadProducts();
      } catch (e) { alert(e.message); }
    }

    // Init

    let currentShift = null;

    async function checkShift() {
      try {
        const res = await fetch('/api/shifts/current', { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } });
        const data = await res.json();
        currentShift = data.id_sesi_kasir ? data : null;
        
        if (!currentShift) {
          document.getElementById('open-shift-modal').classList.remove('hidden');
        } else {
          document.getElementById('btnCloseShift').classList.remove('hidden');
          loadProducts();
        }
      } catch (e) { console.error('Shift check failed', e); }
    }

    async function submitOpenShift() {
      const modal_awal = document.getElementById('shift-modal-awal').value;
      const nomor_kasir = '01'; // 1 kasir default
      try {
        const res = await fetch('/api/shifts/open', {
          method: 'POST',
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ nomor_kasir, modal_awal })
        });
        const d = await res.json();
        if (!res.ok) throw new Error(d.message || 'Gagal buka shift');
        location.reload();
      } catch (e) { alert(e.message); }
    }

    function openCloseShiftModal() {
      document.getElementById('close-shift-modal').classList.remove('hidden');
      document.getElementById('shift-uang-fisik').focus();
    }

    async function submitCloseShift() {
      const uang_fisik = document.getElementById('shift-uang-fisik').value;
      if (!uang_fisik) return alert('Masukkan uang fisik!');
      try {
        const res = await fetch('/api/shifts/close', {
          method: 'POST',
          headers: { Authorization: `Bearer ${token}`, Accept: 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ uang_fisik })
        });
        const d = await res.json();
        if (!res.ok) throw new Error(d.message || 'Gagal tutup shift');
        alert(`Shift ditutup.\nSelisih: ${fmt(d.selisih)}\nUang diharapkan: ${fmt(d.expected_cash)}`);
        logout();
      } catch (e) { alert(e.message); }
    }

    // Replace loadProducts call with checkShift
    checkShift();

  </script>

  <!-- Modal Pembayaran -->
  <div id="payment-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm">
    <div class="bg-slate-900 border border-slate-700 rounded-sm p-6 w-96 shadow-2xl">
      <h3 class="text-lg font-bold text-slate-100 mb-4">Pembayaran</h3>
      <div class="space-y-4">
        <div class="flex justify-between items-center text-slate-400">
          <span>Total Tagihan</span>
          <span id="modal-total" class="font-mono text-emerald-400">Rp 0</span>
        </div>
        <div>
          <label class="text-xs text-slate-500 uppercase">Metode Pembayaran</label>
          <select id="modal-metode" onchange="togglePayInput()" class="w-full bg-slate-800 border border-slate-700 rounded-sm px-4 py-2 text-slate-100 text-sm mt-1 focus:outline-none focus:border-emerald-500">
            <option value="TUNAI">TUNAI</option>
            <option value="DEBIT">DEBIT</option>
          </select>
        </div>
        <div id="wrapper-bayar">
          <label class="text-xs text-slate-500 uppercase">Uang Dibayar</label>
          <input type="number" id="modal-bayar" oninput="calculateChange()" class="w-full bg-slate-800 border border-slate-700 rounded-sm px-4 py-2 text-slate-100 mono text-sm mt-1">
        </div>
        <div id="wrapper-ref" class="hidden">
          <label class="text-xs text-slate-500 uppercase">No. Referensi / Reff Bank</label>
          <input type="text" id="modal-ref" class="w-full bg-slate-800 border border-slate-700 rounded-sm px-4 py-2 text-slate-100 mono text-sm mt-1" placeholder="cth: 987654321">
        </div>
        <div class="flex justify-between items-center text-slate-400">
          <span>Kembalian</span>
          <span id="modal-kembalian" class="font-mono text-emerald-400">Rp 0</span>
        </div>
      </div>
      <div class="mt-6 flex gap-3">
        <button onclick="closeModal()" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 py-2 rounded-sm text-sm">Batal</button>
        <button onclick="confirmPayment()" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white py-2 rounded-sm text-sm font-bold">Bayar</button>
      </div>
    </div>
  </div>

  <!-- Modal Open Shift -->
  <div id="open-shift-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/90 backdrop-blur-md p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-sm p-8 w-full max-w-md shadow-2xl">
      <div class="mb-6 text-center">
        <div class="w-12 h-12 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h3 class="text-xl font-bold text-slate-100">Buka Sesi Kasir (TILL)</h3>
        <p class="text-slate-400 text-sm mt-1">Masukkan modal awal laci kasir hari ini.</p>
      </div>
      <div class="space-y-4">
        <div>
          <label class="text-[10px] font-bold text-slate-500 uppercase">Modal Awal (Rp)</label>
          <input type="number" id="shift-modal-awal" value="500000" class="w-full bg-slate-800 border border-slate-700 rounded-sm px-4 py-3 text-slate-100 mono text-lg mt-1 focus:border-emerald-500 outline-none">
        </div>
        <button onclick="submitOpenShift()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-4 rounded-sm font-bold text-sm tracking-widest uppercase transition-all mt-4">
          MULAI OPERASIONAL
        </button>
      </div>
    </div>
  </div>


  <!-- Modal Histori Transaksi -->
  <div id="history-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/90 backdrop-blur-md p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-sm p-6 w-full max-w-2xl shadow-2xl h-[80vh] flex flex-col">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-slate-100 uppercase">Histori Penjualan</h3>
        <button onclick="document.getElementById('history-modal').classList.add('hidden')" class="text-slate-400 hover:text-white">✕</button>
      </div>
      <div class="flex-1 overflow-y-auto" id="history-list">
        <p class="text-center text-slate-500 py-8">Memuat...</p>
      </div>
    </div>
  </div>

  <!-- Modal Refund/Void -->
  <div id="refund-modal" class="hidden fixed inset-0 z-[110] flex items-center justify-center bg-slate-950/95 backdrop-blur-md p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-sm p-8 w-full max-w-sm shadow-2xl">
      <h3 class="text-lg font-bold text-rose-500 mb-1">Otorisasi Refund/Void</h3>
      <p class="text-[11px] text-slate-400 mono mb-4" id="refund-invoice"></p>
      <div class="space-y-4">
        <div>
          <label class="text-[10px] font-bold text-slate-500 uppercase">Pilih Item Direfund</label>
          <div id="refund-items" class="mt-1 space-y-1 max-h-40 overflow-y-auto border border-slate-700 rounded-sm p-2"></div>
        </div>
        <div>
          <label class="text-[10px] font-bold text-slate-500 uppercase">PIN Supervisor</label>
          <input type="password" id="refund-pin" class="w-full bg-slate-800 border border-slate-700 rounded-sm px-4 py-3 text-slate-100 mono text-lg mt-1 focus:border-rose-500 outline-none" maxlength="6">
        </div>
        <button onclick="submitRefund()" class="w-full bg-rose-600 hover:bg-rose-500 text-white py-3 rounded-sm font-bold uppercase transition-all">Proses</button>
      </div>
    </div>
  </div>

  <!-- Modal Close Shift -->
  <div id="close-shift-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/90 backdrop-blur-md p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-sm p-8 w-full max-w-md shadow-2xl">
      <div class="mb-6 text-center text-rose-500">
        <h3 class="text-xl font-bold uppercase">Penutupan Kasir</h3>
        <p class="text-slate-400 text-sm mt-1">Hitung uang fisik di laci saat ini.</p>
      </div>
      <div class="space-y-4">
        <div>
          <label class="text-[10px] font-bold text-slate-500 uppercase">Total Uang Fisik (Tunai)</label>
          <input type="number" id="shift-uang-fisik" class="w-full bg-slate-800 border border-slate-700 rounded-sm px-4 py-3 text-slate-100 mono text-lg mt-1 focus:border-rose-500 outline-none" placeholder="0">
        </div>
        <div class="p-3 bg-rose-500/5 border border-rose-500/20 rounded-sm text-[11px] text-rose-400 leading-relaxed italic">
          * Menutup shift akan mengirim laporan Daily Closing ke Owner. Pastikan semua transaksi sudah dibayar.
        </div>
        <div class="flex gap-3">
          <button onclick="document.getElementById('close-shift-modal').classList.add('hidden')" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-400 py-3 rounded-sm font-bold text-xs uppercase transition-all">Batal</button>
          <button onclick="submitCloseShift()" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white py-3 rounded-sm font-bold text-xs uppercase transition-all">TUTUP & LOGOUT</button>
        </div>
      </div>
    </div>
  </div>

</body>
</html>