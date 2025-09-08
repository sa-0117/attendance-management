@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="attendance-list__inner">
            <div class="attendance-list__heading">
                <h1>勤怠詳細</h1>
            </div>
            <form class="detail-form" action="{{ $attendance->id ? route('attendance.update', $attendance->id) : route('attendance.request', ['id' => 'new']) }}" method="POST"> 
                @csrf
                @if ($attendance->id)
                    @method('put')
                @endif
                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                <input type="hidden" name="work_date" value="{{ $attendance->work_date }}" >
                <div class="detail-form__group">
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="name">名前</label>
                        <div class="detail-form__data">
                            <div class="data__inner">
                                <input type="text" name="name" value="{{ old('name', $attendance->user->name ?? $user->name ?? '') }}" readonly />
                            </div>
                        </div>                        
                    </div>
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="date">日付</label>
                        <div class="detail-form__data">
                            <div class="data__inner">
                                <input type="text" name="date[year]" value="{{ old('date.year', \Carbon\Carbon::parse($attendance->work_date)->format('Y年')) }}" readonly />
                                <span></span>
                                <input type="text" name="date[day]" value="{{ old('date.day', \Carbon\Carbon::parse($attendance->work_date)->format('n月j日')) }}" readonly />
                            </div>
                        </div>
                    </div>
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="attendance">出勤・退勤</label>
                        <div class="detail-form__data">
                            <div class="data__inner">
                                <input type="text" name="clock_in" value="{{ old('clock_in', $attendance['clock_in'] ? \Carbon\Carbon::parse($attendance['clock_in'])->format('H:i') : '') }}">
                                <span>～</span>
                                <input type="text" name="clock_out" value="{{ old('clock_out', $attendance['clock_out'] ? \Carbon\Carbon::parse($attendance['clock_out'])->format('H:i') : '') }}">
                            </div>
                            @if($errors->has('clock_in') || $errors->has('clock_out'))
                                <p class="error-message">{{ $errors->first('clock_in') ?: $errors->first('clock_out') }}</p>
                            @endif
                        </div>                                             
                    </div>
                    @foreach($attendance->breaks ?? [] as $index => $break)
                        <div class="detail-form__row">
                            <label class="detail-form__label" for="break_{{ $index }}">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</label>
                            <div class="detail-form__data">
                                <div class="data__inner">
                                    <input type="text" name="breaks[{{ $index }}][start]" value="{{ old("breaks.$index.start", $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">
                                    <span>～</span>
                                    <input type="text" name="breaks[{{ $index }}][end]" value="{{ old("breaks.$index.end", $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                                </div>
                                @if($errors->has("breaks.$index.start"))
                                    <p class="error-message">{{ $errors->first("breaks.$index.start") }}</p>
                                @endif
                                @if($errors->has("breaks.$index.end"))
                                    <p class="error-message">{{ $errors->first("breaks.$index.end") }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach                   
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="remarks">備考</label>
                        <div class="detail-form__data">
                            <div class="data__inner">
                                <textarea name="remarks" id="remarks">{{ old('remarks', $attendance->remarks) }}</textarea>
                            </div>
                            @error('remarks')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
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