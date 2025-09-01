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
                            <div class="error-message">
                                @error('clock_in')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                                @error('clock_out')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>  
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
                                <div class="error-message">
                                    @error("breaks.$index.start")
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                    @error("breaks.$index.end")
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach                   
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="remarks">備考</label>
                        <div class="detail-form__data">
                            <div class="data__inner">
                                <textarea name="remarks" id="remarks">{{ old('remarks', $attendance->remarks) }}</textarea>
                            </div>
                            <div class="error-message">
                                @error('remarks')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div> 
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