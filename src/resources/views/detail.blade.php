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
            <form class="detail-form" action="{{ route('attendance.detail.edit', [$id]) }}" method="post">
                @csrf
                <div class="detail-form__group">
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="name">名前</label>
                        <div class="detail-form__data">
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" readonly />
                        </div>
                        
                    </div>
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="date">日付</label>
                        <div class="detail-form__data">
                            <input type="text" name="date[year]" value="{{ old('date', \Carbon\Carbon::parse($attendance->work_date)->format('Y年')) }}" readonly />
                            <input type="text" name="date[day]" value="{{ old('date', \Carbon\Carbon::parse($attendance->work_date)->format('n月j日')) }}" readonly />
                        </div>
                    </div>
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="attendance">出勤・退勤</label>
                        <div class="detail-form__data">
                            <input type="hidden" name="id" value="{{ $attendance->id }}">
                            <input type="text" name="clock_in" value="{{ old('clock_in', $attendance['clock_in'] ? \Carbon\Carbon::parse($attendance['clock_in'])->format('H:i') : '') }}">
                            <span>～</span>
                            <input type="text" name="clock_out" value="{{ old('clock_out', $attendance['clock_out'] ? \Carbon\Carbon::parse($attendance['clock_out'])->format('H:i') : '') }}">
                        </div>                        
                    </div>
                    @foreach($attendance->breaks as $index => $break)
                        <div class="detail-form__row">
                            <label class="detail-form__label" for="break_{{ $index }}">休憩{{ $index + 1 }}</label>
                            <div class="detail-form__data">
                                <input type="hidden" name="id" value="{{ $attendance->id }}">
                                <input type="text" name="breaks[{{ $index }}][start]" value="{{ old("breaks.$index.start", \Carbon\Carbon::parse($break['break_start'])->format('H:i')) }}">
                                <span>～</span>
                                <input type="text" name="breaks[{{ $index }}][end]" value="{{ old("breaks.$index.end", \Carbon\Carbon::parse($break['break_end'])->format('H:i')) }}">
                            </div>
                        </div>
                    @endforeach
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="remarks">備考</label>
                        <div class="detail-form__data">
                            <textarea name="remarks" id="remarks">{{ old('remarks') }}</textarea>
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