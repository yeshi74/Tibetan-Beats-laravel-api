<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TibetTune — Backend & REST API</title>

    <meta name="description"
        content="TibetTune production Laravel backend and REST API powering a Tibetan music streaming platform.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0b0f19;
            --bg-soft: #111827;
            --card: rgba(255, 255, 255, 0.045);
            --card-hover: rgba(255, 255, 255, 0.07);
            --border: rgba(255, 255, 255, 0.09);
            --text: #f5f7fb;
            --muted: #9ca7b8;
            --accent: #7c5cff;
            --accent-2: #5b8cff;
            --green: #34d399;
            --max: 1120px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background:
                radial-gradient(circle at 15% 10%, rgba(124, 92, 255, .16), transparent 30%),
                radial-gradient(circle at 85% 15%, rgba(91, 140, 255, .12), transparent 28%),
                var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .container {
            width: min(var(--max), calc(100% - 40px));
            margin: 0 auto;
        }

        /* Navigation */

        nav {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid rgba(255, 255, 255, .06);
            background: rgba(11, 15, 25, .78);
            backdrop-filter: blur(16px);
        }

        .nav-inner {
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            font-weight: 700;
            font-size: 17px;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            font-size: 19px;
            box-shadow: 0 8px 25px rgba(124, 92, 255, .25);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
            color: var(--muted);
            font-size: 13px;
        }

        .nav-links a:hover {
            color: white;
        }

        /* Hero */

        .hero {
            padding: 105px 0 90px;
            text-align: center;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: rgba(255, 255, 255, .035);
            color: #c9d1df;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 12px rgba(52, 211, 153, .7);
        }

        h1 {
            max-width: 850px;
            margin: 0 auto 22px;
            font-size: clamp(42px, 7vw, 76px);
            line-height: 1.03;
            letter-spacing: -3px;
            font-weight: 800;
        }

        .gradient-text {
            background: linear-gradient(110deg, #ffffff 15%, #a996ff 52%, #72a4ff 90%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-description {
            max-width: 690px;
            margin: 0 auto;
            color: var(--muted);
            font-size: 17px;
        }

        .hero-description strong {
            color: #dce2ed;
            font-weight: 600;
        }

        .buttons {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 34px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid var(--border);
            transition: .2s ease;
        }

        .button:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, .07);
        }

        .button-primary {
            border: none;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 12px 30px rgba(91, 140, 255, .18);
        }

        .button-primary:hover {
            box-shadow: 0 16px 35px rgba(91, 140, 255, .28);
        }

        /* Tech stack */

        .stack {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 48px;
        }

        .tech {
            padding: 7px 11px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: rgba(255, 255, 255, .035);
            color: #b9c3d2;
            font-size: 11px;
            font-weight: 600;
        }

        /* Sections */

        section {
            padding: 75px 0;
        }

        .section-label {
            color: #8f7cff;
            text-transform: uppercase;
            letter-spacing: 1.7px;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        h2 {
            font-size: clamp(28px, 4vw, 40px);
            letter-spacing: -1.3px;
            line-height: 1.15;
            margin-bottom: 14px;
        }

        .section-description {
            max-width: 700px;
            color: var(--muted);
            font-size: 14px;
        }

        /* About */

        .about-grid {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 22px;
            margin-top: 35px;
        }

        .card {
            border: 1px solid var(--border);
            background: var(--card);
            border-radius: 16px;
            padding: 27px;
        }

        .card h3 {
            font-size: 17px;
            margin-bottom: 10px;
        }

        .card p {
            color: var(--muted);
            font-size: 13px;
        }

        /* Features */

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 13px;
            margin-top: 35px;
        }

        .feature {
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 13px;
            background: var(--card);
            transition: .2s ease;
        }

        .feature:hover {
            background: var(--card-hover);
            transform: translateY(-2px);
        }

        .feature-icon {
            font-size: 20px;
            margin-bottom: 12px;
        }

        .feature h3 {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .feature p {
            color: var(--muted);
            font-size: 12px;
        }

        /* Architecture */

        .architecture {
            margin-top: 35px;
            padding: 35px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background:
                linear-gradient(rgba(255, 255, 255, .025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .025) 1px, transparent 1px),
                var(--card);
            background-size: 35px 35px;
            overflow-x: auto;
        }

        .architecture pre {
            min-width: 700px;
            color: #cbd5e1;
            font-family: "SFMono-Regular", Consolas, monospace;
            font-size: 12px;
            line-height: 1.75;
        }

        .architecture .highlight {
            color: #a996ff;
        }

        /* Technical */

        .technical-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 35px;
        }

        .technical-card {
            padding: 23px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card);
        }

        .technical-card h3 {
            font-size: 14px;
            margin-bottom: 14px;
        }

        .technical-card ul {
            list-style: none;
        }

        .technical-card li {
            color: var(--muted);
            font-size: 12px;
            padding: 6px 0;
            border-bottom: 1px solid rgba(255, 255, 255, .05);
        }

        .technical-card li:last-child {
            border-bottom: none;
        }

        .technical-card li::before {
            content: "✓";
            color: var(--green);
            margin-right: 8px;
        }

        /* Production */

        .production {
            display: grid;
            grid-template-columns: .85fr 1.15fr;
            gap: 22px;
            margin-top: 35px;
        }

        .production-card {
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 27px;
            background: var(--card);
        }

        .production-card h3 {
            margin-bottom: 10px;
            font-size: 17px;
        }

        .production-card p {
            color: var(--muted);
            font-size: 13px;
        }

        .production-list {
            margin-top: 18px;
            display: grid;
            gap: 10px;
        }

        .production-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #c7d0de;
            font-size: 12px;
        }

        /* Security */

        .security {
            border: 1px solid rgba(52, 211, 153, .18);
            background: rgba(52, 211, 153, .035);
            border-radius: 16px;
            padding: 25px;
            margin-top: 35px;
        }

        .security h3 {
            font-size: 15px;
            margin-bottom: 8px;
        }

        .security p {
            color: var(--muted);
            font-size: 12px;
        }

        /* Footer */

        footer {
            padding: 45px 0;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 11px;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .footer-links {
            display: flex;
            gap: 18px;
        }

        .footer-links a:hover {
            color: white;
        }

        /* Responsive */

        @media (max-width: 850px) {

            .about-grid,
            .production {
                grid-template-columns: 1fr;
            }

            .feature-grid,
            .technical-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .nav-links {
                display: none;
            }

            h1 {
                letter-spacing: -2px;
            }
        }

        @media (max-width: 560px) {
            .container {
                width: min(var(--max), calc(100% - 28px));
            }

            .hero {
                padding: 75px 0 65px;
            }

            section {
                padding: 55px 0;
            }

            .feature-grid,
            .technical-grid {
                grid-template-columns: 1fr;
            }

            .card,
            .production-card {
                padding: 21px;
            }

            .architecture {
                padding: 20px;
            }

            .architecture pre {
                min-width: 600px;
                font-size: 10px;
            }

            .footer-inner {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

    <nav>
        <div class="container nav-inner">

            <a href="/" class="brand">
                <span class="brand-icon">🎵</span>
                <span>TibetTune</span>
            </a>

            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#architecture">Architecture</a>
                <a href="#technology">Technology</a>
                <a href="#production">Production</a>
            </div>

            <a href="https://github.com/yeshi74/Tibetan-Beats-laravel-api" target="_blank" rel="noopener noreferrer"
                class="button">
                GitHub ↗
            </a>

        </div>
    </nav>


    <main>

        <!-- HERO -->

        <section class="hero">
            <div class="container">

                <div class="eyebrow">
                    <span class="status-dot"></span>
                    Production Laravel Backend
                </div>

                <h1>
                    <span class="gradient-text">TibetTune</span><br>
                    Backend & REST API
                </h1>

                <p class="hero-description">
                    A production-ready <strong>Laravel backend</strong> powering TibetTune,
                    a Tibetan music streaming platform built and maintained by
                    <strong>Yeshi Tempi</strong>.
                </p>

                <div class="buttons">

                    <a href="https://github.com/yeshi74/Tibetan-Beats-laravel-api" target="_blank"
                        rel="noopener noreferrer" class="button button-primary">
                        View Source Code ↗
                    </a>

                    <a href="https://api.tibettune.com/admin" target="_blank" rel="noopener noreferrer" class="button">
                        Admin Dashboard ↗
                    </a>

                    <a href="https://apps.apple.com/in/app/tibettune/id6782834506" target="_blank"
                        rel="noopener noreferrer" class="button">
                        View App ↗
                    </a>

                </div>

                <div class="stack">
                    <span class="tech">PHP 8.2+</span>
                    <span class="tech">Laravel 12</span>
                    <span class="tech">MySQL</span>
                    <span class="tech">REST API</span>
                    <span class="tech">Sanctum</span>
                    <span class="tech">Stripe</span>
                    <span class="tech">RevenueCat</span>
                    <span class="tech">DigitalOcean</span>
                    <span class="tech">Nginx</span>
                </div>

            </div>
        </section>


        <!-- ABOUT -->

        <section>
            <div class="container">

                <div class="section-label">01 / About</div>

                <h2>Built as a real production system.</h2>

                <p class="section-description">
                    TibetTune is a music streaming platform focused on Tibetan music and
                    content. The Laravel backend provides the APIs, business logic,
                    authentication, subscription infrastructure, content management and
                    administration used by the mobile application.
                </p>

                <div class="about-grid">

                    <div class="card">
                        <h3>🎯 What I built</h3>
                        <p>
                            The backend was designed and developed as a production application,
                            including the REST API, database architecture, authentication,
                            audio streaming, subscriptions, integrations, administration
                            and analytics.
                        </p>
                    </div>

                    <div class="card">
                        <h3>⚙️ Production Engineering</h3>
                        <p>
                            The application is deployed on DigitalOcean using Linux, Nginx,
                            PHP-FPM and MySQL and is actively maintained as a live production
                            system.
                        </p>
                    </div>

                </div>

            </div>
        </section>


        <!-- FEATURES -->

        <section id="features">
            <div class="container">

                <div class="section-label">02 / Features</div>

                <h2>Backend capabilities</h2>

                <p class="section-description">
                    The API supports the core functionality required by the TibetTune
                    mobile application and administration platform.
                </p>

                <div class="feature-grid">

                    <div class="feature">
                        <div class="feature-icon">🔐</div>
                        <h3>Authentication</h3>
                        <p>Secure user authentication and API authorization using Laravel Sanctum.</p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">🎵</div>
                        <h3>Music Catalog</h3>
                        <p>Songs, albums, genres, search, releases and music discovery APIs.</p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">🎧</div>
                        <h3>Audio Streaming</h3>
                        <p>HTTP byte-range streaming designed for reliable mobile playback and seeking.</p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">📋</div>
                        <h3>Playlists</h3>
                        <p>User playlists, featured content and playlist management.</p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">🎤</div>
                        <h3>Artist System</h3>
                        <p>Artist profiles, claims, verification and artist-related functionality.</p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">🙏</div>
                        <h3>Dedications</h3>
                        <p>Song dedication functionality with moderation and administration.</p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">❤️</div>
                        <h3>Social Features</h3>
                        <p>Liked songs, recently played content and personalized user libraries.</p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">💳</div>
                        <h3>Subscriptions</h3>
                        <p>Premium subscription infrastructure with Stripe and RevenueCat.</p>
                    </div>

                    <div class="feature">
                        <div class="feature-icon">📊</div>
                        <h3>Administration</h3>
                        <p>Content management, moderation, analytics and operational monitoring.</p>
                    </div>

                </div>

            </div>
        </section>


        <!-- ARCHITECTURE -->

        <section id="architecture">
            <div class="container">

                <div class="section-label">03 / Architecture</div>

                <h2>How the system fits together.</h2>

                <p class="section-description">
                    The Laravel application acts as the central backend layer between
                    the mobile application, database and third-party services.
                </p>

                <div class="architecture">
                    <pre>
                    ┌─────────────────────────┐
                    │    TibetTune Mobile     │
                    │       Application       │
                    └────────────┬────────────┘
                                 │
                                 │ REST API
                                 ▼
                    ┌─────────────────────────┐
                    │    <span class="highlight">Laravel 12 API</span>      │
                    │                         │
                    │  Authentication         │
                    │  Music / Artists        │
                    │  Playlists              │
                    │  Dedications            │
                    │  Subscriptions          │
                    │  Analytics              │
                    └────────────┬────────────┘
                                 │
                    ┌────────────┼────────────┐
                    │            │            │
                    ▼            ▼            ▼
              ┌──────────┐  ┌──────────┐  ┌──────────┐
              │  MySQL   │  │  Stripe  │  │RevenueCat│
              └──────────┘  └──────────┘  └──────────┘
                                                
                    Production Infrastructure
                    DigitalOcean • Nginx • PHP-FPM
</pre>
                </div>

            </div>
        </section>


        <!-- TECHNOLOGY -->

        <section id="technology">
            <div class="container">

                <div class="section-label">04 / Technology</div>

                <h2>Technology & engineering</h2>

                <div class="technical-grid">

                    <div class="technical-card">
                        <h3>Backend</h3>
                        <ul>
                            <li>PHP 8.2+</li>
                            <li>Laravel 12</li>
                            <li>RESTful APIs</li>
                            <li>Laravel Sanctum</li>
                            <li>Eloquent ORM</li>
                            <li>API Resources</li>
                        </ul>
                    </div>

                    <div class="technical-card">
                        <h3>Database & Services</h3>
                        <ul>
                            <li>MySQL</li>
                            <li>Stripe</li>
                            <li>RevenueCat</li>
                            <li>Webhooks</li>
                            <li>JSON / REST integrations</li>
                            <li>Subscription management</li>
                        </ul>
                    </div>

                    <div class="technical-card">
                        <h3>Infrastructure</h3>
                        <ul>
                            <li>Linux</li>
                            <li>Nginx</li>
                            <li>PHP-FPM</li>
                            <li>DigitalOcean</li>
                            <li>Git / GitHub</li>
                            <li>Composer</li>
                        </ul>
                    </div>

                </div>

            </div>
        </section>


        <!-- PRODUCTION -->

        <section id="production">
            <div class="container">

                <div class="section-label">05 / Production</div>

                <h2>Live application. Real infrastructure.</h2>

                <p class="section-description">
                    TibetTune is deployed and maintained as a live production application.
                    The backend is actively consumed by the mobile application.
                </p>

                <div class="production">

                    <div class="production-card">

                        <h3>🌐 Production Backend</h3>

                        <p>
                            The Laravel API is hosted on DigitalOcean and served through
                            Nginx and PHP-FPM.
                        </p>

                        <div class="production-list">
                            <div class="production-item">✓ HTTPS production API</div>
                            <div class="production-item">✓ Linux server environment</div>
                            <div class="production-item">✓ Nginx + PHP-FPM</div>
                            <div class="production-item">✓ MySQL database</div>
                            <div class="production-item">✓ Git-based deployment</div>
                        </div>

                    </div>


                    <div class="production-card">

                        <h3>🔧 Production Engineering Highlights</h3>

                        <p>
                            The project includes several engineering challenges encountered
                            while operating a real production application.
                        </p>

                        <div class="production-list">
                            <div class="production-item">
                                ✓ HTTP byte-range audio streaming
                            </div>

                            <div class="production-item">
                                ✓ Subscription webhook processing
                            </div>

                            <div class="production-item">
                                ✓ API and database troubleshooting
                            </div>

                            <div class="production-item">
                                ✓ Authentication and authorization
                            </div>

                            <div class="production-item">
                                ✓ Admin analytics and moderation
                            </div>

                            <div class="production-item">
                                ✓ Production deployment and maintenance
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </section>


        <!-- SOURCE -->

        <section>
            <div class="container">

                <div class="section-label">06 / Review</div>

                <h2>Explore the project.</h2>

                <p class="section-description">
                    The backend source code is publicly available for technical review.
                    The administration system is authentication protected; demo access
                    can be provided to recruiters or interviewers upon request.
                </p>

                <div class="buttons" style="justify-content:flex-start; margin-top:25px;">

                    <a href="https://github.com/yeshi74/Tibetan-Beats-laravel-api" target="_blank"
                        rel="noopener noreferrer" class="button button-primary">
                        GitHub Repository ↗
                    </a>

                    <a href="https://api.tibettune.com/admin" target="_blank" rel="noopener noreferrer"
                        class="button">
                        Admin Dashboard ↗
                    </a>

                    <a href="https://apps.apple.com/in/app/tibettune/id6782834506" target="_blank"
                        rel="noopener noreferrer" class="button">
                        App Store ↗
                    </a>

                </div>

                <div class="security">

                    <h3>🔒 Security</h3>

                    <p>
                        Production credentials, API keys, database credentials and
                        administration credentials are intentionally not exposed in this
                        repository or on this page. Interview/demo access can be provided
                        separately when required.
                    </p>

                </div>

            </div>
        </section>

    </main>


    <footer>
        <div class="container footer-inner">

            <div>
                © {{ date('Y') }} TibetTune · Built by Yeshi Tempi
            </div>

            <div class="footer-links">
                <a href="https://github.com/yeshi74" target="_blank" rel="noopener noreferrer">
                    GitHub
                </a>

                <a href="https://github.com/yeshi74/Tibetan-Beats-laravel-api" target="_blank"
                    rel="noopener noreferrer">
                    Source
                </a>

                <a href="https://apps.apple.com/in/app/tibettune/id6782834506" target="_blank"
                    rel="noopener noreferrer">
                    App
                </a>
            </div>

        </div>
    </footer>

</body>

</html>
