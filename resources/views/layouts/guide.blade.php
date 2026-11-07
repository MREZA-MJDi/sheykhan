<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'راهنمای کار با شیخان')</title>
    <style>
        *{box-sizing:border-box}
        html{background:#f2f5f8;color:#17253a;font-family:Tahoma,Arial,sans-serif}
        body{margin:0;line-height:1.9}
        a{color:inherit;text-decoration:none}
        button{font:inherit;cursor:pointer}
        .guide-wrap{max-width:1060px;margin:0 auto;padding:28px 20px 64px}
        .guide-top{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px}
        .guide-brand{display:flex;align-items:center;gap:12px;font-weight:900}
        .guide-brand-mark{width:42px;height:42px;display:grid;place-items:center;border-radius:13px;background:#172d49;color:#fff;font-size:20px}
        .guide-brand small{display:block;color:#738196;font-size:11px;font-weight:500}
        .guide-actions{display:flex;gap:8px;flex-wrap:wrap}
        .guide-button{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:8px 14px;border:1px solid #dbe2ea;border-radius:11px;background:#fff;color:#263a54;font-size:12px;font-weight:800}
        .guide-button.primary{border-color:#245f9f;background:#245f9f;color:#fff}
        .guide-sheet{overflow:hidden;border:1px solid #e0e6ed;border-radius:22px;background:#fff;box-shadow:0 18px 55px rgba(24,39,58,.07)}
        .guide-hero{padding:clamp(24px,5vw,48px);background:linear-gradient(120deg,#14273e,#234a70);color:#fff}
        .guide-hero small{color:#c1d3e5;font-size:12px;font-weight:700}
        .guide-hero h1{margin:10px 0 8px;font-size:clamp(25px,4vw,38px);line-height:1.45}
        .guide-hero p{max-width:720px;margin:0;color:#e3ebf4;font-size:14px;line-height:2.1}
        .guide-body{padding:clamp(20px,4vw,40px)}
        .guide-section{margin-bottom:30px}
        .guide-section:last-child{margin-bottom:0}
        .guide-section h2{margin:0 0 13px;padding-bottom:10px;border-bottom:1px solid #e9eef3;font-size:19px;line-height:1.6}
        .guide-section>p{margin:0 0 12px;color:#596a80;font-size:13px}
        .guide-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
        .guide-card{padding:16px;border:1px solid #e4eaf0;border-radius:14px;background:#fbfcfe}
        .guide-card h3{margin:0 0 5px;font-size:14px}
        .guide-card p{margin:0;color:#5a6a7e;font-size:12px;line-height:2}
        .guide-steps{counter-reset:guide;display:grid;gap:10px;padding:0;list-style:none}
        .guide-steps li{position:relative;padding:14px 54px 14px 14px;border:1px solid #e7edf2;border-radius:13px;background:#fff}
        .guide-steps li:before{counter-increment:guide;content:counter(guide, persian);position:absolute;right:14px;top:14px;display:grid;width:28px;height:28px;place-items:center;border-radius:9px;background:#eaf1f8;color:#27577f;font-size:12px;font-weight:900}
        .guide-steps strong{display:block;margin-bottom:3px;font-size:13px}
        .guide-steps p{margin:0;color:#5e6d80;font-size:12px;line-height:2}
        .guide-note{padding:14px 16px;border-right:3px solid #3b78ab;border-radius:9px;background:#eef5fb;color:#35516c;font-size:12px;line-height:2}
        .guide-footer{display:flex;justify-content:space-between;gap:12px;padding:16px 24px;border-top:1px solid #e9eef3;color:#7a8797;font-size:10px}
        @media(max-width:650px){.guide-wrap{padding:14px 10px 34px}.guide-top{align-items:flex-start;flex-direction:column}.guide-grid{grid-template-columns:1fr}.guide-sheet{border-radius:16px}.guide-footer{flex-direction:column}}
        @media print{
            @page{size:A4;margin:14mm}
            html,body{background:#fff!important}
            .guide-wrap{max-width:none;padding:0}
            .guide-top,.guide-actions{display:none!important}
            .guide-sheet{border:0;border-radius:0;box-shadow:none}
            .guide-hero{padding:14mm;background:#eaf0f6!important;color:#14273e!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}
            .guide-hero small,.guide-hero p{color:#344d67!important}
            .guide-body{padding:10mm 0}
            .guide-card,.guide-note{break-inside:avoid;page-break-inside:avoid;-webkit-print-color-adjust:exact;print-color-adjust:exact}
            .guide-section{break-inside:avoid-page}
            .guide-footer{padding:8mm 0 0}
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
