@if (session('ok') || $errors->any())
  <div class="msgs">
    @if (session('ok'))
      <div class="msg ok">{{ session('ok') }}</div>
    @endif
    @foreach ($errors->all() as $error)
      <div class="msg err">{{ $error }}</div>
    @endforeach
  </div>
@endif
