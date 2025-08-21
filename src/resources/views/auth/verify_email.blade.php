@extends('layouts.app')

@section('css')
<link rel="stylesheet" href=" {{ asset('css/email.css') }}">
@endsection

@php
  $HeaderParts = true;
@endphp

@section('content')
    <div class="container">
        <div class="container__inner">
            <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p>メール認証を完了してください。</p>
        </div>
        <div class="email-certification"> 
            <a class="email-certification-link" href="https://mailtrap.io/home">認証はこちらから</a>
        </div>
        <form method="POST" action="/email/verification-notification">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <button type="submit" class="email-resend-link">認証メールを再送する</button>
        </form>
    </div>
@endsection