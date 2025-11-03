<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class CreateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location_id' => 'required|string',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'organizer' => 'required|string|max:255',
            'topic' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'waiting_capacity' => 'nullable|integer|min:0',
            'image_url' => 'nullable|url',
            'status' => 'nullable|in:PENDING,UPCOMING,OPEN,ONGOING,ENDED,CANCELLED',
            'approval_status' => 'nullable|in:WAITING,APPROVED,REJECTED',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề sự kiện là bắt buộc',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'location_id.required' => 'Địa điểm là bắt buộc',
            'start_date.required' => 'Ngày bắt đầu là bắt buộc',
            'start_date.after' => 'Ngày bắt đầu phải sau thời điểm hiện tại',
            'end_date.required' => 'Ngày kết thúc là bắt buộc',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
            'organizer.required' => 'Người tổ chức là bắt buộc',
            'capacity.required' => 'Số lượng chỗ là bắt buộc',
            'capacity.min' => 'Số lượng chỗ phải lớn hơn 0',
            'waiting_capacity.min' => 'Số lượng chỗ chờ không được âm',
            'image_url.url' => 'URL hình ảnh không hợp lệ',
            'status.in' => 'Trạng thái không hợp lệ',
            'approval_status.in' => 'Trạng thái duyệt không hợp lệ',
        ];
    }
}
