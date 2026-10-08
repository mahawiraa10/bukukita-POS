@extends('layouts.app')
@section('title', 'Dashboard — BukuKita')
@section('content')
<div class="space-y-6">
  <div>
    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
    <p class="text-xs text-slate-500">Ringkasan operasional hari ini.</p>
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
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-bold text-slate-900 tracking-tight">Tren Penjualan <span class="text-slate-400 font-mono text-xs">(30 hari)</span></h2>
        <div class="flex gap-1 text-[10px] font-semibold uppercase tracking-wider">
          <button data-period="daily" class="px-2.5 py-1 rounded-sm bg-emerald-500 text-white">Harian</button>
          <button data-period="monthly" class="px-2.5 py-1 rounded-sm bg-white border border-slate-200 text-slate-500">Bulanan</button>
        </div>
      </div>
      <canvas id="trendChart" height="110"></canvas>
    </div>

    <div class="bg-white border border-slate-200 rounded-sm p-5">
      <h2 class="text-sm font-bold text-slate-900 tracking-tight mb-4">Top 5 Produk</h2>
      <canvas id="topChart" height="240"></canvas>
    </div>
  </div>

  <!-- Event Comparison -->
  <div class="bg-white border border-slate-200 rounded-sm p-5">
    <h2 class="text-sm font-bold text-slate-900 tracking-tight mb-4">Perbandingan Omzet Antar Event <span class="text-slate-400 font-mono text-xs">(semua event)</span></h2>
    <canvas id="eventChart" height="100"></canvas>
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

// KPI
api('/api/dashboard/kpi').then(d => {
  document.querySelector('[data-kpi=revenue]').textContent = rp(d.totalRevenue);
  document.querySelector('[data-kpi=orders]').textContent = d.totalOrders;
  document.querySelector('[data-kpi=avg]').textContent = rp(d.avgOrder);
  document.querySelector('[data-kpi=items]').textContent = d.totalItems;
}).catch(e => console.error('kpi', e));

// Trend
let trendChart;
async function loadTrend(period = 'daily') {
  const d = await api(`/api/dashboard/sales-trend?period=${period}`);
  const labels = d.map(x => x.label), data = d.map(x => x.revenue);
  if (trendChart) trendChart.destroy();
  trendChart = new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: { labels, datasets: [{ label: 'Omzet', data, borderColor: '#10b981', borderWidth: 2,
      backgroundColor: 'rgba(16,185,129,.08)', fill: true, tension: .3, pointRadius: 0, pointHoverRadius: 4 }] },
    options: { plugins: { legend: { display: false },
      tooltip: { callbacks: { label: ctx => 'Rp ' + Number(ctx.parsed.y).toLocaleString('id-ID') } } },
      scales: { x: { grid: { display: false }, ticks: { font: { size: 10, family: 'JetBrains Mono' }, color: '#94a3b8' } },
        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10, family: 'JetBrains Mono' }, color: '#94a3b8', callback: v => rp(v) } } } }
  });
}
document.querySelectorAll('[data-period]').forEach(btn => btn.onclick = () => {
  document.querySelectorAll('[data-period]').forEach(b => b.className = 'px-2.5 py-1 rounded-sm bg-white border border-slate-200 text-slate-500');
  btn.className = 'px-2.5 py-1 rounded-sm bg-emerald-500 text-white';
  loadTrend(btn.dataset.period);
});
loadTrend();

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

// Event revenue comparison
api('/api/dashboard/event-revenue').then(d => {
  new Chart(document.getElementById('eventChart'), {
    type: 'bar',
    data: { labels: d.map(x => x.nama_acara), datasets: [{ label: 'Omzet', data: d.map(x => Number(x.revenue)),
      backgroundColor: d.map(x => x.status_acara === 'AKTIF' ? '#10b981' : '#94a3b8'), borderRadius: 2, barThickness: 24 }] },
    options: { plugins: { legend: { display: false },
      tooltip: { callbacks: { label: ctx => 'Rp ' + Number(ctx.parsed.y).toLocaleString('id-ID') } } },
      scales: { x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#0f172a' } },
        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10, family: 'JetBrains Mono' }, color: '#94a3b8', callback: v => rp(v) } } } }
  });
}).catch(e => console.error('event', e));

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