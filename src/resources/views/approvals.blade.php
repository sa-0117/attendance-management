@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/approvals.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="attendance-list__inner">
            <div class="attendance-list__heading">
                <h1>勤怠詳細</h1>
            </div>
            <form class="detail-form" action="{{ route('approval.approve', ['attendance_correct_request' => $approval->id]) }}" method="post">
                @csrf
                <input type="hidden" name="attendance_id" value="{{ $approval->attendance->id }}">
                <input type="hidden" name="work_date" value="{{ optional($approval->attendance)->work_date }}" >
                <div class="detail-form__group">
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="name">名前</label>
                        <div class="detail-form__data">
                            <div class="data__inner">
                                <input type="text" name="name" value="{{ old('name', optional($approval->attendance)->user->name) }}" readonly />
                            </div>
                        </div>                        
                    </div>
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="date">日付</label>
                        <div class="detail-form__data">
                            <div class="data__inner">
                                <input type="text" name="date[year]" value="{{ old('date.year', \Carbon\Carbon::parse(optional($approval->attendance)->work_date)->format('Y年')) }}" readonly />
                                <span></span>
                                <input type="text" name="date[day]" value="{{ old('date.day', \Carbon\Carbon::parse(optional($approval->attendance)->work_date)->format('n月j日')) }}" readonly />
                            </div>
                        </div>
                    </div>
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="attendance">出勤・退勤</label>
                        <div class="detail-form__data">
                            <div class="data__inner">
                                <span class="readonly-field">{{ $approval->clock_in ? \Carbon\Carbon::parse($approval->clock_in)->format('H:i') : '' }}</span>
                                <span>～</span>
                                <span class="readonly-field">{{ $approval->clock_out ? \Carbon\Carbon::parse($approval->clock_out)->format('H:i') : '' }}</span>
                            </div>
                        </div>                                             
                    </div>
                    @foreach($breaks as $index => $break)
                        <div class="detail-form__row">
                            <label class="detail-form__label">
                                {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                            </label>
                            <div class="detail-form__data">
                                <div class="data__inner">
                                    <span class="readonly-field">{{ $break['start'] ? \Carbon\Carbon::parse($break['start'])->format('H:i') : '' }}</span>
                                    <span>～</span>
                                    <span class="readonly-field">{{ $break['end'] ? \Carbon\Carbon::parse($break['end'])->format('H:i') : '' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach                  
                    <div class="detail-form__row">
                        <label class="detail-form__label" for="remarks">備考</label>
                        <div class="detail-form__data">
                            <div class="data__inner">
                                <span class="readonly-field">{{ $approval->remarks }}</span>
                            </div>
                        </div>                                               
                    </div>
                </div>
                <div class="form__button">
                    @if($approval && $approval->status === 'approved')
                        <button disabled class="form__button-approved" type="submin">承認済み</button>
                    @else
                        <input class="form__button-approvals" type="submit" value="承認" name="approvals">
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection