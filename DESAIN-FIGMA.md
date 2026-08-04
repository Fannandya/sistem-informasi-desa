<!-- Rekap KK per RT (Terra Version) -->
<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Rekap KK Per RT</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Literata:wght@400;500;600;700&amp;family=Nunito+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        "colors": {
                "inverse-on-surface": "#f5f0e8",
                "on-tertiary-container": "#554020",
                "primary-container": "#78a886",
                "secondary-fixed-dim": "#d4ccbf",
                "primary-fixed-dim": "#8ecf9e",
                "on-error-container": "#690005",
                "on-background": "#2e3230",
                "tertiary": "#705c30",
                "surface-dim": "#dbd7cf",
                "on-secondary": "#ffffff",
                "inverse-surface": "#2e3230",
                "on-surface": "#2e3230",
                "on-tertiary-fixed": "#221a05",
                "on-secondary-fixed-variant": "#4a4538",
                "surface-container-high": "#eae6de",
                "on-primary-fixed-variant": "#2a6038",
                "on-surface-variant": "#4a4e4a",
                "secondary": "#6b6358",
                "on-secondary-container": "#5e5548",
                "error": "#b83230",
                "surface-variant": "#e4e0d8",
                "tertiary-fixed": "#f8e0a8",
                "secondary-fixed": "#f0e8db",
                "outline-variant": "#c4c8bc",
                "on-error": "#ffffff",
                "error-container": "#ffdad8",
                "on-primary": "#ffffff",
                "surface-container-lowest": "#ffffff",
                "inverse-primary": "#8ecf9e",
                "tertiary-container": "#c4a66a",
                "surface-container-low": "#f5f1ea",
                "on-secondary-fixed": "#1e1a13",
                "surface": "#faf6f0",
                "on-primary-container": "#d8f0de",
                "surface-container": "#f0ece4",
                "secondary-container": "#f0e8db",
                "surface-tint": "#4a7c59",
                "primary": "#4a7c59",
                "background": "#faf6f0",
                "tertiary-fixed-dim": "#dcc48e",
                "outline": "#74796e",
                "surface-bright": "#faf6f0",
                "surface-container-highest": "#e4e0d8",
                "on-tertiary-fixed-variant": "#554020",
                "on-tertiary": "#ffffff",
                "primary-fixed": "#c8e8d0",
                "on-primary-fixed": "#002110"
        },
        "borderRadius": {
                "DEFAULT": "0.75rem",
                "lg": "1rem",
                "xl": "1.5rem",
                "full": "9999px"
        },
        "spacing": {},
        "fontFamily": {
                "headline": [
                        "Literata"
                ],
                "display": [
                        "Literata"
                ],
                "body": [
                        "Nunito Sans"
                ],
                "label": [
                        "Nunito Sans"
                ]
        },
        "fontSize": {}
},
    },
  }
</script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-surface text-on-surface font-body h-screen overflow-hidden flex">
<!-- SideNavBar -->
<aside class="docked left-0 h-full w-64 border-r border-outline-variant flex flex-col p-4 space-y-2 bg-surface-container-low hidden md:flex">
<div class="mb-10">
<h1 class="font-headline text-2xl font-bold text-primary">Admin Desa</h1>
<p class="font-label text-sm text-on-surface-variant mt-1">Petugas Administrasi</p>
</div>
<nav class="flex-1 space-y-2">
<a class="flex items-center space-x-3 px-3 py-2 rounded-xl text-on-surface-variant hover:bg-surface-container-high transition-all" href="#">
<span class="material-symbols-outlined">dashboard</span>
<span class="font-label text-sm">Dashboard</span>
</a>
<a class="flex items-center space-x-3 px-3 py-2 rounded-xl bg-secondary-container text-on-secondary-container font-bold scale-[0.98] duration-100" href="#">
<span class="material-symbols-outlined">groups</span>
<span class="font-label text-sm">Data Penduduk</span>
</a>
<a class="flex items-center space-x-3 px-3 py-2 rounded-xl text-on-surface-variant hover:bg-surface-container-high transition-all" href="#">
<span class="material-symbols-outlined">description</span>
<span class="font-label text-sm">Kelola Konten</span>
</a>
<a class="flex items-center space-x-3 px-3 py-2 rounded-xl text-on-surface-variant hover:bg-surface-container-high transition-all" href="#">
<span class="material-symbols-outlined">settings</span>
<span class="font-label text-sm">Pengaturan Sistem</span>
</a>
</nav>
<div class="mt-auto space-y-2">
<a class="flex items-center space-x-3 px-3 py-2 rounded-xl text-on-surface-variant hover:bg-surface-container-high transition-all" href="#">
<span class="material-symbols-outlined">help</span>
<span class="font-label text-sm">Bantuan</span>
</a>
<a class="flex items-center space-x-3 px-3 py-2 rounded-xl text-on-surface-variant hover:bg-surface-container-high transition-all" href="#">
<span class="material-symbols-outlined">logout</span>
<span class="font-label text-sm">Keluar</span>
</a>
<button class="w-full mt-4 bg-primary text-on-primary py-2 rounded-xl font-label text-sm hover:opacity-90 transition-opacity">Buka Portal Publik</button>
</div>
</aside>
<div class="flex-1 flex flex-col h-screen overflow-hidden">
<!-- TopNavBar (Admin) -->
<header class="docked full-width top-0 sticky border-b border-outline-variant flat no shadows flex justify-between items-center w-full px-6 h-16 bg-surface-container-lowest">
<div class="flex items-center">
<button class="md:hidden mr-4 text-on-surface-variant">
<span class="material-symbols-outlined">menu</span>
</button>
<span class="font-headline text-xl font-bold text-primary hidden md:block">Dashboard Admin</span>
</div>
<div class="flex-1 flex justify-start ml-4 md:ml-12">
<div class="relative w-full max-w-md">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
<input class="w-full pl-10 pr-4 py-2.5 bg-surface-container-low border-none rounded-full font-body text-sm focus:ring-2 focus:ring-primary focus:bg-surface-container-lowest transition-all placeholder:text-on-surface-variant" placeholder="Cari data..." type="text"/>
</div>
</div>
<div class="flex items-center space-x-4">
<button class="text-on-surface-variant hover:bg-surface-container rounded-full p-2 transition-all">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">notifications</span>
</button>
<img alt="User Profile" class="w-9 h-9 rounded-full object-cover border border-outline-variant" data-alt="A small circular profile picture of an administrative official in formal attire, clear lighting, professional setting, highly detailed portrait, corporate style, neutral background." src="https://lh3.googleusercontent.com/aida-public/AB6AXuASo7X3vRsFTE-gwqQ_A1j5GLWVM9ECs7L_EC0cMHR7hWFgJ3MyxpKxNCGM2qieuG6hJxgvCOjQDljQu-U7Kp5754KH8de1cqjdVffhDkEXkBuCmFJr4R4Q-ZDcJkKI9xzpevKLkDaxDDQxWtfRMEtFuVfwYXYq3bA6ipxf_32906UVO5Dqi08-RtEDyBayrU1h5b8JpAe65byrAZJ3ZRH5QL_B-3DgJC3nattqkJ24-310H4bmEJH0HA"/>
</div>
</header>
<!-- Main Content -->
<main class="flex-1 overflow-y-auto p-6 bg-surface">
<div class="max-w-5xl mx-auto space-y-6">
<div class="flex flex-col md:flex-row justify-between items-start md:items-center">
<div>
<h2 class="font-headline text-3xl text-on-surface">Rekap KK Per RT</h2>
<p class="font-body text-base text-on-surface-variant mt-1">Kelola data jumlah Kepala Keluarga untuk setiap Rukun Tetangga.</p>
</div>
</div>
<!-- Spreadsheet Card -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-[12px] overflow-hidden shadow-sm">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant">
<th class="font-label text-sm text-on-surface-variant p-4 w-32 sticky left-0 bg-surface-container-low z-10 font-semibold">Area</th>
<th class="font-label text-sm text-on-surface-variant p-4 min-w-[120px] font-semibold">2020</th>
<th class="font-label text-sm text-on-surface-variant p-4 min-w-[120px] font-semibold">2021</th>
<th class="font-label text-sm text-on-surface-variant p-4 min-w-[120px] font-semibold">2022</th>
<th class="font-label text-sm text-on-surface-variant p-4 min-w-[120px] font-semibold">2023</th>
<th class="font-label text-sm text-on-surface-variant p-4 min-w-[120px] bg-secondary-container/30 font-semibold">2024 (Current)</th>
</tr>
</thead>
<tbody class="font-body text-base text-on-surface divide-y divide-outline-variant">
<tr class="hover:bg-surface-container-lowest transition-colors group">
<td class="p-4 font-medium sticky left-0 bg-surface-container-lowest group-hover:bg-surface-container-low">RT 01</td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="145"/></td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="148"/></td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="152"/></td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="155"/></td>
<td class="p-4 bg-secondary-container/10"><input class="w-full bg-surface-container-lowest border border-outline-variant rounded-[12px] px-3 py-1.5 focus:ring-2 focus:ring-primary focus:border-primary text-on-surface transition-all" type="number" value="160"/></td>
</tr>
<tr class="bg-surface-bright hover:bg-surface-container-lowest transition-colors group">
<td class="p-4 font-medium sticky left-0 bg-surface-bright group-hover:bg-surface-container-low">RT 02</td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="98"/></td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="102"/></td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="105"/></td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="108"/></td>
<td class="p-4 bg-secondary-container/10"><input class="w-full bg-surface-container-lowest border border-outline-variant rounded-[12px] px-3 py-1.5 focus:ring-2 focus:ring-primary focus:border-primary text-on-surface transition-all" type="number" value="110"/></td>
</tr>
<tr class="hover:bg-surface-container-lowest transition-colors group">
<td class="p-4 font-medium sticky left-0 bg-surface-container-lowest group-hover:bg-surface-container-low">RT 03</td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="210"/></td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="215"/></td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="220"/></td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="222"/></td>
<td class="p-4 bg-secondary-container/10"><input class="w-full bg-surface-container-lowest border border-outline-variant rounded-[12px] px-3 py-1.5 focus:ring-2 focus:ring-primary focus:border-primary text-on-surface transition-all" type="number" value="225"/></td>
</tr>
<tr class="bg-surface-bright hover:bg-surface-container-lowest transition-colors group">
<td class="p-4 font-medium sticky left-0 bg-surface-bright group-hover:bg-surface-container-low">RT 04</td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="175"/></td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="175"/></td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="178"/></td>
<td class="p-4"><input class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface-variant" type="number" value="180"/></td>
<td class="p-4 bg-secondary-container/10"><input class="w-full bg-surface-container-lowest border border-outline-variant rounded-[12px] px-3 py-1.5 focus:ring-2 focus:ring-primary focus:border-primary text-on-surface transition-all" type="number" value="182"/></td>
</tr>
</tbody>
<tfoot class="bg-surface-container border-t border-outline-variant">
<tr>
<td class="p-4 font-bold text-on-surface sticky left-0 bg-surface-container">Total</td>
<td class="p-4 font-bold text-on-surface">628</td>
<td class="p-4 font-bold text-on-surface">640</td>
<td class="p-4 font-bold text-on-surface">655</td>
<td class="p-4 font-bold text-on-surface">665</td>
<td class="p-4 font-bold text-primary bg-primary-container/20">677</td>
</tr>
</tfoot>
</table>
</div>
<div class="p-4 border-t border-outline-variant bg-surface-container-lowest flex justify-end rounded-b-[12px]">
<button class="bg-primary hover:bg-primary/90 text-on-primary font-label text-sm px-6 py-2.5 rounded-[12px] shadow-sm transition-all flex items-center space-x-2">
<span class="material-symbols-outlined text-sm">save</span>
<span>Simpan Perubahan</span>
</button>
</div>
</div>
</div>
</main>
</div>
</body></html>

<!-- Dashboard Admin (Terra Version) -->
<!DOCTYPE html>

<html lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>DesaDigital - Admin Dashboard</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Literata:wght@400;500;600;700&amp;family=Nunito+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        "colors": {
                "inverse-on-surface": "#f5f0e8",
                "on-tertiary-container": "#554020",
                "primary-container": "#78a886",
                "secondary-fixed-dim": "#d4ccbf",
                "primary-fixed-dim": "#8ecf9e",
                "on-error-container": "#690005",
                "on-background": "#2e3230",
                "tertiary": "#705c30",
                "surface-dim": "#dbd7cf",
                "on-secondary": "#ffffff",
                "inverse-surface": "#2e3230",
                "on-surface": "#2e3230",
                "on-tertiary-fixed": "#221a05",
                "on-secondary-fixed-variant": "#4a4538",
                "surface-container-high": "#eae6de",
                "on-primary-fixed-variant": "#2a6038",
                "on-surface-variant": "#4a4e4a",
                "secondary": "#6b6358",
                "on-secondary-container": "#5e5548",
                "error": "#b83230",
                "surface-variant": "#e4e0d8",
                "tertiary-fixed": "#f8e0a8",
                "secondary-fixed": "#f0e8db",
                "outline-variant": "#c4c8bc",
                "on-error": "#ffffff",
                "error-container": "#ffdad8",
                "on-primary": "#ffffff",
                "surface-container-lowest": "#ffffff",
                "inverse-primary": "#8ecf9e",
                "tertiary-container": "#c4a66a",
                "surface-container-low": "#f5f1ea",
                "on-secondary-fixed": "#1e1a13",
                "surface": "#faf6f0",
                "on-primary-container": "#d8f0de",
                "surface-container": "#f0ece4",
                "secondary-container": "#f0e8db",
                "surface-tint": "#4a7c59",
                "primary": "#4a7c59",
                "background": "#faf6f0",
                "tertiary-fixed-dim": "#dcc48e",
                "outline": "#74796e",
                "surface-bright": "#faf6f0",
                "surface-container-highest": "#e4e0d8",
                "on-tertiary-fixed-variant": "#554020",
                "on-tertiary": "#ffffff",
                "primary-fixed": "#c8e8d0",
                "on-primary-fixed": "#002110"
        },
        "borderRadius": {
                "DEFAULT": "12px",
                "lg": "12px",
                "xl": "12px",
                "full": "9999px"
        },
        "spacing": {
                        "gutter": "24px",
                        "base": "4px",
                        "margin-desktop": "48px",
                        "sm": "16px",
                        "lg": "40px",
                        "md": "24px",
                        "xl": "64px",
                        "margin-mobile": "16px",
                        "xs": "8px"
                    },
        "fontFamily": {
                "headline": [
                        "Literata"
                ],
                "display": [
                        "Literata"
                ],
                "body": [
                        "Nunito Sans"
                ],
                "label": [
                        "Nunito Sans"
                ],
                "body-md": ["Nunito Sans"],
                        "label-md": ["Nunito Sans"],
                        "headline-lg-mobile": ["Literata"],
                        "label-sm": ["Nunito Sans"],
                        "body-lg": ["Nunito Sans"],
                        "display-lg": ["Literata"],
                        "headline-md": ["Literata"],
                        "headline-lg": ["Literata"]
        },
        "fontSize": {
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "label-md": ["14px", {"lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                        "headline-lg-mobile": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                        "label-sm": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                        "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "headline-lg": ["32px", {"lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700"}]
                    }
},
    },
  }
</script>
<style>
        body { font-family: 'Nunito Sans', sans-serif; background-color: #faf6f0; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-background text-on-background flex h-screen overflow-hidden">
<!-- SideNavBar -->
<nav class="bg-surface-container-low dark:bg-surface-container text-primary dark:text-primary-fixed-dim border-r border-outline-variant dark:border-outline docked left-0 h-full w-64 flex flex-col p-sm space-y-xs z-20">
<div class="px-md py-sm flex flex-col items-start mb-md">
<div class="flex items-center space-x-sm mb-xs">
<img class="w-10 h-10 rounded-full object-cover border border-outline-variant" data-alt="A professional portrait of an administrative official in a modern, well-lit office setting. The lighting is soft and bright, emphasizing a clean, corporate aesthetic suitable for a secure digital platform. The color palette is composed of crisp whites, soft grays, and muted earth tones, conveying reliability and trust." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAZ3CKZrRp9dnefseNoUuIkNT3uLJJvl_DRgOCazRhGy5EhiY3L4aHGHkqwwBHyPotGbBEwd_VRQJAWptU04njXyuaMe3gfxc4fQz-hc0yvmG3BAw74IpTCPFxFVpyNJbuDWIlKhp5YtcZWmZQ4eberf8Rg1eCT_oC5uPNgRPPe1ifzjWgwoLaJ14FxS0VxHQ-8h5XKQ6wbGCPWAfMvLlSCbq3QsekiN3-ObSKfd7lpwnQoava-9KmfFg"/>
<div>
<h2 class="font-headline-md text-label-md font-bold text-primary dark:text-primary-fixed leading-tight">Admin Desa</h2>
<p class="font-label-sm text-label-sm text-on-surface-variant">Petugas Administrasi</p>
</div>
</div>
</div>
<div class="flex-1 space-y-base">
<a class="flex items-center space-x-md px-md py-sm bg-secondary-container dark:bg-primary-container text-on-secondary-container dark:text-on-primary-container font-bold rounded-[12px] scale-[0.98] duration-100" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
<span class="font-label-md text-label-md">Dashboard</span>
</a>
<a class="flex items-center space-x-md px-md py-sm text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-container-highest transition-all rounded-[12px]" href="#">
<span class="material-symbols-outlined">groups</span>
<span class="font-label-md text-label-md">Data Penduduk</span>
</a>
<a class="flex items-center space-x-md px-md py-sm text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-container-highest transition-all rounded-[12px]" href="#">
<span class="material-symbols-outlined">description</span>
<span class="font-label-md text-label-md">Kelola Konten</span>
</a>
<a class="flex items-center space-x-md px-md py-sm text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-container-highest transition-all rounded-[12px]" href="#">
<span class="material-symbols-outlined">settings</span>
<span class="font-label-md text-label-md">Pengaturan Sistem</span>
</a>
</div>
<div class="mt-auto space-y-base pt-md border-t border-outline-variant">
<a class="flex items-center justify-center w-full bg-primary-container text-on-primary font-label-md text-label-md py-sm rounded-[12px] mb-sm hover:brightness-110 transition-all" href="#">
                Buka Portal Publik
            </a>
<a class="flex items-center space-x-md px-md py-sm text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-container-highest transition-all rounded-[12px]" href="#">
<span class="material-symbols-outlined">help</span>
<span class="font-label-md text-label-md">Bantuan</span>
</a>
<a class="flex items-center space-x-md px-md py-sm text-error hover:bg-error-container hover:text-on-error-container transition-all rounded-[12px]" href="#">
<span class="material-symbols-outlined">logout</span>
<span class="font-label-md text-label-md">Keluar</span>
</a>
</div>
</nav>
<!-- Main Content Area -->
<div class="flex-1 flex flex-col min-w-0">
<!-- Admin TopNavBar -->
<header class="bg-surface-container-lowest dark:bg-surface-dim text-primary dark:text-primary-fixed-dim docked full-width top-0 sticky border-b border-outline-variant dark:border-outline flex justify-between items-center w-full px-md h-14 z-10">
<div class="flex items-center flex-1">
<h1 class="font-headline-md text-headline-md font-bold text-primary dark:text-primary-fixed mr-xl hidden md:block">Dashboard Admin</h1>
<!-- Search Bar (on_left) -->
<div class="relative w-64 max-w-sm hidden sm:block">
<span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
<input class="w-full bg-surface-container-low border border-outline-variant rounded-full py-1.5 pl-[36px] pr-sm font-body-md text-label-md focus:outline-none focus:border-tertiary focus:ring-2 focus:ring-tertiary/20 transition-all text-on-surface" placeholder="Cari data..." type="text"/>
</div>
</div>
<div class="flex items-center space-x-sm">
<button class="p-xs text-on-surface-variant dark:text-surface-variant hover:bg-surface-container dark:hover:bg-surface-container-high rounded-full transition-all">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="p-xs text-on-surface-variant dark:text-surface-variant hover:bg-surface-container dark:hover:bg-surface-container-high rounded-full transition-all">
<span class="material-symbols-outlined">account_circle</span>
</button>
</div>
</header>
<!-- Main Scrollable Content -->
<main class="flex-1 overflow-y-auto p-md md:p-lg">
<!-- Warning Banner -->
<div class="bg-secondary-fixed-dim/20 border border-secondary text-primary-container px-md py-sm rounded-[12px] mb-lg flex items-center justify-between shadow-sm">
<div class="flex items-center space-x-sm">
<span class="material-symbols-outlined text-secondary">warning</span>
<span class="font-label-md text-label-md text-on-surface">3 RT belum diisi data KK</span>
</div>
<button class="font-label-sm text-label-sm underline hover:text-primary text-on-surface">Perbaiki Sekarang</button>
</div>
<!-- Metric Summary Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-md mb-lg">
<!-- Card 1 -->
<div class="bg-surface-container-lowest border border-outline-variant p-md rounded-[12px] shadow-[0_4px_20px_rgba(0,0,0,0.05)]">
<div class="flex justify-between items-start mb-sm">
<span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Active Residents</span>
<span class="material-symbols-outlined text-secondary bg-secondary/10 p-1 rounded-full text-[20px]">person</span>
</div>
<div class="font-headline-lg text-display-lg text-on-surface">12,450</div>
<div class="font-label-sm text-label-sm text-secondary flex items-center mt-xs">
<span class="material-symbols-outlined text-[16px]">arrow_upward</span>
<span class="ml-1">+2.4% this month</span>
</div>
</div>
<!-- Card 2 -->
<div class="bg-surface-container-lowest border border-outline-variant p-md rounded-[12px] shadow-[0_4px_20px_rgba(0,0,0,0.05)]">
<div class="flex justify-between items-start mb-sm">
<span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Total Households</span>
<span class="material-symbols-outlined text-tertiary bg-tertiary/10 p-1 rounded-full text-[20px]">home</span>
</div>
<div class="font-headline-lg text-display-lg text-on-surface">3,210</div>
<div class="font-label-sm text-label-sm text-on-surface-variant flex items-center mt-xs">
<span class="ml-1">Across 15 RWs</span>
</div>
</div>
<!-- Card 3 -->
<div class="bg-surface-container-lowest border border-outline-variant p-md rounded-[12px] shadow-[0_4px_20px_rgba(0,0,0,0.05)]">
<div class="flex justify-between items-start mb-sm">
<span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Birth Rate</span>
<span class="material-symbols-outlined text-primary-container bg-primary-container/10 p-1 rounded-full text-[20px]">child_care</span>
</div>
<div class="font-headline-lg text-display-lg text-on-surface">45</div>
<div class="font-label-sm text-label-sm text-secondary flex items-center mt-xs">
<span class="ml-1">YTD 2024</span>
</div>
</div>
<!-- Card 4 -->
<div class="bg-surface-container-lowest border border-outline-variant p-md rounded-[12px] shadow-[0_4px_20px_rgba(0,0,0,0.05)]">
<div class="flex justify-between items-start mb-sm">
<span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Death Rate</span>
<span class="material-symbols-outlined text-error bg-error/10 p-1 rounded-full text-[20px]">church</span>
</div>
<div class="font-headline-lg text-display-lg text-on-surface">12</div>
<div class="font-label-sm text-label-sm text-on-surface-variant flex items-center mt-xs">
<span class="ml-1">YTD 2024</span>
</div>
</div>
</div>
<!-- Charts Grid (Bento Style) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-md">
<!-- Age Distribution (Spans 2 cols) -->
<div class="bg-surface-container-lowest border border-outline-variant p-md rounded-[12px] shadow-[0_4px_20px_rgba(0,0,0,0.05)] lg:col-span-2">
<h3 class="font-headline-md text-body-lg font-semibold text-on-surface mb-md">Age Distribution</h3>
<div class="h-64 w-full bg-surface-container-low rounded-[12px] relative overflow-hidden flex items-end justify-around px-sm pb-sm pt-xl">
<!-- Mock Bar Chart -->
<div class="w-1/12 bg-primary-container/40 hover:bg-primary-container/60 transition-colors rounded-t-sm h-[20%] relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs font-label-sm opacity-0 group-hover:opacity-100 transition-opacity">0-10</span></div>
<div class="w-1/12 bg-primary-container/60 hover:bg-primary-container/80 transition-colors rounded-t-sm h-[40%] relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs font-label-sm opacity-0 group-hover:opacity-100 transition-opacity">11-20</span></div>
<div class="w-1/12 bg-primary-container hover:bg-primary-container/90 transition-colors rounded-t-sm h-[80%] relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs font-label-sm opacity-0 group-hover:opacity-100 transition-opacity">21-30</span></div>
<div class="w-1/12 bg-primary-container hover:bg-primary-container/90 transition-colors rounded-t-sm h-[90%] relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs font-label-sm opacity-0 group-hover:opacity-100 transition-opacity">31-40</span></div>
<div class="w-1/12 bg-primary-container/80 hover:bg-primary-container transition-colors rounded-t-sm h-[70%] relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs font-label-sm opacity-0 group-hover:opacity-100 transition-opacity">41-50</span></div>
<div class="w-1/12 bg-primary-container/60 hover:bg-primary-container/80 transition-colors rounded-t-sm h-[50%] relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs font-label-sm opacity-0 group-hover:opacity-100 transition-opacity">51-60</span></div>
<div class="w-1/12 bg-primary-container/40 hover:bg-primary-container/60 transition-colors rounded-t-sm h-[30%] relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs font-label-sm opacity-0 group-hover:opacity-100 transition-opacity">60+</span></div>
</div>
</div>
<!-- Gender Ratio -->
<div class="bg-surface-container-lowest border border-outline-variant p-md rounded-[12px] shadow-[0_4px_20px_rgba(0,0,0,0.05)]">
<h3 class="font-headline-md text-body-lg font-semibold text-on-surface mb-md">Gender Ratio</h3>
<div class="h-64 w-full flex items-center justify-center relative">
<!-- Mock Pie Chart (CSS) -->
<div class="w-48 h-48 rounded-full" style="background: conic-gradient(#78a886 0% 52%, #8ecf9e 52% 100%);"></div>
<div class="absolute inset-0 flex flex-col items-center justify-center">
<div class="w-32 h-32 bg-surface-container-lowest rounded-full flex items-center justify-center flex-col">
<span class="font-headline-md text-headline-md text-on-surface">52%</span>
<span class="font-label-sm text-label-sm text-on-surface-variant">Female</span>
</div>
</div>
</div>
<div class="flex justify-center space-x-md mt-sm">
<div class="flex items-center space-x-xs"><div class="w-3 h-3 bg-[#78a886] rounded-sm"></div><span class="font-label-sm text-label-sm text-on-surface">Female</span></div>
<div class="flex items-center space-x-xs"><div class="w-3 h-3 bg-[#8ecf9e] rounded-sm"></div><span class="font-label-sm text-label-sm text-on-surface">Male</span></div>
</div>
</div>
<!-- Livelihoods -->
<div class="bg-surface-container-lowest border border-outline-variant p-md rounded-[12px] shadow-[0_4px_20px_rgba(0,0,0,0.05)] lg:col-span-3">
<h3 class="font-headline-md text-body-lg font-semibold text-on-surface mb-md">Livelihoods Snapshot</h3>
<div class="flex flex-col space-y-sm">
<!-- Row 1 -->
<div class="flex items-center">
<div class="w-32 font-label-md text-label-md text-on-surface-variant">Agriculture</div>
<div class="flex-1 h-4 bg-surface-container-low rounded-full overflow-hidden">
<div class="h-full bg-secondary w-[45%] rounded-full"></div>
</div>
<div class="w-16 text-right font-label-md text-label-md text-on-surface">45%</div>
</div>
<!-- Row 2 -->
<div class="flex items-center">
<div class="w-32 font-label-md text-label-md text-on-surface-variant">Trade</div>
<div class="flex-1 h-4 bg-surface-container-low rounded-full overflow-hidden">
<div class="h-full bg-tertiary-container w-[25%] rounded-full"></div>
</div>
<div class="w-16 text-right font-label-md text-label-md text-on-surface">25%</div>
</div>
<!-- Row 3 -->
<div class="flex items-center">
<div class="w-32 font-label-md text-label-md text-on-surface-variant">Civil Service</div>
<div class="flex-1 h-4 bg-surface-container-low rounded-full overflow-hidden">
<div class="h-full bg-primary-fixed w-[15%] rounded-full"></div>
</div>
<div class="w-16 text-right font-label-md text-label-md text-on-surface">15%</div>
</div>
</div>
</div>
</div>
</main>
</div>
</body></html>

<!-- Manajemen Warga (Terra Version) -->
<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>DesaDigital Admin - Data Penduduk</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Literata:opsz,wght@7..72,400..700&amp;family=Nunito+Sans:opsz,wght@6..12,400..700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        "colors": {
                "inverse-on-surface": "#f5f0e8",
                "on-tertiary-container": "#554020",
                "primary-container": "#78a886",
                "secondary-fixed-dim": "#d4ccbf",
                "primary-fixed-dim": "#8ecf9e",
                "on-error-container": "#690005",
                "on-background": "#2e3230",
                "tertiary": "#705c30",
                "surface-dim": "#dbd7cf",
                "on-secondary": "#ffffff",
                "inverse-surface": "#2e3230",
                "on-surface": "#2e3230",
                "on-tertiary-fixed": "#221a05",
                "on-secondary-fixed-variant": "#4a4538",
                "surface-container-high": "#eae6de",
                "on-primary-fixed-variant": "#2a6038",
                "on-surface-variant": "#4a4e4a",
                "secondary": "#6b6358",
                "on-secondary-container": "#5e5548",
                "error": "#b83230",
                "surface-variant": "#e4e0d8",
                "tertiary-fixed": "#f8e0a8",
                "secondary-fixed": "#f0e8db",
                "outline-variant": "#c4c8bc",
                "on-error": "#ffffff",
                "error-container": "#ffdad8",
                "on-primary": "#ffffff",
                "surface-container-lowest": "#ffffff",
                "inverse-primary": "#8ecf9e",
                "tertiary-container": "#c4a66a",
                "surface-container-low": "#f5f1ea",
                "on-secondary-fixed": "#1e1a13",
                "surface": "#faf6f0",
                "on-primary-container": "#d8f0de",
                "surface-container": "#f0ece4",
                "secondary-container": "#f0e8db",
                "surface-tint": "#4a7c59",
                "primary": "#4a7c59",
                "background": "#faf6f0",
                "tertiary-fixed-dim": "#dcc48e",
                "outline": "#74796e",
                "surface-bright": "#faf6f0",
                "surface-container-highest": "#e4e0d8",
                "on-tertiary-fixed-variant": "#554020",
                "on-tertiary": "#ffffff",
                "primary-fixed": "#c8e8d0",
                "on-primary-fixed": "#002110"
        },
        "borderRadius": {
                "DEFAULT": "0.5rem",
                "lg": "1rem",
                "xl": "1.5rem",
                "full": "9999px"
        },
        "spacing": {},
        "fontFamily": {
                "headline": [
                        "Literata"
                ],
                "display": [
                        "Literata"
                ],
                "body": [
                        "Nunito Sans"
                ],
                "label": [
                        "Nunito Sans"
                ]
        },
        "fontSize": {}
},
    },
  }
</script>
<style>
        .material-symbols-outlined {
          font-family: 'Material Symbols Outlined';
          font-weight: normal;
          font-style: normal;
          font-size: 24px;
          line-height: 1;
          letter-spacing: normal;
          text-transform: none;
          display: inline-block;
          white-space: nowrap;
          word-wrap: normal;
          direction: ltr;
          -webkit-font-feature-settings: 'liga';
          -webkit-font-smoothing: antialiased;
        }
      </style>
</head>
<body class="bg-surface text-on-surface font-body text-base h-screen overflow-hidden flex">
<!-- SideNavBar -->
<aside class="docked left-0 h-full w-64 border-r border-outline-variant dark:border-outline bg-surface-container-low dark:bg-surface-container flex flex-col p-4 space-y-2 hidden md:flex z-20">
<!-- Brand/Header -->
<div class="px-6 py-10 mb-2 border-b border-outline-variant flex items-center space-x-6">
<div class="h-10 w-10 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center shrink-0 overflow-hidden relative">
<img alt="Admin Avatar" class="object-cover w-full h-full" data-alt="A professional headshot of a village administrator in a well-lit office setting. The lighting is soft and bright, emphasizing a clean, modern aesthetic with a color palette of subtle earthy tones and pristine whites. The mood is approachable yet official." src="https://lh3.googleusercontent.com/aida-public/AB6AXuC5gYZOwk-2I0oMVMB77HyYWC1dnHii2xBfhOb7cSTETqWIgeW3R4RJGRW46IxGKMSSVsCsg1ntGpgxURb7fzy73U9bNwB7wxIaHxdM_TWQGoRImPdjU4KkLlPBbe646Pzz1-AE-IUxuPjYXAPnwbJlqt3_gFNMZHoxAnqO_4SWeTqeReVL9EeAZ4PYQdASwD1Cr-9CzMbnLM-DUfKwzhVR0Ygqs6hs9EK928MDod2u_HFSQPIwdVXosw"/>
</div>
<div class="flex flex-col">
<span class="font-headline text-2xl font-bold text-primary dark:text-primary-fixed leading-tight">Admin Desa</span>
<span class="font-label text-xs font-semibold text-on-surface-variant dark:text-surface-variant">Petugas Administrasi</span>
</div>
</div>
<!-- Navigation Tabs -->
<nav class="flex-1 space-y-1 py-4">
<a class="flex items-center space-x-6 px-6 py-2 rounded-[12px] text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-container-highest transition-all group font-label text-sm font-medium" href="#">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">dashboard</span>
<span>Dashboard</span>
</a>
<a class="flex items-center space-x-6 px-6 py-2 rounded-[12px] bg-secondary-container dark:bg-primary-container text-on-secondary-container dark:text-on-primary-container font-bold transition-all scale-[0.98] duration-100 font-label text-sm" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">groups</span>
<span>Data Penduduk</span>
</a>
<a class="flex items-center space-x-6 px-6 py-2 rounded-[12px] text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-container-highest transition-all group font-label text-sm font-medium" href="#">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">description</span>
<span>Kelola Konten</span>
</a>
<a class="flex items-center space-x-6 px-6 py-2 rounded-[12px] text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-container-highest transition-all group font-label text-sm font-medium" href="#">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">settings</span>
<span>Pengaturan Sistem</span>
</a>
</nav>
<!-- CTA & Footer Tabs -->
<div class="mt-auto space-y-6 pt-6 border-t border-outline-variant">
<button class="w-full bg-primary text-on-primary py-2 rounded-[12px] font-label text-sm font-medium hover:bg-primary-container hover:text-on-primary-container transition-colors shadow-sm">
                Buka Portal Publik
            </button>
<div class="space-y-1">
<a class="flex items-center space-x-6 px-6 py-2 rounded-[12px] text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-container-highest transition-all group font-label text-sm font-medium" href="#">
<span class="material-symbols-outlined group-hover:text-primary transition-colors">help</span>
<span>Bantuan</span>
</a>
<a class="flex items-center space-x-6 px-6 py-2 rounded-[12px] text-on-surface-variant dark:text-surface-variant hover:bg-surface-container-high dark:hover:bg-surface-container-highest transition-all group font-label text-sm font-medium" href="#">
<span class="material-symbols-outlined group-hover:text-error transition-colors">logout</span>
<span>Keluar</span>
</a>
</div>
</div>
</aside>
<!-- Main Content Area -->
<main class="flex-1 flex flex-col h-full bg-background relative overflow-hidden">
<!-- TopNavBar (Admin) -->
<header class="flex justify-between items-center w-full px-6 h-14 bg-surface-container-lowest dark:bg-surface-dim border-b border-outline-variant dark:border-outline shrink-0 z-10 sticky top-0">
<div class="flex items-center space-x-6">
<!-- Mobile Menu Toggle (Visible only on small screens) -->
<button class="md:hidden text-on-surface-variant hover:bg-surface-container p-2 rounded-full transition-colors">
<span class="material-symbols-outlined">menu</span>
</button>
<h1 class="font-headline text-2xl font-bold text-primary dark:text-primary-fixed hidden md:block">
                    Dashboard Admin
                </h1>
</div>
<!-- Search Bar -->
<div class="flex-1 max-w-md ml-6 hidden sm:block">
<div class="relative w-full">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
<input class="w-full bg-surface-container-low border border-outline-variant rounded-[12px] py-2 pl-10 pr-4 text-base focus:outline-none focus:border-tertiary focus:ring-2 focus:ring-tertiary-fixed transition-shadow" placeholder="Cari penduduk, NIK, dll..." type="text"/>
</div>
</div>
<div class="flex items-center space-x-2 ml-auto">
<button aria-label="notifications" class="p-2 text-on-surface-variant hover:bg-surface-container dark:hover:bg-surface-container-high rounded-full transition-colors relative">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-1 right-1 w-2 h-2 bg-error rounded-full"></span>
</button>
<button aria-label="account_circle" class="p-2 text-on-surface-variant hover:bg-surface-container dark:hover:bg-surface-container-high rounded-full transition-colors">
<span class="material-symbols-outlined">account_circle</span>
</button>
</div>
</header>
<!-- Canvas -->
<div class="flex-1 overflow-y-auto p-6 lg:p-10 space-y-6">
<!-- Page Header & Actions -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 pb-2">
<div>
<h2 class="font-headline text-3xl text-on-surface font-bold">Manajemen Warga</h2>
<p class="font-body text-base text-on-surface-variant mt-1">Kelola data demografi dan informasi penduduk desa.</p>
</div>
<div class="flex flex-wrap gap-4">
<button class="flex items-center px-4 py-2 border border-primary text-primary rounded-[12px] font-label text-sm font-medium hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined mr-2 text-[20px]">file_download</span>
                        Impor/Ekspor Excel
                    </button>
<button class="flex items-center px-4 py-2 bg-primary text-on-primary rounded-[12px] font-label text-sm font-medium hover:bg-primary-container shadow-sm transition-transform hover:scale-[1.02]" onclick="document.getElementById('modal-tambah').classList.remove('hidden')">
<span class="material-symbols-outlined mr-2 text-[20px]">add</span>
                        Tambah Warga
                    </button>
</div>
</div>
<!-- Filters & Search Bar (Complex) -->
<div class="bg-surface-container-lowest p-6 rounded-[12px] border border-outline-variant shadow-[0px_4px_20px_rgba(0,0,0,0.02)] flex flex-col lg:flex-row gap-6">
<div class="flex-1">
<label class="block font-label text-xs font-semibold text-on-surface-variant mb-1">Cari Spesifik</label>
<div class="relative w-full">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
<input class="w-full bg-surface border border-outline-variant rounded-[12px] py-2 pl-10 pr-4 text-base focus:outline-none focus:border-tertiary focus:ring-1 focus:ring-tertiary-fixed transition-shadow" placeholder="Nama, Pekerjaan..." type="text"/>
</div>
</div>
<div class="w-full lg:w-48">
<label class="block font-label text-xs font-semibold text-on-surface-variant mb-1">Dusun</label>
<select class="w-full bg-surface border border-outline-variant rounded-[12px] py-2 px-3 text-base focus:outline-none focus:border-tertiary focus:ring-1 focus:ring-tertiary-fixed transition-shadow appearance-none">
<option>Semua Dusun</option>
<option>Mekar Jaya</option>
<option>Suka Maju</option>
<option>Harapan Baru</option>
</select>
</div>
<div class="w-full lg:w-48">
<label class="block font-label text-xs font-semibold text-on-surface-variant mb-1">RT/RW</label>
<select class="w-full bg-surface border border-outline-variant rounded-[12px] py-2 px-3 text-base focus:outline-none focus:border-tertiary focus:ring-1 focus:ring-tertiary-fixed transition-shadow appearance-none">
<option>Semua RT/RW</option>
<option>RT 01 / RW 01</option>
<option>RT 02 / RW 01</option>
</select>
</div>
<div class="w-full lg:w-48">
<label class="block font-label text-xs font-semibold text-on-surface-variant mb-1">Status</label>
<select class="w-full bg-surface border border-outline-variant rounded-[12px] py-2 px-3 text-base focus:outline-none focus:border-tertiary focus:ring-1 focus:ring-tertiary-fixed transition-shadow appearance-none">
<option>Semua Status</option>
<option>Aktif</option>
<option>Pindah</option>
<option>Meninggal</option>
</select>
</div>
</div>
<!-- Data Table Container -->
<div class="bg-surface-container-lowest rounded-[12px] border border-outline-variant overflow-hidden shadow-[0px_4px_20px_rgba(0,0,0,0.02)]">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant">
<th class="py-3 px-6 font-label text-xs font-semibold text-on-surface-variant font-semibold">Nama Lengkap</th>
<th class="py-3 px-6 font-label text-xs font-semibold text-on-surface-variant font-semibold">Gender</th>
<th class="py-3 px-6 font-label text-xs font-semibold text-on-surface-variant font-semibold">Usia</th>
<th class="py-3 px-6 font-label text-xs font-semibold text-on-surface-variant font-semibold">Area (Dusun/RT)</th>
<th class="py-3 px-6 font-label text-xs font-semibold text-on-surface-variant font-semibold">Pekerjaan</th>
<th class="py-3 px-6 font-label text-xs font-semibold text-on-surface-variant font-semibold text-right">Aksi</th>
</tr>
</thead>
<tbody class="font-body text-base divide-y divide-outline-variant">
<tr class="hover:bg-surface-container-lowest transition-colors bg-surface-bright">
<td class="py-4 px-6">
<div class="flex items-center space-x-3">
<div class="h-8 w-8 rounded-[12px] bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
                                            AS
                                        </div>
<span class="font-medium">Ahmad Sutejo</span>
</div>
</td>
<td class="py-4 px-6 text-on-surface-variant">Laki-laki</td>
<td class="py-4 px-6 text-on-surface-variant">45 thn</td>
<td class="py-4 px-6 text-on-surface-variant">Mekar Jaya (01/01)</td>
<td class="py-4 px-6 text-on-surface-variant">Petani</td>
<td class="py-4 px-6 text-right">
<button class="text-on-surface-variant hover:text-primary transition-colors p-1 rounded-[12px]" title="Edit">
<span class="material-symbols-outlined text-[20px]">edit</span>
</button>
<button class="text-on-surface-variant hover:text-error transition-colors p-1 ml-1 rounded-[12px]" title="Hapus">
<span class="material-symbols-outlined text-[20px]">delete</span>
</button>
</td>
</tr>
<tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-low/50">
<td class="py-4 px-6">
<div class="flex items-center space-x-3">
<div class="h-8 w-8 rounded-[12px] bg-secondary/10 text-secondary flex items-center justify-center font-bold text-sm">
                                            SR
                                        </div>
<span class="font-medium">Siti Rohmah</span>
</div>
</td>
<td class="py-4 px-6 text-on-surface-variant">Perempuan</td>
<td class="py-4 px-6 text-on-surface-variant">32 thn</td>
<td class="py-4 px-6 text-on-surface-variant">Suka Maju (02/03)</td>
<td class="py-4 px-6 text-on-surface-variant">Pedagang</td>
<td class="py-4 px-6 text-right">
<button class="text-on-surface-variant hover:text-primary transition-colors p-1 rounded-[12px]" title="Edit">
<span class="material-symbols-outlined text-[20px]">edit</span>
</button>
<button class="text-on-surface-variant hover:text-error transition-colors p-1 ml-1 rounded-[12px]" title="Hapus">
<span class="material-symbols-outlined text-[20px]">delete</span>
</button>
</td>
</tr>
<tr class="hover:bg-surface-container-lowest transition-colors bg-surface-bright">
<td class="py-4 px-6">
<div class="flex items-center space-x-3">
<div class="h-8 w-8 rounded-[12px] bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
                                            BW
                                        </div>
<span class="font-medium">Budi Waseso</span>
</div>
</td>
<td class="py-4 px-6 text-on-surface-variant">Laki-laki</td>
<td class="py-4 px-6 text-on-surface-variant">28 thn</td>
<td class="py-4 px-6 text-on-surface-variant">Mekar Jaya (01/02)</td>
<td class="py-4 px-6 text-on-surface-variant">Wiraswasta</td>
<td class="py-4 px-6 text-right">
<button class="text-on-surface-variant hover:text-primary transition-colors p-1 rounded-[12px]" title="Edit">
<span class="material-symbols-outlined text-[20px]">edit</span>
</button>
<button class="text-on-surface-variant hover:text-error transition-colors p-1 ml-1 rounded-[12px]" title="Hapus">
<span class="material-symbols-outlined text-[20px]">delete</span>
</button>
</td>
</tr>
<tr class="hover:bg-surface-container-lowest transition-colors bg-surface-container-low/50">
<td class="py-4 px-6">
<div class="flex items-center space-x-3">
<div class="h-8 w-8 rounded-[12px] bg-secondary/10 text-secondary flex items-center justify-center font-bold text-sm">
                                            NN
                                        </div>
<span class="font-medium">Nur Ningsih</span>
</div>
</td>
<td class="py-4 px-6 text-on-surface-variant">Perempuan</td>
<td class="py-4 px-6 text-on-surface-variant">51 thn</td>
<td class="py-4 px-6 text-on-surface-variant">Harapan Baru (03/01)</td>
<td class="py-4 px-6 text-on-surface-variant">Mengurus Rumah Tangga</td>
<td class="py-4 px-6 text-right">
<button class="text-on-surface-variant hover:text-primary transition-colors p-1 rounded-[12px]" title="Edit">
<span class="material-symbols-outlined text-[20px]">edit</span>
</button>
<button class="text-on-surface-variant hover:text-error transition-colors p-1 ml-1 rounded-[12px]" title="Hapus">
<span class="material-symbols-outlined text-[20px]">delete</span>
</button>
</td>
</tr>
</tbody>
</table>
</div>
<!-- Pagination -->
<div class="bg-surface-container-lowest px-6 py-4 border-t border-outline-variant flex items-center justify-between">
<span class="font-label text-xs font-semibold text-on-surface-variant">Menampilkan 1-4 dari 1,204 warga</span>
<div class="flex space-x-2">
<button class="p-1 rounded-[12px] text-outline hover:bg-surface-container transition-colors disabled:opacity-50" disabled="">
<span class="material-symbols-outlined">chevron_left</span>
</button>
<button class="p-1 rounded-[12px] text-on-surface hover:bg-surface-container transition-colors">
<span class="material-symbols-outlined">chevron_right</span>
</button>
</div>
</div>
</div>
</div>
</main>
<!-- Modal: Tambah Warga -->
<div class="fixed inset-0 z-50 flex items-center justify-center bg-on-surface/40 backdrop-blur-sm hidden transition-opacity" id="modal-tambah">
<div class="bg-surface-container-lowest w-full max-w-3xl rounded-[12px] shadow-[0px_10px_30px_rgba(0,0,0,0.1)] border border-outline-variant flex flex-col max-h-[921px] mx-4">
<!-- Modal Header -->
<div class="flex justify-between items-center p-6 border-b border-outline-variant">
<h3 class="font-headline text-2xl font-bold text-on-surface">Tambah Data Warga</h3>
<button class="text-on-surface-variant hover:text-on-surface transition-colors rounded-full p-1 hover:bg-surface-container" onclick="document.getElementById('modal-tambah').classList.add('hidden')">
<span class="material-symbols-outlined">close</span>
</button>
</div>
<!-- Modal Body (Form) -->
<div class="p-6 overflow-y-auto flex-1 font-body text-base">
<form class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6">
<!-- Nama -->
<div class="col-span-1 md:col-span-2">
<label class="block font-label text-sm font-medium text-on-surface font-semibold mb-1">Nama Lengkap</label>
<input class="w-full bg-surface border border-outline-variant rounded-[12px] py-2 px-3 text-base focus:outline-none focus:border-tertiary focus:ring-2 focus:ring-tertiary-fixed transition-shadow" placeholder="Masukkan nama lengkap" type="text"/>
</div>
<!-- Tanggal Lahir -->
<div>
<label class="block font-label text-sm font-medium text-on-surface font-semibold mb-1">Tanggal Lahir</label>
<input class="w-full bg-surface border border-outline-variant rounded-[12px] py-2 px-3 text-base focus:outline-none focus:border-tertiary focus:ring-2 focus:ring-tertiary-fixed transition-shadow" type="date"/>
</div>
<!-- Jenis Kelamin -->
<div>
<label class="block font-label text-sm font-medium text-on-surface font-semibold mb-1">Jenis Kelamin</label>
<select class="w-full bg-surface border border-outline-variant rounded-[12px] py-2 px-3 text-base focus:outline-none focus:border-tertiary focus:ring-2 focus:ring-tertiary-fixed transition-shadow appearance-none">
<option disabled="" selected="" value="">Pilih jenis kelamin</option>
<option>Laki-laki</option>
<option>Perempuan</option>
</select>
</div>
<!-- Area (RT/RW/Dusun) -->
<div>
<label class="block font-label text-sm font-medium text-on-surface font-semibold mb-1">Area (Dusun / RT / RW)</label>
<input class="w-full bg-surface border border-outline-variant rounded-[12px] py-2 px-3 text-base focus:outline-none focus:border-tertiary focus:ring-2 focus:ring-tertiary-fixed transition-shadow" placeholder="Contoh: Mekar Jaya / 01 / 01" type="text"/>
</div>
<!-- Status Kedatangan -->
<div>
<label class="block font-label text-sm font-medium text-on-surface font-semibold mb-1">Status Kedatangan</label>
<select class="w-full bg-surface border border-outline-variant rounded-[12px] py-2 px-3 text-base focus:outline-none focus:border-tertiary focus:ring-2 focus:ring-tertiary-fixed transition-shadow appearance-none">
<option disabled="" selected="" value="">Pilih status</option>
<option>Penduduk Asli</option>
<option>Pendatang</option>
<option>Sementara</option>
</select>
</div>
<!-- Pendidikan -->
<div>
<label class="block font-label text-sm font-medium text-on-surface font-semibold mb-1">Pendidikan Terakhir</label>
<select class="w-full bg-surface border border-outline-variant rounded-[12px] py-2 px-3 text-base focus:outline-none focus:border-tertiary focus:ring-2 focus:ring-tertiary-fixed transition-shadow appearance-none">
<option disabled="" selected="" value="">Pilih pendidikan</option>
<option>Tidak/Belum Sekolah</option>
<option>SD/Sederajat</option>
<option>SMP/Sederajat</option>
<option>SMA/Sederajat</option>
<option>Diploma/Sarjana</option>
</select>
</div>
<!-- Pekerjaan -->
<div>
<label class="block font-label text-sm font-medium text-on-surface font-semibold mb-1">Pekerjaan Utama</label>
<input class="w-full bg-surface border border-outline-variant rounded-[12px] py-2 px-3 text-base focus:outline-none focus:border-tertiary focus:ring-2 focus:ring-tertiary-fixed transition-shadow" placeholder="Contoh: Petani, Pedagang..." type="text"/>
</div>
</form>
</div>
<!-- Modal Footer -->
<div class="p-6 border-t border-outline-variant flex justify-end gap-4 bg-surface-container-lowest rounded-b-[12px]">
<button class="px-4 py-2 border border-outline text-on-surface rounded-[12px] font-label text-sm font-medium hover:bg-surface-container-low transition-colors" onclick="document.getElementById('modal-tambah').classList.add('hidden')" type="button">
                    Batal
                </button>
<button class="px-4 py-2 bg-primary text-on-primary rounded-[12px] font-label text-sm font-medium hover:bg-primary-container shadow-sm transition-colors" type="submit">
                    Simpan Data
                </button>
</div>
</div>
</div>
</body></html>

<!-- Landing Page (Terra Version) -->
<!DOCTYPE html>

<html lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Desa Digital - Sistem Informasi Desa</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Literata:ital,opsz,wght@0,7..72,200..900;1,7..72,200..900&amp;family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "inverse-on-surface": "#f5f0e8",
                        "on-tertiary-container": "#554020",
                        "primary-container": "#78a886",
                        "secondary-fixed-dim": "#d4ccbf",
                        "primary-fixed-dim": "#8ecf9e",
                        "on-error-container": "#690005",
                        "on-background": "#2e3230",
                        "tertiary": "#705c30",
                        "surface-dim": "#dbd7cf",
                        "on-secondary": "#ffffff",
                        "inverse-surface": "#2e3230",
                        "on-surface": "#2e3230",
                        "on-tertiary-fixed": "#221a05",
                        "on-secondary-fixed-variant": "#4a4538",
                        "surface-container-high": "#eae6de",
                        "on-primary-fixed-variant": "#2a6038",
                        "on-surface-variant": "#4a4e4a",
                        "secondary": "#6b6358",
                        "on-secondary-container": "#5e5548",
                        "error": "#b83230",
                        "surface-variant": "#e4e0d8",
                        "tertiary-fixed": "#f8e0a8",
                        "secondary-fixed": "#f0e8db",
                        "outline-variant": "#c4c8bc",
                        "on-error": "#ffffff",
                        "error-container": "#ffdad8",
                        "on-primary": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "inverse-primary": "#8ecf9e",
                        "tertiary-container": "#c4a66a",
                        "surface-container-low": "#f5f1ea",
                        "on-secondary-fixed": "#1e1a13",
                        "surface": "#faf6f0",
                        "on-primary-container": "#d8f0de",
                        "surface-container": "#f0ece4",
                        "secondary-container": "#f0e8db",
                        "surface-tint": "#4a7c59",
                        "primary": "#4a7c59",
                        "background": "#faf6f0",
                        "tertiary-fixed-dim": "#dcc48e",
                        "outline": "#74796e",
                        "surface-bright": "#faf6f0",
                        "surface-container-highest": "#e4e0d8",
                        "on-tertiary-fixed-variant": "#554020",
                        "on-tertiary": "#ffffff",
                        "primary-fixed": "#c8e8d0",
                        "on-primary-fixed": "#002110"
                    },
                    "borderRadius": {
                        "DEFAULT": "12px",
                        "lg": "12px",
                        "xl": "12px",
                        "full": "9999px"
                    },
                    "spacing": {
                        "gutter": "24px",
                        "base": "4px",
                        "margin-desktop": "48px",
                        "sm": "16px",
                        "lg": "40px",
                        "md": "24px",
                        "xl": "64px",
                        "margin-mobile": "16px",
                        "xs": "8px"
                    },
                    "fontFamily": {
                        "body-md": ["Nunito Sans", "sans-serif"],
                        "label-md": ["Nunito Sans", "sans-serif"],
                        "headline-lg-mobile": ["Literata", "serif"],
                        "label-sm": ["Nunito Sans", "sans-serif"],
                        "body-lg": ["Nunito Sans", "sans-serif"],
                        "display-lg": ["Literata", "serif"],
                        "headline-md": ["Literata", "serif"],
                        "headline-lg": ["Literata", "serif"]
                    },
                    "fontSize": {
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500" }],
                        "headline-lg-mobile": ["24px", { "lineHeight": "32px", "fontWeight": "700" }],
                        "label-sm": ["12px", { "lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                        "display-lg": ["48px", { "lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "headline-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "700" }]
                    }
                }
            }
        }
    </script>
<style type="text/tailwindcss">
        @layer utilities {
            .glass-card {
                @apply bg-surface-container-lowest/80 backdrop-blur-md border border-outline-variant/30 shadow-[0px_4px_20px_rgba(0,0,0,0.05)];
            }
            .section-padding {
                @apply py-xl px-gutter md:px-margin-desktop;
            }
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md antialiased selection:bg-primary-container selection:text-on-primary-container">
<!-- 1. Navigation Bar (TopNavBar Shared Component) -->
<nav class="bg-surface-container-lowest shadow-sm z-50 docked full-width top-0 sticky">
<div class="flex justify-between items-center w-full px-gutter max-w-7xl mx-auto h-16">
<div class="font-headline-md text-headline-md font-bold text-primary">DesaDigital</div>
<div class="hidden md:flex space-x-lg">
<a class="text-primary border-b-2 border-primary pb-1 font-label-md text-label-md hover:text-primary transition-colors" href="#">Beranda</a>
<a class="text-on-surface-variant font-label-md text-label-md hover:text-primary transition-colors" href="#">Layanan</a>
<a class="text-on-surface-variant font-label-md text-label-md hover:text-primary transition-colors" href="#">Berita</a>
<a class="text-on-surface-variant font-label-md text-label-md hover:text-primary transition-colors" href="#">Statistik</a>
<a class="text-on-surface-variant font-label-md text-label-md hover:text-primary transition-colors" href="#">Transparansi</a>
</div>
<button class="bg-primary-container text-on-primary-container px-md py-sm rounded-lg font-label-md text-label-md hover:bg-surface-tint hover:text-on-primary transition-colors hidden md:block">
                Masuk Portal
            </button>
<button class="md:hidden text-on-surface">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
</nav>
<!-- 2. Hero Section -->
<header class="relative w-full h-[819px] flex items-center justify-center overflow-hidden">
<div class="absolute inset-0 bg-cover bg-center w-full h-full" data-alt="A sweeping, breathtaking drone shot of a pristine rural Indonesian village nestled among vibrant green rice terraces at sunrise. The soft, golden morning light casts long shadows and highlights the mist lingering in the valleys. Traditional houses with terracotta roofs are scattered throughout the landscape. The atmosphere is serene, majestic, and deeply rooted in nature, perfectly capturing the earthy, professional aesthetic of a modern rural community." style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDZjGfG4svct_wfsRob8TaC5DbatwOK9KkN46Tz_zjPIwZmh1Au6k-FnnmOO6WXOd-W9M7GvguRr207ZV1_PBvTWE9I5b_zpQ7oBYdezVqLmYYQt-nLkmulzN12eY23uCjtj4emp0S5NQ3bkJHmXI-sD9NtlbOD1H_6LAV96uFS2VtEx7Ehg8AQ4edDla-lyr0iQNtk3UE9c0gh9fMx1fHou97JNMGaW2IDQjHkj-Xm7gfMkPV46HCMLA')"></div>
<div class="absolute inset-0 bg-primary/60 mix-blend-multiply"></div>
<div class="relative z-10 text-center px-gutter max-w-4xl mx-auto">
<h1 class="font-display-lg text-display-lg text-on-primary mb-md drop-shadow-md">Selamat Datang di Desa Digital</h1>
<p class="font-body-lg text-body-lg text-on-primary/90 mb-xl drop-shadow max-w-2xl mx-auto">Mewujudkan pelayanan publik yang transparan, inovatif, dan berpusat pada masyarakat melalui teknologi digital untuk kemajuan desa.</p>
<button class="bg-secondary text-on-secondary px-xl py-md rounded-lg font-label-md text-label-md hover:bg-surface-tint transition-all shadow-lg hover:-translate-y-1">
                Kenali Desa Kami
            </button>
</div>
</header>
<!-- 3. Sekilas Desa -->
<section class="section-padding bg-surface">
<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-xl items-center">
<div>
<h2 class="font-headline-lg text-headline-lg text-primary mb-sm">Sekilas Desa</h2>
<div class="w-16 h-1 bg-secondary rounded-full mb-md"></div>
<p class="font-body-md text-body-md text-on-surface-variant mb-lg leading-relaxed">
                    Desa kami terletak di dataran tinggi yang subur, dianugerahi pemandangan alam yang memukau dan potensi agrikultur yang melimpah. Kami berkomitmen untuk menjaga tradisi luhur sembari merangkul inovasi digital untuk meningkatkan kesejahteraan warga. Melalui transparansi dan partisipasi aktif masyarakat, kami membangun fondasi desa yang mandiri dan berkelanjutan.
                </p>
<button class="border-2 border-primary text-primary px-lg py-sm rounded-lg font-label-md text-label-md hover:bg-primary hover:text-on-primary transition-colors inline-flex items-center gap-2">
                    Lihat Profil Lengkap
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
</button>
</div>
<div class="relative h-96 rounded-xl overflow-hidden shadow-[0px_10px_30px_rgba(0,0,0,0.1)]">
<img class="object-cover w-full h-full" data-alt="A beautifully composed medium shot showing a diverse group of villagers, young and old, gathered around a modern digital kiosk in the village center. They are interacting with the screen, smiling, and looking engaged. The setting features traditional architectural elements blending seamlessly with modern digital infrastructure. The lighting is bright and optimistic, reflecting a forward-thinking, harmonious community spirit." src="https://lh3.googleusercontent.com/aida-public/AB6AXuC0XHSmz7cJVj03As05RtYezwbunaNsPbIzPb48nRQFU9WEwJZATRi3Gl4DCSn1OMXIvgl3Z5TMdtm-JKI4lGX2_UolrYGzqRqRWJssegol9oH7GpNi2dg9KCX9xc2vk4t1nktzjVl6loEHmCEyULsyUviEWkKd55x_zmMTwb9hTHF7CUzVxxA7hRO4nztBTrsWTa1uS8jWBOaJpO1CISxL2YVpvlUnclNyDoeNV4C9d4E4QVUmp7cXmg"/>
</div>
</div>
</section>
<!-- 4. Demographics Summary -->
<section class="section-padding bg-surface-container-lowest">
<div class="max-w-7xl mx-auto">
<div class="text-center mb-xl">
<h2 class="font-headline-lg text-headline-lg text-primary mb-sm">Data Demografi</h2>
<p class="font-body-md text-body-md text-on-surface-variant">Gambaran umum kependudukan desa secara aktual.</p>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-md">
<!-- Card 1 -->
<div class="glass-card p-lg rounded-xl text-center hover:-translate-y-2 transition-transform duration-300">
<div class="w-16 h-16 mx-auto bg-primary-fixed rounded-full flex items-center justify-center mb-md">
<span class="material-symbols-outlined text-on-primary-fixed text-3xl">group</span>
</div>
<div class="font-display-lg text-display-lg text-primary mb-xs">3,450</div>
<div class="font-label-md text-label-md text-on-surface-variant">Total Warga</div>
</div>
<!-- Card 2 -->
<div class="glass-card p-lg rounded-xl text-center hover:-translate-y-2 transition-transform duration-300">
<div class="w-16 h-16 mx-auto bg-secondary-fixed rounded-full flex items-center justify-center mb-md">
<span class="material-symbols-outlined text-on-secondary-fixed text-3xl">family_restroom</span>
</div>
<div class="font-display-lg text-display-lg text-primary mb-xs">824</div>
<div class="font-label-md text-label-md text-on-surface-variant">Total Keluarga</div>
</div>
<!-- Card 3 -->
<div class="glass-card p-lg rounded-xl text-center hover:-translate-y-2 transition-transform duration-300">
<div class="w-16 h-16 mx-auto bg-tertiary-fixed rounded-full flex items-center justify-center mb-md">
<span class="material-symbols-outlined text-on-tertiary-fixed text-3xl">wc</span>
</div>
<div class="font-headline-md text-headline-md text-primary mb-xs">51% / 49%</div>
<div class="font-label-md text-label-md text-on-surface-variant">Pria / Wanita</div>
</div>
<!-- Card 4 -->
<div class="glass-card p-lg rounded-xl text-center hover:-translate-y-2 transition-transform duration-300">
<div class="w-16 h-16 mx-auto bg-error-container rounded-full flex items-center justify-center mb-md">
<span class="material-symbols-outlined text-on-error-container text-3xl">elderly</span>
</div>
<div class="font-display-lg text-display-lg text-primary mb-xs">&lt;5%</div>
<div class="font-label-md text-label-md text-on-surface-variant">Lansia Rentan</div>
</div>
</div>
</div>
</section>
<!-- 10. Footer (Shared Component) -->
<footer class="bg-primary dark:bg-primary-container w-full py-xl px-gutter grid grid-cols-1 md:grid-cols-3 gap-lg max-w-7xl mx-auto">
<div>
<div class="font-headline-md text-headline-md font-bold text-on-primary mb-md">DesaDigital</div>
<p class="font-body-md text-body-md text-on-primary/80 mb-sm">Sistem Informasi Desa yang transparan, akuntabel, dan melayani masyarakat.</p>
<p class="font-label-sm text-label-sm text-on-primary/80">© 2024 Sistem Informasi Desa Digital. Hak Cipta Dilindungi.</p>
</div>
<div class="flex flex-col space-y-sm">
<h3 class="font-headline-md text-headline-md text-on-primary mb-sm">Tautan Cepat</h3>
<a class="font-label-sm text-label-sm text-on-primary underline hover:text-on-primary transition-colors" href="#">Tentang Kami</a>
<a class="font-label-sm text-label-sm text-on-primary/80 hover:text-on-primary transition-colors" href="#">Peta Desa</a>
<a class="font-label-sm text-label-sm text-on-primary/80 hover:text-on-primary transition-colors" href="#">Kontak Darurat</a>
<a class="font-label-sm text-label-sm text-on-primary/80 hover:text-on-primary transition-colors" href="#">Kebijakan Privasi</a>
</div>
<div class="h-48 bg-surface-container-highest rounded-lg overflow-hidden relative" data-location="Jakarta, Indonesia" style="">
<div class="absolute inset-0 flex items-center justify-center text-on-surface-variant font-label-md text-label-md bg-surface-variant/50">
                [Peta Desa Placeholder]
            </div>
</div>
</footer>
</body></html>