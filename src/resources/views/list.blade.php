@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="attendance-list__inner">
            <div class="attendance-list__heading">
                <h1>勤怠一覧</h1>
            </div>
            <div class="attendance-list-monthly">
                <div class="monthly-group">
                    <div class="previous-month-arrow">
                        <img src="{{ asset('image/leftarrow.svg') }}" alt="←" class="leftarrow-icon">
                        <p>前月</p>
                    </div>
                    <div class="date-wrapper">
                        <img src="{{ asset('image/calendar.svg') }}"  alt="カレンダー" class="calendar-icon">
                        <input  type="date" name="date">
                        <p>{{ now()->format('Y/m') }}</p>
                    </div>
                    <div class="previous-month-arrow">
                        <p>翌月</p>
                        <img src="{{ asset('image/rightarrow.svg') }}" alt="→" class="rightarrow-icon">
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
                <tr class="table__row">
                    <td class="table__data"></td>
                    <td class="table__data"></td>
                    <td class="table__data"></td>
                    <td class="table__data"></td>
                    <td class="table__data"></td>
                    <td class="table__data">
                        <a class="table__detail-button" href="">詳細</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endsection