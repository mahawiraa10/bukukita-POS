@extends('layouts.app')
@section('title', 'Dashboard — BukuKita')
@section('content')
<div class="space-y-6">
  <!-- Header & Clean Date Range Bar -->
  <div class="bg-white border border-slate-200 rounded-sm p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-xl font-bold text-slate-900 tracking-tight">Dashboard Utama</h1>
      <p class="text-xs text-slate-500 mt-0.5">Analisis tren penjualan dan performa operasional toko.</p>
    </div>

    <!-- Simplified Controls -->
    <div class="flex flex-wrap items-center gap-3">
      <!-- Date Range Inputs -->
      <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-sm px-3 py-1.5">
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Dari:</span>
        <input type="date" id="dateFrom" class="text-xs font-mono text-slate-700 bg-transparent border-0 focus:ring-0 p-0">
        <span class="text-slate-300 font-bold">/</span>
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sampai:</span>
        <input type="date" id="dateTo" class="text-xs font-mono text-slate-700 bg-transparent border-0 focus:ring-0 p-0">
      </div>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="kpiGrid">
    @foreach([['Total Penjualan','revenue','Rp'],['Total Transaksi','orders',''],['Rata-rata / Nota','avg','Rp'],['Item Terjual','items','']] as [$label, $key, $prefix])
    <div class="bg-white border border-slate-200 rounded-sm p-5">
      <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
      <p class="text-2xl font-bold text-slate-900 mono mt-1"><span class="text-emerald-600">{{ $prefix }}</span><span data-kpi="{{ $key }}">0</span></p>
    </div>
    @endforeach
  </div>

  <!-- Charts Row -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-sm p-5">
      <h2 class="text-sm font-bold text-slate-900 tracking-tight mb-4">Tren Penjualan</h2>
      <canvas id="trendChart" height="110"></canvas>
    </div>

    <div class="bg-white border border-slate-200 rounded-sm p-5">
      <h2 class="text-sm font-bold text-slate-900 tracking-tight mb-4">Top 5 Produk</h2>
      <canvas id="topChart" height="240"></canvas>
    </div>
  </div>

  <!-- Low Stock -->
  <div class="bg-white border border-slate-200 rounded-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-200 flex items-center justify-between">
      <h2 class="text-sm font-bold text-slate-900 tracking-tight">Stok Menipis</h2>
      <span class="text-[10px] font-mono text-slate-400">batas 5 unit</span>
    </div>
    <table class="w-full text-sm text-left">
      <thead>
        <tr class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
          <th class="px-5 py-2.5">Produk</th>
          <th class="px-5 py-2.5">Penerbit</th>
          <th class="px-5 py-2.5 text-right">Stok</th>
          <th class="px-5 py-2.5 text-right">Min</th>
          <th class="px-5 py-2.5 text-right">Status</th>
        </tr>
      </thead>
      <tbody id="lowStockBody">
        <tr><td colspan="5" class="px-5 py-6 text-center text-xs text-slate-400 mono italic">Memuat...</td></tr>
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const H = { Authorization: `Bearer ${token}`, 'Accept': 'application/json' };
const rp = n => n >= 1e6 ? (n/1e6).toFixed(1).replace('.0','') + ' jt' : n.toLocaleString('id-ID');

async function api(path){ const r = await fetch(path, { headers: H }); if(!r.ok) throw new Error(path + ' -> ' + r.status); return r.json(); }

async function loadKPI(from = '', to = '') {
  const params = new URLSearchParams();
  if (from) params.set('from', from);
  if (to) params.set('to', to);
  const d = await api(`/api/dashboard/kpi?${params}`);
  document.querySelector('[data-kpi=revenue]').textContent = rp(d.totalRevenue);
  document.querySelector('[data-kpi=orders]').textContent = d.totalOrders;
  document.querySelector('[data-kpi=avg]').textContent = rp(d.avgOrder);
  document.querySelector('[data-kpi=items]').textContent = d.totalItems;
}
let trendChart = null;
const now = new Date();
const toStr = now.toISOString().split('T')[0];
const fromStr = new Date(now.setDate(now.getDate() - 30)).toISOString().split('T')[0];
document.getElementById('dateFrom').value = fromStr;
document.getElementById('dateTo').value = toStr;

async function reloadAll() {
  const from = document.getElementById('dateFrom').value;
  const to = document.getElementById('dateTo').value;
  
  loadKPI(from, to);
  loadTrend(from, to);
}

async function loadTrend(from = '', to = '') {
  const params = new URLSearchParams({ period: 'daily' });
  if (from) params.set('from', from);
  if (to) params.set('to', to);
  const d = await api(`/api/dashboard/sales-trend?${params}`);
  
  // Format labels dengan tanggal
  const labels = d.map(x => {
    const dt = x.label;
    const date = new Date(dt);
    const day = date.getDate();
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return `${day} ${months[date.getMonth()]}`;
  });
  const data = d.map(x => x.revenue);
  if (trendChart) trendChart.destroy();
  trendChart = new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: { labels, datasets: [{ label: 'Omzet', data, borderColor: '#10b981', borderWidth: 2,
      backgroundColor: 'rgba(16,185,129,.08)', fill: true, tension: .3, pointRadius: 2 }] },
    options: { plugins: { legend: { display: false },
      tooltip: { callbacks: { label: ctx => 'Rp ' + Number(ctx.parsed.y).toLocaleString('id-ID') } } },
      scales: { x: { grid: { display: false }, ticks: { font: { size: 10, family: 'JetBrains Mono' }, color: '#94a3b8' } },
        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10, family: 'JetBrains Mono' }, color: '#94a3b8', callback: v => rp(v) } } } }
  });
}

document.getElementById('dateFrom').onchange = reloadAll;
document.getElementById('dateTo').onchange = reloadAll;

loadKPI(fromStr, toStr);
loadTrend(fromStr, toStr);

// Top products
api('/api/dashboard/top-products?limit=5').then(d => {
  new Chart(document.getElementById('topChart'), {
    type: 'bar',
    data: { labels: d.map(x => x.nama_produk), datasets: [{ data: d.map(x => x.total_qty),
      backgroundColor: '#10b981', borderRadius: 2, barThickness: 16 }] },
    options: { indexAxis: 'y', plugins: { legend: { display: false } },
      scales: { x: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10, family: 'JetBrains Mono' }, color: '#94a3b8' } },
        y: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#0f172a' } } } }
  });
}).catch(e => console.error('top', e));

// Low stock
api('/api/dashboard/low-stock').then(d => {
  const tb = document.getElementById('lowStockBody');
  if (!d.length) { tb.innerHTML = '<tr><td colspan="5" class="px-5 py-6 text-center text-xs text-slate-400 mono">Semua stok aman.</td></tr>'; return; }
  tb.innerHTML = d.map(x => `
    <tr class="border-t border-slate-100">
      <td class="px-5 py-3 font-medium text-slate-900">${x.nama_produk}</td>
      <td class="px-5 py-3 text-slate-500">${x.penerbit || '-'}</td>
      <td class="px-5 py-3 text-right mono">${x.stok}</td>
      <td class="px-5 py-3 text-right mono text-slate-400">${x.min_stock}</td>
      <td class="px-5 py-3 text-right">
        <span class="px-2 py-0.5 rounded-sm text-[10px] font-bold uppercase tracking-wider ${x.stok <= x.min_stock ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'}">
          ${x.stok <= x.min_stock ? 'Kritis' : 'Menipis'}
        </span>
      </td>
    </tr>`).join('');
}).catch(e => console.error('lowstock', e));
</script>
@endpush