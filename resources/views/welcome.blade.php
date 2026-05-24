<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KeyForge - The Forge Experience</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #030305;
            --primary: #6366f1; /* Indigo */
            --secondary: #ec4899; /* Pink */
            --accent: #06b6d4; /* Cyan */
            --text-main: #ffffff;
            --text-muted: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            width: 100%;
            min-height: 100%;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        /* 1. Animated Mesh Gradient (No Images) */
        .ambient-light {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            z-index: -2;
            opacity: 0.5;
            animation: floatBlob 20s infinite alternate ease-in-out;
        }

        .light-1 {
            width: 40vw; height: 40vw;
            background: var(--primary);
            top: -10%; left: -10%;
        }

        .light-2 {
            width: 35vw; height: 35vw;
            background: var(--secondary);
            bottom: -10%; right: -10%;
            animation-delay: -5s;
            animation-duration: 25s;
        }

        .light-3 {
            width: 30vw; height: 30vw;
            background: var(--accent);
            top: 40%; left: 30%;
            animation-delay: -10s;
            animation-duration: 30s;
        }

        @keyframes floatBlob {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            50% { transform: translate(15vw, 10vw) scale(1.2) rotate(180deg); }
            100% { transform: translate(-5vw, 20vw) scale(0.9) rotate(360deg); }
        }

        /* 2. Cyber Grid Background */
        .grid-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: 
                linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: -1;
            transform: perspective(500px) rotateX(60deg) translateY(-100px) translateZ(-200px);
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            0% { background-position: 0 0; }
            100% { background-position: 0 50px; }
        }

        /* 3. Interactive Mouse Glow Tracker */
        #cursor-glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            z-index: 1;
            transition: width 0.3s, height 0.3s;
        }

        /* Layout */
        .container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 10;
        }

        /* Nav */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 4rem;
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(10px);
            background: rgba(3, 3, 5, 0.45);
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--text-main);
            text-decoration: none;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.5);
        }

        .nav-links { display: flex; gap: 3rem; align-items: center; }
        .nav-links a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .nav-links a:hover { color: var(--text-main); }

        .btn-login {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            padding: 0.6rem 1.8rem;
            border-radius: 50px;
            backdrop-filter: blur(10px);
            color: white !important;
            transition: all 0.3s ease !important;
        }

        .btn-login:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.3);
            box-shadow: 0 0 20px rgba(255,255,255,0.1);
        }

        /* 4. 3D Hero Section */
        .hero {
            min-height: calc(100vh - 96px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 1.5rem 5rem;
        }

        .scene {
            position: relative;
        }

        /* The Glass Card */
        .hero-card {
            position: relative;
            background: rgba(10, 10, 15, 0.4);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 5rem 4rem;
            border-radius: 32px;
            text-align: center;
            max-width: 900px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.6), inset 0 0 0 1px rgba(255,255,255,0.05);
            overflow: hidden;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border-radius: 32px;
            padding: 1.5px; /* Border thickness */
            background: radial-gradient(
                500px circle at var(--mouse-x, -999px) var(--mouse-y, -999px),
                rgba(99, 102, 241, 0.5),
                rgba(236, 72, 153, 0.4) 30%,
                transparent 70%
            );
            -webkit-mask: 
                linear-gradient(#fff 0 0) content-box, 
                linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            z-index: 2;
        }

        .hero-card-glow {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border-radius: 32px;
            background: radial-gradient(
                800px circle at var(--mouse-x, -999px) var(--mouse-y, -999px),
                rgba(255, 255, 255, 0.05),
                transparent 50%
            );
            pointer-events: none;
            z-index: 0;
        }

        .hero-card > *:not(.hero-card-glow) {
            position: relative;
            z-index: 1;
        }

        /* 3D Pop Out Elements */
        .tagline {
            display: inline-block;
            color: var(--accent);
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            background: rgba(6, 182, 212, 0.1);
            border: 1px solid rgba(6, 182, 212, 0.2);
        }

        .title {
            font-size: 5rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(to right, #ffffff, #a5b4fc, #f9a8d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }

        .subtitle {
            font-size: 1.3rem;
            color: var(--text-muted);
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto; margin-right: auto;
            line-height: 1.6;
        }

        .action-btns {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
        }

        .btn-primary-3d {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-decoration: none;
            padding: 1.2rem 3rem;
            border-radius: 50px;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .btn-primary-3d:hover {
            box-shadow: 0 15px 40px rgba(236, 72, 153, 0.5);
            transform: scale(1.05) translateY(-5px);
        }

        .btn-secondary-3d {
            background: transparent;
            color: white;
            text-decoration: none;
            padding: 1.2rem 3rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .btn-secondary-3d:hover {
            background: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.3);
            transform: translateY(-5px);
        }

        /* Floating Pure CSS Geometries */
        .geometry {
            position: absolute;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            animation: float 10s infinite alternate ease-in-out;
            pointer-events: none;
        }

        .geo-1 {
            width: 150px; height: 150px;
            top: 20%; left: 10%;
        }

        .geo-2 {
            width: 100px; height: 100px;
            border-radius: 50%;
            bottom: 20%; right: 15%;
            animation-duration: 8s;
            animation-delay: -2s;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            100% { transform: translateY(-50px); }
        }

        .content-stack {
            width: min(1180px, calc(100% - 3rem));
            margin: 0 auto 5rem;
            display: grid;
            gap: 2rem;
        }

        .info-section {
            background: rgba(10, 10, 15, 0.45);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 28px;
            padding: 2.5rem;
            backdrop-filter: blur(18px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.35);
        }

        .section-header {
            display: grid;
            gap: 0.8rem;
            margin-bottom: 1.75rem;
        }

        .section-header h2 {
            font-size: 2.1rem;
            font-weight: 800;
        }

        .section-header p {
            color: var(--text-muted);
            line-height: 1.7;
            max-width: 720px;
        }

        .feature-grid,
        .doc-grid,
        .pricing-grid {
            display: grid;
            gap: 1.25rem;
        }

        .feature-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .doc-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .pricing-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .panel {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 22px;
            padding: 1.5rem;
            display: grid;
            gap: 0.85rem;
        }

        .panel h3 {
            font-size: 1.15rem;
            font-weight: 700;
        }

        .panel p,
        .panel li {
            color: var(--text-muted);
            line-height: 1.6;
        }

        .panel ul {
            list-style: none;
            display: grid;
            gap: 0.65rem;
        }

        .price {
            font-size: 2.3rem;
            font-weight: 900;
            color: white;
        }

        .eyebrow {
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 0.16em;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .panel .btn-secondary-3d,
        .panel .btn-primary-3d {
            justify-self: start;
            padding: 0.9rem 1.4rem;
            font-size: 0.95rem;
        }

        @media (max-width: 980px) {
            nav {
                padding: 1.25rem 1.5rem;
                gap: 1rem;
                flex-wrap: wrap;
            }

            .nav-links {
                gap: 1rem;
                flex-wrap: wrap;
            }

            .title {
                font-size: 3.6rem;
            }

            .hero-card {
                padding: 3rem 2rem;
            }

            .feature-grid,
            .doc-grid,
            .pricing-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .title {
                font-size: 2.8rem;
            }

            .subtitle {
                font-size: 1.05rem;
            }

            .action-btns {
                flex-direction: column;
            }

            .action-btns a {
                width: 100%;
                text-align: center;
            }

            .info-section {
                padding: 1.5rem;
            }
        }

    </style>
</head>
<body>

    <!-- CSS Only Ambient Background -->
    <div class="ambient-light light-1"></div>
    <div class="ambient-light light-2"></div>
    <div class="ambient-light light-3"></div>
    <div class="grid-bg"></div>

    <!-- Mouse Tracking Glow -->
    <div id="cursor-glow"></div>

    <!-- Floating Geometries -->
    <div class="geometry geo-1"></div>
    <div class="geometry geo-2"></div>

    <div class="container">
        <nav>
            <a href="{{ url('/') }}" class="logo">
                <div class="logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                    </svg>
                </div>
                KeyForge
            </a>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#documentation">Documentation</a>
                <a href="#pricing">Pricing</a>
                <a href="{{ route('login') }}" class="btn-login">Sign In</a>
            </div>
        </nav>

        <section class="hero" id="hero">
            <div class="scene" id="scene">
                <div class="hero-card">
                    <div class="tagline">Next-Gen API Management</div>
                    <h1 class="title">Forge Your<br>API Ecosystem</h1>
                    <div class="subtitle">
                        No images. Just pure code, mathematics, and design. Protect, manage, and scale your API keys with a platform built for the future.
                    </div>
                    <div class="action-btns">
                        <a href="{{ route('register') }}" class="btn-primary-3d">Start For Free</a>
                        <a href="#documentation" class="btn-secondary-3d">View Documentation</a>
                    </div>
                </div>
            </div>
        </section>

        <div class="content-stack">
            <section class="info-section" id="features">
                <div class="section-header">
                    <div class="eyebrow">Features</div>
                    <h2>Built for teams that manage keys, clients, and auth risk at scale.</h2>
                    <p>KeyForge gives you a single surface to control account access, protect sensitive settings, and move fast without turning your internal admin flow into a pile of ad-hoc forms.</p>
                </div>

                <div class="feature-grid">
                    <article class="panel">
                        <h3>Authentication Core</h3>
                        <p>Registration, login, password reset, email verification, and protected account screens are wired through Laravel Fortify.</p>
                    </article>
                    <article class="panel">
                        <h3>Two-Factor Security</h3>
                        <p>Users can enable 2FA, confirm it with a TOTP code, regenerate recovery codes, and disable it when needed.</p>
                    </article>
                    <article class="panel">
                        <h3>Profile Management</h3>
                        <p>Account owners can update their name, email, password, and avatar from a single authenticated workspace.</p>
                    </article>
                </div>
            </section>

            <section class="info-section" id="documentation">
                <div class="section-header">
                    <div class="eyebrow">Documentation</div>
                    <h2>Operational docs for API key lifecycle, usage control, and team access.</h2>
                    <p>KeyForge v2 is designed as a production-grade SaaS for managing API keys across multiple projects, tracking request usage, and enforcing plan-based limits without burying teams in manual admin work.</p>
                </div>

                <div class="doc-grid">
                    <article class="panel">
                        <h3>Project & Key Management</h3>
                        <ul>
                            <li>Create and manage multiple projects under a single account.</li>
                            <li>Issue, rotate, and revoke API keys per project with clear ownership.</li>
                            <li>Track quota, rate limits, expiration, and status for every active key.</li>
                        </ul>
                    </article>
                    <article class="panel">
                        <h3>Usage, Billing & Collaboration</h3>
                        <ul>
                            <li>Monitor usage logs, dashboards, and alert thresholds by key and project.</li>
                            <li>Support Free, Pro, and Enterprise plans with billing-aware quota controls.</li>
                            <li>Invite team members into projects with role-based access for Owner, Admin, Member, and Viewer.</li>
                        </ul>
                    </article>
                </div>
            </section>

            <section class="info-section" id="pricing">
                <div class="section-header">
                    <div class="eyebrow">Pricing</div>
                    <h2>Pricing aligned with real API operations, not vanity seat counts.</h2>
                    <p>Each plan maps to how teams usually adopt API infrastructure: start with a single project, grow into shared environments, then move into governance, billing controls, and advanced collaboration.</p>
                </div>

                <div class="pricing-grid">
                    <article class="panel">
                        <h3>Free</h3>
                        <div class="price">$0</div>
                        <p>For solo builders validating an API product before they need billing automation or team workflows.</p>
                        <ul>
                            <li>1 project workspace</li>
                            <li>Basic API key management</li>
                            <li>Starter usage dashboard</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn-secondary-3d">Create Free Workspace</a>
                    </article>
                    <article class="panel">
                        <h3>Pro</h3>
                        <div class="price">$29</div>
                        <p>For growing SaaS teams that need stronger quota control, collaboration, and operational visibility.</p>
                        <ul>
                            <li>Multiple projects and keys</li>
                            <li>Usage alerts and rate-limit controls</li>
                            <li>Team roles and collaboration</li>
                        </ul>
                        <a href="{{ route('register') }}" class="btn-primary-3d">Start Pro</a>
                    </article>
                    <article class="panel">
                        <h3>Enterprise</h3>
                        <div class="price">Custom</div>
                        <p>For platforms operating shared environments, strict governance, and billing integrations across larger teams.</p>
                        <ul>
                            <li>Custom quota and billing workflows</li>
                            <li>Advanced audit and compliance controls</li>
                            <li>Priority onboarding for internal rollout</li>
                        </ul>
                        <a href="#documentation" class="btn-secondary-3d">Review Platform Scope</a>
                    </article>
                </div>
            </section>
        </div>
    </div>

    <script>
        // Interactive Mouse Glow
        const cursorGlow = document.getElementById('cursor-glow');
        const scene = document.getElementById('scene');
        const heroSection = document.querySelector('.hero');
        
        // Track mouse position for the glow
        document.addEventListener('mousemove', (e) => {
            cursorGlow.style.left = e.clientX + 'px';
            cursorGlow.style.top = e.clientY + 'px';
        });

        // Add interactive click effect to the glow
        document.addEventListener('mousedown', () => {
            cursorGlow.style.transform = 'translate(-50%, -50%) scale(1.5)';
            cursorGlow.style.background = 'radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%)';
        });

        document.addEventListener('mouseup', () => {
            cursorGlow.style.transform = 'translate(-50%, -50%) scale(1)';
            cursorGlow.style.background = 'radial-gradient(circle, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 70%)';
        });

        // 3D Mouse Parallax Effect disabled (rigid design)
        
        // Interactive Spotlight effect on the hero card
        const heroCard = document.querySelector('.hero-card');
        if (heroCard) {
            if (!heroCard.querySelector('.hero-card-glow')) {
                const glow = document.createElement('div');
                glow.className = 'hero-card-glow';
                heroCard.appendChild(glow);
            }

            heroCard.addEventListener('mousemove', (e) => {
                const rect = heroCard.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                heroCard.style.setProperty('--mouse-x', `${x}px`);
                heroCard.style.setProperty('--mouse-y', `${y}px`);
            });

            heroCard.addEventListener('mouseleave', () => {
                heroCard.style.setProperty('--mouse-x', `-999px`);
                heroCard.style.setProperty('--mouse-y', `-999px`);
            });
        }
        
        // Add dynamic particles/stars in background using Canvas for extra "life"
        const canvas = document.createElement('canvas');
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        canvas.style.zIndex = '-1';
        canvas.style.pointerEvents = 'none';
        document.body.insertBefore(canvas, document.body.firstChild);
        
        const ctx = canvas.getContext('2d');
        
        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();
        
        const stars = [];
        for(let i=0; i<100; i++) {
            stars.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                size: Math.random() * 1.5,
                speedY: Math.random() * 0.5 + 0.1,
                alpha: Math.random()
            });
        }
        
        function animateStars() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = 'white';
            
            stars.forEach(star => {
                ctx.globalAlpha = star.alpha;
                ctx.beginPath();
                ctx.arc(star.x, star.y, star.size, 0, Math.PI * 2);
                ctx.fill();
                
                star.y -= star.speedY;
                if(star.y < 0) {
                    star.y = canvas.height;
                    star.x = Math.random() * canvas.width;
                }
            });
            requestAnimationFrame(animateStars);
        }
        animateStars();
    </script>
</body>
</html>
