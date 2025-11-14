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
            'location_id' => 'required|string|exists:locations,id',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'organizer' => 'required|string|max:255',
            'topic' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'waiting_capacity' => 'nullable|integer|min:0',
            'image_url' => 'nullable|url',
            'speakers' => 'nullable|array',
            'speakers.*.name' => 'required|string|max:255',
            'speakers.*.title' => 'nullable|string|max:255',
            'speakers.*.bio' => 'nullable|string|max:1000',
            'speakers.*.email' => 'nullable|email|max:255',
            'speakers.*.phone' => 'nullable|string|max:20',
            'speakers.*.avatar_url' => 'nullable|url|max:500',
            'speakers.*.organization' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề sự kiện là bắt buộc',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'location_id.required' => 'Địa điểm là bắt buộc',
            'location_id.exists' => 'Địa điểm không tồn tại trong hệ thống',
            'start_date.required' => 'Ngày bắt đầu là bắt buộc',
            'start_date.after' => 'Ngày bắt đầu phải sau thời điểm hiện tại',
            'end_date.required' => 'Ngày kết thúc là bắt buộc',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu',
            'organizer.required' => 'Người tổ chức là bắt buộc',
            'capacity.required' => 'Số lượng chỗ là bắt buộc',
            'capacity.min' => 'Số lượng chỗ phải lớn hơn 0',
            'waiting_capacity.min' => 'Số lượng chỗ chờ không được âm',
            'image_url.url' => 'URL hình ảnh không hợp lệ',
            
            // Speakers validation messages
            'speakers.array' => 'Danh sách diễn giả phải là mảng',
            'speakers.*.name.required' => 'Tên diễn giả là bắt buộc',
            'speakers.*.name.max' => 'Tên diễn giả không được vượt quá 255 ký tự',
            'speakers.*.title.max' => 'Chức danh không được vượt quá 255 ký tự',
            'speakers.*.bio.max' => 'Tiểu sử không được vượt quá 1000 ký tự',
            'speakers.*.email.email' => 'Email không hợp lệ',
            'speakers.*.email.max' => 'Email không được vượt quá 255 ký tự',
            'speakers.*.phone.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            'speakers.*.avatar_url.url' => 'URL avatar không hợp lệ',
            'speakers.*.avatar_url.max' => 'URL avatar không được vượt quá 500 ký tự',
            'speakers.*.organization.max' => 'Tổ chức không được vượt quá 255 ký tự',
        ];
    }
}
