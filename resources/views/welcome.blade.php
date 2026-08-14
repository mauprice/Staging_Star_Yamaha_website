<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Northstar Yamaha</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: #111111;
            color: #f0f0f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 1.5rem;
            text-align: center;
            background: radial-gradient(ellipse at 50% 0%, rgba(0, 60, 160, 0.15) 0%, transparent 70%),
                        linear-gradient(180deg, #141414 0%, #0e0e0e 100%);
        }

        .logo-mark {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 3.5rem;
        }

        .logo-icon {
            width: 52px;
            height: 52px;
            color: #1565c0;
            flex-shrink: 0;
        }

        .logo-text-wrap {
            display: flex;
            flex-direction: column;
            text-align: left;
            line-height: 1.15;
        }

        .brand-name {
            font-size: 1.625rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #ffffff;
            text-transform: uppercase;
        }

        .brand-sub {
            font-size: 0.875rem;
            font-weight: 500;
            letter-spacing: 0.18em;
            color: #4a90d9;
            text-transform: uppercase;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: rgba(21, 101, 192, 0.1);
            border: 1px solid rgba(21, 101, 192, 0.35);
            color: #7eb8f5;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 0.45rem 1.1rem;
            border-radius: 999px;
            margin-bottom: 2rem;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: #4a90d9;
            animation: blink 2s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        .hero h1 {
            font-size: clamp(2.25rem, 6vw, 4rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
            color: #ffffff;
            max-width: 640px;
            margin-bottom: 1.25rem;
        }

        .hero h1 .accent {
            color: #4a90d9;
        }

        .hero p {
            font-size: 1.0625rem;
            color: #888888;
            max-width: 440px;
            line-height: 1.75;
            margin-bottom: 3rem;
        }

        .divider {
            width: 48px;
            height: 3px;
            background: linear-gradient(90deg, #1565c0, #4a90d9);
            border-radius: 2px;
            margin: 0 auto 2.5rem;
        }

        .sub-cta {
            font-size: 0.875rem;
            color: #555555;
        }

        .contact-strip {
            background-color: #191919;
            border-top: 1px solid #242424;
            padding: 2rem 1.5rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem 3rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            color: #999999;
            font-size: 0.875rem;
        }

        .contact-item svg {
            color: #1565c0;
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .contact-item a {
            color: #cccccc;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .contact-item a:hover {
            color: #ffffff;
        }

        .footer-bar {
            background-color: #0d0d0d;
            border-top: 1px solid #1e1e1e;
            padding: 1.125rem 1.5rem;
            text-align: center;
            font-size: 0.78rem;
            color: #444444;
        }

        @media (min-width: 640px) {
            .logo-icon { width: 60px; height: 60px; }
            .brand-name { font-size: 2rem; }
        }
    </style>
</head>
<body>

    <section class="hero">

        <div class="logo-mark">
            <svg class="logo-icon" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <polygon
                    points="26,3 32,19.5 49.5,19.5 35.5,29.5 41,46.5 26,36.5 11,46.5 16.5,29.5 2.5,19.5 20,19.5"
                    fill="currentColor"
                />
            </svg>
            <div class="logo-text-wrap">
                <span class="brand-name">Northstar</span>
                <span class="brand-sub">Yamaha</span>
            </div>
        </div>

        <div class="status-badge">
            <span class="status-dot"></span>
            Coming Soon
        </div>

        <h1>Your new <span class="accent">Yamaha</span><br>experience awaits</h1>

        <p>Our new website is currently under construction. We'll be back shortly with something worth the wait.</p>

        <div class="divider"></div>

        <p class="sub-cta">In the meantime, feel free to get in touch with us directly.</p>

    </section>

    <div class="contact-strip">
        <div class="contact-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.99 12 19.79 19.79 0 0 1 1.91 3.44 2 2 0 0 1 3.88 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            <span>Give us a call</span>
        </div>
        <div class="contact-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
            </svg>
            <span>Drop us an email</span>
        </div>
        <div class="contact-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            <span>Visit us in store</span>
        </div>
    </div>

    <div class="footer-bar">
        &copy; {{ date('Y') }} Northstar Yamaha. All rights reserved.
    </div>

</body>
</html>
