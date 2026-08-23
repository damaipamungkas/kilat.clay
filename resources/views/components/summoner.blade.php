<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summoner Cheat Sheet - KILAT Laravel</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-main: #ebe5ee; --text-dark: #2a2245; --text-gray: #6b6288;
            --clay-purple: #c8b8ff; --clay-blue: #a3d5ff; --clay-green: #a8e6a1; --clay-yellow: #ffda85; --clay-pink: #ffb8c6;
            --clay-shadow-card: 8px 8px 16px rgba(150, 140, 170, 0.5), inset 6px 6px 12px rgba(255, 255, 255, 0.8), inset -6px -6px 16px rgba(0, 0, 0, 0.08);
            --text-timbul-dark: 1px 1px 0px #ffffff, 2px 2px 5px rgba(150, 140, 170, 0.7);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Nunito', sans-serif; }
        body { background: var(--bg-main); color: var(--text-dark); padding: 30px; }
        h1 { font-size: 2rem; font-weight: 900; margin-bottom: 25px; text-shadow: var(--text-timbul-dark); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }
        .card { border-radius: 25px; padding: 25px; box-shadow: var(--clay-shadow-card); margin-bottom: 20px; }
        .card h3 { font-size: 1.2rem; font-weight: 900; margin-bottom: 10px; display: flex; align-items: center; gap: 10px; text-shadow: var(--text-timbul-dark); }
        pre { background: var(--bg-main); padding: 15px; border-radius: 15px; font-family: monospace; font-weight: 800; font-size: 0.85rem; overflow-x: auto; box-shadow: inset 4px 4px 8px rgba(150,140,170,0.5), inset -4px -4px 8px rgba(255,255,255,0.8); margin-top: 10px; color: var(--text-dark); }
        p { font-size: 0.9rem; font-weight: 700; color: var(--text-gray); }
    </style>
</head>
<body>
    <h1>⚡ Summoner: Catatan Perintah & Layout Laravel KILAT</h1>

    <div class="grid">
        <!-- 1. Master Layout Publik -->
        <div class="card" style="background: var(--clay-blue);">
            <h3><i class="fa-solid fa-globe"></i> Master Layout Publik (Site)</h3>
            <p>Digunakan untuk membungkus halaman utama / publik.</p>
            <pre>@extends('layouts.site')

@section('content')
    &lt;!-- Konten spesifik halaman di sini --&gt;
@endsection</pre>
        </div>

        <!-- 2. Master Layout Admin -->
        <div class="card" style="background: var(--clay-green);">
            <h3><i class="fa-solid fa-shield-halved"></i> Master Layout Admin (App)</h3>
            <p>Digunakan untuk membungkus halaman admin (Dashboard, Absensi, Billing, dll).</p>
            <pre>@extends('layouts.components.app')

@section('content')
    &lt;!-- Konten admin di sini --&gt;
@endsection</pre>
        </div>

        <!-- 3. Komponen Navigasi & Footer Publik -->
        <div class="card" style="background: var(--clay-yellow);">
            <h3><i class="fa-solid fa-bars"></i> Navigasi & Footer Publik</h3>
            <p>Pemanggilan navbar atas, ikon menu cepat, dan footer.</p>
            <pre>@include('layouts.navbar')
@include('layouts.icon-menu')
@include('layouts.footer')</pre>
        </div>

        <!-- 4. Komponen Dekoratif (Divider & Slider) -->
        <div class="card" style="background: var(--clay-purple);">
            <h3><i class="fa-solid fa-code-merge"></i> Divider & Color Slider</h3>
            <p>Penyisipan garis pemisah cyber dan pengatur warna latar belakang.</p>
            <pre>@include('layouts.divider')
@include('layouts.slider')</pre>
        </div>

        <!-- 5. Sidebar Admin -->
        <div class="card" style="background: var(--clay-pink);">
            <h3><i class="fa-solid fa-folder-tree"></i> Sidebar Admin</h3>
            <p>Komponen menu navigasi samping khusus halaman administrator.</p>
            <pre>@include('layouts.sidebar')</pre>
        </div>
    </div>
</body>
</html>
