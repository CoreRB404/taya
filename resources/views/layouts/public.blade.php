<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('app.name', 'TAYA') }} - Detainee Rights & Overstay Alert System</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])

<style>
  :root{
    --bg-deep:#0d1128;
    --bg-mid:#1a2151;
    --bg-card:#141a35;
    --border:#2a3363;
    --border-soft:#232b52;
    --text-primary:#f5f7fa;
    --text-secondary:#a2ade0;
    --text-muted:#6b76a8;
    --accent-cyan:#22d3ee;
    --accent-blue:#3b82f6;
    --accent-gradient:linear-gradient(90deg,var(--accent-cyan),var(--accent-blue));
  }

  *{box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{
    margin:0;
    background:
      radial-gradient(ellipse 900px 500px at 20% -10%, #232d63 0%, transparent 60%),
      radial-gradient(ellipse 700px 600px at 100% 20%, #1c2657 0%, transparent 55%),
      linear-gradient(160deg, var(--bg-mid) 0%, var(--bg-deep) 65%);
    background-attachment:fixed;
    color:var(--text-primary);
    font-family:'IBM Plex Sans', sans-serif;
    -webkit-font-smoothing:antialiased;
    overflow-x:hidden;
  }
  h1,h2,h3,.display{
    font-family:'Space Grotesk', sans-serif;
    letter-spacing:-0.01em;
  }
  .mono{ font-family:'IBM Plex Mono', monospace; }
  a{ color:inherit; }

  header{
    display:flex; align-items:center; justify-content:space-between;
    padding:22px 48px;
    border-bottom:1px solid var(--border-soft);
    background:rgba(13,17,40,0.35);
    backdrop-filter:blur(6px);
    position:sticky; top:0; z-index:50;
  }
  .brand{ display:flex; align-items:center; gap:14px; }
  .brand-icon{
    width:42px; height:42px; border-radius:11px;
    background:linear-gradient(145deg,#5b6ee8,#3b4cc4);
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 4px 18px rgba(91,110,232,0.45);
    flex-shrink:0;
  }
  .brand-name{ font-size:19px; font-weight:700; line-height:1.1; }
  .brand-tag{ font-size:12.5px; color:var(--text-muted); margin-top:2px; }
  nav{ display:flex; align-items:center; gap:28px; }
  nav a{
    font-size:14px; color:var(--text-secondary); text-decoration:none;
    transition:color .12s ease, transform .12s ease;
    padding:6px 8px; border-radius:8px;
  }
  nav a:hover{ color:var(--text-primary); background:rgba(255,255,255,0.02); transform:translateY(-1px); }
  .staff-pill{
    display:inline-flex; align-items:center; gap:6px;
    padding:9px 18px; border-radius:100px;
    background:#fff; color:#1a2151 !important;
    font-size:13.5px; font-weight:600;
    text-decoration:none;
  }

  .page-shell{ min-height:100vh; min-height:100dvh; display:flex; flex-direction:column; }
  main{ flex:1; }

  footer{
    margin-top:110px; border-top:1px solid var(--border-soft);
    padding:36px 48px; display:flex; align-items:center; justify-content:space-between;
  }
  .footer-brand{ display:flex; align-items:center; gap:10px; font-size:13.5px; color:var(--text-muted); }
  .footer-links{ display:flex; gap:26px; font-size:13px; color:var(--text-muted); }
  .footer-links a{ text-decoration:none; }
  .footer-links a:hover{ color:var(--text-secondary); }

  @media(max-width:820px){
    header{ padding:14px 18px; }
    .brand-icon{ width:38px; height:38px; }
    .brand-name{ font-size:17px; }
    nav{ gap:8px; }
    nav a:not(.staff-pill){ display:none; }
    .staff-pill{ padding:8px 13px; white-space:nowrap; }
    footer{ flex-direction:column; gap:14px; padding:28px 22px; text-align:center; }
    .footer-links{ justify-content:center; flex-wrap:wrap; gap:12px 18px; }
  }

  @media(max-width:420px){
    header{ padding-inline:12px; }
    .brand-tag{ display:none; }
    .staff-pill{ font-size:12.5px; }
  }

  @media (prefers-reduced-motion: reduce) {
    html{ scroll-behavior:auto; }
    *,*::before,*::after{ animation:none !important; transition-duration:.01ms !important; }
  }
</style>
@stack('styles')
</head>
<body>
<div class="page-shell">
  <header>
    <a href="/" class="brand" aria-label="TAYA home">
      <div class="brand-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 3v4"/><path d="M12 21v-8"/><path d="M5 8l7-1 7 1"/><path d="M5 8l-2 6a3 3 0 0 0 6 0Z"/><path d="M19 8l2 6a3 3 0 0 1-6 0Z"/></svg>
      </div>
      <div>
        <div class="brand-name">TAYA</div>
        <div class="brand-tag">Detainee Rights &amp; Overstay Alert System</div>
      </div>
    </a>
      <nav aria-label="Public navigation">
        @unless(request()->routeIs('tracking.show'))
          <a href="{{ route('tracking.landing') }}">Estimate Duration</a>
          <a href="{{ route('tracking.lookup') }}">Track a Detainee</a>
          <a href="{{ route('tracking.landing') }}#rights">Know Your Rights</a>
          <a href="{{ route('tracking.landing') }}#how">How It Works</a>
        @endunless
        <a class="staff-pill" href="{{ route('login') }}">Staff Login &rarr;</a>
      </nav>
  </header>

  <main>
    @yield('content')
  </main>

  <footer>
    <div class="footer-brand">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 3v4"/><path d="M12 21v-8"/></svg>
      &copy; {{ date('Y') }} TAYA System. All rights reserved.
    </div>
    <div class="footer-links">
      <a href="#">Privacy Policy</a>
      <a href="#">Data Privacy Act Notice</a>
      <a href="#">Contact BJMP</a>
      <a href="{{ route('login') }}">Staff Login &rarr;</a>
    </div>
  </footer>
</div>
</body>
</html>
