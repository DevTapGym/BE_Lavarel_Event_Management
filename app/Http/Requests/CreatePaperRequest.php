<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaperRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền thực hiện request này không
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Các quy tắc validation cho request
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'abstract' => 'nullable|string|max:5000',
            'author' => 'required|array|min:1',
            'author.*' => 'required|string|max:255',
            'event_id' => 'required|string',
            'file_url' => 'nullable|url|max:500',
            'category' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:50',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
        ];
    }

    /**
     * Các thông báo lỗi tùy chỉnh
     * 
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Title
            'title.required' => 'Tiêu đề bài báo là bắt buộc.',
            'title.string' => 'Tiêu đề phải là chuỗi ký tự.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',

            // Abstract
            'abstract.string' => 'Tóm tắt phải là chuỗi ký tự.',
            'abstract.max' => 'Tóm tắt không được vượt quá 5000 ký tự.',

            // Author
            'author.required' => 'Danh sách tác giả là bắt buộc.',
            'author.array' => 'Danh sách tác giả phải là mảng.',
            'author.min' => 'Phải có ít nhất một tác giả.',
            'author.*.required' => 'Tên tác giả không được để trống.',
            'author.*.string' => 'Tên tác giả phải là chuỗi ký tự.',
            'author.*.max' => 'Tên tác giả không được vượt quá 255 ký tự.',

            // Event ID
            'event_id.required' => 'Mã sự kiện là bắt buộc.',
            'event_id.string' => 'Mã sự kiện phải là chuỗi ký tự.',

            // File URL
            'file_url.max' => 'URL file không được vượt quá 500 ký tự.',

            // Category
            'category.string' => 'Danh mục phải là chuỗi ký tự.',
            'category.max' => 'Danh mục không được vượt quá 100 ký tự.',

            // Language
            'language.string' => 'Ngôn ngữ phải là chuỗi ký tự.',
            'language.max' => 'Ngôn ngữ không được vượt quá 50 ký tự.',

            // Keywords
            'keywords.array' => 'Từ khóa phải là mảng.',
            'keywords.*.string' => 'Mỗi từ khóa phải là chuỗi ký tự.',
            'keywords.*.max' => 'Mỗi từ khóa không được vượt quá 100 ký tự.',
        ];
    }
}
