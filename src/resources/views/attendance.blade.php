@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
  <div class="attendance">
    <form action="">
      <div class="attendance-status">
        <div class="attendance-status__list">
          <p>勤務外</p>
        </div>
      </div>
      <div class="attendance-date">
        <p>{{ now()->format('Y年m月d日') }}（{{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }}）</p>
      </div>
      <div class="attendance-time">
        <p> {{ now()->format('H:i') }}</p>
      </div>
      <div class="form__button">
          <input class="form__button-attendance" type="submit" value="出勤" name="work">
      </div>
    </form>
  </div>
@endsection