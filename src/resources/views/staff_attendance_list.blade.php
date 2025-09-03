@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
<link rel="stylesheet" href="{{ asset('css/staff_attendance_list.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="attendance-list__inner">
            <div class="attendance-list__heading">
                <h1>{{ $user->name }}さんの勤怠</h1>
            </div>
            @php
                $prev = $targetDate->copy()->subMonth()->toDateString();
                $next = $targetDate->copy()->addMonth()->toDateString();
            @endphp
            <div class="attendance-list-monthly">
                <div class="monthly-group">
                    <div class="previous-month-arrow">
                        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'date'=> $prev]) }}">
                            <img src="{{ asset('image/leftarrow.svg') }}" alt="←" class="leftarrow-icon">
                            <p>前月</p>
                        </a>
                    </div>
                    <div class="date-wrapper">
                        <div class="calendar-container">
                            <img src="{{ asset('image/calendar.svg') }}"  alt="カレンダー" class="calendar-icon">
                            <input  type="date" name="date">
                        </div>
                        <p>{{ $targetDate->format('Y/m') }}</p>
                    </div>
                    <div class="previous-month-arrow">
                        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'date'=> $next]) }}">
                            <p>翌月</p>
                            <img src="{{ asset('image/rightarrow.svg') }}" alt="→" class="rightarrow-icon">
                        </a>
                    </div>
                </div>
            </div>
            <table class="attendance-list-table">
                <tr class="table__row">
                    <th class="table__label">日付</th>
                    <th class="table__label">出勤</th>
                    <th class="table__label">退勤</th>
                    <th class="table__label">休憩</th>
                    <th class="table__label">合計</th>
                    <th class="table__label">詳細</th>
                </tr>
                @foreach($attendances as $attendance)
                <tr class="table__row">
                    <td class="table__data">{{ $attendance['date']->format('m月d日') }}（{{ $attendance['day_of_week'] }}）</td>
                    <td class="table__data">{{ optional($attendance['clock_in'])->format('H:i') ?? '' }}</td>
                    <td class="table__data">{{ optional($attendance['clock_out'])->format('H:i') ?? '' }}</td>
                    <td class="table__data">
                        @if ($attendance['break_time'])
                            {{ gmdate('H:i', $attendance['break_time'] ?? 0) }}
                        @endif
                    </td>
                    <td class="table__data">
                        @if ($attendance['work_time'])
                            {{ gmdate('H:i', $attendance['work_time'] ?? 0) }}
                        @endif
                    </td>
                    <td class="table__data">
                        <a class="table__detail-button" href="{{ route('attendance.detail', [
                            'id' => $attendance['id'] ?? 'new',
                            'staff_id' => $attendance['user_id'] ?? $user->id, 
                            'date' => $attendance['work_date'] ?? now()->toDateString(),
                        ]) }}">詳細</a>
                    </td>
                </tr>
                @endforeach
            </table>
            <form action="{{ route('admin.attendance.staff.csv', ['id' => $user->id]) }}" method="post">
                @csrf
                <input type="hidden" name="date" value="{{ $targetDate->toDateString() }}">
                <div class="form__button">
                    <input class="form__button-scv" type="submit" value="CSV出力" name="csv">
                </div>
            </form>
        </div>
    </div>
@endsection