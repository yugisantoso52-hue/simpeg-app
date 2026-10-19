<?php
$htmlContent = <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login SIKAP - Fakultas Keperawatan Universitas Riau</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts: Plus Jakarta Sans & Cinzel -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Font Awesome untuk Ikon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }

    /* 1. Tekstur Latar Belakang Biru Marmer Mewah (Berdasarkan warna #3F80F6) */
    .bg-luxury-blue {
      background-color: #16408b;
      background-image: 
        radial-gradient(at 15% 15%, rgba(63, 128, 246, 0.85) 0px, transparent 55%),
        radial-gradient(at 85% 80%, rgba(29, 78, 216, 0.9) 0px, transparent 55%),
        radial-gradient(at 50% 50%, rgba(15, 35, 75, 0.95) 0px, transparent 100%),
        url("data:image/svg+xml,%3Csvg viewBox='0 0 400 400' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.018' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.08'/%3E%3C/svg%3E");
      background-size: cover;
      position: relative;
    }

    /* 2. Tekstur Marmer Putih Gading (Ivory Marble) untuk Area Form */
    .bg-ivory-marble {
      background-color: #fcfbf7;
      background-image: 
        radial-gradient(at 20% 25%, rgba(212, 175, 55, 0.07) 0px, transparent 50%),
        radial-gradient(at 80% 75%, rgba(180, 150, 70, 0.06) 0px, transparent 50%),
        linear-gradient(125deg, transparent 38%, rgba(212, 175, 55, 0.09) 40%, transparent 42%),
        linear-gradient(25deg, transparent 63%, rgba(160, 140, 100, 0.07) 65%, transparent 67%);
    }

    /* 3. Efek Kartu Kaca (Glassmorphism) Berbingkai Emas di Panel Kiri */
    .glass-gold-card {
      background: rgba(15, 38, 82, 0.55);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(212, 175, 55, 0.45);
      box-shadow: 0 12px 32px 0 rgba(0, 0, 0, 0.28);
    }

    /* 4. Input Form dengan Border Emas Halus */
    .input-gold {
      background-color: rgba(255, 255, 255, 0.92);
      border: 1.5px solid #c8a951;
      transition: all 0.25s ease;
    }
    .input-gold:focus-within {
      border-color: #3F80F6;
      box-shadow: 0 0 0 3px rgba(63, 128, 246, 0.2), 0 0 12px rgba(212, 175, 55, 0.35);
      background-color: #ffffff;
    }

    /* 5. Tombol Utama Gradasi Biru-Emas Premium (Sesuai Gbr 1) */
    .btn-luxury-blue-gold {
      background: linear-gradient(95deg, #102a5c 0%, #2563eb 45%, #c59b27 90%, #e6c565 100%);
      border: 1px solid #eab308;
      box-shadow: 0 8px 20px -4px rgba(37, 99, 235, 0.45), 0 4px 10px -2px rgba(212, 175, 55, 0.35);
      transition: all 0.3s ease;
    }
    .btn-luxury-blue-gold:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 25px -4px rgba(37, 99, 235, 0.6), 0 6px 14px -2px rgba(212, 175, 55, 0.5);
      filter: brightness(1.06);
    }

    /* 6. Tombol Pill Emas Eksekutif */
    .pill-gold {
      background: linear-gradient(90deg, #b8860b 0%, #e6c565 50%, #c59b27 100%);
      color: #0f172a;
      border: 1px solid #fef08a;
      box-shadow: 0 4px 12px rgba(184, 134, 11, 0.3);
    }
  </style>
</head>
<body class="min-h-screen flex flex-col lg:flex-row bg-ivory-marble">

  <!-- ===================================================================== -->
  <!-- PANEL KIRI: HANYA TAMPIL DI DESKTOP (LAYAR PENUH >= 1024px)            -->
  <!-- ===================================================================== -->
  <section class="hidden lg:flex lg:w-[56%] bg-luxury-blue text-white p-12 xl:p-16 flex-col justify-between relative overflow-hidden">
    
    <!-- Ornamen Garis Geometris Emas Berkilau (Atas Kanan & Bawah Kiri) -->
    <svg class="absolute top-0 right-0 w-96 h-96 pointer-events-none opacity-85" viewBox="0 0 300 300" fill="none">
      <path d="M120 -10 L310 140 M180 -10 L240 300 M70 -10 L310 70" stroke="url(#goldGrad1)" stroke-width="1.8"/>
      <circle cx="212" cy="64" r="3.5" fill="#fde047" />
      <defs>
        <linearGradient id="goldGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="#fde047" stop-opacity="0.9"/>
          <stop offset="50%" stop-color="#d4af37" stop-opacity="0.8"/>
          <stop offset="100%" stop-color="#9a7b2c" stop-opacity="0.1"/>
        </linearGradient>
      </defs>
    </svg>

    <svg class="absolute bottom-0 left-0 w-96 h-96 pointer-events-none opacity-85" viewBox="0 0 300 300" fill="none">
      <path d="M-10 140 L200 310 M-10 210 L260 280 M40 310 L140 160" stroke="url(#goldGrad2)" stroke-width="1.8"/>
      <circle cx="92" cy="223" r="4" fill="#fde047" />
      <defs>
        <linearGradient id="goldGrad2" x1="0%" y1="100%" x2="100%" y2="0%">
          <stop offset="0%" stop-color="#fde047" stop-opacity="0.95"/>
          <stop offset="60%" stop-color="#d4af37" stop-opacity="0.7"/>
          <stop offset="100%" stop-color="#d4af37" stop-opacity="0"/>
        </linearGradient>
      </defs>
    </svg>

    <!-- Bagian Atas: Identitas Kampus & Logo Asli (Warna Hijau Asli Dipertahankan) -->
    <div class="relative z-10 flex items-center gap-4">
      <div class="w-16 h-16 rounded-full bg-white/10 p-1.5 border border-yellow-400/50 shadow-lg flex items-center justify-center shrink-0">
        <!-- Logo UNRI -->
        <img src="LOGO_UNRI_PLACEHOLDER" 
             alt="Logo Universitas Riau" class="w-full h-full object-contain">
      </div>
      <div>
        <p class="text-xs font-semibold tracking-widest text-blue-100 uppercase">Kementerian Pendidikan Tinggi, Sains, dan Teknologi</p>
        <h2 class="text-lg font-extrabold tracking-wide text-amber-300">UNIVERSITAS RIAU</h2>
        <p class="text-sm font-bold tracking-wider text-white">FAKULTAS KEPERAWATAN</p>
      </div>
    </div>

    <!-- Bagian Tengah: Headline & Kartu Keamanan Glassmorphic -->
    <div class="relative z-10 my-auto py-8 max-w-2xl">
      <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-blue-950/70 border border-amber-400/40 text-xs font-semibold tracking-wider text-amber-200 mb-5">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        SISTEM INFORMASI KEPEGAWAIAN (SIKAP)
      </div>

      <h1 class="text-4xl xl:text-5xl font-extrabold leading-tight tracking-tight text-white mb-4 drop-shadow">
        Platform Terpadu Manajemen SDM & Kinerja ASN
      </h1>
      <p class="text-blue-100/90 text-sm xl:text-base leading-relaxed mb-8">
        Mewujudkan tata kelola kepegawaian Fakultas Keperawatan Universitas Riau yang transparan, akuntabel, dan berbasis digital untuk seluruh Dosen dan Tenaga Kependidikan.
      </p>

      <!-- Kartu Informasi Glassmorphic Berbingkai Emas -->
      <div class="glass-gold-card rounded-2xl p-6 mb-7">
        <div class="flex items-center gap-2.5 text-amber-300 font-bold text-xs tracking-wider uppercase mb-4">
          <i class="fa-solid fa-triangle-exclamation text-amber-400"></i>
          <span>Keamanan Akun & Fitur Resmi SIKAP</span>
        </div>
        <ul class="space-y-3 text-xs xl:text-sm text-blue-50/90 leading-relaxed">
          <li class="flex items-start gap-2.5">
            <span class="text-amber-400 font-bold">•</span>
            <span><strong class="text-white">Jaga Kerahasiaan Akun:</strong> Jangan bagikan NIP dan kata sandi Anda kepada pihak lain yang tidak berwenang. <span class="text-amber-300 font-medium">Segera ganti Password login pertama Anda.</span></span>
          </li>
          <li class="flex items-start gap-2.5">
            <span class="text-amber-400 font-bold">•</span>
            <span><strong class="text-white">Watermark & Tanda Tangan Digital:</strong> Seluruh berkas SK dan Formulir Cuti yang diunduh kini otomatis dilindungi tanda tangan digital terverifikasi.</span>
          </li>
          <li class="flex items-start gap-2.5">
            <span class="text-amber-400 font-bold">•</span>
            <span><strong class="text-white">Dukungan PWA & Offline:</strong> Aplikasi dapat diinstal ke layar utama HP Anda dengan fitur penyimpanan draf logbook saat tanpa internet.</span>
          </li>
        </ul>
      </div>

      <!-- Deretan Tombol Pintasan Bawah -->
      <div class="flex flex-wrap items-center gap-3">
        <a href="#" class="pill-gold px-4 py-2 rounded-full text-xs font-bold flex items-center gap-2 transition hover:scale-105">
          <span>🎯</span>
          <span>Paparan Eksekutif & Ringkasan SIKAP</span>
        </a>
        <a href="#" class="glass-gold-card px-4 py-2 rounded-full text-xs font-semibold text-blue-100 hover:text-white hover:border-amber-300 flex items-center gap-2 transition">
          <i class="fa-solid fa-magnifying-glass text-sky-300"></i>
          <span>Verifikasi Dokumen SK Publik</span>
        </a>
        <a href="#" class="glass-gold-card px-4 py-2 rounded-full text-xs font-semibold text-blue-100 hover:text-white hover:border-amber-300 flex items-center gap-2 transition">
          <i class="fa-solid fa-globe text-sky-300"></i>
          <span>Portal Resmi FKP UNRI</span>
        </a>
      </div>
    </div>

    <!-- Bagian Bawah: Footer Kiri -->
    <div class="relative z-10 flex items-center justify-between text-xs text-blue-200/80 border-t border-amber-400/20 pt-4">
      <div>
        <p class="font-medium text-white/90">© 2026 Fakultas Keperawatan Universitas Riau</p>
        <p>Pengembang Sistem: Rahmad Hidayat Majlan, S.T. (RHM)</p>
      </div>
      <div class="text-right">
        <p>Kampus Bina Widya Gedung HSC Km. 12,5 Pekanbaru</p>
      </div>
    </div>
  </section>

  <!-- ===================================================================== -->
  <!-- PANEL KANAN (DESKTOP) & TAMPILAN UTAMA (MOBILE / RESPONSIF)           -->
  <!-- ===================================================================== -->
  <main class="w-full lg:w-[44%] min-h-screen bg-ivory-marble flex flex-col justify-between relative p-4 sm:p-8 lg:p-6">

    <!-- ORNAMEN SUDUT ATAS KHUSUS MOBILE (Tampil saat browser dikecilkan) -->
    <div class="lg:hidden absolute top-0 left-0 right-0 h-28 pointer-events-none overflow-hidden z-0">
      <svg class="w-full h-full" viewBox="0 0 400 110" preserveAspectRatio="none">
        <polygon points="0,0 165,0 0,95" fill="#1d4ed8"/>
        <polygon points="0,0 135,0 0,75" fill="#3F80F6"/>
        <polyline points="0,97 168,0" stroke="#d4af37" stroke-width="4" fill="none"/>
        
        <polygon points="400,0 235,0 400,95" fill="#1d4ed8"/>
        <polygon points="400,0 265,0 400,75" fill="#3F80F6"/>
        <polyline points="400,97 232,0" stroke="#d4af37" stroke-width="4" fill="none"/>
      </svg>
    </div>

    <!-- Bingkai Emas Halus Mengelilingi Panel Kanan -->
    <div class="w-full h-full flex-1 border-[1.5px] border-[#d4af37]/70 rounded-2xl p-6 sm:p-10 xl:p-12 flex flex-col justify-between relative z-10 shadow-[inset_0_0_25px_rgba(212,175,55,0.08)]">

      <!-- HEADER KHUSUS MOBILE (Logo Asli + Identitas Fakultas saat layar dikecilkan) -->
      <div class="lg:hidden flex flex-col items-center text-center mt-4 mb-6">
        <div class="w-16 h-16 rounded-full bg-white p-1.5 border-2 border-[#d4af37] shadow-md mb-3">
          <img src="LOGO_UNRI_PLACEHOLDER" 
               alt="Logo UNRI" class="w-full h-full object-contain">
        </div>
        <p class="text-[10px] sm:text-xs font-bold tracking-wider text-slate-600 uppercase">Kementerian Pendidikan Tinggi, Sains, dan Teknologi</p>
        <h2 class="text-base sm:text-lg font-extrabold text-[#16408b] tracking-wide">UNIVERSITAS RIAU</h2>
        <p class="text-xs sm:text-sm font-bold text-slate-800">FAKULTAS KEPERAWATAN</p>
        
        <a href="#" class="mt-3 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-amber-100/90 border border-amber-400 text-[11px] font-bold text-amber-900 shadow-sm">
          <span>🎯 Paparan Eksekutif & Ringkasan SIKAP</span>
        </a>
        <div class="w-full border-b border-amber-500/20 mt-6"></div>
      </div>

      <!-- Spacer Atas untuk Desktop -->
      <div class="hidden lg:block"></div>

      <!-- AREA FORM LOGIN -->
      <div class="w-full max-w-md mx-auto my-auto">
        
        <!-- Badge Portal Akses Pegawai -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-50 border border-[#d4af37]/60 text-[11px] font-bold text-amber-900 tracking-wide uppercase mb-4 shadow-sm">
          <i class="fa-solid fa-lock text-amber-600"></i>
          <span>Portal Akses Pegawai</span>
        </div>

        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-2">
          Selamat Datang
        </h2>
        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-7">
          Silakan masukkan identitas NIP atau Username untuk mengakses sistem kepegawaian Anda.
        </p>

        <form action="#" method="POST" class="space-y-5">
          <!-- Input NIP / Username -->
          <div>
            <label for="username" class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-2">
              NIP / USERNAME
            </label>
            <div class="input-gold rounded-xl flex items-center px-4 py-3">
              <i class="fa-regular fa-user text-slate-400 text-sm mr-3"></i>
              <input type="text" id="username" name="username" 
                     placeholder="Contoh: 198501012010121001" 
                     class="w-full bg-transparent text-sm text-slate-800 placeholder-slate-400 focus:outline-none" required>
            </div>
          </div>

          <!-- Input Kata Sandi -->
          <div>
            <label for="password" class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider mb-2">
              KATA SANDI
            </label>
            <div class="input-gold rounded-xl flex items-center px-4 py-3">
              <i class="fa-solid fa-lock text-slate-400 text-sm mr-3"></i>
              <input type="password" id="password" name="password" 
                     placeholder="Masukkan kata sandi akun Anda" 
                     class="w-full bg-transparent text-sm text-slate-800 placeholder-slate-400 focus:outline-none" required>
              <button type="button" onclick="togglePass()" class="text-slate-400 hover:text-slate-600 focus:outline-none ml-2">
                <i id="eyeIcon" class="fa-regular fa-eye text-sm"></i>
              </button>
            </div>
          </div>

          <!-- Info Box Login Pertama Kali -->
          <div class="rounded-xl bg-blue-50/80 border border-blue-200/90 p-3.5 flex items-start gap-3">
            <span class="text-base leading-none mt-0.5">💡</span>
            <p class="text-xs text-slate-700 leading-relaxed">
              <strong class="font-bold text-slate-900">Login Pertama Kali:</strong> Masukkan 18 digit NIP Anda dan kata sandi default 
              <code class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-900 font-bold border border-blue-300">Password</code>.
            </p>
          </div>

          <!-- Ingat Saya & Lupa Password -->
          <div class="flex items-center justify-between text-xs pt-1">
            <label class="inline-flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
              <input type="checkbox" class="rounded border-amber-500 text-blue-600 focus:ring-blue-500 w-4 h-4">
              <span>Ingat saya</span>
            </label>
            <a href="#" class="font-bold text-[#16408b] hover:text-blue-600 transition">
              Lupa password?
            </a>
          </div>

          <!-- Tombol Submit Gradasi Biru-Emas -->
          <button type="submit" class="btn-luxury-blue-gold w-full py-3.5 px-6 rounded-full text-white font-bold text-sm tracking-wider uppercase flex items-center justify-center gap-2 cursor-pointer">
            <span>MASUK KE SIKAP</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
          </button>
        </form>

        <!-- Tautan Layanan Publik Khusus Mobile (Muncul saat layar dikecilkan) -->
        <div class="lg:hidden mt-8 pt-5 border-t border-amber-500/20 text-center">
          <p class="text-[11px] font-bold tracking-widest text-slate-500 uppercase mb-3">LAYANAN PUBLIK</p>
          <div class="flex items-center justify-center gap-4 text-xs font-bold text-[#16408b]">
            <a href="#" class="flex items-center gap-1.5 hover:underline">
              <span>🔍 Cek SK Publik</span>
            </a>
            <span class="text-slate-300">•</span>
            <a href="#" class="flex items-center gap-1.5 hover:underline">
              <span>🌐 Web Fakultas</span>
            </a>
          </div>
        </div>

      </div>

      <!-- FOOTER STATUS SERVER -->
      <div class="w-full pt-6 mt-6 border-t border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-slate-600">
        <div>
          <div class="flex items-center gap-2 font-semibold text-slate-800">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Server SIKAP Online</span>
          </div>
          <p class="text-slate-500 mt-0.5">Pengembang: Rahmad Hidayat Majlan, S.T. (RHM)</p>
        </div>
        <div class="sm:text-right font-medium text-slate-500">
          Enkripsi TLS/SSL 256-Bit
        </div>
      </div>

    </div>

    <!-- ORNAMEN SUDUT BAWAH KHUSUS MOBILE -->
    <div class="lg:hidden absolute bottom-0 left-0 right-0 h-6 bg-gradient-to-r from-[#16408b] via-[#d4af37] to-[#16408b] opacity-90"></div>
  </main>

  <!-- Script Fitur Lihat/Sembunyikan Kata Sandi -->
  <script>
    function togglePass() {
      const passInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
      if (passInput.type === 'password') {
        passInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
      } else {
        passInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>
HTML;

$logoData = base64_encode(file_get_contents('c:/laragon/www/simpeg/public/logo-unri.png'));
$logoDataUri = 'data:image/png;base64,' . $logoData;

$htmlContent = str_replace('LOGO_UNRI_PLACEHOLDER', $logoDataUri, $htmlContent);

$artifactDir = 'C:/Users/OPAC/.gemini/antigravity/brain/4932a1e1-a6f3-40b8-85f2-b94516259d03';
file_put_contents($artifactDir . '/preview-script-user.html', $htmlContent);
echo "Wrote preview-script-user.html successfully!\n";
