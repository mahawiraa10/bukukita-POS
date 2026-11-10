<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS Kasir — BukuKita</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #0f172a; -webkit-font-smoothing: antialiased; }
    .mono { font-family: 'JetBrains Mono', monospace; }
    input, select { transition: border-color .15s ease; }
    input:focus, select:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.12); }
  </style>
</head>
<body class="min-h-screen flex flex-col">

  <!-- Navbar POS -->
  <header class="bg-white/80 backdrop-blur-sm border-b border-slate-200 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-6 h-14 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-6 h-6 rounded-sm bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">BK</div>
        <span class="font-bold tracking-tight text-slate-900">BukuKita <span class="text-emerald-700">POS</span></span>
      </div>
      <div class="flex items-center gap-4 text-xs">
        <span id="userBadge" class="mono text-slate-600">Memuat...</span>
        <button id="btnCloseShift" onclick="openCloseShiftModal()" class="hidden px-2.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-sm text-[10px] font-bold">TUTUP SHIFT</button>
        <button onclick="openHistory()" class="px-2.5 py-1.5 border border-slate-300 rounded-sm hover:bg-slate-50 text-slate-700">Histori</button>
        <button onclick="logout()" class="px-2.5 py-1.5 border border-slate-300 rounded-sm hover:bg-slate-50 text-slate-700">Keluar</button>
      </div>
    </div>
  </header>

  <!-- Main POS -->
  <main class="flex-1 p-6 space-y-4 max-w-7xl mx-auto w-full">

    <!-- Middle Section: Full-Width Table Grid -->
    <div class="bg-white border border-slate-200 rounded-sm overflow-hidden flex-1 shadow-lg shadow-slate-200">
      <div class="p-3 bg-slate-50/60 border-b border-slate-200 flex justify-between items-center">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Daftar Belanja (Tabel POS)</h3>
        <span class="text-xs text-slate-600 mono" id="cart-item-count">0 Item</span>
      </div>
      <div class="relative overflow-auto h-[calc(100vh-340px)] min-h-[300px] border border-slate-300">
              <table class="w-full h-full text-left text-slate-700 border-collapse border border-slate-300 text-xs">
                <thead class="bg-slate-50 text-slate-800 uppercase sticky top-0 font-semibold tracking-wide">
                  <tr class="border-b-2 border-slate-300">
                    <th class="border border-slate-300 px-3 py-2.5 w-12 text-center bg-slate-50">No</th>
                    <th class="border border-slate-300 px-3 py-2.5 w-44 bg-slate-50">Kode / SKU / ISBN</th>
                    <th class="border border-slate-300 px-3 py-2.5 bg-slate-50">Nama Produk / Buku</th>
                    <th class="border border-slate-300 px-3 py-2.5 w-32 text-right bg-slate-50">Harga Satuan</th>
                    <th class="border border-slate-300 px-3 py-2.5 w-24 text-center bg-slate-50">Diskon (%)</th>
                    <th class="border border-slate-300 px-3 py-2.5 w-28 text-center bg-slate-50">Qty</th>
                    <th class="border border-slate-300 px-3 py-2.5 w-36 text-right bg-slate-50">Subtotal</th>
                    <th class="border border-slate-300 px-3 py-2.5 w-16 text-center bg-slate-50">Aksi</th>
                  </tr>
                </thead>
                <tbody id="cart-items-body">
                  <tr class="h-full">
                    <td class="border-x border-slate-300"></td><td class="border-x border-slate-300"></td><td class="border-x border-slate-300"></td><td class="border-x border-slate-300"></td><td class="border-x border-slate-300"></td><td class="border-x border-slate-300"></td><td class="border-x border-slate-300"></td><td class="border-x border-slate-300"></td>
                  </tr>
                </tbody>
              </table>
          </div>
    </div>

    <!-- Bottom Section: Compact Scanner Input Bar, Lookup Button & Cash Tender -->
    <div class="bg-white border border-slate-200 rounded-sm p-2 flex items-center justify-between gap-3">
      <div class="flex items-center gap-3 w-full max-w-2xl">
        <div class="w-full max-w-sm">
          <input type="text" id="product-search" placeholder="Scan ISBN / SKU / Barcode..." 
                 class="w-full bg-slate-50 border border-slate-300 rounded-sm px-3 py-1.5 text-slate-900 mono text-xs focus:outline-none focus:border-emerald-600 shadow-inner" autofocus />
        </div>
        <button onclick="openProductCatalogModal()" class="bg-slate-50 hover:bg-slate-100 border border-slate-300 text-slate-800 text-xs px-3 py-1.5 rounded-sm flex items-center gap-1.5 font-medium transition-colors whitespace-nowrap">
          <span>📋</span> List Produk
        </button>
      </div>
      
      <div class="flex items-center gap-2 pl-4 border-l border-slate-200">
         <label class="text-xs text-slate-500 font-medium">Cash Tender:</label>
         <input type="number" id="main-cash-tender" oninput="updateMainUI()" placeholder="Rp..."
                class="w-32 bg-white border border-slate-300 rounded-sm px-2 py-1.5 text-emerald-700 font-mono text-xs focus:outline-none focus:border-emerald-600">
         <div class="text-xs text-slate-600">
            Kembali: <span id="main-change" class="font-mono font-bold text-slate-900">Rp 0</span>
         </div>
      </div>
    </div>

    <!-- Bottom Section: Summary & Checkout Footer -->
    <div class="bg-white border border-slate-200 rounded-sm p-4 flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-6 text-xs text-slate-600">
        <div>
          <span>Subtotal:</span>
          <span id="cart-subtotal" class="font-mono text-slate-800 ml-1 font-semibold">Rp 0</span>
        </div>
        <div class="flex items-center gap-2">
          <div class="flex items-center gap-2">
          <label class="text-[10px] text-slate-500 uppercase">Member</label>
          <div class="flex gap-1">
              <input type="text" id="member-search" placeholder="Cari No HP/Nama..." onkeyup="searchMember(event)" 
                     class="w-32 bg-slate-50 border border-slate-300 rounded-sm px-2 py-0.5 text-emerald-700 font-mono text-xs focus:outline-none focus:border-emerald-600">
              <input type="hidden" id="selected-member-id" value="">
              <button onclick="openRegisterModal()" class="text-[10px] bg-slate-100 px-1 border border-slate-300 rounded">Daftar</button>
          </div>
          <span id="member-display" class="text-xs text-slate-600 font-semibold truncate max-w-[100px]"></span>
        </div>
        <div><label class="text-[10px] text-slate-500 uppercase">Diskon Global</label><input type="number" id="global-discount-input" oninput="renderCart()" value="0" class="w-20 bg-slate-50 border border-slate-300 rounded-sm px-2 py-0.5 text-rose-700 font-mono text-xs focus:outline-none"></div><span>Metode:</span>
          <select id="main-payment-method" class="w-24 bg-white border border-slate-300 rounded-sm px-2 py-1 text-slate-800 text-xs focus:outline-none">
            <option value="TUNAI">TUNAI</option>
            <option value="DEBIT">DEBIT</option>
          </select>
        </div>
      </div>
      <div class="flex items-center gap-4 w-full md:w-auto">
        <div class="text-right">
          <div class="text-[10px] text-slate-600 uppercase tracking-wider font-semibold">Total Pembayaran</div>
          <div id="cart-total" class="font-mono text-emerald-700 font-bold text-2xl">Rp 0</div>
        </div>
        <button onclick="confirmPaymentDirect()" class="bg-emerald-600 hover:bg-emerald-500 text-white px-8 py-3 rounded-sm font-bold transition-colors shadow-lg shadow-slate-200 text-sm">
          BAYAR
        </button>
      </div>
    </div>


  <div id="success-payment-modal" class="hidden fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/40 backdrop-blur-[2px] p-4">
    <div class="bg-white rounded-sm p-8 w-full max-w-sm shadow-2xl text-center border border-slate-200">
      <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">✓</div>
      <h3 class="text-lg font-bold text-slate-900 uppercase tracking-tight">Transaksi Berhasil</h3>
      <p id="success-invoice-code" class="text-xs text-slate-500 mono mt-1 font-semibold"></p>
      
      <div class="mt-6 space-y-2">
        <button onclick="printReceiptFromModal()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-3 rounded-sm font-bold text-xs uppercase tracking-wider transition-all">Cetak Struk</button>
        <button onclick="closeSuccessModal()" class="w-full bg-white border border-slate-300 text-slate-600 py-2.5 rounded-sm font-bold text-[10px] uppercase tracking-wider hover:bg-slate-50 transition-all">Selesai (Esc)</button>
      </div>
    </div>
  </div>


  <div id="pos-alert-modal" class="hidden fixed inset-0 z-[300] flex items-center justify-center bg-slate-900/40 backdrop-blur-[2px] p-4">
    <div class="bg-white rounded-sm w-full max-w-sm shadow-2xl border border-slate-200 overflow-hidden">
      <div class="flex items-start gap-4 p-6">
        <div id="pos-alert-icon" class="w-11 h-11 shrink-0 rounded-full flex items-center justify-center text-lg font-bold bg-amber-100 text-amber-600">!</div>
        <div class="flex-1 min-w-0 pt-0.5">
          <h3 id="pos-alert-title" class="text-sm font-bold text-slate-900 uppercase tracking-tight">Perhatian</h3>
          <div id="pos-alert-message" class="text-xs text-slate-600 mt-1.5 leading-relaxed space-y-1"></div>
        </div>
      </div>
      <div class="px-6 pb-5 flex gap-2">
        <button id="pos-alert-ok" onclick="closePosAlert()" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white py-2.5 rounded-sm font-bold text-[11px] uppercase tracking-wider transition-all">Mengerti (Enter)</button>
      </div>
    </div>
  </div>
  </main>

  <script>

    // ---- POS Alert Modal (pengganti alert() bawaan browser) ----
    const POS_ALERT_STYLES = {
        warning: { wrap: 'bg-amber-100 text-amber-600', glyph: '!' },
        error:   { wrap: 'bg-rose-100 text-rose-600',   glyph: '✕' },
        success: { wrap: 'bg-emerald-100 text-emerald-600', glyph: '✓' },
        info:    { wrap: 'bg-slate-100 text-slate-600', glyph: 'i' }
    };


    function showPosAlert(message, opts = {}) {
        const type = opts.type || 'warning';
        const style = POS_ALERT_STYLES[type] || POS_ALERT_STYLES.warning;
        const modal = document.getElementById('pos-alert-modal');
        const icon = document.getElementById('pos-alert-icon');

        document.getElementById('pos-alert-title').textContent = opts.title || 'Perhatian';
        const msgEl = document.getElementById('pos-alert-message');
        msgEl.innerHTML = String(message)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .split('\n').map(l => l.trim()).filter(l => l.length)
            .map(l => '<div>' + l + '</div>').join('');
        icon.className = 'w-11 h-11 shrink-0 rounded-full flex items-center justify-center text-lg font-bold ' + style.wrap;
        icon.textContent = style.glyph;

        window._posAlertFocus = opts.focus || null;
        window._posAlertOnClose = opts.onClose || null;
        modal.classList.remove('hidden');
        setTimeout(() => document.getElementById('pos-alert-ok').focus(), 30);
    }

    function closePosAlert() {
        document.getElementById('pos-alert-modal').classList.add('hidden');
        const cb = window._posAlertOnClose;
        const target = window._posAlertFocus;
        window._posAlertOnClose = null;
        window._posAlertFocus = null;
        if (typeof cb === 'function') { cb(); return; }
        if (target) {
            const el = document.getElementById(target);
            if (el) { el.focus(); el.select && el.select(); return; }
        }
        document.getElementById('product-search')?.focus();
    }

    // Enter = OK, Escape = tutup
    window.addEventListener('keydown', (e) => {
        const modal = document.getElementById('pos-alert-modal');
        if (modal && !modal.classList.contains('hidden')) {
            if (e.key === 'Enter' || e.key === 'Escape') { e.preventDefault(); closePosAlert(); }
        }
    });

    function closeSuccessModal() {
        document.getElementById('success-payment-modal').classList.add('hidden');
        document.getElementById('product-search')?.focus();
    }

    function printReceiptFromModal() {
        const id = window.lastSaleId;
        if (id) {
            window.open('/receipt/' + encodeURIComponent(id), '_blank', 'width=320,height=640');
        } else {
            showPosAlert('ID transaksi tidak ditemukan.', { title: 'Struk Gagal', type: 'error' });
        }
    }

    // Close on Escape
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeSuccessModal();
    });


    

    async function confirmPaymentDirect() {
        if (!cart.length) { showPosAlert('Belum ada buku di keranjang. Scan dulu ISBN-nya.', { title: 'Keranjang Kosong', type: 'warning' }); return; }
        
        const subtotal = cart.reduce((sum, i) => sum + (i.price * i.qty) - ((i.price * i.qty) * (i.discount / 100)), 0);
        const globalDiscount = parseFloat(document.getElementById('global-discount-input')?.value) || 0;
        const total = Math.max(0, subtotal - globalDiscount);
        const tender = parseFloat(document.getElementById('main-cash-tender')?.value) || 0;
        const metode = document.getElementById('main-payment-method').value;

        if (metode === 'TUNAI') {
            if (!tender || tender <= 0) {
                showPosAlert('Kolom Cash Tender masih kosong. Isi nominal uang tunai yang diterima dari pembeli.', {
                    title: 'Cash Tender Belum Diisi',
                    type: 'warning',
                    focus: 'main-cash-tender'
                });
                return;
            }
            if (tender < total) {
                const kurang = total - tender;
                showPosAlert(
                    'Uang tunai yang diterima KURANG dari total belanja.\n\n' +
                    'Total Belanja : ' + fmt(total) + '\n' +
                    'Uang Diterima : ' + fmt(tender) + '\n' +
                    'Kurang        : ' + fmt(kurang),
                    { title: 'Pembayaran Kurang', type: 'error', focus: 'main-cash-tender' }
                );
                return;
            }
        }

        const payload = {
            id_anggota: document.getElementById('selected-member-id').value || null,
            items: cart.map(i => ({ id_produk: i.id, qty: i.qty, diskon_tambahan: i.discount })),
            metode_bayar: metode,
            uang_diterima: metode === 'TUNAI' ? tender : total,
            no_referensi_bank: metode === 'DEBIT' ? ('DEBIT-' + Date.now()) : null
        };

        try {
            const res = await fetch('/api/pos/sales', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const d = await res.json();
            if (!res.ok) throw new Error(d.message || 'Gagal simpan transaksi');
            
            // Tampilkan modal sukses
            document.getElementById('success-invoice-code').textContent = d.sale.invoice_code;
            window.lastSaleId = d.sale.id || (d.sale.data && d.sale.data.id);
            window.lastInvoiceCode = d.sale.invoice_code;
            document.getElementById('success-payment-modal').classList.remove('hidden');
            
            // Reset state
            cart.length = 0;
            if(document.getElementById('main-cash-tender')) document.getElementById('main-cash-tender').value = '';
            if(document.getElementById('global-discount-input')) document.getElementById('global-discount-input').value = '0';
            if(document.getElementById('selected-member-id')) document.getElementById('selected-member-id').value = '';
            if(document.getElementById('member-display')) document.getElementById('member-display').textContent = '';
            renderCart();
            loadProducts();
        } catch (e) { showPosAlert(e.message, { title: 'Transaksi Gagal', type: 'error' }); }
    }

    function openRegisterModal() {
        document.getElementById('member-register-modal').classList.remove('hidden');
    }

    async function registerMember() {
        const data = {
            name: document.getElementById('reg-name').value,
            phone: document.getElementById('reg-phone').value,
            gender: document.getElementById('reg-gender').value,
            city: document.getElementById('reg-city').value
        };
        try {
            const res = await fetch('/api/members', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            });
            const d = await res.json();
            if (!res.ok) throw new Error(d.message);
            showPosAlert('Member baru berhasil didaftarkan dan langsung terhubung ke transaksi ini.', { title: 'Member Terdaftar', type: 'success' });
            document.getElementById('member-register-modal').classList.add('hidden');
            // Auto-select the new member
            document.getElementById('member-search').value = data.phone;
            // Trigger search
            searchMember({ key: 'Enter', target: { value: data.phone } });
        } catch(e) { showPosAlert(e.message, { title: 'Gagal Daftar Member', type: 'error' }); }
    }

    async function searchMember(e) {
        if (e.key === 'Enter') {
            const val = e.target.value;
            if (!val) return;
            try {
                const res = await fetch('/api/members?cari=' + encodeURIComponent(val), {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                const d = await res.json();
                if (d.data && d.data.length > 0) {
                    const m = d.data[0];
                    document.getElementById('selected-member-id').value = m.id_anggota;
                    document.getElementById('member-display').textContent = m.name;
                    showPosAlert('Member ditemukan: ' + m.name + ' (' + m.phone + ')', { title: 'Member Ditemukan', type: 'success' });
                } else {
                    showPosAlert('Tidak ada member dengan No. HP / nama tersebut. Klik tombol "Daftar" untuk membuat member baru.', { title: 'Member Tidak Ditemukan', type: 'warning' });
                    document.getElementById('selected-member-id').value = '';
                    document.getElementById('member-display').textContent = '';
                }
            } catch (e) { console.error(e); }
        }
    }

    const cart = [];
    let products = [];
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    function openProductCatalogModal() {
        const body = document.getElementById('catalog-body');
        body.innerHTML = products.map(p => `
            <tr class="hover:bg-slate-50 cursor-pointer border-b border-slate-300" ondblclick="addToCart('${p.id_produk}', '${p.nama_produk}', ${p.harga_jual}, ${p.stok || 0}, '${p.sku || p.kode_produk || ''}', '${p.isbn || ''}'); document.getElementById('catalog-modal').classList.add('hidden');">
                <td class="p-3 mono">${p.isbn || p.sku || '-'}</td>
                <td class="p-3">${p.nama_produk}</td>
                <td class="p-3 text-right mono">${fmt(p.harga_jual)}</td>
                <td class="p-3 text-center mono">${p.stok || 0}</td>
            </tr>
        `).join('');
        document.getElementById('catalog-modal').classList.remove('hidden');
    }

    function filterCatalog() {
        const term = document.getElementById('catalog-search').value.toLowerCase();
        const rows = document.querySelectorAll('#catalog-body tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    }


    // User badge
    if(user.name) {
      const cleanName = user.name.replace(/\s*\([^)]*\)/g, '').trim();
      const roleName = (user.role?.name || 'STAFF').toUpperCase();
      document.getElementById('userBadge').textContent = `${cleanName} (${roleName})`;
    }

    const fmt = (n) => 'Rp ' + n.toLocaleString('id-ID');

    let scanHistory = [];



    async function loadProducts() {
      try {
        const res = await fetch('/api/products?available=1', {
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        });
        if(!res.ok) throw new Error('Gagal muat');
        const data = await res.json();
        products = data.data || data;
      } catch (e) {
      }
    }

    function handleScan(e) {
      const val = document.getElementById('product-search').value.trim();
      const q = val.toLowerCase();

      // Auto-trigger: when ISBN/SKU reaches 13 digits via input event, search instantly
      if (e.type === 'input') {
        if (!/^[\d]{13}$/.test(val)) return;
        doSearch(val);
        return;
      }

      // Enter key also triggers search
      if (e.key === 'Enter') {
        if (!val) return;
        doSearch(val);
      }
    }

    function doSearch(val) {
      const q = val.toLowerCase();
      const p = products.find(item =>
        String(item.sku || '').toLowerCase() === q ||
        String(item.isbn || '').toLowerCase() === q ||
        String(item.kode_produk || '').toLowerCase() === q ||
        String(item.nama_produk || '').toLowerCase().includes(q)
      );

      if (p) {
        if ((p.stok || 0) <= 0) {
          showPosAlert('Stok produk "' + p.nama_produk + '" habis. Lakukan penerimaan barang dulu.', { title: 'Stok Habis', type: 'error' });
          return;
        }
        addToCart(p.id_produk, p.nama_produk, p.harga_jual, p.stok || 0, p.sku || p.kode_produk || '', p.isbn || '');

        scanHistory.unshift({
          name: p.nama_produk,
          sku: p.sku || p.kode_produk || '-',
          price: p.harga_jual,
          time: new Date().toLocaleTimeString()
        });
        if (scanHistory.length > 20) scanHistory.pop();
        renderScanHistory();

        document.getElementById('product-search').value = '';
      } else {
        showPosAlert('Produk dengan kode / nama "' + val + '" tidak ditemukan.', { title: 'Produk Tidak Ditemukan', type: 'warning' });
      }
    }

    function renderScanHistory() {
      const el = document.getElementById('scan-history');
      if (!scanHistory.length) {
        el.innerHTML = '<p class="text-center text-slate-500 py-8 text-sm mono">Belum ada item yang di-scan pada sesi ini.</p>';
        return;
      }
      el.innerHTML = scanHistory.map(s => `
        <div class="flex items-center justify-between p-2.5 bg-slate-50/80 border border-slate-300/60 rounded-sm text-xs">
          <div>
            <div class="font-medium text-slate-800">${s.name}</div>
            <div class="text-[10px] text-slate-600 mono">SKU: ${s.sku} • ${s.time}</div>
          </div>
          <div class="font-mono text-emerald-700 font-semibold">${fmt(s.price)}</div>
        </div>
      `).join('');
    }

    function addToCart(id, name, price, stock, sku='', isbn='') {
      const existing = cart.find(item => item.id === id);
      if (existing) {
        if (existing.qty >= stock) { showPosAlert('Stok tidak mencukupi untuk jumlah tersebut.', { title: 'Stok Kurang', type: 'error' }); return; }
        existing.qty++;
      } else {
        cart.push({ id, name, price, qty: 1, discount: 0, stock, sku, isbn });
      }
      renderCart();
    }

    function setQty(id, val) {
      const item = cart.find(i => i.id === id);
      if (!item) return;
      const newQty = parseInt(val);
      if (isNaN(newQty) || newQty <= 0) {
        removeFromCart(id);
        return;
      }
      if (newQty > item.stock) {
        showPosAlert('Stok tidak mencukupi. Maksimal: ' + item.stock + ' unit.', { title: 'Stok Kurang', type: 'error' });
        item.qty = item.stock;
        renderCart();
        return;
      }
      item.qty = newQty;
      renderCart();
    }

    function removeFromCart(id) {
      const idx = cart.findIndex(i => i.id === id);
      if (idx > -1) cart.splice(idx, 1);
      renderCart();
    }

    const FILLER_ROW = '<tr class="h-full">' + '<td class="border-x border-slate-300"></td>'.repeat(8) + '</tr>';

        function updateItemDiscount(id, val) {
      const item = cart.find(i => i.id === id);
      if (item) {
        item.discount = parseFloat(val) || 0; // %
        renderCart();
      }
    }

    
    function updateMainUI() {
      const subtotal = cart.reduce((sum, i) => sum + (i.price * i.qty) - ((i.price * i.qty) * (i.discount / 100)), 0);
      const globalDiscount = parseFloat(document.getElementById('global-discount-input')?.value) || 0;
      const total = Math.max(0, subtotal - (parseFloat(document.getElementById('global-discount-input')?.value) || 0));
      const tender = parseFloat(document.getElementById('main-cash-tender')?.value) || 0;
      const change = tender - total;
      
      const changeEl = document.getElementById('main-change');
      if (changeEl) {
        changeEl.textContent = fmt(change >= 0 ? change : 0);
        changeEl.className = change < 0 && tender > 0 ? 'font-mono font-bold text-rose-600' : 'font-mono font-bold text-slate-900';
      }
    }

    function renderCart() {
      const bodyEl = document.getElementById('cart-items-body');
      const countEl = document.getElementById('cart-item-count');
      const globalDiscount = parseFloat(document.getElementById('global-discount-input')?.value) || 0;
      
      if (!cart.length) {
        bodyEl.innerHTML = FILLER_ROW;
        document.getElementById('cart-empty-msg')?.classList.remove('hidden');
        if (countEl) countEl.textContent = '0 Item';
        document.getElementById('cart-subtotal').textContent = fmt(0);
        document.getElementById('cart-total').textContent = fmt(0);
        return;
      }
      
      const subtotal = cart.reduce((sum, i) => {
        const itemTotal = i.price * i.qty;
        const discountAmt = itemTotal * (i.discount / 100);
        return sum + (itemTotal - discountAmt);
      }, 0);
      
      const totalItems = cart.reduce((sum, i) => sum + i.qty, 0);
      if (countEl) countEl.textContent = `${totalItems} Item (${cart.length} Jenis)`;
      
      bodyEl.innerHTML = cart.map((item, index) => {
        const itemTotal = item.price * item.qty;
        const discountAmt = itemTotal * (item.discount / 100);
        const finalItemTotal = itemTotal - discountAmt;
        
        return `
        <tr class="hover:bg-slate-50/60 transition-colors">
          <td class="border border-slate-300 px-3 py-2.5 text-center text-slate-600 mono">${index + 1}</td>
          <td class="border border-slate-300 px-3 py-2.5 text-slate-700 mono text-[11px]">${item.isbn || item.sku || '-'}</td>
          <td class="border border-slate-300 px-3 py-2.5 text-slate-900 font-medium">${item.name}</td>
          <td class="border border-slate-300 px-3 py-2.5 text-right mono text-slate-700">${fmt(item.price)}</td>
          <td class="border border-slate-300 px-3 py-2.5 text-center">
            <input type="number" onchange="updateItemDiscount('${item.id}', this.value)" value="${item.discount}" 
                   class="w-full bg-white border border-slate-300 rounded-sm px-2 py-1 text-right text-rose-700 font-mono text-[11px] focus:outline-none focus:border-rose-500" placeholder="0">
          </td>
          <td class="border border-slate-300 px-2 py-2 text-center">
            <input type="number" onchange="setQty('${item.id}', this.value)" value="${item.qty}" min="1" max="${item.stock}"
                   class="w-16 bg-white border border-slate-300 rounded-sm px-2 py-1 text-center text-slate-900 font-mono text-xs focus:outline-none focus:border-emerald-600">
          </td>
          <td class="border border-slate-300 px-3 py-2.5 text-right mono text-emerald-700 font-semibold">${fmt(finalItemTotal)}</td>
          <td class="border border-slate-300 px-3 py-2.5 text-center">
            <button onclick="removeFromCart('${item.id}')" class="w-6 h-6 bg-rose-600/80 hover:bg-rose-500 rounded text-white flex items-center justify-center text-xs font-bold">✕</button>
          </td>
        </tr>`;
      }).join('') + FILLER_ROW;
      
      document.getElementById('cart-empty-msg')?.classList.add('hidden');
      
      const finalTotal = subtotal - globalDiscount;
      document.getElementById('cart-subtotal').textContent = fmt(subtotal);
      document.getElementById('cart-total').textContent = fmt(finalTotal >= 0 ? finalTotal : 0);
      updateMainUI();
    }

    function logout() {
      fetch('/api/logout', { method: 'POST', headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } }).catch(()=>{});
      localStorage.clear();
      location.href = '/login';
    }

    // Event listeners
    document.getElementById('product-search').addEventListener('keydown', handleScan);
    document.getElementById('product-search').addEventListener('input', handleScan);

    let activeSale = null;

    async function openHistory() {
      document.getElementById('history-modal').classList.remove('hidden');
      const res = await fetch('/api/sales', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
      const d = await res.json();
      const list = d.data || d || [];
      document.getElementById('history-list').innerHTML = list.length ? list.map(sale => `
        <div class="p-3 border-b border-slate-300 flex justify-between items-center hover:bg-slate-50 cursor-pointer" onclick="showRefundModal('${sale.invoice_code}')">
          <div><div class="font-mono text-sm">${sale.invoice_code}</div><div class="text-[10px] text-slate-600">${(sale.sale_date || sale.created_at || '').slice(0, 16)}</div></div>
          <div class="font-bold text-emerald-700">${fmt(parseFloat(sale.final_amount))}</div>
        </div>
      `).join('') : '<p class="text-center text-slate-500 py-8">Belum ada transaksi.</p>';
    }

    async function showRefundModal(invoice) {
      if (!['owner', 'supervisor'].includes(user.role?.name)) { showPosAlert('Hanya Supervisor / Owner yang dapat melakukan refund.', { title: 'Akses Ditolak', type: 'error' }); return; }
      const res = await fetch('/api/sales/' + encodeURIComponent(invoice), { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
      if (!res.ok) { showPosAlert('Nota dengan kode invoice tersebut tidak ditemukan.', { title: 'Nota Tidak Ditemukan', type: 'warning' }); return; }
      const d = await res.json();
      activeSale = invoice;
      document.getElementById('refund-invoice').textContent = invoice;
      document.getElementById('refund-items').innerHTML = (d.data ? d.data : d).items.map(it => `
        <label class="flex items-center justify-between gap-2 text-xs text-slate-700 bg-slate-50 px-2 py-1.5 rounded-sm cursor-pointer">
          <span class="flex items-center gap-2 min-w-0">
            <input type="checkbox" class="refund-chk accent-rose-500" value="${it.id_produk}" data-qty="${it.qty}">
            <span class="truncate">${(it.product && it.product.nama_produk) || it.nama_produk || it.id_produk}</span>
          </span>
          <span class="mono text-[10px] text-slate-600">x${it.qty}</span>
        </label>
      `).join('');
      document.getElementById('refund-pin').value = '';
      document.getElementById('history-modal').classList.add('hidden');
      document.getElementById('refund-modal').classList.remove('hidden');
      // Show PIN only for supervisor
      const pinContainer = document.getElementById('refund-pin-container');
      if (user.role?.name === 'supervisor') {
        pinContainer.classList.remove('hidden');
      } else {
        pinContainer.classList.add('hidden');
      }
      if (user.role?.name === 'supervisor') {
        document.getElementById('refund-pin').focus();
      }
    }

    async function submitRefund() {
      const items = [...document.querySelectorAll('.refund-chk:checked')].map(c => ({ id_produk: c.value, qty: parseInt(c.dataset.qty) }));
      if (!items.length) { showPosAlert('Pilih minimal satu item yang mau direfund.', { title: 'Item Belum Dipilih', type: 'warning' }); return; }
      try {
        const res = await fetch('/api/refunds', {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_transaksi_referensi: activeSale, items, sumber_pengembalian: 'TUNAI' })
        });
        const d = await res.json();
        if (!res.ok) throw new Error(d.message || 'Refund gagal');
        showPosAlert('Refund berhasil diproses.\n\nNilai refund: ' + fmt(d.nilai_refund), { title: 'Refund Berhasil', type: 'success' });
        document.getElementById('refund-modal').classList.add('hidden');
        loadProducts();
      } catch (e) { showPosAlert(e.message, { title: 'Refund Gagal', type: 'error' }); }
    }

    // Init

    let currentShift = null;

    async function checkShift() {
      try {
        const res = await fetch('/api/shifts/current', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } });
        const data = await res.json();
        currentShift = data.id_sesi_kasir ? data : null;
        
        if (!currentShift) {
          // Auto open shift with 0 if none, bypassing the UI modal completely
          await submitOpenShift(0); 
        } else {
          document.getElementById('btnCloseShift').classList.remove('hidden');
          loadProducts();
        }
      } catch (e) { console.error('Shift check failed', e); }
    }

    async function submitOpenShift(modal_awal = 0) {
      const nomor_kasir = '01'; // 1 kasir default
      try {
        const res = await fetch('/api/shifts/open', {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ nomor_kasir, modal_awal })
        });
        const d = await res.json();
        if (!res.ok) throw new Error(d.message || 'Gagal buka shift');
        location.reload(); // Refresh halaman agar state shift baru langsung aktif
      } catch (e) { showPosAlert(e.message, { title: 'Shift Bermasalah', type: 'error' }); }
    }

    function openCloseShiftModal() {
      document.getElementById('close-shift-modal').classList.remove('hidden');
      document.getElementById('shift-uang-fisik').focus();
    }

    async function submitCloseShift() {
      const uang_fisik = document.getElementById('shift-uang-fisik').value;
      if (!uang_fisik) return showPosAlert('Isi dulu nominal uang fisik yang ada di laci sebelum menutup shift.', { title: 'Uang Fisik Kosong', type: 'warning' });
      try {
        const res = await fetch('/api/shifts/close', {
          method: 'POST',
          headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ uang_fisik })
        });
        const d = await res.json();
        if (!res.ok) throw new Error(d.message || 'Gagal tutup shift');
        showPosAlert('Shift berhasil ditutup.\n\nUang diharapkan: ' + fmt(d.expected_cash) + '\nSelisih: ' + fmt(d.selisih) + '\n\nAnda akan logout otomatis.', { title: 'Shift Ditutup', type: 'success', onClose: () => logout() });
      } catch (e) { showPosAlert(e.message, { title: 'Gagal Tutup Shift', type: 'error' }); }
    }

    checkShift();

  </script>

  <!-- Modal Histori Transaksi -->
  <div id="history-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-white/90 backdrop-blur-md p-4">
    <div class="bg-white border border-slate-300 rounded-sm p-6 w-full max-w-2xl shadow-2xl shadow-slate-200 h-[80vh] flex flex-col">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-slate-900 uppercase">Histori Penjualan</h3>
        <button onclick="document.getElementById('history-modal').classList.add('hidden')" class="text-slate-600 hover:text-white">✕</button>
      </div>
      <div class="flex-1 overflow-y-auto" id="history-list">
        <p class="text-center text-slate-500 py-8">Memuat...</p>
      </div>
    </div>
  </div>

  <!-- Modal Refund/Void -->
  <div id="refund-modal" class="hidden fixed inset-0 z-[110] flex items-center justify-center bg-white/95 backdrop-blur-md p-4">
    <div class="bg-white border border-slate-300 rounded-sm p-8 w-full max-w-sm shadow-2xl shadow-slate-200">
      <h3 class="text-lg font-bold text-rose-500 mb-1">Otorisasi Refund/Void</h3>
      <p class="text-[11px] text-slate-600 mono mb-4" id="refund-invoice"></p>
      <div class="space-y-4">
        <div>
          <label class="text-[10px] font-bold text-slate-500 uppercase">Pilih Item Direfund</label>
          <div id="refund-items" class="mt-1 space-y-1 max-h-40 overflow-y-auto border border-slate-300 rounded-sm p-2"></div>
        </div>
        <button onclick="submitRefund()" class="w-full bg-rose-600 hover:bg-rose-500 text-white py-3 rounded-sm font-bold uppercase transition-all">Proses</button>
      </div>
    </div>
  </div>

  
  <!-- Modal Katalog Produk -->
  <div id="catalog-modal" class="hidden fixed inset-0 z-[120] flex items-center justify-center bg-white/90 backdrop-blur-md p-4">
    <div class="bg-white border border-slate-300 rounded-sm p-6 w-full max-w-4xl shadow-2xl shadow-slate-200 h-[80vh] flex flex-col">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-slate-900 uppercase">Katalog Produk</h3>
        <input type="text" id="catalog-search" oninput="filterCatalog()" placeholder="Cari nama / ISBN..." class="bg-slate-50 border border-slate-300 rounded-sm px-3 py-1.5 text-slate-900 text-sm w-64 focus:border-emerald-600 outline-none">
        <button onclick="document.getElementById('catalog-modal').classList.add('hidden')" class="text-slate-600 hover:text-white">✕</button>
      </div>
      <div class="flex-1 overflow-y-auto border border-slate-300 rounded-sm">
        <table class="w-full text-left text-slate-700 text-xs">
          <thead class="bg-slate-50 text-slate-800 uppercase sticky top-0">
            <tr>
              <th class="p-3">SKU / ISBN</th>
              <th class="p-3">Nama Produk</th>
              <th class="p-3 text-right">Harga</th>
              <th class="p-3 text-center">Stok</th>
            </tr>
          </thead>
          <tbody id="catalog-body" class="divide-y divide-slate-200">
          </tbody>
        </table>
      </div>
      <p class="text-[10px] text-slate-500 mt-2 italic">Klik 2x pada baris produk untuk memasukkan ke keranjang.</p>
    </div>
  </div>

  <!-- Modal Close Shift -->

  <div id="member-register-modal" class="hidden fixed inset-0 z-[150] flex items-center justify-center bg-white/90 backdrop-blur-md p-4">
    <div class="bg-white border border-slate-300 rounded-sm p-6 w-full max-w-sm shadow-2xl">
      <h3 class="text-sm font-bold mb-4 uppercase">Daftar Member Baru</h3>
      <div class="space-y-3">
        <input type="text" id="reg-name" placeholder="Nama Lengkap" class="w-full border p-2 text-xs">
        <input type="text" id="reg-phone" placeholder="No HP" class="w-full border p-2 text-xs">
        <select id="reg-gender" class="w-full border p-2 text-xs">
            <option value="male">Laki-laki</option>
            <option value="female">Perempuan</option>
        </select>
        <input type="text" id="reg-city" placeholder="Kota" class="w-full border p-2 text-xs">
      </div>
      <div class="mt-4 flex gap-2">
        <button onclick="registerMember()" class="flex-1 bg-emerald-600 text-white py-2 text-xs rounded-sm">Daftar</button>
        <button onclick="document.getElementById('member-register-modal').classList.add('hidden')" class="flex-1 bg-slate-200 py-2 text-xs rounded-sm">Batal</button>
      </div>
    </div>
  </div>

  <div id="close-shift-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center bg-white/90 backdrop-blur-md p-4">
    <div class="bg-white border border-slate-300 rounded-sm p-8 w-full max-w-md shadow-2xl shadow-slate-200">
      <div class="mb-6 text-center text-rose-500">
        <h3 class="text-xl font-bold uppercase">Penutupan Kasir</h3>
        <p class="text-slate-600 text-sm mt-1">Hitung uang fisik di laci saat ini.</p>
      </div>
      <div class="space-y-4">
        <div id="refund-pin-container" class="hidden">
          <label class="text-[10px] font-bold text-slate-500 uppercase">PIN Supervisor</label>
          <input type="password" id="refund-pin" class="w-full bg-slate-50 border border-slate-300 rounded-sm px-4 py-3 text-slate-900 mono text-lg mt-1 focus:border-rose-600 outline-none" maxlength="6">
        </div>
        <div class="p-3 bg-rose-500/5 border border-rose-500/20 rounded-sm text-[11px] text-rose-700 leading-relaxed italic">
          * Menutup shift akan mengirim laporan Daily Closing ke Owner. Pastikan semua transaksi sudah dibayar.
        </div>
        <div class="flex gap-3">
          <button onclick="document.getElementById('close-shift-modal').classList.add('hidden')" class="flex-1 bg-slate-50 hover:bg-slate-100 text-slate-600 py-3 rounded-sm font-bold text-xs uppercase transition-all">Batal</button>
          <button onclick="submitCloseShift()" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white py-3 rounded-sm font-bold text-xs uppercase transition-all">TUTUP & LOGOUT</button>
        </div>
      </div>
    </div>
  </div>

</body>
</html>