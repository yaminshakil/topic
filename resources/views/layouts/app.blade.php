<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'AI Topic Tracker')</title>
<script>
// Applied before first paint so there's no flash of the wrong theme.
(function () {
  try {
    var t = localStorage.getItem('theme');
    if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  } catch (e) {}
})();
</script>
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@stack('styles')
</head>
<body @hasSection('sidebar') class="has-sidebar" @endif>
@yield('body')

@hasSection('sidebar')
@else
  <button type="button" id="theme-toggle" class="theme-toggle" aria-label="Toggle dark mode" title="Toggle dark mode">
    <span class="tt-icon">🌙</span>
  </button>
@endif

@stack('scripts')
<script>
(function () {
  var btn = document.getElementById('theme-toggle');
  if (!btn) return;
  var icon  = btn.querySelector('.tt-icon');
  var label = btn.querySelector('.tt-label');

  var sync = function () {
    var dark = document.documentElement.getAttribute('data-theme') === 'dark';
    if (icon) icon.textContent = dark ? '☀️' : '🌙';
    if (label) label.textContent = dark ? 'Light mode' : 'Dark mode';
  };
  sync();

  btn.addEventListener('click', function () {
    var dark = document.documentElement.getAttribute('data-theme') === 'dark';
    if (dark) {
      document.documentElement.removeAttribute('data-theme');
      try { localStorage.setItem('theme', 'light'); } catch (e) {}
    } else {
      document.documentElement.setAttribute('data-theme', 'dark');
      try { localStorage.setItem('theme', 'dark'); } catch (e) {}
    }
    sync();
  });
})();
</script>
</body>
</html>
