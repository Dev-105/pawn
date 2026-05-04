<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            cursor: none;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: #010105;
            overflow-x: hidden;
            min-height: 100vh;
            cursor: none;
        }

        /* --- CUSTOM CURSOR --- */
        .cursor-dot {
            width: 8px;
            height: 8px;
            background: #C084FC;
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.1s ease, width 0.2s, height 0.2s;
            box-shadow: 0 0 12px rgba(192, 132, 252, 0.8);
        }

        .cursor-ring {
            width: 32px;
            height: 32px;
            border: 2px solid rgba(192, 132, 252, 0.7);
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9998;
            transition: transform 0.15s ease, width 0.3s, height 0.3s, border-color 0.2s;
            backdrop-filter: blur(2px);
        }

        .hover-grow {
            transform: scale(1.8);
            background: #f0a8ff;
            box-shadow: 0 0 20px #C084FC;
        }

        .ring-hover {
            transform: scale(0.6);
            border-color: #e9d5ff;
            border-width: 3px;
        }

        /* animated cosmic orbs */
        .orb-1 {
            position: fixed;
            width: 70vw;
            height: 70vw;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.35) 0%, rgba(76, 29, 149, 0) 70%);
            border-radius: 62% 38% 72% 28% / 46% 45% 55% 54%;
            filter: blur(90px);
            top: -20vh;
            left: -30vw;
            z-index: -2;
            animation: floatOrb 22s infinite alternate ease-in-out;
            pointer-events: none;
        }

        .orb-2 {
            position: fixed;
            width: 75vw;
            height: 75vw;
            background: radial-gradient(circle, rgba(192, 132, 252, 0.3) 0%, rgba(88, 28, 135, 0) 80%);
            bottom: -25vh;
            right: -30vw;
            filter: blur(100px);
            border-radius: 45% 55% 70% 30% / 55% 45% 55% 45%;
            animation: floatOrb2 26s infinite alternate;
            z-index: -2;
        }

        .orb-3 {
            position: fixed;
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, rgba(17, 24, 39, 0) 80%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            filter: blur(110px);
            z-index: -2;
            animation: pulseGlow 12s infinite alternate;
        }

        @keyframes floatOrb {
            0% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }

            100% {
                transform: translate(10%, 15%) rotate(8deg) scale(1.2);
            }
        }

        @keyframes floatOrb2 {
            0% {
                transform: translate(0, 0) rotate(0deg) scale(1);
            }

            100% {
                transform: translate(-12%, -12%) rotate(-6deg) scale(1.25);
            }
        }

        @keyframes pulseGlow {
            0% {
                opacity: 0.3;
                transform: translate(-50%, -50%) scale(0.9);
            }

            100% {
                opacity: 0.7;
                transform: translate(-50%, -50%) scale(1.2);
            }
        }

        ::selection {
            background: #f2eefb;
            color: #8B5CF6;
        }

        /* elegant loading overlay */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: #02020A;
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
            transition: opacity 1.2s cubic-bezier(0.19, 1, 0.22, 1), visibility 0.3s;
            opacity: 1;
            visibility: visible;
        }

        .loading-overlay.hide {
            opacity: 0;
            visibility: hidden;
        }

        .loader-content {
            text-align: center;
            transform: translateY(-10px);
        }

        .logo-glow {
            font-size: 4rem;
            background: linear-gradient(135deg, #C084FC, #8B5CF6, #A855F7);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: pulseLogo 1.8s infinite alternate;
        }

        @keyframes pulseLogo {
            0% {
                text-shadow: 0 0 0px rgba(192, 132, 252, 0);
                opacity: 0.7;
                transform: scale(0.98);
            }

            100% {
                text-shadow: 0 0 20px rgba(192, 132, 252, 0.5);
                opacity: 1;
                transform: scale(1.02);
            }
        }

        .loader-ring {
            width: 60px;
            height: 60px;
            border: 3px solid rgba(139, 92, 246, 0.3);
            border-top: 3px solid #C084FC;
            border-radius: 50%;
            animation: spinRing 0.9s linear infinite;
            margin: 1.5rem auto;
        }

        @keyframes spinRing {
            to {
                transform: rotate(360deg);
            }
        }

        .loader-text {
            font-weight: 500;
            letter-spacing: 2px;
            background: linear-gradient(120deg, #a78bfa, #e879f9);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* glass morphism components */
        .glass-panel {
            background: rgba(12, 10, 22, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(139, 92, 246, 0.35);
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(139, 92, 246, 0.15) inset;
        }

        .feature-card {
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            border-color: rgba(192, 132, 252, 0.7);
            box-shadow: 0 25px 40px -12px rgba(139, 92, 246, 0.4);
        }

        .btn-enter {
            background: linear-gradient(105deg, #8B5CF6, #C084FC);
            transition: 0.2s;
            box-shadow: 0 4px 14px rgba(139, 92, 246, 0.4);
        }

        .btn-enter:active {
            transform: scale(0.96);
        }

        .stat-number {
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }

        .reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.7s ease, transform 0.6s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* floating ambient particles */
        .particle {
            position: fixed;
            background: rgba(192, 132, 252, 0.4);
            border-radius: 50%;
            pointer-events: none;
            z-index: -1;
            filter: blur(1px);
        }

        @keyframes floatParticle {
            0% {
                transform: translateY(0px) translateX(0px);
                opacity: 0.2;
            }

            100% {
                transform: translateY(-50px) translateX(25px);
                opacity: 0.7;
            }
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #8B5CF6;
            border-radius: 10px;
        }

        /* advanced data badges */
        .data-badge {
            background: linear-gradient(145deg, #1e1b2e, #0b0a18);
            border-radius: 20px;
            padding: 2px 12px;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .grid-overlay {
            background-image: radial-gradient(rgba(139, 92, 246, 0.15) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        @media (max-width: 640px) {
            .hero-title {
                font-size: 2rem;
            }

            .glass-panel {
                padding: 1.25rem;
            }

            .cursor-dot,
            .cursor-ring {
                display: none;
            }

            body {
                cursor: auto;
            }
        }
    </style>
    <title>Pawn Rfifisa - Play & Explore</title>
  <meta name="description" content="Pawn Rfifisa platform to play, explore features and enjoy online experience.">

  <link rel="canonical" href="https://pawn.rfifisa.space/">

  <meta name="robots" content="index, follow">

  <!-- Open Graph (Facebook / WhatsApp preview) -->
  <meta property="og:title" content="Pawn Rfifisa - Play & Explore">
  <meta property="og:description" content="Discover Pawn Rfifisa platform and enjoy features">
  <meta property="og:image" content="https://pawn.rfifisa.space/preview.jpg">
  <meta property="og:url" content="https://pawn.rfifisa.space/">
  <meta property="og:type" content="website">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Pawn Rfifisa - Play & Explore">
  <meta name="twitter:description" content="Discover Pawn Rfifisa platform and enjoy features">
  <meta name="twitter:image" content="https://pawn.rfifisa.space/preview.jpg">

  <!-- Optional keywords (Google ما بقاش كيستعملهم بزاف) -->
  <meta name="keywords" content="pawn, rfifisa, platform, game, online">

</head>

<body class="grid-overlay">

    <!-- custom cursor elements -->
    <div class="cursor-dot"></div>
    <div class="cursor-ring"></div>

    <!-- PREMIUM LOADING SCREEN -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loader-content">
            <div class="logo-glow">
                <i class="bi bi-stars"></i> Aetheris
            </div>
            <div class="loader-ring"></div>
            <div class="loader-text text-sm tracking-wider">ORCHESTRATING NEXUS</div>
            <div class="text-purple-300/50 text-[11px] mt-3">secure gateway · neural handshake</div>
        </div>
    </div>

    <!-- dynamic atmospheric orbs -->
    <div class="orb-1"></div>
    <div class="orb-2"></div>
    <div class="orb-3"></div>

    <div class="relative z-10">
        <div class="max-w-7xl mx-auto px-5 py-8 md:py-12">
            <!-- Hero Section Enhanced -->
            <div class="flex flex-col items-center text-center mb-12 reveal" id="heroReveal">
                <div class="inline-flex items-center gap-2 glass-panel rounded-full px-5 py-2 mb-6 backdrop-blur-sm">
                    <i class="bi bi-shield-check text-purple-400 text-sm"></i>
                    <h1 class="text-xs text-gray-300">Pawn Rfifisa</h1>
                    <i class="bi bi-dot text-purple-400"></i>
                    <span class="text-xs text-gray-400">live · 99.99% uptime</span>
                    <i class="bi bi-lightning-charge-fill text-yellow-400 text-[10px] ml-1"></i>
                </div>
                <h1
                    class="hero-title text-4xl sm:text-6xl md:text-7xl font-black bg-gradient-to-r from-purple-300 via-fuchsia-300 to-indigo-300 bg-clip-text text-transparent leading-tight">
                    Where Services<br>Become <span class="underline decoration-purple-500/40">Infinite</span>
                </h1>
                <p class="text-gray-400 mt-6 max-w-2xl text-base sm:text-lg">
                    Unified dashboard for social ecosystems, pawn records, and intelligent automation.
                    Seamless access, biometric-grade security, and real-time orchestration across 14+ platforms.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 mt-8">
                    <a href="./board/" id="enterDashboardBtn"
                        class="btn-enter px-8 py-3 rounded-full text-white font-semibold flex items-center justify-center gap-2 transition-all hover:shadow-xl hover:shadow-purple-600/30">
                        <i class="bi bi-box-arrow-in-right"></i> Enter Dashboard
                    </a>
                    <a href="#features"
                        class="glass-panel px-6 py-3 rounded-full text-purple-200 font-medium flex items-center gap-2 transition-all hover:bg-purple-900/30">
                        <i class="bi bi-compass"></i> Explore Features
                    </a>
                </div>
            </div>

            <!-- extended Platform Statistics with more data -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-20 reveal">
                <div
                    class="glass-panel rounded-2xl p-4 text-center backdrop-blur-md transition-all hover:border-purple-500/60">
                    <i class="bi bi-grid-3x3-gap-fill text-2xl text-purple-400 mb-1 block"></i>
                    <div class="stat-number text-2xl font-bold text-white" data-target="14">0</div>
                    <div class="text-gray-400 text-[10px] mt-1">Active Services</div>
                </div>
                <div class="glass-panel rounded-2xl p-4 text-center backdrop-blur-md">
                    <i class="bi bi-person-vcard text-2xl text-fuchsia-400 mb-1 block"></i>
                    <div class="stat-number text-2xl font-bold text-white" data-target="2847">0</div>
                    <div class="text-gray-400 text-[10px] mt-1">Pawn Records</div>
                </div>
                <div class="glass-panel rounded-2xl p-4 text-center backdrop-blur-md">
                    <i class="bi bi-robot text-2xl text-indigo-400 mb-1 block"></i>
                    <div class="stat-number text-2xl font-bold text-white" data-target="56">0</div>
                    <div class="text-gray-400 text-[10px] mt-1">AI Automations</div>
                </div>
                <div class="glass-panel rounded-2xl p-4 text-center backdrop-blur-md">
                    <i class="bi bi-shield-lock-fill text-2xl text-emerald-400 mb-1 block"></i>
                    <div class="stat-number text-2xl font-bold text-white" data-target="100">0</div>
                    <div class="text-gray-400 text-[10px] mt-1">Encrypted Links</div>
                </div>
                <div class="glass-panel rounded-2xl p-4 text-center backdrop-blur-md">
                    <i class="bi bi-code-slash text-2xl text-orange-400 mb-1 block"></i>
                    <div class="stat-number text-2xl font-bold text-white" data-target="247">0</div>
                    <div class="text-gray-400 text-[10px] mt-1">Dev Projects</div>
                </div>
                <div class="glass-panel rounded-2xl p-4 text-center backdrop-blur-md">
                    <i class="bi bi-cloud-upload text-2xl text-sky-400 mb-1 block"></i>
                    <div class="stat-number text-2xl font-bold text-white" data-target="15820">0</div>
                    <div class="text-gray-400 text-[10px] mt-1">API Calls (24h)</div>
                </div>
            </div>

            <!-- Core Ecosystem Explanation Grid - more cards -->
            <div id="features" class="mb-20">
                <div class="text-center mb-10 reveal">
                    <h2
                        class="text-2xl sm:text-4xl font-bold bg-gradient-to-r from-white to-purple-200 bg-clip-text text-transparent">
                        Core Ecosystem</h2>
                    <p class="text-gray-400 mt-2">Navigate your digital kingdom with fluid intelligence & cross-protocol
                        sync</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="feature-card glass-panel rounded-2xl p-5 transition-all">
                        <div
                            class="w-12 h-12 rounded-xl bg-purple-900/40 flex items-center justify-center mb-4 border border-purple-500/40">
                            <i class="bi bi-wifi text-2xl text-purple-300"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Live Location Mesh</h3>
                        <p class="text-gray-400 text-sm mt-2">Real-time geolocation & proximity services. Connect with
                            spatial intelligence across devices, embed maps and coordinates directly in pawn records.
                            Supports Google Maps & GPS.</p>
                        <div class="mt-3 flex gap-2"><span class="data-badge text-purple-300">12.4k pings</span><span
                                class="data-badge text-gray-400">low latency</span></div>
                    </div>
                    <div class="feature-card glass-panel rounded-2xl p-5 transition-all">
                        <div
                            class="w-12 h-12 rounded-xl bg-pink-900/40 flex items-center justify-center mb-4 border border-pink-500/40">
                            <i class="bi bi-camera-fill text-2xl text-pink-300"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Smart Camera Core</h3>
                        <p class="text-gray-400 text-sm mt-2">Selfie integration & visual recognition. Capture moments,
                            analyzed by neural filters. Image pawns stored with preview gallery and direct lightbox.</p>
                        <div class="mt-3 flex gap-2"><span class="data-badge text-pink-300">+3.2k assets</span><span
                                class="data-badge text-gray-400">facial detection</span></div>
                    </div>
                    <div class="feature-card glass-panel rounded-2xl p-5 transition-all">
                        <div
                            class="w-12 h-12 rounded-xl bg-blue-900/40 flex items-center justify-center mb-4 border border-blue-500/40">
                            <i class="bi bi-spotify text-2xl text-green-400"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Music Stream Sync</h3>
                        <p class="text-gray-400 text-sm mt-2">Spotify integration, curated playlists, and mood-based
                            audio orchestration. Store credentials, manage playback directly inside vault.</p>
                        <div class="mt-3 flex gap-2"><span class="data-badge text-green-300">24M tracks</span><span
                                class="data-badge text-gray-400">real-time sync</span></div>
                    </div>
                    <div class="feature-card glass-panel rounded-2xl p-5 transition-all">
                        <div
                            class="w-12 h-12 rounded-xl bg-orange-900/40 flex items-center justify-center mb-4 border border-orange-500/40">
                            <i class="bi bi-github text-2xl text-gray-200"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white">DevOps Hub</h3>
                        <p class="text-gray-400 text-sm mt-2">GitHub integration, commit analytics, repository
                            management inside Aetheris. Store tokens, webhooks and deploy projects from sandbox.</p>
                        <div class="mt-3 flex gap-2"><span class="data-badge text-orange-300">147 repos</span><span
                                class="data-badge text-gray-400">CI/CD ready</span></div>
                    </div>
                    <div class="feature-card glass-panel rounded-2xl p-5 transition-all">
                        <div
                            class="w-12 h-12 rounded-xl bg-sky-900/40 flex items-center justify-center mb-4 border border-sky-500/40">
                            <i class="bi bi-apple text-2xl text-white"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white">iCloud Bridge</h3>
                        <p class="text-gray-400 text-sm mt-2">Seamless Apple ID ecosystem link & storage vault access.
                            Synchronize credentials with end-to-end encryption and two-factor fallback.</p>
                        <div class="mt-3 flex gap-2"><span class="data-badge text-sky-300">encrypted vault</span><span
                                class="data-badge text-gray-400">iCloud Drive</span></div>
                    </div>
                    <div class="feature-card glass-panel rounded-2xl p-5 transition-all">
                        <div
                            class="w-12 h-12 rounded-xl bg-emerald-900/40 flex items-center justify-center mb-4 border border-emerald-500/40">
                            <i class="bi bi-tiktok text-xl text-white"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Viral Insights</h3>
                        <p class="text-gray-400 text-sm mt-2">TikTok analytics & content scheduler — trends detection in
                            realtime. Monitor growth, hashtag performance, and engagement metrics.</p>
                        <div class="mt-3 flex gap-2"><span class="data-badge text-emerald-300">+230% reach</span><span
                                class="data-badge text-gray-400">smart scheduler</span></div>
                    </div>
                    <!-- two extra cards for more data -->
                    <div class="feature-card glass-panel rounded-2xl p-5 transition-all">
                        <div
                            class="w-12 h-12 rounded-xl bg-amber-900/40 flex items-center justify-center mb-4 border border-amber-500/40">
                            <i class="bi bi-twitter-x text-2xl text-white"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white">X (Twitter) Pulse</h3>
                        <p class="text-gray-400 text-sm mt-2">Real-time feed, sentiment analysis, and automated replies.
                            Manage multiple X accounts and schedule posts.</p>
                        <div class="mt-3 flex gap-2"><span class="data-badge text-amber-300">67k impressions</span>
                        </div>
                    </div>
                    <div class="feature-card glass-panel rounded-2xl p-5 transition-all">
                        <div
                            class="w-12 h-12 rounded-xl bg-rose-900/40 flex items-center justify-center mb-4 border border-rose-500/40">
                            <i class="bi bi-discord text-2xl text-indigo-300"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-white">Discord Gateway</h3>
                        <p class="text-gray-400 text-sm mt-2">Server management, webhook integrations, and community
                            analytics. Automate roles and moderation.</p>
                        <div class="mt-3 flex gap-2"><span class="data-badge text-rose-300">24 servers</span><span
                                class="data-badge text-gray-400">bot active</span></div>
                    </div>
                </div>
            </div>

            <!-- Intelligent Pawn System Explain Section (enhanced stats) -->
            <div class="glass-panel rounded-3xl p-6 md:p-8 mb-16 reveal relative overflow-hidden">
                <div class="absolute right-0 top-0 opacity-10">
                    <i class="bi bi-diagram-3 text-9xl text-purple-500"></i>
                </div>
                <div class="relative z-2">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-white">🧩 Pawn Intelligence Vault</h3>
                            <p class="text-gray-400 text-sm">Credential orchestration · multi-page filters · smart copy
                                actions</p>
                        </div>
                        <div
                            class="flex gap-2 text-purple-300 text-sm border border-purple-500/40 px-3 py-1 rounded-full">
                            <i class="bi bi-database-fill"></i> <span id="pawnFeatureCounter">2.8k+</span> entries
                            managed
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mt-3">
                        <div class="bg-black/40 rounded-xl p-4 border border-purple-500/20">
                            <i class="bi bi-funnel-fill text-purple-300 text-lg"></i>
                            <h4 class="text-white font-semibold mt-1">Dynamic Filtering</h4>
                            <p class="text-gray-300 text-xs">Filter by page (Facebook, Instagram, TikTok) or type:
                                credentials, location, image — instant JavaScript filtering without page reload.
                                Supports 12+ categories.</p>
                        </div>
                        <div class="bg-black/40 rounded-xl p-4 border border-purple-500/20">
                            <i class="bi bi-clipboard-data text-purple-300 text-lg"></i>
                            <h4 class="text-white font-semibold mt-1">One-Click Copy & Export</h4>
                            <p class="text-gray-300 text-xs">Copy emails, passwords, map data or export visible cards as
                                high-res PNG. Integrated with toast notifications. Bulk actions supported.</p>
                        </div>
                        <div class="bg-black/40 rounded-xl p-4 border border-purple-500/20">
                            <i class="bi bi-calendar-check text-purple-300 text-lg"></i>
                            <h4 class="text-white font-semibold mt-1">30-Day Subscription Reset</h4>
                            <p class="text-gray-300 text-xs">Automatic reset of expired subscription limits. Each user
                                has dynamic "count" limit (default 4 pawns). Upgrade plans increase capacity up to
                                unlimited.</p>
                        </div>
                        <div class="bg-black/40 rounded-xl p-4 border border-purple-500/20">
                            <i class="bi bi-images text-purple-300 text-lg"></i>
                            <h4 class="text-white font-semibold mt-1">Gallery & Map Previews</h4>
                            <p class="text-gray-300 text-xs">Location cards show interactive map iframes, image cards
                                display lightbox preview. Fully integrated with Bootstrap Icons and lazy loading.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-wrap justify-between items-center gap-3">
                        <div class="text-gray-400 text-xs flex gap-4">
                            <span><i class="bi bi-shield-check text-purple-400"></i> SSL encrypted vault</span>
                            <span><i class="bi bi-arrow-repeat"></i> real-time sync</span>
                            <span><i class="bi bi-grid-3x3-gap-fill"></i> FAB quick menu</span>
                            <span><i class="bi bi-key-fill"></i> 2FA ready</span>
                        </div>
                        <a href="../rook/"
                            class="text-purple-300 text-sm font-medium flex items-center gap-1 hover:gap-2 transition-all">Access
                            Pawn Dashboard <i class="bi bi-arrow-right-circle-fill"></i></a>
                    </div>
                </div>
            </div>

            <!-- Developer studio & upgrade teaser (extended) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-16 reveal">
                <div class="glass-panel rounded-2xl p-6 flex flex-col">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-900/50 flex items-center justify-center"><i
                                class="bi bi-code-slash text-xl text-indigo-300"></i></div>
                        <h3 class="text-xl font-bold text-white">Aetheris Studio</h3>
                    </div>
                    <p class="text-gray-300 text-sm mb-4">Built-in developer sandbox: create projects (HTML/CSS/JS),
                        upload assets, live preview, and use global <code
                            class="bg-black/50 px-1.5 py-0.5 rounded text-purple-300">send(username, password, id)</code>
                        functions. Desktop only for full editing. Supports multiple projects up to your tier limit.</p>
                    <div class="mt-auto flex justify-between items-center">
                        <span class="text-[11px] text-gray-400"><i class="bi bi-folder2-open"></i> Project limit based
                            on subscription</span>
                        <a href="../developer/" class="text-purple-300 text-sm font-medium">Launch Studio →</a>
                    </div>
                </div>
                <div
                    class="glass-panel rounded-2xl p-6 flex flex-col bg-gradient-to-br from-purple-900/20 to-transparent">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-900/50 flex items-center justify-center"><i
                                class="bi bi-gem text-xl text-amber-300"></i></div>
                        <h3 class="text-xl font-bold text-white">Upgrade Privileges</h3>
                    </div>
                    <p class="text-gray-300 text-sm mb-4">Increase pawn storage up to 5000 records, unlock unlimited
                        developer projects, get extended API access, priority support, early features and custom
                        webhooks. Subscription resets after 30 days but your data remains safe.</p>
                    <div class="mt-auto flex justify-between items-center flex-wrap gap-2">
                        <div class="flex gap-2">
                            <span class="text-[10px] bg-purple-500/20 px-2 py-0.5 rounded-full">+50 projects</span>
                            <span class="text-[10px] bg-purple-500/20 px-2 py-0.5 rounded-full">unlimited pawns</span>
                            <span class="text-[10px] bg-purple-500/20 px-2 py-0.5 rounded-full">API access</span>
                        </div>
                        <a href="../upgrade/"
                            class="btn-enter px-4 py-1.5 rounded-full text-white text-xs font-medium">View plans →</a>
                    </div>
                </div>
            </div>

            <!-- service showcase bar (extended) -->
            <div class="glass-panel rounded-2xl p-5 mb-8 reveal flex flex-wrap justify-between items-center gap-3">
                <div class="flex items-center gap-3">
                    <i class="bi bi-stars text-purple-400 text-xl"></i>
                    <span class="text-white text-sm font-medium">Connected ecosystem</span>
                </div>
                <div class="flex flex-wrap gap-4 text-gray-300 text-sm">
                    <span><i class="bi bi-facebook text-blue-400"></i> Facebook</span>
                    <span><i class="bi bi-instagram text-pink-400"></i> Instagram</span>
                    <span><i class="bi bi-tiktok"></i> TikTok</span>
                    <span><i class="bi bi-github"></i> GitHub</span>
                    <span><i class="bi bi-apple"></i> iCloud</span>
                    <span><i class="bi bi-spotify text-green-400"></i> Spotify</span>
                    <span><i class="bi bi-geo-alt-fill text-red-400"></i> Location</span>
                    <span><i class="bi bi-camera-fill text-yellow-400"></i> Photomath</span>
                    <span><i class="bi bi-discord text-indigo-400"></i> Discord</span>
                    <span><i class="bi bi-twitter-x"></i> X</span>
                    <span><i class="bi bi-whatsapp text-green-500"></i> WhatsApp</span>
                </div>
            </div>

            <div class="text-center text-gray-500 text-xs border-t border-purple-900/30 pt-8 mt-8">
                <i class="bi bi-stars"></i> Aetheris Services — next-generation account hub | secure · modular ·
                infinite
            </div>
        </div>
    </div>

    <script>
        (function () {
            // loading overlay
            const loadingOverlay = document.getElementById('loadingOverlay');
            if (loadingOverlay) {
                setTimeout(() => {
                    loadingOverlay.classList.add('hide');
                    setTimeout(() => { if (loadingOverlay.parentNode) loadingOverlay.style.display = 'none'; }, 1000);
                }, 1300);
            }

            // dynamic counters
            const statNumbers = document.querySelectorAll('.stat-number');
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const target = parseInt(el.getAttribute('data-target'), 10);
                        if (!el.classList.contains('counted')) {
                            el.classList.add('counted');
                            animateNumber(el, 0, target, 1600);
                        }
                        counterObserver.unobserve(el);
                    }
                });
            }, { threshold: 0.3 });
            statNumbers.forEach(el => counterObserver.observe(el));

            function animateNumber(el, start, end, duration) {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const current = Math.floor(progress * (end - start) + start);
                    el.innerText = current.toLocaleString();
                    if (progress < 1) requestAnimationFrame(step);
                    else el.innerText = end.toLocaleString();
                };
                requestAnimationFrame(step);
            }

            // custom cursor logic
            const dot = document.querySelector('.cursor-dot');
            const ring = document.querySelector('.cursor-ring');
            if (dot && ring && window.innerWidth > 768) {
                document.addEventListener('mousemove', (e) => {
                    dot.style.transform = `translate(${e.clientX - 4}px, ${e.clientY - 4}px)`;
                    ring.style.transform = `translate(${e.clientX - 16}px, ${e.clientY - 16}px)`;
                });
                const interactive = document.querySelectorAll('a, button, .feature-card, .btn-enter, [href], .glass-panel');
                interactive.forEach(el => {
                    el.addEventListener('mouseenter', () => {
                        dot.classList.add('hover-grow');
                        ring.classList.add('ring-hover');
                    });
                    el.addEventListener('mouseleave', () => {
                        dot.classList.remove('hover-grow');
                        ring.classList.remove('ring-hover');
                    });
                });
            }

            // floating particles
            function createParticles() {
                for (let i = 0; i < 70; i++) {
                    const particle = document.createElement('div');
                    particle.classList.add('particle');
                    const size = Math.random() * 5 + 2;
                    particle.style.width = size + 'px';
                    particle.style.height = size + 'px';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.top = Math.random() * 100 + '%';
                    particle.style.animation = `floatParticle ${Math.random() * 20 + 15}s infinite alternate ease-in-out`;
                    particle.style.opacity = Math.random() * 0.5 + 0.1;
                    document.body.appendChild(particle);
                }
            }
            const styleSheet = document.createElement("style");
            styleSheet.textContent = `@keyframes floatParticle { 0% { transform: translateY(0px) translateX(0px); opacity: 0.2; } 100% { transform: translateY(-45px) translateX(20px); opacity: 0.7; } }`;
            document.head.appendChild(styleSheet);
            createParticles();

            // scroll reveal
            const revealElements = document.querySelectorAll('.reveal');
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('visible'); revealObserver.unobserve(entry.target); } });
            }, { threshold: 0.15 });
            revealElements.forEach(el => revealObserver.observe(el));

            // dashboard btn smooth transition
            const dashboardBtn = document.getElementById('enterDashboardBtn');
            if (dashboardBtn) {
                dashboardBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const overlay = document.createElement('div');
                    overlay.style.position = 'fixed';
                    overlay.style.inset = '0';
                    overlay.style.backgroundColor = '#000';
                    overlay.style.zIndex = '9999';
                    overlay.style.opacity = '0';
                    overlay.style.transition = 'opacity 0.5s ease';
                    document.body.appendChild(overlay);
                    setTimeout(() => overlay.style.opacity = '1', 10);
                    setTimeout(() => { window.location.href = dashboardBtn.getAttribute('href') || './board/'; }, 500);
                });
            }

            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href && href !== "#" && href.startsWith('#')) {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });

            const pawnSpan = document.getElementById('pawnFeatureCounter');
            if (pawnSpan) {
                let count = 0;
                const interval = setInterval(() => {
                    if (count < 2840) { count += 89; pawnSpan.innerText = count + '+'; }
                    else clearInterval(interval);
                }, 28);
            }
        })();
    </script>
</body>

</html>