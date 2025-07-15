@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
  <div class="attendance">
    <div class="attendance-status">
      <div class="attendance-status__list">
        @if ($status === 'off')
          <p>勤務外</p>
        @elseif ($status === 'working')
          <p>出勤中</p>
        @elseif ($status === 'break')
          <p>休憩中</p>
        @elseif ($status === 'end')
          <p>退勤済</p>
        @endif
      </div>
    </div>
    <div class="attendance-date">
      <p>{{ now()->format('Y年m月d日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）</p>
    </div>
    <div class="attendance-time">
      <p> {{ now()->format('H:i') }}</p>
    </div>
    <div class="form__button">
      @if ($status === 'off')
        <form action="{{ route('attendance.start') }}" method="post">
          @csrf
            <input class="form__button-attendance" type="submit" value="出勤" name="start">
        </form>
      @elseif ($status === 'working')
        <div class="attendance-status-working">
          <form action="{{ route('attendance.end') }}" method="post">
            @csrf
              <input class="form__button-attendance" type="submit" value="退勤" name="end">
          </form>
          <form action="{{ route('break.start') }}" method="post">
            @csrf
              <input class="form__button-attendance" type="submit" value="休憩入" name="break_start">
          </form>
        </div>
      @elseif ($status === 'break')
        <form action="{{ route('break.end') }}" method="post">
          @csrf
            <input class="form__button-attendance" type="submit" value="休憩戻" name="break_end">
        </form>
      @elseif ($status === 'end')
        <div class="atttendance-end-comment">
          <p>お疲れ様でした</p>
        </div>
      @endif
    </div>    
  </div>
@endsection