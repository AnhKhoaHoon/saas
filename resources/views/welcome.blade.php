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
            height: 100%;
            overflow: hidden; /* No scroll, pure app-like feel */
            perspective: 1000px;
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
            height: 100%;
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
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            perspective: 1500px; /* 3D depth for the hero */
        }

        .scene {
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.1s ease-out;
            /* Will be manipulated by JS */
        }

        /* The Glass Card */
        .hero-card {
            background: rgba(10, 10, 15, 0.4);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-top: 1px solid rgba(255,255,255,0.2);
            border-left: 1px solid rgba(255,255,255,0.2);
            padding: 5rem 4rem;
            border-radius: 32px;
            text-align: center;
            max-width: 900px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.6), inset 0 0 0 1px rgba(255,255,255,0.05);
            transform-style: preserve-3d; /* Allows children to pop out */
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
            transform: translateZ(40px); /* 3D POP */
        }

        .title {
            font-size: 5rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            transform: translateZ(80px); /* MAX 3D POP */
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
            transform: translateZ(50px); /* 3D POP */
            line-height: 1.6;
        }

        .action-btns {
            transform: translateZ(60px); /* 3D POP */
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
            transform-style: preserve-3d;
            animation: float 10s infinite alternate ease-in-out;
            pointer-events: none;
        }

        .geo-1 {
            width: 150px; height: 150px;
            top: 20%; left: 10%;
            transform: rotateX(45deg) rotateY(45deg) translateZ(-100px);
        }

        .geo-2 {
            width: 100px; height: 100px;
            border-radius: 50%;
            bottom: 20%; right: 15%;
            transform: translateZ(100px);
            animation-duration: 8s;
            animation-delay: -2s;
        }

        @keyframes float {
            0% { transform: translateY(0px) rotateX(45deg) rotateY(45deg); }
            100% { transform: translateY(-50px) rotateX(60deg) rotateY(90deg); }
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
            <a href="#" class="logo">
                <div class="logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                    </svg>
                </div>
                KeyForge
            </a>
            <div class="nav-links">
                <a href="#">Features</a>
                <a href="#">Documentation</a>
                <a href="#">Pricing</a>
                <a href="#" class="btn-login">Sign In</a>
            </div>
        </nav>

        <section class="hero">
            <div class="scene" id="scene">
                <div class="hero-card">
                    <div class="tagline">Next-Gen API Management</div>
                    <h1 class="title">Forge Your<br>API Ecosystem</h1>
                    <div class="subtitle">
                        No images. Just pure code, mathematics, and design. Protect, manage, and scale your API keys with a platform built for the future.
                    </div>
                    <div class="action-btns">
                        <a href="#" class="btn-primary-3d">Start For Free</a>
                        <a href="#" class="btn-secondary-3d">View Documentation</a>
                    </div>
                </div>
            </div>
        </section>
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

        // 3D Mouse Parallax Effect on the whole Scene
        let isHovering = false;

        heroSection.addEventListener('mouseenter', () => isHovering = true);
        heroSection.addEventListener('mouseleave', () => {
            isHovering = false;
            // Reset to default
            scene.style.transform = `rotateY(0deg) rotateX(0deg)`;
        });

        heroSection.addEventListener('mousemove', (e) => {
            if(!isHovering) return;
            
            // Calculate center of screen
            const centerX = window.innerWidth / 2;
            const centerY = window.innerHeight / 2;
            
            // Max rotation degree
            const maxRotate = 15; 
            
            // Calculate rotation based on mouse position relative to center
            const rotateX = ((e.clientY - centerY) / centerY) * -maxRotate;
            const rotateY = ((e.clientX - centerX) / centerX) * maxRotate;
            
            // Apply 3D transform to the scene
            scene.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });
        
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
