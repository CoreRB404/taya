<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>TAYA — Estimate Detention Duration</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;600;700&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
  <style>
    :root{ --bg-deep:#0d1128; --bg-mid:#1a2151; --bg-card:#141a35; --border:#2a3363; --border-soft:#232b52; --text-primary:#f5f7fa; --text-secondary:#a2ade0; --text-muted:#6b76a8; --accent-cyan:#22d3ee; --accent-blue:#3b82f6; --accent-gradient:linear-gradient(90deg,var(--accent-cyan),var(--accent-blue)); }
    *{box-sizing:border-box;} body{ margin:0; background:linear-gradient(160deg, var(--bg-mid) 0%, var(--bg-deep) 65%); color:var(--text-primary); font-family:'IBM Plex Sans',sans-serif; -webkit-font-smoothing:antialiased; }
    h1,h2,h3{ font-family:'Space Grotesk',sans-serif; letter-spacing:-0.01em; margin:0; }
    header{ display:flex; align-items:center; justify-content:space-between; padding:20px 48px; border-bottom:1px solid var(--border-soft); background:rgba(13,17,40,0.35); backdrop-filter:blur(6px); position:sticky; top:0; z-index:50; }
    .brand{ display:flex; align-items:center; gap:14px; }
    .brand-icon{ width:42px; height:42px; border-radius:11px; background:linear-gradient(145deg,#5b6ee8,#3b4cc4); display:flex; align-items:center; justify-content:center; box-shadow:0 4px 18px rgba(91,110,232,0.45); }
    .brand-name{ font-size:19px; font-weight:700; line-height:1.1; }
    nav{ display:flex; align-items:center; gap:30px; }
    nav a.navlink{ font-size:14px; color:var(--text-secondary); text-decoration:none; }
    .btn-ghost{ display:inline-flex; align-items:center; gap:7px; padding:11px 20px; border-radius:100px; background:rgba(255,255,255,0.04); border:1px solid var(--border); color:var(--text-secondary) !important; font-size:14px; font-weight:500; text-decoration:none; }

    .hero{ max-width:680px; margin:0 auto; text-align:center; padding:76px 24px 0; }
    .eyebrow{ display:inline-flex; align-items:center; gap:8px; font-size:12.5px; letter-spacing:0.08em; text-transform:uppercase; color:var(--accent-cyan); font-weight:500; padding:6px 14px; border:1px solid rgba(34,211,238,0.35); border-radius:100px; background:rgba(34,211,238,0.06); margin-bottom:22px; }
    .dot{ width:6px; height:6px; border-radius:50%; background:var(--accent-cyan); }
    h1.headline{ font-size:46px; font-weight:800; line-height:1.15; letter-spacing:-0.02em; margin:0 0 16px; }
    .headline .grad{ background:var(--accent-gradient); -webkit-background-clip:text; background-clip:text; color:transparent; }
    .hero p.sub{ font-size:15.5px; color:var(--text-secondary); line-height:1.6; max-width:500px; margin:0 auto; }

    .wrap{ max-width:760px; margin:44px auto 0; padding:0 24px; }
    .card{ background:var(--bg-card); border:1px solid var(--border); border-radius:18px; padding:36px 40px 32px; box-shadow:0 20px 50px -20px rgba(0,0,0,0.4); }
    .form-grid{ display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:22px; }
    .select-box, .date-box{ display:flex; align-items:center; gap:10px; background:#1b2244; border:1px solid var(--border); border-radius:11px; padding:14px 16px; position:relative; }
    select, input[type="date"]{ background:none; border:none; outline:none; color:var(--text-primary); font-size:14px; width:100%; }
    /* Native select appearance tweaks for better contrast and overlay behavior */
    select{ appearance:none; -webkit-appearance:none; -moz-appearance:none; z-index:1000; position:relative; color:var(--text-primary); background:transparent; }
    select:focus{ box-shadow:0 6px 24px rgba(11,15,30,0.45); }
    /* Option styling (supported on most modern browsers) */
    select option{ background:var(--bg-card); color:var(--text-primary); }
    /* Improve scrollbar styling inside option lists (WebKit-based) */
    @media all and (-webkit-min-device-pixel-ratio:0) {
      select::-webkit-scrollbar{ width:12px }
      select::-webkit-scrollbar-thumb{ background: rgba(255,255,255,0.06); border-radius:8px }
    }
    select::-ms-expand{ display:none; }
    .btn-primary{ display:inline-flex; align-items:center; justify-content:center; gap:9px; background:var(--accent-gradient); border:none; border-radius:11px; color:#08121c; font-weight:700; font-size:14.5px; padding:14px 30px; cursor:pointer; transition:transform .12s ease, box-shadow .12s ease; }
    .btn-primary:hover{ transform:translateY(-3px); box-shadow:0 12px 30px rgba(59,130,246,0.18); }
    .track-prompt{ display:flex; align-items:center; justify-content:space-between; gap:16px; margin-top:24px; padding-top:22px; border-top:1px solid var(--border-soft); }
    .btn-track{ display:inline-flex; align-items:center; gap:8px; padding:11px 20px; border-radius:11px; background:rgba(255,255,255,0.04); border:1px solid var(--border); color:var(--text-primary) !important; font-size:13.5px; font-weight:600; text-decoration:none; }

    .result-panel{ display:none; margin-top:24px; transition:opacity .18s ease, transform .18s ease; }
    .result-panel.show{ display:block; opacity:1; transform:translateY(0); animation:fadein .35s ease; }
    @keyframes fadein{ from{ opacity:0; transform:translateY(6px);} to{ opacity:1; transform:translateY(0);} }
    .stat-grid{ display:grid; grid-template-columns:repeat(2,1fr); gap:18px 22px; padding:18px 0; border-top:1px solid var(--border-soft); border-bottom:1px solid var(--border-soft); margin-bottom:20px; }
    .recommendation{ display:flex; gap:13px; align-items:flex-start; border-radius:13px; padding:16px 18px; margin-bottom:22px; }
    .timeline{ display:flex; flex-direction:column; gap:9px; }

    .helper-note{ margin-top:14px; color:var(--text-muted); display:flex; gap:10px; align-items:center; max-width:760px; margin-left:auto; margin-right:auto; padding:12px 24px; }

    .section-head{ max-width:760px; margin:44px auto 6px; padding:0 24px; color:var(--text-primary); }
    .how-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:18px; max-width:980px; margin:18px auto 40px; padding:0 24px; }
    .how-card{ background:rgba(255,255,255,0.03); border:1px solid var(--border-soft); border-radius:12px; padding:18px; }
    .rights-card{ max-width:980px; margin:12px auto 80px; padding:18px 24px; background:rgba(255,255,255,0.02); border:1px solid var(--border-soft); border-radius:12px; }

    footer{ display:flex; justify-content:space-between; align-items:center; gap:12px; padding:26px 48px; border-top:1px solid var(--border-soft); color:var(--text-muted); }
    @media(max-width:820px){ header{ padding:16px 20px; } h1.headline{ font-size:30px; } .form-grid{ grid-template-columns:1fr; } .how-grid{ grid-template-columns:1fr; } footer{ flex-direction:column; align-items:flex-start; } }
    .field label{ display:block; font-size:13px; color:var(--text-secondary); margin-bottom:8px; }
    .err-msg{ color:#ff8b8b; font-size:13px; margin-top:8px; display:none; }
    .field.error .err-msg{ display:block; }
    .t-row{ display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border-radius:8px; background:rgba(255,255,255,0.01); margin-bottom:8px; transition:background .12s ease, transform .12s ease; }
    .t-row.next{ transform:translateX(0); }
    .t-left{ display:flex; gap:12px; align-items:center; }
    .t-index{ width:40px; height:40px; border-radius:8px; background:rgba(255,255,255,0.03); display:flex; align-items:center; justify-content:center; font-weight:700; color:var(--text-secondary); }
    .t-name{ font-weight:700; }
    .t-due{ color:var(--text-muted); font-size:13px; }
    .t-badge{ padding:6px 10px; border-radius:999px; font-size:12px; font-weight:700; }
    .t-badge.completed{ background:#103928; color:#6ef1b2; }
    .t-badge.next{ background:#2b3056; color:var(--accent-cyan); }
    .t-badge.upcoming{ background:rgba(255,255,255,0.03); color:var(--text-secondary); }
    .recommendation.ok{ border-left:4px solid #2bd37a; }
    .recommendation.warn{ border-left:4px solid #fbbf24; }
    .recommendation.bad{ border-left:4px solid #fb7185; }
  </style>
</head>
<body>
  <header>
    <div class="brand">
      <div class="brand-icon" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 3v4"/><path d="M12 21v-8"/><path d="M5 8l7-1 7 1"/><path d="M5 8l-2 6a3 3 0 0 0 6 0Z"/><path d="M19 8l2 6a3 3 0 0 1-6 0Z"/></svg>
      </div>
      <div>
        <div class="brand-name">TAYA</div>
        <div class="brand-tag">Detainee Rights & Overstay Alert System</div>
      </div>
    </div>
    <nav>
      <a class="navlink current" href="#">Estimate Duration</a>
      <a class="navlink" href="{{ route('tracking.lookup') }}">Track a Detainee</a>
      <a class="navlink" href="#rights">Know Your Rights</a>
      <a class="navlink" href="#how">How It Works</a>
      <a class="btn-ghost" href="{{ route('login') }}">Staff Login →</a>
    </nav>
  </header>

  <main>
    <section class="hero">
      <span class="eyebrow"><span class="dot"></span>Estimate Detention Duration</span>
      <h1 class="headline">Project a detention timeline <span class="grad">without a tracking code</span></h1>
      <p class="sub">Choose a case and commitment date to generate an expected penalty estimate and phase timeline.</p>
    </section>

    <div class="wrap">
      <div class="card">
        <div class="form-grid">
          <div class="field" id="case-field">
            <label for="case-select">Choose a case</label>
            <div class="select-box">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
              <select id="case-select" aria-label="Case type">
                <option value="">Select a case</option>
                @if(!empty($cases))
                  @foreach($cases as $case)
                    <option value="{{ $case['id'] }}" data-years="{{ $case['years'] }}" data-months="{{ $case['months'] ?? 0 }}" data-desc="{{ e($case['label']) }}" data-penalty="{{ e($case['penalty_display'] ?? '') }}">{{ $case['label'] }}</option>
                  @endforeach
                @else
                  <option value="u_entry" data-years="2" data-months="6" data-desc="Unlawful entry / trespass" data-penalty="2 yrs 6 mos">Unlawful Entry — 2 yrs 6 mos</option>
                  <option value="resist" data-years="1" data-months="0" data-desc="Resistance to arrest" data-penalty="1 yr">Resistance to Arrest — 1 yr</option>
                  <option value="serious" data-years="6" data-months="0" data-desc="Serious physical injury" data-penalty="6 yrs">Serious Physical Injury — 6 yrs</option>
                @endif
              </select>
            </div>
            <div class="case-desc" id="case-desc"></div>
            <div class="err-msg">Please select a case type.</div>
          </div>

          <div class="field" id="date-field">
            <label for="commit-date">Commitment date</label>
            <div class="date-box">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
              <input type="date" id="commit-date" aria-label="Commitment date">
            </div>
            <div class="err-msg">Please enter a commitment date.</div>
          </div>
        </div>

        <button class="btn-primary" id="estimate-btn" type="button">Estimate</button>

        <div class="result-panel" id="result-panel" aria-live="polite">
          <div class="result-head">
            <h3>Estimate Result</h3>
            <span class="case-tag" id="res-case-tag">—</span>
          </div>

          <div class="stat-grid">
            <div class="stat">
              <div class="stat-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 7v6c0 5 4 9 9 9s9-4 9-9V7l-9-5z"/></svg></div>
              <div><div class="k">Max Penalty</div><div class="v" id="res-penalty">—</div></div>
            </div>
            <div class="stat">
              <div class="stat-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
              <div><div class="k">Days Detained</div><div class="v" id="res-days">—</div></div>
            </div>
            <div class="stat">
              <div class="stat-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
              <div><div class="k">Remaining Duration</div><div class="v" id="res-remaining">—</div></div>
            </div>
            <div class="stat">
              <div class="stat-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
              <div><div class="k">Status</div><div class="v" id="res-status">—</div></div>
            </div>
          </div>

          <div class="recommendation" id="res-recommendation">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <div>
              <div class="k">Recommendation</div>
              <p id="res-recommendation-text">—</p>
            </div>
          </div>

          <div class="panel-title" style="margin-bottom:10px; color:var(--text-secondary); display:flex; gap:8px; align-items:center;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            Projected Phases
          </div>
          <div class="timeline" id="res-timeline"></div>
        </div>

        <div class="track-prompt">
          <p>Already have a tracking code? <strong>Look up an existing case's live status instead.</strong></p>
          <a class="btn-track" href="{{ route('tracking.lookup') }}">
            Track a Detainee
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </div>
      </div>

      <div class="helper-note">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        Pick a case and date above, then hit Estimate — your result appears right here, with a projected phase timeline below it.
      </div>
    </div>

    <section class="how-section" id="how">
      <div class="section-head">
        <div class="kicker">Using the estimator</div>
        <h2>Three inputs, one clear picture</h2>
      </div>
      <div class="how-grid">
        <div class="how-card">
          <div class="how-num">STEP 1</div>
          <h3>Pick the charge</h3>
          <p>Select the case type from the list of covered offenses.</p>
        </div>
        <div class="how-card">
          <div class="how-num">STEP 2</div>
          <h3>Enter commitment date</h3>
          <p>The date custody began — this anchors every phase deadline that follows.</p>
        </div>
        <div class="how-card">
          <div class="how-num">STEP 3</div>
          <h3>Read the estimate</h3>
          <p>Get max penalty, days already served, remaining duration, and a plain-language recommendation.</p>
        </div>
      </div>
    </section>

    <section class="rights-section" id="rights">
      <div class="section-head">
        <div class="kicker">Before you file or wait</div>
        <h2>Know your rights during detention</h2>
      </div>
      <div class="rights-card">
        <div>
          <p>TAYA is built around rights guaranteed under Philippine law. This page is a starting point, not legal advice — for case-specific guidance, contact the Public Attorney's Office or your counsel of record.</p>
        </div>
        <ul class="rights-list" style="margin-top:12px; color:var(--text-secondary);">
          <li style="margin:8px 0;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M20 6 9 17l-5-5"/></svg> Right to be informed of the charges against you</li>
          <li style="margin:8px 0;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M20 6 9 17l-5-5"/></svg> Right to counsel at every stage of custody</li>
          <li style="margin:8px 0;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M20 6 9 17l-5-5"/></svg> Right to inquest within the period fixed by law</li>
          <li style="margin:8px 0;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;"><path d="M20 6 9 17l-5-5"/></svg> Right against prolonged detention without charge</li>
        </ul>
      </div>
    </section>
  </main>

  <footer>
    <div class="footer-brand">© 2026 TAYA System. All rights reserved.</div>
    <div class="footer-links">
      <a href="#" style="color:var(--text-secondary); margin-right:14px;">Privacy Policy</a>
      <a href="#" style="color:var(--text-secondary); margin-right:14px;">Data Privacy Act Notice</a>
      <a href="#" style="color:var(--text-secondary); margin-right:14px;">Contact BJMP</a>
      <a class="btn-ghost" href="{{ route('login') }}">Staff Login →</a>
    </div>
  </footer>

  <script>
    const PHASES = [
      { name: 'Preliminary Investigation', offsetDays: 15 },
      { name: 'Filing of Information',      offsetDays: 25 },
      { name: 'Arraignment',                offsetDays: 55 },
      { name: 'Pre-Trial',                  offsetDays: 75 }
    ];
    function fmtDate(d){ return d.toLocaleDateString('en-US', { month:'long', day:'numeric', year:'numeric' }); }
    function ymd(y,m,d){ return new Date(y, m, d); }
    function daysBetween(a,b){ return Math.round((b - a) / 86400000); }
    function fmtDuration(totalDays){ if(totalDays <= 0) return '0 days'; const y = Math.floor(totalDays/365); const m = Math.floor((totalDays%365)/30); const d = (totalDays%365)%30; let parts=[]; if(y) parts.push(y+' yr'+(y>1?'s':'')); if(m) parts.push(m+' mo'+(m>1?'s':'')); if(d || parts.length===0) parts.push(d+' day'+(d!==1?'s':'')); return parts.join(' '); }

    document.getElementById('case-select')?.addEventListener('change', function(){ const opt = this.selectedOptions[0]; document.getElementById('case-desc').textContent = opt?.dataset.desc || ''; });

    document.getElementById('estimate-btn')?.addEventListener('click', function(){
      const caseSelect = document.getElementById('case-select');
      const dateInput = document.getElementById('commit-date');
      const caseField = document.getElementById('case-field');
      const dateField = document.getElementById('date-field');

      let valid = true;
      if(!caseSelect.value){ caseField.classList.add('error'); valid = false; } else caseField.classList.remove('error');
      if(!dateInput.value){ dateField.classList.add('error'); valid = false; } else dateField.classList.remove('error');
      if(!valid) return;

      const years = parseInt(caseSelect.selectedOptions[0].dataset.years, 10)||0;
      const months = parseInt(caseSelect.selectedOptions[0].dataset.months,10)||0;
      const [cy,cm,cd] = dateInput.value.split('-').map(Number);
      const commitDate = ymd(cy, cm-1, cd);
      const today = new Date(); today.setHours(0,0,0,0);

      const daysDetained = Math.max(0, daysBetween(commitDate, today));
      const maxPenaltyDays = years * 365 + months * 30;
      const remainingDays = Math.max(0, maxPenaltyDays - daysDetained);
      const overPenalty = daysDetained > maxPenaltyDays;

      document.getElementById('res-case-tag').textContent = caseSelect.selectedOptions[0].textContent.trim();
      document.getElementById('res-penalty').textContent = (years ? years + ' yrs' : '') + (months ? ' ' + months + ' mos' : '');
      document.getElementById('res-days').textContent = daysDetained + ' days';
      document.getElementById('res-remaining').textContent = overPenalty ? 'Exceeded' : fmtDuration(remainingDays);
      document.getElementById('res-status').textContent = overPenalty ? 'Overdue' : 'Within range';

      const rec = document.getElementById('res-recommendation');
      const recText = document.getElementById('res-recommendation-text');
      rec.classList.remove('ok','warn','bad');
      if(overPenalty){ rec.classList.add('bad'); recText.textContent = 'Detention has run past the maximum penalty period for this offense. This case needs immediate review — contact the facility or counsel of record right away.'; }
      else if(remainingDays < maxPenaltyDays * 0.1){ rec.classList.add('warn'); recText.textContent = 'Case is approaching the maximum detention period. Verify the court schedule soon.'; }
      else { rec.classList.add('ok'); recText.textContent = 'Case is within the expected detention period. Continue monitoring status.'; }

      const timeline = document.getElementById('res-timeline'); timeline.innerHTML = '';
      let nextAssigned = false;
      PHASES.forEach((phase, i) => {
        const dueDate = new Date(commitDate); dueDate.setDate(dueDate.getDate() + phase.offsetDays);
        const isPast = dueDate < today;
        let rowClass = 'upcoming', badgeClass = 'upcoming', badgeText = 'Upcoming';
        if(isPast){ rowClass = 'completed'; badgeClass = 'completed'; badgeText = 'Completed'; }
        else if(!nextAssigned){ rowClass = 'next'; badgeClass = 'next'; badgeText = 'Next up'; nextAssigned = true; }
        const daysFromNow = daysBetween(today, dueDate);
        const row = document.createElement('div');
        row.className = 't-row ' + rowClass;
        row.innerHTML = `\n        <div class="t-left">\n          <div class="t-index">${String(i+1).padStart(2,'0')}</div>\n          <div>\n            <div class="t-name">${phase.name}</div>\n            <div class="t-due">Due ${fmtDate(dueDate)}${isPast ? '' : ' · ' + daysFromNow + ' days from now'}</div>\n          </div>\n        </div>\n        <span class="t-badge ${badgeClass}">${badgeText}</span>`;
        timeline.appendChild(row);
      });

      document.getElementById('result-panel').classList.add('show');
      document.getElementById('result-panel').scrollIntoView({ behavior:'smooth', block:'nearest' });
    });
  </script>
</body>
</html>
