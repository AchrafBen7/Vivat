<style>
    .vp-wrap { display:flex; flex-direction:column; gap:20px; }
    .vp-hero { position:relative; overflow:hidden; border-radius:24px; padding:24px; color:#fff; background:#004241; }
    .vp-hero-inner { position:relative; display:flex; align-items:center; gap:16px; }
    .vp-hero-box { flex-shrink:0; min-width:180px; padding:12px 16px; border-radius:16px; background:rgba(255,255,255,0.12); backdrop-filter:blur(8px); }
    .vp-hero-box-step { font-size:11px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:rgba(255,255,255,0.72); }
    .vp-hero-box-title { margin-top:4px; font-size:18px; font-weight:700; line-height:1.1; }
    .vp-hero-text p { margin-top:4px; font-size:14px; color:rgba(255,255,255,0.68); }
    .vp-hero-circle { position:absolute; border-radius:50%; background:rgba(255,255,255,0.05); pointer-events:none; }

    .vp-tip { display:grid; grid-template-columns:42px 1fr; gap:14px; align-items:flex-start; border-radius:16px; padding:18px 20px; background:#F7FAF9; border:1px solid #D6E1DD; }
    .vp-tip-icon { width:42px; height:42px; display:flex; align-items:center; justify-content:center; border-radius:14px; background:#FFF0B6; color:#004241; flex-shrink:0; }
    .vp-tip h4 { font-size:14px; font-weight:700; color:#004241; }
    .vp-tip p { margin-top:4px; font-size:13px; line-height:1.55; color:rgba(0,66,65,0.68); }

    .vp-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
    .vp-stat { border-radius:16px; padding:20px; }
    .vp-stat-val { font-size:30px; font-weight:700; }
    .vp-stat-label { margin-top:4px; font-size:12px; font-weight:500; }

    .vp-filters { display:grid; grid-template-columns:2fr 1fr; gap:16px; }
    .vp-input, .vp-select { width:100%; border:1px solid #D6E1DD; border-radius:14px; padding:12px 14px; font-size:14px; color:#004241; background:#fff; }
    .vp-input:focus, .vp-select:focus { outline:none; border-color:#4C807C; box-shadow:0 0 0 3px rgba(76,128,124,0.12); }

    .vp-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:20px; }
    .vp-card { overflow:hidden; border-radius:18px; background:#fff; border:1px solid #D6E1DD; }
    .vp-cover { width:100%; height:210px; object-fit:cover; display:block; background:#EBF1EF; }
    .vp-card-body { padding:20px; }
    .vp-badges { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
    .vp-badge { display:inline-flex; align-items:center; border-radius:10px; padding:4px 10px; font-size:12px; font-weight:700; }
    .vp-title { margin-top:10px; font-size:17px; font-weight:700; line-height:1.3; color:#004241; }
    .vp-meta { margin-top:8px; display:flex; align-items:center; flex-wrap:wrap; gap:8px; font-size:12px; color:rgba(0,66,65,0.45); }
    .vp-text { margin-top:10px; font-size:13px; line-height:1.6; color:rgba(0,66,65,0.62); }
    .vp-topic { margin-top:12px; border-radius:12px; padding:10px 12px; background:#F7FAF9; font-size:12px; color:#004241; }
    .vp-author { margin-top:12px; border-radius:12px; padding:10px 12px; background:#F7FAF9; font-size:12px; color:#004241; }
    .vp-actions { margin-top:16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .vp-btn { display:inline-flex; align-items:center; justify-content:center; border:none; border-radius:12px; padding:10px 14px; font-size:12px; font-weight:700; cursor:pointer; text-decoration:none; transition:background .15s; }
    .vp-btn-primary { color:#fff; background:#004241; }
    .vp-btn-primary:hover { background:#003130; }
    .vp-btn-secondary { color:#004241; background:#EBF1EF; }
    .vp-btn-secondary:hover { background:#DEE7E4; }
    .vp-btn-warning { color:#6b5200; background:#FFF0B6; }
    .vp-btn-warning:hover { background:#f6e39a; }
    .vp-empty { border-radius:18px; border:1px dashed #C9D8D4; background:#fff; padding:48px 24px; text-align:center; color:rgba(0,66,65,0.5); }

    @media(max-width:1024px) { .vp-grid, .vp-stats, .vp-filters { grid-template-columns:1fr; } }
</style>
