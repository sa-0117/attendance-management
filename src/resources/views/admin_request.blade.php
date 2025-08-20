@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="attendance-list__inner">
            <div class="attendance-list__heading">
                <h1>申請一覧</h1>
            </div>
            <div class="border">
                <ul class="border__tab">
                    <li><a href="{{ route('request.list', ['tab' => 'pending']) }}" class="{{ $tab === 'pending' ? 'active' : '' }}">承認待ち</a></li>
                    <li><a href="{{ route('request.list', ['tab' => 'approved']) }}" class="{{ $tab === 'approved' ? 'active' : '' }}">承認済み</a></li>
                </ul>
            </div>
            <table class="attendance-list-table">
                <tr class="table__row">
                    <th class="table__label">状態</th>
                    <th class="table__label">名前</th>
                    <th class="table__label">対象日時</th>
                    <th class="table__label">申請理由</th>
                    <th class="table__label">申請日時</th>
                    <th class="table__label">詳細</th>
                </tr>
                @foreach ($approvals as $approval)
                    <tr class="table__row">
                        <td class="table__data">{{ $approval->status_label }}</td>
                        <td class="table__data">{{ $approval->user->name }}</td>
                        <td class="table__data">{{ optional($approval->attendance)->work_date->format('Y/m/d') ?? '' }}</td>
                        <td class="table__data">{{ $approval->remarks }}</td>
                        <td class="table__data">{{ $approval->created_at->format('Y/m/d') }}</td>
                        <td class="table__data">
                            <a class="table__detail-button" href="{{ route('approval.show', ['attendance_correct_request' => $approval->id]) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection