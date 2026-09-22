<div class="atopbar">
  <div class="tb-date">{{ now()->format('l, j F Y') }}</div>
  <div class="tb-who">
    <div class="tb-avatar">{{ Str::upper(Str::substr($topbarName ?? 'A', 0, 1)) }}</div>
    <div>
      <b>{{ $topbarName ?? 'Admin' }}</b>
      <span>{{ $topbarRole ?? 'Administrator' }}</span>
    </div>
  </div>
</div>