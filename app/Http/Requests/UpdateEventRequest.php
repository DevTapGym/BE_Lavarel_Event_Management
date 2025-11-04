<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'location_id' => 'sometimes|string',
            'start_date' => 'sometimes|date|after:now',
            'end_date' => 'sometimes|date|after:start_date',
            'organizer' => 'sometimes|string|max:255',
            'topic' => 'sometimes|nullable|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'waiting_capacity' => 'sometimes|integer|min:0',
            'image_url' => 'sometimes|nullable|url',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'start_date.after' => 'Ngày bắt đầu phải sau thời điểm hiện tại',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
            'capacity.min' => 'Số lượng chỗ phải lớn hơn 0',
            'waiting_capacity.min' => 'Số lượng chỗ chờ không được âm',
            'image_url.url' => 'URL hình ảnh không hợp lệ',
        ];
    }
}
