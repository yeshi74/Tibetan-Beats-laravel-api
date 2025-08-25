<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Subscription active</title>
  <style>
    body{font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto;
         background:#f6f8fb; color:#111; display:flex; align-items:center;
         justify-content:center; min-height:100vh; margin:0}
    .card{background:#fff; padding:28px 22px; border-radius:14px;
          box-shadow:0 10px 30px rgba(0,0,0,.08); max-width:520px; text-align:center}
    .ok{font-size:44px; color:#16a34a; margin-bottom:8px}
    a.btn{display:inline-block; padding:12px 18px; border-radius:10px; text-decoration:none;
          background:#111; color:#fff; margin-top:14px}
    .muted{color:#666; font-size:14px; margin-top:8px}
  </style>
</head>
<body>
  <div class="card">
    <div class="ok">✅</div>
    <h2>You're all set!</h2>
    <p>Your subscription is active. You can return to the app now.</p>

    <!-- Optional deep link back to the app (custom scheme) -->
    <a class="btn" href="tibetanmusic://checkout/success">Return to Tibetan Music</a>

    <div class="muted">If nothing happens, just switch back to the app.</div>
  </div>

  <!-- Try to open the app automatically after 1.5s (won't hurt if not installed) -->
  <script>
    setTimeout(function(){
      window.location.href = 'tibetanmusic://checkout/success';
    }, 1500);
  </script>
</body>
</html>
