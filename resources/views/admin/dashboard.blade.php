@extends('layouts.app')

@section('title', 'Dashboard — Admin')
@section('sidebar', 1)

@php use App\Support\Money; @endphp

@push('styles')
<style>
  .tscroll{overflow-x:auto;-webkit-overflow-scrolling:touch}
  @media(max-width:850px){.wrap{padding-top:20px}}
</style>
@endpush

@section('body')
<div class="wrap">
  @include('admin._nav')
  @include('admin._topbar')

  <div class="dhead">
    <h1>Dashboard</h1>
    <div class="actions">
      <a href="{{ route('admin.payroll') }}" class="ghost">💰 Payroll</a>
      <a href="{{ route('admin.topics.index') }}" class="solid">＋ Add topic</a>
    </div>
  </div>

  <section class="stats2">
    <div class="stat2">
      <div class="top">
        <div>
          <div class="val">{{ $stats['employees']['value'] }}</div>
          <div class="lbl">Total Employees</div>
        </div>
        <div class="icon tone-{{ $stats['employees']['tone'] }}">{{ $stats['employees']['icon'] }}</div>
      </div>
      <div class="delta">{{ $stats['employees']['sub'] }}</div>
    </div>

    <div class="stat2">
      <div class="top">
        <div>
          <div class="val">{{ Money::tk($stats['earned']['value']) }}</div>
          <div class="lbl">Earned This Month</div>
        </div>
        <div class="icon tone-{{ $stats['earned']['tone'] }}">{{ $stats['earned']['icon'] }}</div>
      </div>
      <div class="delta {{ $stats['earned']['trend'] }}">
        @if ($stats['earned']['trend'] === 'up') ↑ @elseif ($stats['earned']['trend'] === 'down') ↓ @endif
        {{ $stats['earned']['sub'] }}
      </div>
    </div>

    <div class="stat2">
      <div class="top">
        <div>
          <div class="val">{{ $stats['completed']['value'] }}</div>
          <div class="lbl">Completed This Month</div>
        </div>
        <div class="icon tone-{{ $stats['completed']['tone'] }}">{{ $stats['completed']['icon'] }}</div>
      </div>
      <div class="delta {{ $stats['completed']['trend'] }}">
        @if ($stats['completed']['trend'] === 'up') ↑ @elseif ($stats['completed']['trend'] === 'down') ↓ @endif
        {{ $stats['completed']['sub'] }}
      </div>
    </div>

    <div class="stat2">
      <div class="top">
        <div>
          <div class="val">{{ $stats['rate']['value'] }}%</div>
          <div class="lbl">Completion Rate</div>
        </div>
        <div class="icon tone-{{ $stats['rate']['tone'] }}">{{ $stats['rate']['icon'] }}</div>
      </div>
      <div class="delta">{{ $stats['rate']['sub'] }}</div>
    </div>
  </section>

  <section class="rcard">
    <div class="rhead">
      <h2>🕓 Recent Activity</h2>
      <a href="{{ route('admin.topics.index') }}">View All</a>
    </div>
    <div class="tscroll">
      @if ($recent->isEmpty())
        <div class="empty">No topics yet.</div>
      @else
        <table class="rtable">
          <tr><th>Topic</th><th>Assigned to</th><th>Updated</th><th>Earned</th><th>Status</th><th>Actions</th></tr>
          @foreach ($recent as $t)
            <tr>
              <td>{{ $t->channel->icon }} {{ Str::limit($t->title, 42) }}</td>
              <td>{{ $t->employee->name ?? '—' }}</td>
              <td class="nw">{{ $t->updated_at->format('j M Y') }}</td>
              <td class="nw">{{ $t->is_done ? Money::tk($t->earned_amount) : '—' }}</td>
              <td>
                @if ($t->is_done)
                  <span class="pill pill-done">● Done</span>
                @else
                  <span class="pill pill-pending">● Pending</span>
                @endif
              </td>
              <td>
                @if ($t->video_url)
                  <a class="btn-view" href="{{ $t->video_url }}" target="_blank" rel="noopener">👁 View</a>
                @else
                  <span style="font-size:14px;color:var(--muted)">—</span>
                @endif
              </td>
            </tr>
          @endforeach
        </table>
      @endif
    </div>
  </section>
</div>
@endsection
