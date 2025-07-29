@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@php
  $HeaderParts = true;
@endphp

@section('content')
  <div class="login-form">
    <div class="login-form__heading">
      <h2>ログイン</h2>
    </div>
    <form class="login-form__form" action="{{ route('/login') }}" method="post" > 
    @csrf
      <div class="login-form__group">
        <label class="login-form__label" for="email">メールアドレス</label>          
        <input class="login-form__input" type="email" name="email" id="email" value="{{ old('email') }}">
        <div class="login-form__error-message">
          @error('email')
            <div class="error-message">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="login-form__group">
        <label class="login-form__label" for="password">パスワード</label>          
        <input class="login-form__input" type="password" name="password" id="password">
        <div class="login-form__error-message">
          @error('password')
            <div class="error-message">{{ $message }}</div>
          @enderror
        </div>
      </div>  
      <div class="login-form__button">
        <button class="login-form__button-submit" type="submit">ログインする</button>
      </div>
    </form>
    <div class="register-form__link">
      <a class="register-form__button-submit" href="/register">会員登録はこちら</a>
    </div>
  </div>
@endsection