@php
  use App\Support\Money;
  $isMine = $t->added_by === $employee->id;
  $editFailed = $isMine && $errors->hasAny(['title', 'link']) && old('_editing') !== null && (int) old('_editing') === $t->id;
@endphp
<div class="et {{ $t->is_done ? 'done' : '' }} {{ session('error_topic') === $t->id ? 'flash' : '' }}" id="t-{{ $t->id }}">
  <div class="row1">
    <form method="post" action="{{ route('employee.toggle', $t) }}">
      @csrf
      <input type="checkbox" aria-label="Mark done: {{ $t->title }}"
             @checked($t->is_done) @disabled(! $t->video_url)
             onchange="this.form.submit()">
    </form>
    <span class="tt">{{ $t->title }}</span>
    @if ($isMine)
      <span class="addedbadge">✍️ Added by you</span>
    @endif
    <span class="chbadge">{{ $channel->icon }} {{ $channel->name }}</span>
    @if ($t->link !== '')
      <a class="ref" href="{{ $t->link }}" target="_blank" rel="noopener" title="Reference link">&#128279;</a>
    @endif
    @if ($isMine && ! $t->is_done)
      <button type="button" class="chg" onclick="showTopicEdit(this)" style="{{ $editFailed ? 'display:none' : '' }}">edit</button>
      <form method="post" action="{{ route('employee.topics.destroy', $t) }}"
            onsubmit="return confirm('Delete this topic? This can\'t be undone.')">
        @csrf @method('DELETE')
        <button type="submit" class="chg chg-rm">delete</button>
      </form>
    @endif
  </div>

  @if ($isMine && ! $t->is_done)
    <form class="addform editform" method="post" action="{{ route('employee.topics.update', $t) }}"
          style="{{ $editFailed ? 'display:flex' : 'display:none' }};margin:8px 0 0 32px">
      @csrf @method('PUT')
      <input type="hidden" name="_editing" value="{{ $t->id }}">
      <input type="text" name="title" placeholder="Topic title…" required maxlength="255"
             value="{{ $editFailed ? old('title') : $t->title }}">
      <input type="url" name="link" placeholder="Reference link (optional)"
             value="{{ $editFailed ? old('link') : $t->link }}">
      <button type="submit">Save</button>
    </form>
    @if ($editFailed)
      <div class="adderr" style="margin-left:32px">{{ $errors->first('title') ?: $errors->first('link') }}</div>
    @endif
  @endif

  <div class="row2">
    @if ($t->is_done)
      <div class="vid">🎬 <a href="{{ $t->video_url }}" target="_blank" rel="noopener">{{ $t->video_title ?: $t->video_url }}</a>
        @if ($t->video_channel)<span class="ytch">by {{ $t->video_channel }}</span>@endif
        @if ($t->video_published_at)<span class="ytch">· uploaded {{ $t->video_published_at->format('j M Y') }}</span>@endif</div>
      <div class="doneinfo">✓ Done{{ $t->completed_at ? ' ' . $t->completed_at->format('j M Y, g:i A') : '' }} · earned {{ Money::tk($t->earned_amount) }}</div>
    @else
      @if ($t->video_url)
        <div class="vid">🎬 <a href="{{ $t->video_url }}" target="_blank" rel="noopener">{{ $t->video_title ?: $t->video_url }}</a>
          @if ($t->video_channel)<span class="ytch">by {{ $t->video_channel }}</span>@endif
          @if ($t->video_published_at)<span class="ytch">· uploaded {{ $t->video_published_at->format('j M Y') }}</span>@endif
          <button type="button" class="chg" onclick="showEdit(this)">change</button>
          <form method="post" action="{{ route('employee.video.remove', $t) }}"
                onsubmit="return confirm('Remove this video link?')">
            @csrf @method('DELETE')
            <button type="submit" class="chg chg-rm">remove</button>
          </form>
        </div>
      @else
        <div class="need" data-need>Add your YouTube video link to unlock the checkbox.</div>
      @endif
      <form class="vform" method="post" action="{{ route('employee.video.save', $t) }}" style="{{ $t->video_url ? 'display:none;margin-top:8px' : 'margin-top:6px' }}">
        @csrf
        <input type="text" name="video_url" class="vid-input" inputmode="url" autocomplete="off"
               placeholder="https://www.youtube.com/watch?v=…" aria-label="YouTube link for {{ $t->title }}">
        <button type="submit">{{ $t->video_url ? 'Update link' : 'Save link' }}</button>
      </form>
      <div class="vprev" aria-live="polite"></div>
    @endif
  </div>
</div>
