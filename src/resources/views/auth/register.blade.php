@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@php
  $HeaderParts = true;
@endphp

@section('content')
  <div class="register-form">
    <div class="register-form__heading">
      <h2>会員登録</h2>
    </div>
    <form class="register-form__form" action="{{ route('register') }}" method="post">
      @csrf
        <div class="register-form__group">
          <label class="register-form__label" for="name">ユーザー名</label>          
          <input class="register-form__input" type="text" name="name" id="name" value="{{ old('name') }}">
          @error('name')
            <div class="register-form__error-message">{{ $message }}</div>
          @enderror
        </div>
        <div class="register-form__group">
          <label class="register-form__label" for="email">メールアドレス</label>          
          <input class="register-form__input" type="email" name="email" id="email" value="{{ old('email') }}">
          @error('email')
            <div class="register-form__error-message">{{ $message }}</div>
          @enderror
        </div>
        <div class="register-form__group">
          <label class="register-form__label" for="password">パスワード</label>          
          <input class="register-form__input" type="password" name="password" id="password">
          @error('password')
            <div class="register-form__error-message">{{ $message }}</div>
          @enderror
        </div>
        <div class="register-form__group">
          <label class="register-form__label" for="password_confirmation">確認用パスワード</label>          
          <input class="register-form__input" type="password" name="password_confirmation" id="password_confirmation">
          @error('password_confirmation') 
            <div class="register-form__error-message">{{ $message }}</div>
          @enderror
        </div> 
        <div class="register-form__button">
          <button class="register-form__button-submit" type="submit">登録する</button>
        </div>
    </form>
    <div class="login-form__link">
      <a class="login-form__button-submit" href="/login">ログインはこちら</a>
    </div>
  </div>
@endsection