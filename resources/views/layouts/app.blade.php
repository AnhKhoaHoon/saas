<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'KeyForge') }}</title>
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
            --glass-bg: rgba(10, 10, 15, 0.4);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-border-light: rgba(255, 255, 255, 0.2);
            --danger: #ef4444;
            --success: #10b981;
        }

        * { box-sizing: border-box; }
        
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Ambient Backgrounds */
        .ambient-light {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            z-index: -2;
            opacity: 0.5;
            animation: floatBlob 20s infinite alternate ease-in-out;
            pointer-events: none;
        }

        .light-1 { width: 40vw; height: 40vw; background: var(--primary); top: -10%; left: -10%; }
        .light-2 { width: 35vw; height: 35vw; background: var(--secondary); bottom: -10%; right: -10%; animation-delay: -5s; animation-duration: 25s; }
        .light-3 { width: 30vw; height: 30vw; background: var(--accent); top: 40%; left: 30%; animation-delay: -10s; animation-duration: 30s; }

        @keyframes floatBlob {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            50% { transform: translate(15vw, 10vw) scale(1.2) rotate(180deg); }
            100% { transform: translate(-5vw, 20vw) scale(0.9) rotate(360deg); }
        }

        .grid-bg {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background-image: 
                linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: -1;
            transform: perspective(500px) rotateX(60deg) translateY(-100px) translateZ(-200px);
            animation: gridMove 20s linear infinite;
            pointer-events: none;
        }

        @keyframes gridMove {
            0% { background-position: 0 0; }
            100% { background-position: 0 50px; }
        }

        #cursor-glow {
            position: fixed;
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
        .shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 10;
        }

        /* Topbar */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
        }

        .brand {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--text-main);
            text-decoration: none;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 28px; height: 28px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.5);
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Container & Cards */
        .container {
            width: min(1100px, calc(100% - 32px));
            margin: 40px auto;
            perspective: 1500px; /* 3D context */
        }

        .scene {
            transform-style: preserve-3d;
            transition: transform 0.1s ease-out;
        }

        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-top: 1px solid var(--glass-border-light);
            border-left: 1px solid var(--glass-border-light);
            border-radius: 24px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5), inset 0 0 0 1px rgba(255,255,255,0.05);
            padding: 40px;
            transform-style: preserve-3d; /* Allows children to pop */
        }

        .stack { display: grid; gap: 24px; }
        .grid { display: grid; gap: 24px; }
        .cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        
        /* Typography */
        h1, h2, h3, p { margin: 0; }
        h1 { 
            font-size: 2.5rem; 
            font-weight: 900; 
            margin-bottom: 10px; 
            background: linear-gradient(to right, #ffffff, #a5b4fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transform: translateZ(40px); /* 3D Pop */
        }
        h2 { font-size: 1.35rem; transform: translateZ(30px); }
        p.lead { 
            color: var(--text-muted); 
            line-height: 1.6; 
            transform: translateZ(30px); 
        }

        /* Forms */
        form { transform: translateZ(20px); }
        .field { display: grid; gap: 8px; }
        .field label { 
            font-weight: 600; 
            font-size: 0.95rem;
            color: var(--text-muted);
        }
        
        .field input[type="email"],
        .field input[type="password"],
        .field input[type="text"],
        .field select {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 14px 16px;
            font: inherit;
            color: var(--text-main);
            transition: all 0.3s ease;
        }

        .field input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.3);
            background: rgba(0, 0, 0, 0.4);
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        /* Buttons */
        .btn, button {
            border: 0;
            border-radius: 50px;
            padding: 12px 24px;
            font-family: inherit;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .btn:hover, button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(236, 72, 153, 0.4);
        }

        .btn.secondary, button.secondary {
            background: transparent;
            color: var(--text-muted);
            box-shadow: none;
            border: 1px solid var(--glass-border);
        }

        .btn.secondary:hover, button.secondary:hover {
            background: rgba(255,255,255,0.05);
            color: var(--text-main);
            border-color: rgba(255,255,255,0.3);
            transform: translateY(-3px);
        }

        /* Alerts */
        .notice, .errors {
            border-radius: 12px;
            padding: 16px;
            font-size: 0.95rem;
            backdrop-filter: blur(10px);
            transform: translateZ(25px);
        }

        .notice {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .errors {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .errors ul { margin: 0; padding-left: 18px; }
        .muted { color: var(--text-muted); }
        .mono { font-family: "Consolas", "Courier New", monospace; }
        .code-list { margin: 0; padding-left: 18px; }

        /* Custom Checkbox */
        input[type="checkbox"] {
            accent-color: var(--primary);
            width: 16px; height: 16px;
            cursor: pointer;
        }

        /* Utilities */
        form.inline { display: inline; }
        
        @media (max-width: 600px) {
            .topbar { padding: 15px 20px; }
            .card { padding: 25px; }
            .actions { flex-direction: column; align-items: stretch; }
            .btn { width: 100%; }
            .cols-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Ambient Backgrounds -->
    <div class="ambient-light light-1"></div>
    <div class="ambient-light light-2"></div>
    <div class="ambient-light light-3"></div>
    <div class="grid-bg"></div>
    
    <div id="cursor-glow"></div>

    <div class="shell">
        <header class="topbar">
            <a class="brand" href="{{ url('/') }}">
                <div class="brand-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                    </svg>
                </div>
                {{ config('app.name', 'KeyForge') }}
            </a>

            <nav class="nav">
                @auth
                    <a class="btn secondary" href="{{ route('home') }}">Dashboard</a>
                    <form class="inline" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="secondary" type="submit">Log out</button>
                    </form>
                @else
                    <a class="btn secondary" href="{{ route('login') }}">Login</a>
                    <a class="btn" href="{{ route('register') }}">Register</a>
                @endauth
            </nav>
        </header>

        <main class="container stack">
            <div class="scene" id="auth-scene">
                @if (session('status'))
                    <div class="notice">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="errors">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        // Mouse Tracking Glow
        const cursorGlow = document.getElementById('cursor-glow');
        
        document.addEventListener('mousemove', (e) => {
            cursorGlow.style.left = e.clientX + 'px';
            cursorGlow.style.top = e.clientY + 'px';
        });

        document.addEventListener('mousedown', () => {
            cursorGlow.style.transform = 'translate(-50%, -50%) scale(1.5)';
            cursorGlow.style.background = 'radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%)';
        });

        document.addEventListener('mouseup', () => {
            cursorGlow.style.transform = 'translate(-50%, -50%) scale(1)';
            cursorGlow.style.background = 'radial-gradient(circle, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0) 70%)';
        });

        // 3D Parallax Tilt for Auth Card
        const scene = document.getElementById('auth-scene');
        const container = document.querySelector('.container');
        let isHovering = false;

        container.addEventListener('mouseenter', () => isHovering = true);
        container.addEventListener('mouseleave', () => {
            isHovering = false;
            scene.style.transform = `rotateY(0deg) rotateX(0deg)`;
        });

        container.addEventListener('mousemove', (e) => {
            if(!isHovering) return;
            
            const rect = container.getBoundingClientRect();
            // Calculate mouse position relative to the center of the container
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            
            const maxRotate = 8; // less rotation for forms to keep them usable
            
            const rotateX = ((e.clientY - centerY) / (rect.height / 2)) * -maxRotate;
            const rotateY = ((e.clientX - centerX) / (rect.width / 2)) * maxRotate;
            
            scene.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });

        // Background Stars
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
        for(let i=0; i<80; i++) {
            stars.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                size: Math.random() * 1.5,
                speedY: Math.random() * 0.3 + 0.1,
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
