@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="attendance-list__inner">
            <div class="attendance-list__heading">
                <h1>{{ $targetDate->format('Y年n月j日') }}の勤怠</h1>
            </div>
            @php
                $prev = $targetDate->copy()->subDay()->toDateString();
                $next = $targetDate->copy()->addDay()->toDateString();
            @endphp
            <div class="attendance-list-monthly">
                <div class="monthly-group">
                    <div class="previous-month-arrow">
                        <a href="{{ route('attendance.admin.list', ['date' => $prev]) }}">
                            <img src="{{ asset('image/leftarrow.svg') }}" alt="←" class="leftarrow-icon">
                            <p>前日</p>
                        </a>
                    </div>
                    <div class="date-wrapper">
                        <div class="calendar-container">
                            <img src="{{ asset('image/calendar.svg') }}"  alt="カレンダー" class="calendar-icon">
                            <input  type="date" name="date">
                        </div>
                        <div class="calendar-date">
                            <p>{{ $targetDate->format('Y/m/d') }}</p>
                        </div>
                    </div>
                    <div class="previous-month-arrow">
                        <a href="{{ route('attendance.admin.list', ['date' => $next]) }}">
                            <p>翌日</p>
                            <img src="{{ asset('image/rightarrow.svg') }}" alt="→" class="rightarrow-icon">
                        </a>
                    </div>
                </div>
            </div>
            <table class="attendance-list-table">
                <tr class="table__row">
                    <th class="table__label">名前</th>
                    <th class="table__label">出勤</th>
                    <th class="table__label">退勤</th>
                    <th class="table__label">休憩</th>
                    <th class="table__label">合計</th>
                    <th class="table__label">詳細</th>
                </tr>
                @foreach($attendances as $attendance)
                    <tr class="table__row">
                    
                        <td class="table__data">{{ $attendance->user->name }}</td>
                        <td class="table__data">{{ $attendance['clock_in'] ? \Carbon\Carbon::parse($attendance['clock_in'])->format('H:i') : '' }}</td>
                        <td class="table__data">{{ $attendance['clock_out'] ? \Carbon\Carbon::parse($attendance['clock_out'])->format('H:i') : '' }}</td>
                        <td class="table__data">{{ $attendance->break_time_formatted }}</td>
                        <td class="table__data">{{ $attendance->work_time_formatted }}</td>
                        <td class="table__data">
                            <a class="table__detail-button" href="{{ route('attendance.detail', [
                            'id' => $attendance['id'] ?? 'new',
                            'staff_id' => $attendance['user_id'] ?? $user->id, 
                            'date' => ($attendance['date'] ?? now())->format('Y-m-d'),
                        ]) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach  
            </table>
        </div>
    </div>
@endsection