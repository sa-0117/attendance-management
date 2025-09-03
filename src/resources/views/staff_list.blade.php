@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="attendance-list__inner">
            <div class="attendance-list__heading">
                <h1>スタッフ一覧</h1>
            </div>
            <table class="attendance-list-table">
                <tr class="table__row">
                    <th class="table__label">名前</th>
                    <th class="table__label">メールアドレス</th>
                    <th class="table__label">月次勤怠</th>
                </tr>
                @foreach($users as $user)
                    <tr class="table__row">                
                        <td class="table__data">{{ $user->name }}</td>
                        <td class="table__data">{{ $user->email }}</td>
                        <td class="table__data">
                            <a class="table__detail-button" href="{{ route('admin.attendance.staff', ['id' => $user->id]) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection