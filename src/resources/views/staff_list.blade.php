@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_list.css') }}">
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
                <tr class="table__row">
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