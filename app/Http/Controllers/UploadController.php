<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\Paper;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Validation\ValidationException;

class UploadController extends Controller
{
    use ApiResponse;

    public function uploadAvatar(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
            ]);

            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse(
                    401,
                    'Unauthorized',
                    'User not authenticated'
                );
            }

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();

            $fileName = 'Avatar_' . $user->id . '_' . now()->format('Ymd_His') . '.' . $extension;

            $path = $file->storeAs('Avatar', $fileName, 'public');
            $url = Storage::url($path);

            // Xóa avatar cũ nếu có (tùy chọn)
            if ($user->avatar) {
                $oldPath = str_replace('/storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $user->update([
                'avatar' => $url
            ]);

            return $this->successResponse(
                200,
                'Upload avatar successful',
                [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'updated_at' => $user->updated_at
                ]
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                $e->getMessage(),
            );
        }
    }

    public function uploadEventImage(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120', // Max 5MB
                'event_id' => 'required|string',
            ], [
                'image.required' => 'Hình ảnh là bắt buộc',
                'image.mimes' => 'Chỉ chấp nhận file jpg, jpeg, png, webp',
                'image.max' => 'Kích thước file không được vượt quá 5MB',
                'event_id.required' => 'Mã sự kiện là bắt buộc',
            ]);

            $eventId = $request->input('event_id');

            // Tìm event
            $event = Event::find($eventId);
            if (!$event) {
                return $this->errorResponse(
                    404,
                    'Not Found',
                    'Sự kiện không tồn tại trong hệ thống'
                );
            }

            // Upload file
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();

            // Tạo tên file: Event_EventID_Timestamp.ext
            $fileName = 'Event_' . $eventId . '_' . now()->format('Ymd_His') . '.' . $extension;

            // Lưu vào thư mục events
            $path = $file->storeAs('events', $fileName, 'public');
            $url = Storage::url($path);

            // Xóa image cũ nếu có
            if ($event->image_url) {
                $oldPath = str_replace('/storage/', '', $event->image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Cập nhật image_url cho event
            $event->image_url = $url;
            $event->save();

            return $this->successResponse(
                200,
                'Upload hình ảnh sự kiện thành công',
                [
                    'event_id' => $eventId,
                    'title' => $event->title,
                    'image_url' => $url,
                    'uploaded_at' => now()->format('Y-m-d H:i:s')
                ]
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                422,
                'Validation Error',
                $e->validator->errors()->first()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                $e->getMessage()
            );
        }
    }

        public function uploadSpeakerAvatar(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'image' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120', // Max 5MB
                'event_id' => 'required|string',
                'email' => 'required|email',
            ], [
                'image.required' => 'Hình ảnh là bắt buộc',
                'image.mimes' => 'Chỉ chấp nhận file jpg, jpeg, png, webp',
                'image.max' => 'Kích thước file không được vượt quá 5MB',
                'event_id.required' => 'Mã sự kiện là bắt buộc',
                'email.required' => 'Email diễn giả là bắt buộc',
                'email.email' => 'Email không hợp lệ',
            ]);

            $eventId = $request->input('event_id');
            $speakerEmail = $request->input('email');

            // Tìm event
            $event = Event::find($eventId);
            if (!$event) {
                return $this->errorResponse(
                    404,
                    'Not Found',
                    'Sự kiện không tồn tại trong hệ thống'
                );
            }

            // Kiểm tra speakers có tồn tại không
            $speakers = $event->speakers ?? [];
            if (empty($speakers)) {
                return $this->errorResponse(
                    404,
                    'Not Found',
                    'Sự kiện này chưa có diễn giả nào'
                );
            }

            // Tìm speaker theo email
            $speakerIndex = null;
            $speaker = null;
            foreach ($speakers as $index => $spk) {
                if (isset($spk['email']) && $spk['email'] === $speakerEmail) {
                    $speakerIndex = $index;
                    $speaker = $spk;
                    break;
                }
            }

            if ($speakerIndex === null) {
                return $this->errorResponse(
                    404,
                    'Not Found',
                    'Không tìm thấy diễn giả với email: ' . $speakerEmail
                );
            }

            // Upload file
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();

            // Tạo tên file: Speaker_EventID_Email_Timestamp.ext
            $safeEmail = str_replace(['@', '.'], '_', $speakerEmail);
            $fileName = 'Speaker_' . $eventId . '_' . $safeEmail . '_' . now()->format('Ymd_His') . '.' . $extension;

            // Lưu vào thư mục speakers
            $path = $file->storeAs('Speakers', $fileName, 'public');
            $url = Storage::url($path);

            // Xóa avatar cũ nếu có
            if (isset($speaker['avatar_url']) && !empty($speaker['avatar_url'])) {
                $oldPath = str_replace('/storage/', '', $speaker['avatar_url']);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Cập nhật avatar_url cho speaker
            $speakers[$speakerIndex]['avatar_url'] = $url;
            $event->speakers = $speakers;
            $event->save();

            return $this->successResponse(
                200,
                'Upload avatar diễn giả thành công',
                [
                    'event_id' => $eventId,
                    'speaker' => [
                        'name' => $speaker['name'] ?? null,
                        'email' => $speakerEmail,
                        'avatar_url' => $url,
                        'title' => $speaker['title'] ?? null,
                        'organization' => $speaker['organization'] ?? null,
                    ],
                    'uploaded_at' => now()->format('Y-m-d H:i:s')
                ]
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                422,
                'Validation Error',
                $e->validator->errors()->first()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                $e->getMessage()
            );
        }
    }

    public function uploadPages(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'pdf' => 'required|file|mimes:pdf|max:10240', // Max 10MB
                'paper_id' => 'required|string',
            ], [
                'pdf.required' => 'File PDF là bắt buộc',
                'pdf.mimes' => 'Chỉ chấp nhận file PDF',
                'pdf.max' => 'Kích thước file không được vượt quá 10MB',
                'paper_id.required' => 'Mã bài báo là bắt buộc',
            ]);

            $paperId = $request->input('paper_id');

            // Tìm paper
            $paper = Paper::find($paperId);
            if (!$paper) {
                return $this->errorResponse(
                    404,
                    'Not Found',
                    'Bài báo không tồn tại trong hệ thống'
                );
            }

            // Upload file
            $file = $request->file('pdf');
            $extension = $file->getClientOriginalExtension();

            // Tạo tên file: Paper_PaperID_Timestamp.pdf
            $fileName = 'Paper_' . $paperId . '_' . now()->format('Ymd_His') . '.' . $extension;

            // Lưu vào thư mục papers
            $path = $file->storeAs('papers', $fileName, 'public');
            $url = Storage::url($path);

            // Xóa file cũ nếu có
            if ($paper->file_url) {
                $oldPath = str_replace('/storage/', '', $paper->file_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Cập nhật file_url cho paper
            $paper->file_url = $url;
            $paper->save();

            return $this->successResponse(
                200,
                'Upload file PDF bài báo thành công',
                [
                    'paper_id' => $paperId,
                    'title' => $paper->title,
                    'file_url' => $url,
                    'file_size' => $file->getSize(),
                    'uploaded_at' => now()->format('Y-m-d H:i:s')
                ]
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                422,
                'Validation Error',
                $e->validator->errors()->first()
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                $e->getMessage()
            );
        }
    }

    public function downloadPaper($paperId)
    {
        try {
            // Tìm paper
            $paper = Paper::find($paperId);
            if (!$paper) {
                return $this->errorResponse(
                    404,
                    'Not Found',
                    'Bài báo không tồn tại trong hệ thống'
                );
            }

            // Kiểm tra file_url có tồn tại không
            if (!$paper->file_url) {
                return $this->errorResponse(
                    404,
                    'Not Found',
                    'Bài báo chưa có file PDF'
                );
            }

            // Lấy đường dẫn file từ file_url
            $path = str_replace('/storage/', '', $paper->file_url);

            // Kiểm tra file có tồn tại không
            if (!Storage::disk('public')->exists($path)) {
                return $this->errorResponse(
                    404,
                    'Not Found',
                    'File PDF không tồn tại trên hệ thống'
                );
            }

            // Tăng số lượt download
            $paper->increment('download');

            // Lấy đường dẫn tuyệt đối của file
            $absolutePath = Storage::disk('public')->path($path);

            // Tạo tên file download với title của paper
            $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $paper->title);
            $downloadName = $safeTitle . '.pdf';

            // Download file
            return response()->download($absolutePath, $downloadName);

        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                $e->getMessage()
            );
        }
    }
}
