---
name: Sistem Informasi Desa — Terra Design System
description: Sistem Desain Terintegrasi untuk Website Profil dan Pendataan Demografi Desa (Terra Palette & Literary Sans Typography)
colors:
  primary: "#4a7c59"
  primary-container: "#78a886"
  primary-fixed: "#c8e8d0"
  secondary: "#6b6358"
  secondary-container: "#f0e8db"
  tertiary: "#705c30"
  tertiary-container: "#c4a66a"
  surface: "#faf6f0"
  surface-container-low: "#f5f1ea"
  surface-container: "#f0ece4"
  surface-container-highest: "#e4e0d8"
  surface-container-lowest: "#ffffff"
  on-surface: "#2e3230"
  on-surface-variant: "#4a4e4a"
  outline: "#74796e"
  outline-variant: "#c4c8bc"
  error: "#b83230"
typography:
  display:
    fontFamily: "'Literata', Georgia, serif"
    fontSize: "clamp(2rem, 5vw, 3.5rem)"
    fontWeight: 600
    lineHeight: 1.2
  headline:
    fontFamily: "'Literata', Georgia, serif"
    fontSize: "1.625rem"
    fontWeight: 600
    lineHeight: 1.3
  title:
    fontFamily: "'Nunito Sans', sans-serif"
    fontSize: "1.125rem"
    fontWeight: 700
    lineHeight: 1.4
  body:
    fontFamily: "'Nunito Sans', sans-serif"
    fontSize: "0.9375rem"
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: "'Nunito Sans', sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    letterSpacing: "0.05em"
rounded:
  sm: "4px"
  md: "8px"
  lg: "12px"
  full: "9999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
  2xl: "48px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#ffffff"
    rounded: "{rounded.lg}"
    padding: "12px 24px"
  button-primary-hover:
    backgroundColor: "{colors.primary-container}"
  button-secondary:
    backgroundColor: "{colors.secondary-container}"
    textColor: "{colors.secondary}"
    rounded: "{rounded.lg}"
    padding: "12px 24px"
  card-surface:
    backgroundColor: "{colors.surface-container-lowest}"
    rounded: "{rounded.lg}"
    padding: "24px"
---

# Design System: Sistem Informasi Desa (Terra Design System)

## Overview

**Creative North Star: "The Terra Oasis (Sanctuarium Bumi Desa)"**

Sistem Informasi Desa dibangun di atas landasan estetika alami yang memadukan kehangatan pedesaan dengan kepraktisan antarmuka digital modern. Penggunaan palet warna tanah (Terra Green, Ochre Bronze, Warm Cream Paper) dan tipografi ganda (Literata Serif & Nunito Sans) menciptakan nuansa resmi, terpercaya, sekaligus ramah bagi masyarakat desa maupun pengunjung umum.

Antarmuka ini memprioritaskan keterbacaan tinggi, pergerakan visual yang halus, dan privasi data kependudukan. Setiap permukaan dirancang menggunakan kontras yang seimbang tanpa warna neon yang menyengat, memberikan rasa aman dan kenyamanan membaca informasi desa secara mendalam.

**Key Characteristics:**
- **Warm Organic Paper Surface**: Latar belakang bernuansa krem/kertas hangat (#FAF6F0) menggantikan warna putih steril.
- **Literary Typography Pairing**: Literata Serif memberikan wibawa pada judul & visi desa, disandingkan dengan Nunito Sans yang jernih untuk teks tubuh & data.
- **Tactile Border Framing**: Menggunakan garis tepi lembut (`outline-variant: #C4C8BC`) daripada bayangan gelap yang berlebihan.
- **Privacy-Aware Data Cards**: Visualisasi data demografi dibuat agregat dan bersih, memfokuskan pada kemudahan pemahaman publik.

---

## Colors

Palet warna Terra terinspirasi dari alam pedesaan: perbukitan hijau, tanah hangat, kerajinan bambu, dan warna beras krem alami.

### Primary
- **Forest Terra Green** (#4A7C59): Digunakan untuk tombol utama, indikator navigasi aktif, header identitas desa, dan elemen aksi utama.
- **Primary Container / Hover** (#78A886): Digunakan untuk status hover tombol utama dan aksen latar lembut.
- **Primary Fixed Dim** (#8ECF9E): Digunakan untuk badge status aktif dan sorotan visual.

### Secondary
- **Warm Taupe Earth** (#6B6358): Digunakan untuk sub-label, teks sekunder, dan garis penanda pendukung.
- **Secondary Container** (#F0E8DB): Digunakan untuk kartu informasi sekunder dan latar belakang tombol ringan.

### Tertiary
- **Ochre Bronze** (#705C30): Digunakan untuk pilar visi desa, lencana prestasi, aksen kategori penting, dan angka statistik puncak.
- **Tertiary Container** (#C4A66A): Digunakan untuk latar badge perunggu/emas dan sorotan penghargaan.

### Neutral
- **Cream Paper Surface** (#FAF6F0): Latar belakang utama seluruh halaman publik.
- **Container Surface Lowest** (#FFFFFF): Latar belakang kartu data dan kontainer putih bersih.
- **Charcoal Slate Text** (#2E3230): Warna teks utama dengan tingkat keterbacaan tinggi.
- **Subtle Outline Variant** (#C4C8BC): Warna pembatas/garis tepi antar elemen.

### Named Rules
**The Natural Earth Rule.** Jangan pernah menggunakan warna murni `#000000` (seram/kaku) atau warna neon berkecerahan 100%. Gunakan Charcoal `#2E3230` untuk teks dan Forest Green `#4A7C59` untuk aksen utama.

---

## Typography

**Display & Headline Font:** Literata (Serif)
**Body & Label Font:** Nunito Sans (Sans-Serif)

**Character:** Kombinasi anggun antara karakter sastra/dokumen resmi desa (Literata) dan kemudahan baca antarmuka aplikasi modern (Nunito Sans).

### Hierarchy
- **Display** (Semibold 600, 34px-48px, line-height 1.2): Tajuk utama hero beranda dan judul utama portal desa.
- **Headline** (Semibold 600, 24px-28px, line-height 1.3): Judul seksi utama (Profil, Data Penduduk, Potensi, Berita).
- **Title** (Bold 700, 16px-18px, line-height 1.4): Judul kartu, sub-bagian, dan nama variabel data.
- **Body** (Regular 400, 14px-16px, line-height 1.6-1.75): Teks deskripsi, isi berita (`.prose-desa`), dan paragraf penjelas.
- **Label** (Semibold 600, 11px-12px, letter-spacing 0.05em, uppercase/normal): Label statistik, nama dusun, tag kategori, dan timestamp.

### Named Rules
**The Literary Header Rule.** Seluruh judul bab dan kutipan visi desa wajib menggunakan font `Literata`. Kata kunci khusus dalam judul dapat menggunakan variasi *italic* Literata untuk memberikan artikulasi estetis.

---

## Layout

- **Container Bounds**: Maksimum 1280px (`max-w-7xl`) dengan padding horizontal `px-4 sm:px-6 lg:px-8`.
- **Rhythm & Grid**: Grid 12-kolom responsif (1 kolom di mobile, 2 kolom di tablet, 3-4 kolom di desktop).
- **Horizontal Scroll Carousel**: Seksi beranda mobile-first mendukung geser horizontal tanpa scrollbar kasar (`scrollbar-hide`).
- **Responsive Breakpoints**: Mobile (<640px), Tablet (640px–1024px), Desktop (>1024px).

---

## Elevation & Depth

Sistem Terra mengedepankan elevasi datar dan berlapis (*tonal layering*) dibandingkan bayangan tinggi yang dramatis.

### Shadow Vocabulary
- **Ambient Card Glow** (`box-shadow: 0px 4px 24px rgba(0, 0, 0, 0.06)`): Digunakan pada kartu statistik & potensi utama.
- **Floating Header Shadow** (`box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.05)`): Digunakan pada navigasi atas sticky saat discroll.
- **Button Lift Shadow** (`box-shadow: 0px 4px 20px rgba(74, 124, 89, 0.35)`): Digunakan khusus pada tombol CTA Hero utama.

### Named Rules
**The Border-Before-Shadow Rule.** Setiap kontainer putih wajib memiliki garis pembatas halus `border border-[#C4C8BC]/50` sebelum menambahkan efek bayangan.

---

## Shapes

- **Base Radius**: `12px` (`rounded-xl` / `0.75rem`) untuk seluruh kartu, tombol, gambar, dan dialog modal.
- **Compact Radius**: `8px` (`rounded-lg`) untuk bidang input teks, dropdown, dan item navigasi mobile.
- **Pill Radius**: `999px` (`rounded-full`) untuk badge kategori, indikator lokasi header, dan chip filter.

---

## Components

### Buttons
- **Primary CTA**: Latar `#4A7C59`, teks `#FFFFFF`, radius `12px`, padding `12px 24px`, font Nunito Sans Bold 14px. Hover: `#78A886` dengan transisi 200ms.
- **Secondary / Ghost**: Latar `#F0E8DB`, teks `#6B6358`, radius `12px`, hover: `#E4DEC8`.

### Demographic Stat Cards
- Latar `#FFFFFF`, garis tepi `1.18px #C4C8BC`, radius `12px`, padding `16px`. Label angka menggunakan Font Literata/Nunito Sans Bold dengan warna Charcoal `#2E3230`.

### Navigation Header
- Kop sticky transparan di atas gambar hero beranda, berubah menjadi putih `bg-surface-container-lowest` berketinggian 64px (`h-16`) saat di-scroll.

---

## Do's and Don'ts

### Do:
- **Do** gunakan variasi *italic* font Literata pada kata kunci judul utama untuk memberikan sentuhan hangat sastra.
- **Do** terapkan penyembunyian sel kecil (<5 warga) pada statistik publik sesuai amanat privasi data kependudukan.
- **Do** pastikan seluruh tombol dan bidang interaktif memiliki state `:hover` dan `:focus-visible` yang terdefinisi dengan jelas.

### Don't:
- **Don't** pernah menampilkan data sensitif individu (NIK, Nomor KK, No HP warga, alamat detail) di halaman atau komponen publik.
- **Don't** gunakan warna latar putih polos murni (`#FFFFFF`) untuk seluruh halaman body; selalu gunakan `#FAF6F0`.
- **Don't** gunakan sudut siku tajam (`0px radius`) pada kartu atau tombol utama portal desa.
