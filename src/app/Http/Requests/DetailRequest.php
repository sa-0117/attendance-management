<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'breaks.*.start' => 'nullable',
            'breaks.*.end'   => 'nullable',
            'remarks' => 'required',
        ];
    }

    public function messages()
    {
        return [ 'remarks.required' => '備考を記入してください' ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            $clockIn = $data['clock_in'] ?? null;
            $clockOut = $data['clock_out'] ?? null;

            if ($clockIn && $clockOut && strtotime($clockIn) >= strtotime($clockOut)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            if (!empty($data['breaks'])) {
                foreach ($data['breaks'] as $index => $break) {
                    $start = $break['start'] ?? null;
                    $end   = $break['end'] ?? null;

                    if ($start) {
                        if ($clockIn && strtotime($start) < strtotime($clockIn)) {
                            $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                        }
                        if ($clockOut && strtotime($start) > strtotime($clockOut)) {
                            $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                        }
                    }

                    if ($end) {
                        if ($clockOut && strtotime($end) > strtotime($clockOut)) {
                            $validator->errors()->add("breaks.$index.end", '休憩時間もしくは退勤時間が不適切な値です');
                        }
                    }
                }
            }

        });
    }
}