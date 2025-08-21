<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in'  => 'before:clock_out',
            'clock_out' => 'after:clock_in',
            'breaks.*.start' => 'nullable|after:clock_in|before:clock_out',
            'breaks.*.end'   => 'nullable|after:clock_in|before:clock_out',
            'remarks' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'clock_in.before'   => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.after'   => '出勤時間もしくは退勤時間が不適切な値です',

            'breaks.*.start.after' => '休憩時間が不適切な値です',
            'breaks.*.start.before' => '休憩時間が不適切な値です',

            'breaks.*.end.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.end.before'  => '休憩時間もしくは退勤時間が不適切な値です',

            'remarks.required' => '備考を記入してください',
        ];
    }
}