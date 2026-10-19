<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-white">@yield('title', 'Investor profile')</h2>
            <a href="{{ route('support.index') }}" class="text-sm font-medium text-blue-300 hover:text-blue-200">Need a hand? We’re here to help ↗</a>
        </div>
    </x-slot>
    <style>
        .setup-page { margin:0; background:#020617; color:#f8fafc; font-family:inherit; color-scheme:dark; -webkit-font-smoothing:antialiased; }
        .setup-page * { box-sizing:border-box; }
        .setup-main { max-width:1180px; margin:auto; padding:36px 32px 44px; }
        .setup-steps { display:flex; align-items:center; gap:18px; padding:0; margin:0 0 40px; list-style:none; }
        .setup-steps li { display:flex; align-items:center; gap:10px; font-size:13px; font-weight:600; color:#94a3b8; }
        .setup-steps li + li:before { content:''; width:66px; height:1px; background:#334155; margin-right:8px; }
        .setup-step-number { width:28px; height:28px; border-radius:50%; background:#1e293b; display:grid; place-items:center; color:#94a3b8; }
        .setup-steps [aria-current="step"] { color:#93c5fd; }
        .setup-steps [aria-current="step"] .setup-step-number { background:#2563eb; color:#fff; box-shadow:0 0 0 5px #172554; }
        .setup-grid { display:grid; grid-template-columns:minmax(0,.85fr) minmax(0,1.35fr); gap:56px; align-items:start; }
        .setup-intro { padding-top:18px; }
        .setup-eyebrow { color:#60a5fa; font-size:12px; font-weight:700; letter-spacing:1.3px; text-transform:uppercase; margin:0 0 16px; }
        .setup-intro h1 { margin:0; font-size:43px; line-height:1.12; letter-spacing:-1.7px; font-weight:700; }
        .setup-intro h1 span { color:#60a5fa; }
        .setup-lead { color:#94a3b8; font-size:16px; line-height:1.8; margin:22px 0 28px; max-width:370px; }
        .setup-note { display:flex; gap:12px; margin:20px 0; }
        .setup-note-icon { flex-shrink:0; width:34px; height:34px; display:grid; place-items:center; border-radius:11px; color:#93c5fd; background:#172554; }
        .setup-note svg { width:18px; height:18px; }
        .setup-note strong { display:block; font-size:14px; font-weight:600; margin-bottom:5px; }
        .setup-note p { margin:0; color:#94a3b8; font-size:13px; line-height:1.65; }
        .setup-next { margin-top:32px; border-top:1px solid #1e293b; padding-top:22px; color:#94a3b8; font-size:13px; line-height:1.8; }
        .setup-next strong { color:#e2e8f0; }
        .setup-card { background:#0f172a; padding:30px; border:1px solid #1e293b; border-radius:24px; box-shadow:0 12px 40px -24px #00000066; }
        .setup-card-heading { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:7px; }
        .setup-card h2 { margin:0; font-size:21px; font-weight:700; letter-spacing:-.5px; }
        .setup-time { white-space:nowrap; font-size:12px; color:#bfdbfe; background:#172554; padding:6px 10px; border-radius:30px; }
        .setup-card-description { color:#94a3b8; font-size:13px; line-height:1.7; margin:0 0 26px; }
        .setup-fields { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:23px 18px; }
        .setup-field-wide { grid-column:1 / -1; }
        .setup-field label { display:block; color:#e2e8f0; font-size:13px; font-weight:600; line-height:1.6; }
        .setup-field select,.setup-field input { display:block; width:100%; min-height:48px; margin-top:8px; padding:11px 34px 11px 13px; border:1px solid #334155; border-radius:10px; background-color:#020617; color:#e2e8f0; font-family:inherit; font-size:14px; line-height:1.5; }
        .setup-field input { padding-right:13px; }
        .setup-field select:focus,.setup-field input:focus { outline:3px solid #1e3a8a; border-color:#2563eb; outline-offset:1px; }
        .setup-field select[aria-invalid="true"],.setup-field input[aria-invalid="true"] { border-color:#fca5a5; }
        .setup-help { color:#94a3b8; font-size:12px; line-height:1.6; margin:7px 0 0; }
        .setup-error { color:#fca5a5; font-size:12px; margin:7px 0 0; }
        .setup-submit { margin-top:26px; width:100%; display:flex; align-items:center; justify-content:center; gap:12px; border:0; border-radius:12px; background:#2563eb; color:#fff; padding:15px 20px; font-family:inherit; font-size:14px; font-weight:600; cursor:pointer; box-shadow:0 4px 10px #2563eb22; }
        .setup-submit:hover { background:#1d4ed8; }
        .setup-submit:focus-visible { outline:3px solid #93c5fd; outline-offset:4px; }
        .setup-submit svg { width:17px; height:17px; }
        .setup-footer-note { text-align:center; color:#94a3b8; font-size:12px; line-height:1.6; margin:13px 0 0; }
        @media (max-width:1280px) { .setup-grid { grid-template-columns:1fr; gap:26px; } .setup-intro { padding:0; } .setup-intro h1 { font-size:36px; } .setup-lead { max-width:620px; margin:15px 0 0; } .setup-reassurance { display:none; } .setup-steps { margin-bottom:30px; } }
        @media (max-width:540px) { .setup-main { padding:28px 18px; } .setup-steps { gap:9px; justify-content:space-between; } .setup-steps li { gap:7px; font-size:11px; } .setup-steps li + li:before { display:none; } .setup-step-number { width:24px; height:24px; flex-shrink:0; } .setup-intro h1 { font-size:32px; } .setup-lead { font-size:14px; } .setup-card { padding:24px 20px; border-radius:20px; } .setup-fields { grid-template-columns:1fr; gap:21px; } .setup-card h2 { font-size:19px; } .setup-time { font-size:11px; } }
    </style>
    <div class="setup-page">
        <div class="setup-main">
            @yield('content')
        </div>
    </div>
</x-app-layout>
