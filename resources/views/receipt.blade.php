<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Struk {{ $sale->invoice_code }}</title>
<style>
  @page { size: 72mm auto; margin: 0; }
  body { font-family: 'JetBrains Mono', monospace; font-size: 11px; width: 72mm; margin: 0 auto; padding: 8px; }
  h1 { font-size: 14px; text-align: center; margin: 0 0 4px; }
  .meta { text-align: center; margin-bottom: 8px; }
  table { width: 100%; border-collapse: collapse; }
  td { padding: 2px 0; vertical-align: top; }
  .r { text-align: right; }
  hr { border: none; border-top: 1px dashed #000; margin: 6px 0; }
  .total td { font-weight: 700; }
</style>
</head>
<body>
  <h1>{{ $company->nama_perusahaan ?? 'NOIRISH BOOKS' }}</h1>
  <div class="meta">
    {{ $company->alamat_perusahaan ?? '' }}<br>
    Telp: {{ $company->nomor_telepon ?? '-' }}
  </div>

  @if($event = $sale->event)
  <div style="border:1px solid #000;padding:4px;margin-bottom:8px;">
    <div style="font-weight:700;text-align:center">{{ strtoupper($event->nama_acara) }}</div>
    <div class="meta" style="margin:2px 0 0;font-size:10px">{{ $event->lokasi ?? '-' }}<br>
    {{ \Carbon\Carbon::parse($event->tanggal_mulai)->format('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($event->tanggal_selesai)->format('d/m/Y') }}
    @if($event->jenis_diskon)<br>Diskon event: {{ $event->jenis_diskon === 'PERSEN' ? $event->nilai_diskon.'%' : 'Rp'.number_format($event->nilai_diskon,0,',','.') }}@endif
    </div>
  </div>
  @endif

  <div class="meta">
    {{ $sale->invoice_code }}<br>
    {{ $sale->sale_date->format('d/m/Y H:i') }}<br>
    Kasir: {{ $sale->user->name ?? '-' }}<br>
    Customer: {{ $sale->customer->name ?? 'Guest' }}
    @if($sale->customer && $sale->customer->id_anggota)
    <br>Poin Member: +{{ (int) floor($sale->final_amount / 1000) }}
    @endif
  </div>
  <hr>
  <table>
    @foreach($sale->items as $it)
    <tr>
      <td>{{ $it->product->nama_produk ?? 'Produk' }}<br><span style="color:#666">Rp {{ number_format($it->price, 0, ',', '.') }} x {{ $it->qty }}</span></td>
      <td class="r">{{ number_format($it->subtotal, 0, ',', '.') }}</td>
    </tr>
    @endforeach
  </table>
  <hr>
  <table>
    <tr><td>Total</td><td class="r">{{ number_format($sale->total_amount, 0, ',', '.') }}</td></tr>
    @if($sale->discount > 0)
    <tr><td>Diskon</td><td class="r">-{{ number_format($sale->discount, 0, ',', '.') }}</td></tr>
    @endif
    <tr class="total"><td>KEWAJIBAN</td><td class="r">Rp {{ number_format($sale->final_amount, 0, ',', '.') }}</td></tr>
    @php
      $metode = strtoupper($sale->payment_method);
      $kembali = 0;
      if ($metode === 'CASH') {
        $dibayar = $sale->payment?->amount ?? $sale->final_amount;
        $kembali = max(0, $dibayar - $sale->final_amount);
      }
    @endphp
    @if($kembali > 0)
    <tr><td>TUNAI DIBAYAR</td><td class="r">{{ number_format($sale->payment->amount, 0, ',', '.') }}</td></tr>
    <tr><td>KEMBALI</td><td class="r">{{ number_format($kembali, 0, ',', '.') }}</td></tr>
    @endif
    <tr><td>Metode</td><td class="r">{{ $metode }}</td></tr>
  </table>
  <hr>
  <div class="meta">Terima kasih sudah berbelanja ✦</div>
</body>
</html>
