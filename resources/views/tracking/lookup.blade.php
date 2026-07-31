@extends('layouts.public')

@section('content')
<style>
  .hero{
    max-width:760px; margin:0 auto;
    text-align:center;
    padding:80px 24px 0;
  }
  .eyebrow{
    display:inline-flex; align-items:center; gap:8px;
    font-size:12.5px; letter-spacing:0.08em; text-transform:uppercase;
    color:var(--accent-cyan); font-weight:500;
    padding:6px 14px; border:1px solid rgba(34,211,238,0.35);
    border-radius:100px; background:rgba(34,211,238,0.06);
    margin-bottom:24px;
  }
  .dot{ width:6px; height:6px; border-radius:50%; background:var(--accent-cyan); }
  .headline{ font-size:44px; font-weight:700; line-height:1.15; margin:0 0 16px; }
  .headline .grad{ background:var(--accent-gradient); -webkit-background-clip:text; background-clip:text; color:transparent; }
  .sub{ font-size:16.5px; color:var(--text-secondary); line-height:1.6; max-width:560px; margin:0 auto; }

  .search-wrap{ max-width:760px; margin:44px auto 0; padding:0 24px; }
  .card{
    background:var(--bg-card);
    border:1px solid var(--border);
    border-radius:18px;
    padding:36px 40px 32px;
  }
  .card-label{ font-size:13.5px; font-weight:600; color:var(--text-secondary); margin-bottom:10px; display:block; }
  .input-row{ display:flex; gap:12px; }
  .code-input{
    flex:1;
    display:flex; align-items:center; gap:10px;
    background:#1b2244; border:1px solid var(--border);
    border-radius:11px; padding:15px 18px;
  }
  .code-input svg{ flex-shrink:0; color:var(--text-muted); }
  .code-input input{
    background:none; border:none; outline:none;
    color:var(--text-primary); font-family:'IBM Plex Mono',monospace;
    font-size:14.5px; width:100%;
  }
  .code-input input::placeholder{ color:#565f8f; }
  .btn-search{
    display:flex; align-items:center; justify-content:center; gap:9px;
    background:var(--accent-gradient);
    border:none; border-radius:11px;
    color:#08121c; font-weight:700; font-size:14.5px;
    padding:0 26px; cursor:pointer; white-space:nowrap;
    transition:filter .15s ease, transform .15s ease;
  }
  .btn-search:hover{ filter:brightness(1.08); transform:translateY(-1px); }
  .card-foot{
    margin-top:20px; padding-top:18px; border-top:1px solid var(--border-soft);
    font-size:13px; color:var(--text-muted); text-align:center; line-height:1.7;
  }
  .card-foot a{ color:var(--accent-cyan); text-decoration:none; font-weight:500; }

  .rail-section{ max-width:960px; margin:96px auto 0; padding:0 24px; }
  .section-head{ text-align:center; margin-bottom:56px; }
  .section-head .kicker{ font-size:12.5px; letter-spacing:0.08em; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px; }
  .section-head h2{ font-size:27px; font-weight:600; margin:0 0 10px; }
  .section-head p{ color:var(--text-secondary); font-size:14.5px; max-width:480px; margin:0 auto; }

  .rail{ position:relative; padding:0 10px 10px; }
  .rail-track{
    position:absolute; top:23px; left:34px; right:34px; height:2px;
    background:linear-gradient(90deg,var(--accent-cyan),var(--border) 85%);
  }
  .rail-steps{ display:flex; justify-content:space-between; position:relative; }
  .rail-step{ flex:1; display:flex; flex-direction:column; align-items:center; text-align:center; padding:0 6px; }
  .rail-node{
    width:46px; height:46px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-family:'IBM Plex Mono',monospace; font-size:13px; font-weight:500;
    background:var(--bg-card); border:2px solid var(--border); color:var(--text-muted);
    z-index:2; margin-bottom:16px;
  }
  .rail-step.active .rail-node{
    background:var(--accent-gradient); border-color:transparent; color:#08121c; font-weight:700;
  }
  .rail-step .rname{ font-size:13.5px; font-weight:600; margin-bottom:4px; }
  .rail-step .rdesc{ font-size:12px; color:var(--text-muted); line-height:1.5; max-width:120px; }
  .rail-step.active .rname{ color:var(--accent-cyan); }

  .how-section{ max-width:960px; margin:110px auto 0; padding:0 24px; }
  .how-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:22px; }
  .how-card{
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:16px; padding:28px 26px;
  }
  .how-num{ font-family:'IBM Plex Mono',monospace; font-size:12.5px; color:var(--accent-cyan); margin-bottom:14px; }
  .how-card h3{ font-size:16.5px; font-weight:600; margin:0 0 8px; }
  .how-card p{ font-size:13.5px; color:var(--text-secondary); line-height:1.6; margin:0; }

  .rights-section{ max-width:960px; margin:96px auto 0; padding:0 24px 24px; }
  .rights-card{
    background:linear-gradient(135deg, rgba(34,211,238,0.07), rgba(59,130,246,0.05));
    border:1px solid rgba(59,130,246,0.28);
    border-radius:18px; padding:34px 38px;
    display:grid; grid-template-columns:1.1fr 1.4fr; gap:34px; align-items:center;
  }
  .rights-card h3{ font-size:19px; margin:0 0 10px; }
  .rights-card p{ color:var(--text-secondary); font-size:14px; line-height:1.65; margin:0; }
  .rights-list{ list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:12px; }
  .rights-list li{
    display:flex; gap:10px; font-size:13.5px; color:var(--text-secondary); line-height:1.5;
  }
  .rights-list svg{ flex-shrink:0; margin-top:2px; color:var(--accent-cyan); }

  @media(max-width:820px){
    .headline{ font-size:32px; }
    .card{ padding:26px 22px; }
    .input-row{ flex-direction:column; }
    .btn-search{ padding:0.9rem 1rem; }
    .rail-steps{ flex-wrap:wrap; row-gap:28px; }
    .rail-track{ display:none; }
    .rail-step{ flex:0 0 33%; }
    .how-grid{ grid-template-columns:1fr; }
    .rights-card{ grid-template-columns:1fr; }
  }
</style>

<section class="hero">
  <span class="eyebrow"><span class="dot"></span>Built for transparency in detention</span>
  <h1 class="headline">Know exactly <span class="grad">where a case stands</span> — and what comes next.</h1>
  <p class="sub">TAYA gives families, counsel, and facility staff a shared, real-time view of detention status, so no one waits in the dark or overstays a phase that should have ended.</p>
</section>

<div class="search-wrap" id="track">
  <div class="card">
    <span class="card-label">Tracking Code</span>
    <form action="{{ route('tracking.lookup') }}" method="GET">
      <div class="input-row">
        <div class="code-input">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
          <input type="text" name="code" id="code" placeholder="e.g., TAYA-ABC123" value="{{ old('code', request('code')) }}" autocomplete="off" required>
        </div>
        <button type="submit" class="btn-search">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          Search
        </button>
      </div>
    </form>

    @error('code')
      <p class="mt-3 text-sm text-red-400">{{ $message }}</p>
    @enderror

    @if($error ?? false)
      <div class="mt-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300 text-sm">
        {{ $error }}
      </div>
    @endif

    <div class="card-foot">
      Don't have a tracking code? <a href="#">Contact the detention facility directly</a> to request one.
    </div>
  </div>
</div>

<section class="rail-section">
  <div class="section-head">
    <div class="kicker">What a tracking code shows</div>
    <h2>Every case moves through five phases</h2>
    <p>TAYA flags a case the moment it runs past the expected time in any phase — that's the overstay alert in the name.</p>
  </div>
  <div class="rail">
    <div class="rail-track"></div>
    <div class="rail-steps">
      <div class="rail-step active"><div class="rail-node">01</div><div class="rname">Booking</div><div class="rdesc">Intake &amp; charge recorded</div></div>
      <div class="rail-step active"><div class="rail-node">02</div><div class="rname">Inquest</div><div class="rdesc">Preliminary review of charges</div></div>
      <div class="rail-step"><div class="rail-node">03</div><div class="rname">Arraignment</div><div class="rdesc">Formal charge &amp; plea</div></div>
      <div class="rail-step"><div class="rail-node">04</div><div class="rname">Trial</div><div class="rdesc">Case heard in court</div></div>
      <div class="rail-step"><div class="rail-node">05</div><div class="rname">Resolution</div><div class="rdesc">Release, transfer, or sentencing</div></div>
    </div>
  </div>
</section>

<section class="how-section" id="how">
  <div class="section-head">
    <div class="kicker">Using TAYA</div>
    <h2>Three steps, no account needed</h2>
  </div>
  <div class="how-grid">
    <div class="how-card"><div class="how-num">STEP 1</div><h3>Get a tracking code</h3><p>The detention facility issues a unique code to a detainee's family or counsel when a case is opened.</p></div>
    <div class="how-card"><div class="how-num">STEP 2</div><h3>Enter it above</h3><p>No login, no personal data required — the code alone unlocks the current phase, timeline, and any flags on the case.</p></div>
    <div class="how-card"><div class="how-num">STEP 3</div><h3>Watch for alerts</h3><p>If a case runs past its expected phase duration, the timeline marks it clearly so you know when to follow up.</p></div>
  </div>
</section>

<section class="rights-section" id="rights">
  <div class="rights-card">
    <div>
      <h3>Know your rights during detention</h3>
      <p>TAYA is built around rights guaranteed under Philippine law. This page is a starting point, not legal advice — for case-specific guidance, contact the Public Attorney's Office or your counsel of record.</p>
    </div>
    <ul class="rights-list">
      <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>Right to be informed of the charges against you</li>
      <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>Right to counsel at every stage of custody</li>
      <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>Right to inquest within the period fixed by law</li>
      <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>Right against prolonged detention without charge</li>
    </ul>
  </div>
</section>
@endsection
