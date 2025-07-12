@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="attendance-list__inner">
            <div class="attendance-list__heading">
                <h2>勤怠詳細</h2>
            </div>
            <form action="">
                <div class="detail-form__group">
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="name">名前</label>
                        <div class="detail-form__data">
                            <input type="text" name="name" value="">
                        </div>
                        
                    </div>
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="date">日付</label>
                        <div class="detail-form__data">
                            <input type="text" name="date" value="">
                            <input type="text" name="date" value="">
                        </div>
                    </div>
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="attendance">出勤・退勤</label>
                        <div class="detail-form__data">
                            <input type="number" name="attendance" value="">
                            <span>～</span>
                            <input type="text" name="attendance" value="">
                        </div>
                        
                    </div>
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="break">休憩</label>
                        <div class="detail-form__data">
                            <input type="number" name="break" value="">
                            <span>～</span>
                            <input type="number" name="break" value="">
                        </div>
                    </div>
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="break">休憩２</label>
                        <div class="detail-form__data">
                            <input type="number" name="break" value="">
                            <span>～</span>
                            <input type="number" name="break" value="">
                        </div>
                    </div>
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="remarks">備考</label>
                        <div class="detail-form__data">
                            <textarea name="" id="remarks"></textarea>
                        </div>
                        
                    </div>
                </div>
                <div class="detail-form__button">
                    <input class="detail-form__button-back" type="submit" value="修正" name="back">
                </div>
            </form>
        </div>
    </div>
@endsection