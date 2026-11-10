<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Struk {{ $sale->invoice_code }}</title>
<style>
  @page { size: 72mm auto; margin: 0; }
  body { font-family: 'JetBrains Mono', monospace; font-size: 11px; width: 72mm; margin: 0 auto; padding: 8px; color: #000; }
  h1 { font-size: 14px; text-align: center; margin: 0 0 4px; font-weight: bold; }
  .meta { text-align: center; margin-bottom: 8px; }
  .meta-left { text-align: left; margin-bottom: 8px; }
  table { width: 100%; border-collapse: collapse; }
  td { padding: 2px 0; vertical-align: top; }
  .r { text-align: right; }
  hr { border: none; border-top: 1px dashed #000; margin: 6px 0; }
  .total td { font-weight: 700; font-size: 12px; }
  .footer-note { text-align: center; font-size: 10px; margin-top: 8px; color: #333; }
</style>
</head>
<body>
  <h1>{{ $company->nama_perusahaan ?? 'BukuKita' }}</h1>
  <div class="meta">
    {{ $company->alamat_perusahaan ?? '' }}<br>
    Telp: {{ $company->nomor_telepon ?? '-' }}
  </div>

  @if($event = $sale->event)
  <div style="border:1px solid #000;padding:4px;margin-bottom:8px;">
    <div style="font-weight:700;text-align:center">{{ strtoupper($event->nama_acara) }}</div>
    <div class="meta" style="margin:2px 0 0;font-size:10px">{{ $event->lokasi ?? '-' }}<br>
    {{ \Carbon\Carbon::parse($event->tanggal_mulai)->format('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($event->tanggal_selesai)->format('d/m/Y') }}
    </div>
  </div>
  @endif

  <div class="meta-left">
    Nota    : {{ $sale->invoice_code }}<br>
    Tanggal : {{ $sale->sale_date->format('d/m/Y H:i') }}<br>
    Kasir   : {{ $sale->user->name ?? '-' }}<br>
    Pelanggan: {{ $sale->customer->name ?? 'Guest' }}
  </div>
  <hr>
  <table>
    @foreach($sale->items as $it)
    <tr>
      <td colspan="2">
        <b>{{ $it->product->nama_produk ?? 'Produk' }}</b><br>
        <span style="font-size:10px;">ISBN: {{ $it->product->isbn ?? '-' }}</span><br>
        <span style="font-size:10px;">{{ $it->qty }} x Rp {{ number_format($it->price, 0, ',', '.') }} @if($it->discount > 0) (Disc {{ $it->discount }}%) @endif</span>
      </td>
      <td class="r" style="vertical-align: bottom;"><b>Rp {{ number_format($it->subtotal, 0, ',', '.') }}</b></td>
    </tr>
    @endforeach
  </table>
  <hr>
  <table>
    <tr><td>Subtotal</td><td class="r">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td></tr>
    @if($sale->discount > 0)
    <tr><td>Potongan</td><td class="r">- Rp {{ number_format($sale->discount, 0, ',', '.') }}</td></tr>
    @endif
    <tr class="total"><td>TOTAL</td><td class="r">Rp {{ number_format($sale->final_amount, 0, ',', '.') }}</td></tr>
    @php
      $metode = strtoupper($sale->payment_method);
      $kembali = 0;
      $dibayar = $sale->payment?->amount ?? $sale->final_amount;
      if ($metode === 'CASH' || $metode === 'TUNAI') {
        $kembali = max(0, $dibayar - $sale->final_amount);
      }
    @endphp
    <tr><td>Metode Bayar</td><td class="r">{{ $metode }}</td></tr>
    @if($metode === 'CASH' || $metode === 'TUNAI')
    <tr><td>Tunai Diterima</td><td class="r">Rp {{ number_format($dibayar, 0, ',', '.') }}</td></tr>
    <tr><td>Kembali</td><td class="r">Rp {{ number_format($kembali, 0, ',', '.') }}</td></tr>
    @endif
  </table>

  @if($sale->customer && $sale->customer->id_anggota)
  <hr>
  <div style="font-size:10px;">
      @if($sale->original_invoice_code)
      <b>Referensi Transaksi:</b> {{ $sale->original_invoice_code }}<br>
      @endif
      <b>Info Member:</b><br>
      Poin Diperoleh : +{{ (int) floor($sale->final_amount / 1000) }} Poin<br>
      Total Poin Anda: {{ ($sale->customer->poin ?? 0) }} Poin
  </div>
  @endif

  <hr>
  <div class="footer-note">
    Barang yang sudah dibeli tidak dapat<br>
    ditukar/dikembalikan kecuali ada<br>
    perjanjian di awal.<br><br>
    <b>~ TERIMA KASIH TELAH BERBELANJA ~</b><br>
    Buku adalah Jendela Dunia
  </div>
</body>
</html>
