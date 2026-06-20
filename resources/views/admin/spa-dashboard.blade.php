<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin • Tibetan Music</title>

  {{-- Tailwind CDN for quick dev styling. Replace with your compiled CSS in production. --}}
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    /* small helpers for the SPA box */
    .nav-link:hover { text-decoration: underline; }
    .loading-overlay {
      position: absolute;
      inset: 0;
      display: grid;
      place-items: center;
      background: rgba(0,0,0,0.45);
      color: white;
      z-index: 40;
    }
  </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">

<div class="min-h-screen flex">
  <!-- Sidebar -->
  <aside class="w-72 bg-gray-800 p-6 border-r border-gray-700">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-amber-400"><br/>Admin<br/>Panel</h1>
    </div>

    <nav class="space-y-2 mb-6">

      <div class="flex items-center gap-4">
        <button id="btn-dashboard" class="bg-amber-400 text-black px-3 py-1 rounded">Admin Dashboard</button>
      </div>
      

      <a href="{{ route('admin.settings.edit') }}" data-href="{{ route('admin.settings.edit') }}" class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">App Settings</a>

      <a href="{{ route('admin.app-sections.index') }}" data-href="{{ route('admin.app-sections.index') }}" class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">App Sections</a>

      <a href="{{ route('admin.songs.index') }}" data-href="{{ route('admin.songs.index') }}" class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">🎵 Songs</a>
      <a href="{{ route('admin.songs.create') }}" data-href="{{ route('admin.songs.create') }}" class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">＋ Upload Song</a>
      <a href="{{ route('admin.albums.index') }}" data-href="{{ route('admin.albums.index') }}" class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">💿 Albums</a>
      <a href="{{ route('admin.playlists.index') }}" data-href="{{ route('admin.playlists.index') }}" class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">📚 Playlists</a>
      <a href="{{ route('admin.artists.index') }}" data-href="{{ route('admin.artists.index') }}" class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">🎤 Artists</a>
      <a href="{{ route('admin.song-claims.index') }}"
       data-href="{{ route('admin.song-claims.index') }}"
       class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">
       ⚖ Ownership Claims
    </a>

    <a href="{{ route('admin.artist-claims.index') }}"
       data-href="{{ route('admin.artist-claims.index') }}"
       class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">
        🎤 Artist Claims
    </a>

    <a href="{{ route('admin.artist-applications.index') }}"
      class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">
        🎵 Artist Applications
    </a>

      <a href="{{ route('admin.users.index') }}" data-href="{{ route('admin.users.index') }}" class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">👥 Users</a>
      <a href="{{ route('admin.in_app_messages.index') }}" data-href="{{ route('admin.in_app_messages.index') }}" class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">✉️ In-App Messages</a>
      <a href="{{ route('admin.genres.index') }}" data-href="{{ route('admin.genres.index') }}" class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">📁 Genres</a>

      <a href="{{ route('admin.moderation.dedications.index') }}" data-href="{{ route('admin.moderation.dedications.index') }}" class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">📁 Dedications</a>

      <a href="{{ route('admin.song-reports.index') }}" data-href="{{ route('admin.song-reports.index') }}" class="nav-link block px-3 py-2 rounded bg-gray-700 hover:bg-gray-700/80">🚩 Song Reports</a>

      

    </nav>

    <div class="mt-auto">
      <a class="text-sm text-amber-300 block mb-2" href="{{ url('/') }}" target="_blank">View Site</a>

      <form id="logout-form" method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded">Logout</button>
      </form>

      <div class="mt-4 text-sm text-gray-400">
        Logged as: <span class="text-white">{{ auth()->user()->name ?? '—' }}</span><br>
        <span class="text-xs">Laravel {{ app()->version() }}</span>
      </div>
    </div>
  </aside>

  <!-- Main content -->
  <main class="flex-1 relative">
    <header class="bg-gray-800 border-b border-gray-700 p-4 flex items-center justify-between">
      <div class="flex items-center gap-4">
        <button id="btn-dashboard" class="bg-amber-400 text-black px-3 py-1 rounded">Admin Dashboard</button>

        <div class="relative">
          <input id="quick-search" class="px-3 py-1 rounded bg-gray-700 text-sm text-gray-100" placeholder="Search current page..." />
          <button id="reload-btn" class="ml-2 bg-blue-600 text-white px-3 py-1 rounded text-sm">Reload</button>
        </div>
      </div>

      <div class="text-sm text-gray-300">Quick Links — click to navigate in the SPA</div>
    </header>

    <section id="spa-root" class="p-6 overflow-auto h-[calc(100vh-64px)]">
      <!-- Dashboard card -->
      <div id="spa-dashboard" class="grid grid-cols-3 gap-6">
        <div class="col-span-3 bg-gray-800 p-6 rounded shadow">
          <h2 class="text-xl font-semibold text-white mb-2">Welcome to Admin Panel</h2>

          <div class="grid grid-cols-4 gap-4 mb-6">

        <div class="bg-gray-700 p-4 rounded">
            <div class="text-sm text-gray-400">Users</div>
            <div class="text-3xl font-bold">{{ $users }}</div>
        </div>

        <div class="bg-gray-700 p-4 rounded">
            <div class="text-sm text-gray-400">Songs</div>
            <div class="text-3xl font-bold">{{ $songs }}</div>
        </div>

        <div class="bg-cyan-700 p-4 rounded">
          <div class="text-sm text-cyan-100">Total Plays</div>
          <div class="text-3xl font-bold">
              {{ number_format($totalPlays) }}
          </div>
      </div>

        <div class="bg-gray-700 p-4 rounded">
            <div class="text-sm text-gray-400">Artists</div>
            <div class="text-3xl font-bold">{{ $artists }}</div>
        </div>

        <div class="bg-gray-700 p-4 rounded">
            <div class="text-sm text-gray-400">Playlists</div>
            <div class="text-3xl font-bold">{{ $playlists }}</div>
        </div>

    </div>

    <div class="grid grid-cols-4 gap-4 mb-6">

      <div class="bg-yellow-700 p-4 rounded">
          <div class="text-sm text-yellow-100">Pending Songs</div>
          <div class="text-3xl font-bold">
              {{ $pendingSongs }}
          </div>
      </div>

      <div class="bg-red-700 p-4 rounded">
          <div class="text-sm text-red-100">Pending Artist Claims</div>
          <div class="text-3xl font-bold">
              {{ $pendingClaims }}
          </div>
      </div>

      <div class="bg-indigo-700 p-4 rounded">
          <div class="text-sm text-indigo-100">Dedications</div>
          <div class="text-3xl font-bold">
              {{ $dedications }}
          </div>
      </div>

      <div class="bg-pink-700 p-4 rounded">
          <div class="text-sm text-pink-100">Unclaimed Artists</div>
          <div class="text-3xl font-bold">
              {{ $unclaimedArtists }}
          </div>
      </div>

  </div>

  <h3 class="text-lg font-semibold text-white mt-6 mb-3">
      System Health
  </h3>

  <div class="grid grid-cols-4 gap-4 mb-6">

      <div class="bg-gray-700 p-4 rounded">
          <div class="text-sm text-gray-400">Missing Covers</div>
          <div class="text-3xl font-bold">
              {{ $songsMissingCover }}
          </div>
      </div>

      <div class="bg-gray-700 p-4 rounded">
          <div class="text-sm text-gray-400">Missing Audio</div>
          <div class="text-3xl font-bold">
              {{ $songsMissingAudio }}
          </div>
      </div>

      <div class="bg-gray-700 p-4 rounded">
          <div class="text-sm text-gray-400">Empty Playlists</div>
          <div class="text-3xl font-bold">
              {{ $emptyPlaylists }}
          </div>
      </div>

      <div class="bg-gray-700 p-4 rounded">
          <div class="text-sm text-gray-400">Unclaimed Artists</div>
          <div class="text-3xl font-bold">
              {{ $unclaimedArtists }}
          </div>
      </div>

  </div>
          

          <div class="grid grid-cols-3 gap-4">
            @php
              $links = [
                ['label'=>'App Settings','route'=>route('admin.settings.edit')],
                ['label'=>'App Sections','route'=>route('admin.app-sections.index')],
                ['label'=>'Songs','route'=>route('admin.songs.index')],
                ['label'=>'Upload Song','route'=>route('admin.songs.create')],
                ['label'=>'Albums','route'=>route('admin.albums.index')],
                ['label'=>'Playlists','route'=>route('admin.playlists.index')],
                ['label'=>'Artists','route'=>route('admin.artists.index')],
                ['label'=>'Ownership Claims','route'=>route('admin.song-claims.index')],
                ['label'=>'Artist Claims','route'=>route('admin.artist-claims.index')],
                ['label'=>'Artist applications','route'=>route('admin.artist-applications.index')],
                ['label'=>'Users','route'=>route('admin.users.index')],
                ['label'=>'In-App Messages','route'=>route('admin.in_app_messages.index')],
                ['label'=>'Genres','route'=>route('admin.genres.index')],
                ['label'=>'Dedications','route'=>route('admin.moderation.dedications.index')],
                ['label'=>'Song Report','route'=>route('admin.song-reports.index')],
              ];
            @endphp

            @foreach($links as $l)
              <a href="{{ $l['route'] }}" data-href="{{ $l['route'] }}" class="block p-4 rounded bg-gray-700 hover:bg-gray-700/90">
                <div class="font-semibold text-white">{{ $l['label'] }}</div>
                <div class="text-xs text-gray-400 mt-1 break-all">{{ $l['route'] }}</div>
              </a>
            @endforeach
          </div>
        </div>
      </div>

      <!-- area where AJAX-loaded admin pages will be injected -->
      <div id="spa-content" class="mt-6"></div>
    </section>

    <div id="loading" class="hidden loading-overlay">
      <div class="text-center">
        <div class="loader mb-2"></div>
        <div>Loading...</div>
      </div>
    </div>
  </main>
</div>

<script>
  // tiny SPA loader: intercept clicks on links with data-href and fetch content
  (function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const spaRoot = document.getElementById('spa-content');
    const spaDashboard = document.getElementById('spa-dashboard');
    const loading = document.getElementById('loading');

    function showLoading(show=true) {
      loading.classList.toggle('hidden', !show);
    }

    async function loadUrl(url, pushState=true) {
      try {
        showLoading(true);
        const res = await fetch(url, { credentials: 'same-origin', headers: {'X-CSRF-TOKEN': csrf }});
        if (!res.ok) {
          const text = await res.text();
          spaRoot.innerHTML = `<div class="p-6 bg-red-800 rounded text-white"><strong>Error ${res.status}</strong><pre class="whitespace-pre-wrap mt-2 text-xs">${escapeHtml(text)}</pre></div>`;
          return;
        }
        const html = await res.text();
        // naive: inject server response inside spaRoot
        spaRoot.innerHTML = html;
        spaDashboard?.classList?.add('hidden'); // hide dashboard
        if (pushState) history.pushState({url}, '', url);
      } catch (err) {
        spaRoot.innerHTML = `<div class="p-6 bg-red-800 rounded text-white">Network error: ${err}</div>`;
      } finally {
        showLoading(false);
      }
    }

    function escapeHtml(s){ return s.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;'); }

    // attach handlers
    document.querySelectorAll('a[data-href]').forEach(a=>{
      a.addEventListener('click', function(e){
        e.preventDefault();
        const url = this.getAttribute('data-href');
        loadUrl(url);
      });
    });

    // reload button
    document.getElementById('reload-btn').addEventListener('click', ()=> {
      const current = history.state && history.state.url;
      if (current) loadUrl(current, false);
      else location.reload();
    });

    // dashboard button
    document.getElementById('btn-dashboard').addEventListener('click', ()=>{
      spaRoot.innerHTML = '';
      spaDashboard.classList.remove('hidden');
      history.pushState({}, '', '{{ route("admin.dashboard") ?? route("admin.songs.index") }}');
    });

    // handle back button
    window.addEventListener('popstate', (ev) => {
      const url = ev.state && ev.state.url;
      if (!url) {
        spaRoot.innerHTML = '';
        spaDashboard.classList.remove('hidden');
        return;
      }
      loadUrl(url, false);
    });

    // allow clicking links that are regular <a href> inside injected content to be loaded in SPA as well
    document.addEventListener('click', function(e) {
      const a = e.target.closest && e.target.closest('a');
      if (!a) return;
      if (a.hasAttribute('data-href')) return; // already handled
      // if link points to same-origin admin route, intercept
      try {
        const href = a.getAttribute('href');
        if (!href) return;
        if (href.startsWith('{{ url("/admin") }}') || href.startsWith('/admin')) {
          e.preventDefault();
          loadUrl(href);
        }
      } catch (_) {}
    });
  })();
</script>

</body>
</html>
