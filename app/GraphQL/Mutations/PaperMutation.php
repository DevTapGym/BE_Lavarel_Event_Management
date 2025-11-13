<?php

namespace App\GraphQL\Mutations;

use App\Models\Paper;
use App\Models\Event;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\CreatePaperRequest;
use App\Http\Requests\UpdatePaperRequest;
use Illuminate\Validation\ValidationException;
use Exception;

class PaperMutation
{
    /**
     * Tạo paper mới
     * 
     * @param mixed $_ 
     * @param array $args
     * @return Paper
     * @throws ValidationException
     */
    public function create($_, array $args)
    {
        $request = new CreatePaperRequest();
        $validator = Validator::make($args, $request->rules(), $request->messages());
        $validator->validate();

        // Kiểm tra event_id có tồn tại không
        $event = Event::find($args['event_id']);
        if (!$event) {
            throw ValidationException::withMessages([
                'event_id' => ['Sự kiện không tồn tại trong hệ thống.'],
            ]);
        }

        // Tạo paper mới
        $paper = Paper::create([
            'title' => $args['title'],
            'abstract' => $args['abstract'] ?? null,
            'author' => $args['author'],
            'event_id' => $args['event_id'],
            'file_url' => $args['file_url'] ?? null,
            'category' => $args['category'] ?? null,
            'language' => $args['language'] ?? null,
            'keywords' => $args['keywords'] ?? [],
            'view' => 0,
            'download' => 0,
        ]);

        return $paper;
    }

    /**
     * Cập nhật paper
     * 
     * @param mixed $_
     * @param array $args
     * @return Paper
     * @throws ValidationException
     */
    public function update($_, array $args)
    {
        $request = new UpdatePaperRequest();
        $validator = Validator::make($args, $request->rules(), $request->messages());
        $validator->validate();

        // Tìm paper cần cập nhật
        $paper = Paper::find($args['_id']);
        if (!$paper) {
            throw ValidationException::withMessages([
                '_id' => ['Paper không tồn tại trong hệ thống.'],
            ]);
        }

        // Nếu có event_id mới, kiểm tra tồn tại
        if (isset($args['event_id'])) {
            $event = Event::find($args['event_id']);
            if (!$event) {
                throw ValidationException::withMessages([
                    'event_id' => ['Sự kiện không tồn tại trong hệ thống.'],
                ]);
            }
        }

        // Cập nhật các trường
        $updateFields = [
            'title',
            'abstract',
            'author',
            'event_id',
            'file_url',
            'category',
            'language',
            'keywords'
        ];

        foreach ($updateFields as $field) {
            if (isset($args[$field])) {
                $paper->$field = $args[$field];
            }
        }

        $paper->save();
        return $paper->fresh();
    }

    /**
     * Xóa paper
     * 
     * @param mixed $_
     * @param array $args
     * @return Paper
     * @throws ValidationException
     */
    public function delete($_, array $args)
    {
        // Validate _id
        $validator = Validator::make($args, [
            '_id' => 'required|string',
        ], [
            '_id.required' => 'ID paper là bắt buộc.',
            '_id.string' => 'ID paper phải là chuỗi.',
        ]);

        $validator->validate();

        // Tìm paper cần xóa
        $paper = Paper::find($args['_id']);
        if (!$paper) {
            throw ValidationException::withMessages([
                '_id' => ['Paper không tồn tại trong hệ thống.'],
            ]);
        }

        // Lưu thông tin paper trước khi xóa để trả về
        $deletedPaper = $paper->replicate();

        // Xóa paper
        $paper->delete();

        return $deletedPaper;
    }
}
