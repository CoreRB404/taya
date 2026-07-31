<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TAYA — Detainee Rights & Overstay Alert System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --bg-deep:#0d1128; --bg-mid:#1a2151; --bg-card:#141a35; --bg-card-soft:#1a2044;
    --border:#2a3363; --border-soft:#232b52;
    --text-primary:#f5f7fa; --text-secondary:#a2ade0; --text-muted:#6b76a8;
    --accent-cyan:#22d3ee; --accent-blue:#3b82f6;
    --accent-gradient:linear-gradient(90deg,var(--accent-cyan),var(--accent-blue));
    --green:#34d399; --amber:#f5b942; --red:#f87171; --slate:#8891c4;
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
    color:var(--text-primary); font-family:'IBM Plex Sans', sans-serif;
    -webkit-font-smoothing:antialiased;
  }
  h1,h2,h3{ font-family:'Space Grotesk', sans-serif; letter-spacing:-0.01em; margin:0; }
  .mono{ font-family:'IBM Plex Mono', monospace; }
  a{ color:inherit; }

  header{
    display:flex; align-items:center; justify-content:space-between;
    padding:20px 48px; border-bottom:1px solid var(--border-soft);
    background:rgba(13,17,40,0.35); backdrop-filter:blur(6px);
    position:sticky; top:0; z-index:50;
  }
  .brand{ display:flex; align-items:center; gap:14px; }
  .brand-icon{
    width:42px; height:42px; border-radius:11px;
    background:linear-gradient(145deg,#5b6ee8,#3b4cc4);
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 4px 18px rgba(91,110,232,0.45); flex-shrink:0;
  }
  .brand-name{ font-size:19px; font-weight:700; line-height:1.1; }
  .brand-tag{ font-size:12.5px; color:var(--text-muted); margin-top:2px; }
  nav{ display:flex; align-items:center; gap:30px; }
  nav a.navlink{ font-size:14px; color:var(--text-secondary); text-decoration:none; }
  nav a.navlink:hover{ color:var(--text-primary); }
  .header-actions{ display:flex; align-items:center; gap:10px; }

  .btn-primary{
    display:inline-flex; align-items:center; justify-content:center; gap:9px;
    background:var(--accent-gradient); border:none; border-radius:11px;
    color:#08121c; font-weight:700; font-size:14px; padding:11px 22px; cursor:pointer; white-space:nowrap;
    text-decoration:none; transition:filter .15s ease, transform .15s ease;
  }
  .btn-primary:hover{ filter:brightness(1.08); transform:translateY(-1px); }
  .btn-primary.full{ width:100%; }
  .btn-primary.small{ padding:0 26px; height:48px; border-radius:11px; }

  .btn-ghost{
    display:inline-flex; align-items:center; gap:7px;
    padding:11px 20px; border-radius:100px;
    background:rgba(255,255,255,0.04); border:1px solid var(--border);
    color:var(--text-secondary) !important; font-size:14px; font-weight:500;
    text-decoration:none; white-space:nowrap;
    transition:background .15s ease, border-color .15s ease, color .15s ease;
  }
  .btn-ghost:hover{ background:rgba(255,255,255,0.07); border-color:rgba(34,211,238,0.4); color:var(--text-primary) !important; }

  .hero{ max-width:760px; margin:0 auto; text-align:center; padding:80px 24px 0; }
  .eyebrow{
    display:inline-flex; align-items:center; gap:8px; font-size:12.5px; letter-spacing:0.08em;
    text-transform:uppercase; color:var(--accent-cyan); font-weight:500;
    padding:6px 14px; border:1px solid rgba(34,211,238,0.35); border-radius:100px;
    background:rgba(34,211,238,0.06); margin-bottom:24px;
  }
  .dot{ width:6px; height:6px; border-radius:50%; background:var(--accent-cyan); }
  h1.headline{ font-size:44px; font-weight:700; line-height:1.15; margin:0 0 16px; }
  .headline .grad{ background:var(--accent-gradient); -webkit-background-clip:text; background-clip:text; color:transparent; }
  .hero p.sub{ font-size:16.5px; color:var(--text-secondary); line-height:1.6; max-width:560px; margin:0 auto; }

  .wrap{ max-width:640px; margin:0 auto; display:flex; flex-direction:column; gap:24px; }
  .search-wrap{ margin-top:44px; padding:0 24px; }
  .card{ background:var(--bg-card); border:1px solid var(--border); border-radius:18px; padding:32px 36px; }
  .card-label{ font-size:13.5px; font-weight:600; color:var(--text-secondary); margin-bottom:10px; display:block; }
  .input-row{ display:flex; gap:12px; }
  .code-input{
    flex:1; display:flex; align-items:center; gap:10px;
    background:#1b2244; border:1px solid var(--border); border-radius:11px; padding:14px 16px;
  }
  .code-input svg{ flex-shrink:0; color:var(--text-muted); }
  .code-input input{ background:none; border:none; outline:none; color:var(--text-primary); font-family:'IBM Plex Mono',monospace; font-size:14.5px; width:100%; }
  .code-input input::placeholder{ color:#565f8f; }
  .card-foot{ margin-top:20px; padding-top:18px; border-top:1px solid var(--border-soft); font-size:13px; color:var(--text-muted); text-align:center; line-height:1.7; }
  .card-foot a{ color:var(--accent-cyan); text-decoration:none; font-weight:500; }
  .divider{ border:none; border-top:1px solid var(--border-soft); margin:26px 0; }

  .form-head{ margin-bottom:20px; }
  .form-head h3{ font-size:16px; margin-bottom:4px; }
  .form-head p{ font-size:13px; color:var(--text-muted); margin:0; }
  .form-grid{ display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:16px; }
  .field label{ display:block; font-size:12.5px; font-weight:600; color:var(--text-secondary); margin-bottom:8px; }
  .select-box, .date-box{ display:flex; align-items:center; gap:10px; background:#1b2244; border:1px solid var(--border); border-radius:11px; padding:13px 16px; }
  select, input[type="date"]{ background:none; border:none; outline:none; color:var(--text-primary); font-family:'IBM Plex Sans',sans-serif; font-size:14px; width:100%; }
  select option{ background:#1b2244; }
  input[type="date"]::-webkit-calendar-picker-indicator{ filter:invert(0.6); cursor:pointer; }

  .result-head{ display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; }
  .result-head h3{ font-size:17px; }
  .case-tag{ font-size:12px; color:var(--accent-cyan); background:rgba(34,211,238,0.08); border:1px solid rgba(34,211,238,0.25); padding:5px 12px; border-radius:100px; font-weight:500; }
  .stat-grid{ display:grid; grid-template-columns:repeat(2,1fr); gap:20px 24px; padding:20px 0; border-top:1px solid var(--border-soft); border-bottom:1px solid var(--border-soft); margin-bottom:22px; }
  .stat{ display:flex; gap:12px; align-items:flex-start; }
  .stat-icon{ width:36px; height:36px; border-radius:9px; flex-shrink:0; background:rgba(59,130,246,0.1); display:flex; align-items:center; justify-content:center; color:var(--accent-cyan); }
  .stat .k{ font-size:12px; color:var(--text-muted); margin-bottom:3px; }
  .stat .v{ font-size:16px; font-weight:600; }
  .recommendation{ display:flex; gap:14px; align-items:flex-start; background:rgba(52,211,153,0.06); border:1px solid rgba(52,211,153,0.3); border-radius:13px; padding:18px 20px; }
  .recommendation svg{ flex-shrink:0; color:var(--green); margin-top:2px; }
  .recommendation .k{ font-size:12px; color:var(--green); font-weight:600; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:5px; }
  .recommendation p{ font-size:13.5px; color:var(--text-secondary); line-height:1.55; margin:0; }

  .panel-title{ font-size:15px; font-weight:600; margin-bottom:16px; display:flex; align-items:center; gap:8px; color:var(--text-secondary); }
  .panel-title svg{ color:var(--accent-cyan); }
  .timeline{ display:flex; flex-direction:column; gap:10px; }
  .t-row{ display:flex; align-items:center; justify-content:space-between; gap:16px; background:var(--bg-card-soft); border:1px solid var(--border-soft); border-radius:13px; padding:16px 20px; }
  .t-row.next{ border-color:rgba(59,130,246,0.4); background:linear-gradient(135deg, rgba(59,130,246,0.06), rgba(34,211,238,0.03)); }
  .t-left{ display:flex; align-items:center; gap:14px; }
  .t-index{ width:30px; height:30px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-family:'IBM Plex Mono',monospace; font-size:12px; font-weight:500; background:var(--bg-card); border:1px solid var(--border); color:var(--text-muted); }
  .t-row.next .t-index{ background:var(--accent-gradient); border-color:transparent; color:#08121c; font-weight:700; }
  .t-name{ font-size:14.5px; font-weight:600; margin-bottom:3px; }
  .t-due{ font-size:12.5px; color:var(--text-muted); }
  .t-badge{ font-size:11.5px; font-weight:600; padding:5px 12px; border-radius:100px; white-space:nowrap; letter-spacing:0.02em; }
  .t-badge.next{ color:var(--accent-cyan); background:rgba(34,211,238,0.1); }
  .t-badge.upcoming{ color:var(--slate); background:rgba(136,145,196,0.1); }

  .rail-section{ max-width:960px; margin:100px auto 0; padding:0 24px; }
  .section-head{ text-align:center; margin-bottom:56px; }
  .section-head .kicker{ font-size:12.5px; letter-spacing:0.08em; text-transform:uppercase; color:var(--text-muted); margin-bottom:10px; }
  .section-head h2{ font-size:27px; font-weight:600; margin:0 0 10px; }
  .section-head p{ color:var(--text-secondary); font-size:14.5px; max-width:480px; margin:0 auto; }
  .rail{ position:relative; padding:0 10px 10px; }
  .rail-track{ position:absolute; top:23px; left:34px; right:34px; height:2px; background:linear-gradient(90deg,var(--accent-cyan),var(--border) 85%); }
  .rail-steps{ display:flex; justify-content:space-between; position:relative; }
  .rail-step{ flex:1; display:flex; flex-direction:column; align-items:center; text-align:center; padding:0 6px; }
  .rail-node{ width:46px; height:46px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-family:'IBM Plex Mono',monospace; font-size:13px; font-weight:500; background:var(--bg-card); border:2px solid var(--border); color:var(--text-muted); z-index:2; margin-bottom:16px; }
  .rail-step.active .rail-node{ background:var(--accent-gradient); border-color:transparent; color:#08121c; font-weight:700; }
  .rail-step .rname{ font-size:13.5px; font-weight:600; margin-bottom:4px; }
  .rail-step .rdesc{ font-size:12px; color:var(--text-muted); line-height:1.5; max-width:120px; }
  .rail-step.active .rname{ color:var(--accent-cyan); }

  .how-section{ max-width:960px; margin:110px auto 0; padding:0 24px; }
  .how-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:22px; }
  .how-card{ background:var(--bg-card); border:1px solid var(--border); border-radius:16px; padding:28px 26px; }
  .how-num{ font-family:'IBM Plex Mono',monospace; font-size:12.5px; color:var(--accent-cyan); margin-bottom:14px; }
  .how-card h3{ font-size:16.5px; font-weight:600; margin:0 0 8px; }
  .how-card p{ font-size:13.5px; color:var(--text-secondary); line-height:1.6; margin:0; }

  .rights-section{ max-width:960px; margin:96px auto 0; padding:0 24px; }
  .rights-card{ background:linear-gradient(135deg, rgba(34,211,238,0.07), rgba(59,130,246,0.05)); border:1px solid rgba(59,130,246,0.28); border-radius:18px; padding:34px 38px; display:grid; grid-template-columns:1.1fr 1.4fr; gap:34px; align-items:center; }
  .rights-card h3{ font-size:19px; margin:0 0 10px; }
  .rights-card p{ color:var(--text-secondary); font-size:14px; line-height:1.65; margin:0; }
  .rights-list{ list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:12px; }
  .rights-list li{ display:flex; gap:10px; font-size:13.5px; color:var(--text-secondary); line-height:1.5; }
  .rights-list svg{ flex-shrink:0; margin-top:2px; color:var(--accent-cyan); }

  footer{ margin-top:110px; border-top:1px solid var(--border-soft); padding:36px 48px; display:flex; align-items:center; justify-content:space-between; }
  .footer-brand{ display:flex; align-items:center; gap:10px; font-size:13.5px; color:var(--text-muted); }
  .footer-links{ display:flex; gap:26px; font-size:13px; color:var(--text-muted); }
  .footer-links a{ text-decoration:none; }
  .footer-links a:hover{ color:var(--text-secondary); }

  @media(max-width:820px){
    header{ padding:16px 20px; }
    nav{ gap:14px; }
    nav a.navlink{ display:none; }
    h1.headline{ font-size:32px; }
    .card{ padding:24px 20px; }
    .input-row{ flex-direction:column; }
    .form-grid{ grid-template-columns:1fr; }
    .stat-grid{ grid-template-columns:1fr; }
    .t-row{ flex-direction:column; align-items:flex-start; gap:10px; }
    .rail-steps{ flex-wrap:wrap; row-gap:28px; }
    .rail-track{ display:none; }
    .rail-step{ flex:0 0 33%; }
    .how-grid{ grid-template-columns:1fr; }
    .rights-card{ grid-template-columns:1fr; }
    footer{ flex-direction:column; gap:14px; padding:28px 22px; text-align:center; }
  }
</style>
</head>
<body>

<header>
  <div class="brand">
    <div class="brand-icon">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 3v4"/><path d="M12 21v-8"/><path d="M5 8l7-1 7 1"/><path d="M5 8l-2 6a3 3 0 0 0 6 0Z"/><path d="M19 8l2 6a3 3 0 0 1-6 0Z"/></svg>
    </div>
    <div>
      <div class="brand-name">TAYA</div>
      <div class="brand-tag">Detainee Rights & Overstay Alert System</div>
    </div>
  </div>
  <nav>
    <a class="navlink" href="{{ route('tracking.lookup') }}">Track a Detainee</a>
    <a class="navlink" href="#rights">Know Your Rights</a>
    <a class="navlink" href="#how">How It Works</a>
    <div class="header-actions">
      <a class="btn-primary" href="{{ route('tracking.lookup') }}">Track a Detainee</a>
      <a class="btn-ghost" href="#">Staff Login →</a>
    </div>
  </nav>
</header>

<section class="hero">
  <span class="eyebrow"><span class="dot"></span>Built for transparency in detention</span>
  <h1 class="headline">Know exactly <span class="grad">where a case stands</span> — and what comes next.</h1>
  <p class="sub">TAYA gives families, counsel, and facility staff a shared, real-time view of detention status, so no one waits in the dark or overstays a phase that should have ended.</p>
</section>

<div class="search-wrap" id="track">
  <div class="wrap">

    <div class="card">
      <div class="form-head">
        <h3>Estimate Detention Duration</h3>
        <p>Project the expected timeline for a case based on its charge and commitment date.</p>
      </div>
      <div class="form-grid">
        <div class="field">
          <label>Choose a case</label>
          <div class="select-box">
            <select id="caseSelect">
              <option value="" selected disabled>Select a case</option>
              @foreach($cases as $case)
                <option value="{{ $case['id'] }}"
                        data-label="{{ $case['label'] }}"
                        data-penalty="{{ $case['penalty_display'] }}"
                        data-years="{{ $case['max_penalty_years'] }}"
                        data-months="{{ $case['max_penalty_months'] }}">
                  {{ $case['label'] }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="field">
          <label>Commitment date</label>
          <div class="date-box"><input id="commitmentDate" type="date"></div>
        </div>
      </div>
      <button class="btn-primary full" type="button" onclick="runEstimate()">Estimate</button>
    </div>

    <div class="card" id="estimate-empty" style="display:block;">
      <div class="result-head">
        <h3>Estimate Result</h3>
      </div>
      <p style="margin:0; color:var(--text-secondary); line-height:1.7;">Choose a case and commitment date to generate a detention estimate.</p>
    </div>

    <div class="card" id="estimate-result" style="display:none;">
      <div class="result-head">
        <h3>Estimate Result</h3>
        <span class="case-tag" id="resultCaseTag">Case</span>
      </div>
      <div class="stat-grid">
        <div class="stat">
          <div class="stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 7v6c0 5 4 9 9 9s9-4 9-9V7l-9-5z"/></svg></div>
          <div><div class="k">Max Penalty</div><div class="v" id="resultMaxPenalty">--</div></div>
        </div>
        <div class="stat">
          <div class="stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
          <div><div class="k">Days Detained</div><div class="v" id="resultDaysDetained">--</div></div>
        </div>
        <div class="stat">
          <div class="stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
          <div><div class="k">Remaining Duration</div><div class="v" id="resultRemainingDuration">--</div></div>
        </div>
        <div class="stat">
          <div class="stat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
          <div><div class="k">Case</div><div class="v" id="resultStatus">--</div></div>
        </div>
      </div>
      <div class="recommendation">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        <div>
          <div class="k">Recommendation</div>
          <p id="resultRecommendation">Select a case and commitment date to view the estimate summary.</p>
        </div>
      </div>
      <div class="panel-title" style="margin-top:24px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Projected Phases
      </div>
      <div class="timeline" id="phasesList"></div>
    </div>

  </div>
</div>

<section class="rail-section">
  <div class="section-head">
    <div class="kicker">What a tracking code shows</div>
    <h2>Every case moves through five phases</h2>
    <p>TAYA flags a case the moment it runs past the expected time in any phase — that's the "overstay alert" in the name.</p>
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
    <div class="how-card"><div class="how-num">STEP 1</div><h3>Get a tracking code</h3><p>The detention facility issues a unique code to a detainee's family or counsel of record when a case is opened.</p></div>
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

<footer>
  <div class="footer-brand">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 3v4"/><path d="M12 21v-8"/></svg>
    © 2026 TAYA System. All rights reserved.
  </div>
  <div class="footer-links">
    <a href="#">Privacy Policy</a>
    <a href="#">Data Privacy Act Notice</a>
    <a href="#">Contact BJMP</a>
    <a class="btn-ghost" href="#">Staff Login →</a>
  </div>
</footer>

<script>
  function runEstimate() {
    const caseSelect = document.getElementById('caseSelect');
    const commitmentDate = document.getElementById('commitmentDate');
    const emptyState = document.getElementById('estimate-empty');
    const resultBlock = document.getElementById('estimate-result');
    const resultCaseTag = document.getElementById('resultCaseTag');
    const resultMaxPenalty = document.getElementById('resultMaxPenalty');
    const resultDaysDetained = document.getElementById('resultDaysDetained');
    const resultRemainingDuration = document.getElementById('resultRemainingDuration');
    const resultStatus = document.getElementById('resultStatus');
    const resultRecommendation = document.getElementById('resultRecommendation');
    const phasesList = document.getElementById('phasesList');

    if (!caseSelect.value || !commitmentDate.value) {
      emptyState.style.display = 'block';
      resultBlock.style.display = 'none';
      return;
    }

    const selectedOption = caseSelect.selectedOptions[0];
    const penaltyDisplay = selectedOption.dataset.penalty || 'Not set';
    const years = Number(selectedOption.dataset.years || 0);
    const months = Number(selectedOption.dataset.months || 0);
    const commitment = new Date(commitmentDate.value);
    const today = new Date();
    const daysDetained = Math.max(0, Math.floor((today - commitment) / (1000 * 60 * 60 * 24)));
    const totalPenaltyDays = years * 365 + months * 30;
    const remainingDays = Math.max(0, totalPenaltyDays - daysDetained);

    const formatDays = (days) => {
      const yearsLeft = Math.floor(days / 365);
      const monthsLeft = Math.floor((days % 365) / 30);
      const daysLeft = days % 30;
      const parts = [];
      if (yearsLeft) parts.push(`${yearsLeft} yr${yearsLeft > 1 ? 's' : ''}`);
      if (monthsLeft) parts.push(`${monthsLeft} mo${monthsLeft > 1 ? 's' : ''}`);
      if (daysLeft) parts.push(`${daysLeft} day${daysLeft > 1 ? 's' : ''}`);
      return parts.length ? parts.join(' ') : '0 days';
    };

    const addDays = (date, days) => {
      const next = new Date(date);
      next.setDate(next.getDate() + days);
      return next;
    };

    const formatter = new Intl.DateTimeFormat('en', { month: 'short', day: 'numeric', year: 'numeric' });
    const phases = [
      { name: 'Preliminary Investigation', days: 15 },
      { name: 'Filing of Information', days: 25 },
      { name: 'Arraignment', days: 30 },
      { name: 'Pre-Trial', days: 20 },
    ];

    phasesList.innerHTML = phases.map((phase, index) => {
      const dueDate = formatter.format(addDays(commitment, phase.days));
      const isNext = index === 0;
      return `
        <div class="t-row${isNext ? ' next' : ''}">
          <div class="t-left">
            <div class="t-index">${String(index + 1).padStart(2, '0')}</div>
            <div>
              <div class="t-name">${phase.name}</div>
              <div class="t-due">Due ${dueDate} · ${phase.days} days from commitment</div>
            </div>
          </div>
          <span class="t-badge${isNext ? ' next' : ' upcoming'}">${isNext ? 'Next up' : 'Upcoming'}</span>
        </div>`;
    }).join('');

    resultCaseTag.textContent = selectedOption.dataset.label || 'Case';
    resultMaxPenalty.textContent = penaltyDisplay;
    resultDaysDetained.textContent = `${daysDetained} day${daysDetained === 1 ? '' : 's'}`;
    resultRemainingDuration.textContent = formatDays(remainingDays);
    resultStatus.textContent = remainingDays > 0 ? 'Within Range' : 'Review Needed';
    resultRecommendation.textContent = remainingDays > 0
      ? 'Case remains within the expected detention range. Continue monitoring and verify the next court schedule.'
      : 'The case appears to have exceeded the projected range. Review the schedule and case status promptly.';

    emptyState.style.display = 'none';
    resultBlock.style.display = 'block';
  }
</script>
</body>
</html>
