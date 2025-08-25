<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payment Successful</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;
         background:#0F1122;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
    .card{max-width:520px;padding:24px;border:1px solid #ffffff22;border-radius:16px;background:#12142a}
    .btn{display:inline-block;margin-top:14px;padding:12px 16px;background:#FFD700;color:#000;
         border-radius:10px;text-decoration:none;font-weight:800}
    .hint{opacity:.65;font-size:14px;margin-top:10px}
  </style>
</head>
<body>
  <div class="card">
    <h2>Payment successful 🎉</h2>
    <p>You can return to the app now.</p>
    <a id="open" class="btn" href="tibetanmusic://premium/success?session_id={{ urlencode($sessionId) }}">Open the app</a>
    <p class="hint">If nothing happens, tap the button above.</p>
  </div>

  <script>
    // Try to auto-open the app after a short delay
    setTimeout(function () {
      window.location.href = document.getElementById('open').href;
    }, 300);
  </script>
</body>
</html>
