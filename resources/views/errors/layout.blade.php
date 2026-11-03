<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BukuKita — @yield('title')</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root { --bg:#f8fafc; --card:#ffffff; --border:#e2e8f0; --text:#0f172a; --accent:#10b981; }
    body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased}
    .mono{font-family:'JetBrains Mono',monospace}
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

<div class="w-full max-w-md bg-white border border-slate-200 rounded-sm p-8">

  <div class="flex items-center gap-2 mb-6">
    <div class="inline-flex w-8 h-8 rounded-sm bg-emerald-500 text-white items-center justify-center text-sm font-bold">BK</div>
    <span class="text-xs font-semibold text-slate-500 uppercase tracking-widest">BukuKita</span>
  </div>

  <p class="mono text-[11px] font-semibold uppercase tracking-widest mb-2 @yield('accent', 'text-emerald-600')">Error @yield('code')</p>
  <h1 class="text-xl font-bold text-slate-900 tracking-tight mb-2">@yield('title')</h1>
  <p class="text-sm text-slate-500 leading-relaxed mb-6">@yield('message')</p>

  <div class="bg-slate-50 border border-slate-200 rounded-sm px-3 py-2 mb-6">
    <p class="text-[10px] font-semibold uppercase text-slate-500 mb-0.5">Path diminta</p>
    <p class="mono text-xs text-slate-700 break-all">{{ '/' . ltrim(request()->path(), '/') }}</p>
  </div>

  <div class="flex gap-2">
    <a href="@yield('primary_url', '/dashboard')"
       class="flex-1 text-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-sm py-2.5 text-xs font-semibold uppercase tracking-widest">
      @yield('primary_label', 'Ke Dashboard')
    </a>
    <a href="{{ url('/') }}"
       class="flex-1 text-center bg-white border border-slate-200 hover:border-slate-300 text-slate-700 rounded-sm py-2.5 text-xs font-semibold uppercase tracking-widest">
      Beranda
    </a>
  </div>

</div>

</body>
</html>
