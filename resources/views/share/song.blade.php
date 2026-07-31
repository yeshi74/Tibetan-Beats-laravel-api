<!DOCTYPE html>
<html>

<head>
    <title>{{ $song->title }} - TibetTune</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $song->title }}">
    <meta property="og:description" content="Listen to {{ $song->artist ?: 'Unknown Artist' }} on TibetTune">
    <meta property="og:image" content="{{ $song->cover_image_url }}">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:type" content="music.song">
    <meta property="og:site_name" content="TibetTune">

    <!-- Twitter / Telegram -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $song->title }}">
    <meta name="twitter:description" content="Listen to {{ $song->artist ?: 'Unknown Artist' }} on TibetTune">
    <meta name="twitter:image" content="{{ $song->cover_image_url }}">
</head>

<body
    style="
    margin:0;
    background:#0f172a;
    color:white;
    font-family:-apple-system,BlinkMacSystemFont,sans-serif;
">

    <div style="
    max-width:420px;
    margin:0 auto;
    padding:40px 20px;
    text-align:center;
">

        <img src="{{ $song->cover_image_url }}"
            style="
            width:100%;
            max-width:320px;
            border-radius:24px;
            box-shadow:0 20px 50px rgba(255,215,0,.15);
        ">

        <h1
            style="
        margin-top:24px;
        margin-bottom:8px;
        font-size:38px;
        font-weight:800;
    ">
            {{ $song->title }}
        </h1>

        <p style="
        color:#6ec1ff;
        font-size:20px;
        margin-bottom:36px;
    ">
            {{ $song->artist ?: 'Unknown Artist' }}
        </p>

        <button onclick="openApp()"
            style="
        background:#ffd700;
        color:#000;
        padding:16px 34px;
        border-radius:999px;
        border:none;
        font-weight:700;
        font-size:18px;
        cursor:pointer;
    ">
            Open in TibetTune
        </button>

        <script>
            function openApp() {
                window.location = 'TibetTune://song/{{ $song->id }}';

                setTimeout(() => {
                    window.location =
                        'https://apps.apple.com/in/app/tibettune/id6782834506';
                }, 1500);
            }
        </script>

    </div>

</body>

</html>
