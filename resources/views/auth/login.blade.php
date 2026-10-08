<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BukuKita — Masuk</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    :root { --bg:#f8fafc; --card:#ffffff; --border:#e2e8f0; --text:#0f172a; --accent:#10b981; }
    body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased}
    .mono{font-family:'JetBrains Mono',monospace}
    input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(16,185,129,.12)}
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

<div class="w-full max-w-sm bg-white border border-slate-200 rounded-sm p-8">
  
  <!-- Logo -->
  <div class="text-center mb-6">
    <div class="inline-flex w-8 h-8 rounded-sm bg-emerald-500 text-white items-center justify-center text-sm font-bold mb-2">BK</div>
    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Masuk</h2>
  </div>

  <!-- Form -->
  <form id="form" class="space-y-4">

    <div>
      <label class="block text-[10px] font-semibold uppercase label text-slate-600 mb-1.5">Email</label>
      <input id="email" type="email" required autocomplete="email" placeholder="nama@bukukita.test"
        class="w-full bg-white border border-slate-200 rounded-sm px-3 py-2.5 text-sm font-mono text-slate-900 placeholder:text-slate-400">
    </div>

    <div>
      <label class="block text-[10px] font-semibold uppercase label text-slate-600 mb-1.5">Password</label>
      <input id="password" type="password" required autocomplete="current-password" placeholder="••••••••"
        class="w-full bg-white border border-slate-200 rounded-sm px-3 py-2.5 text-sm font-mono text-slate-900 placeholder:text-slate-400">
    </div>

    <p id="err" class="hidden text-xs text-red-600"></p>

    <button id="btn" type="submit"
      class="w-full bg-emerald-500 hover:bg-emerald-600 text-white rounded-sm py-2.5 text-sm font-semibold uppercase tracking-widest disabled:opacity-60">
      Masuk
    </button>
  </form>

  <script>
    const $=id=>document.getElementById(id);
    $('form').onsubmit=async e=>{
      e.preventDefault();
      const email=$('email').value.trim(), pass=$('password').value;
      if(!email||!pass) return;
      $('btn').disabled=true; $('err').classList.add('hidden');
      try{
        const r=await fetch('/api/login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email,password:pass})});
        const j=await r.json();
        if(!r.ok) throw new Error(j.message||'Kredensial salah');
        localStorage.setItem('token',j.token);
        localStorage.setItem('user',JSON.stringify(j.user));
        const role=(j.user?.role?.name||'').toLowerCase();
        location.href=role==='kasir'?'/pos':'/dashboard';
      }catch(e){$('err').textContent=e.message;$('err').classList.remove('hidden');$('btn').disabled=false;}
    };
  </script>

</div>

</body>
</html>
