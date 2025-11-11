<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Exception;

class UploadController extends Controller
{
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

    public function uploadPages(Request $request)
    {
        try {
            $request->validate([
                'pdf' => 'required|file|mimes:pdf|max:10240',
            ]);

            $file = $request->file('pdf');

            $extension = $file->getClientOriginalExtension();
            $fileName = 'Document_' . now()->format('Ymd_His') . '.' . $extension;
            $path = $file->storeAs('pdfs', $fileName, 'local');

            return $this->successResponse(
                200,
                'Upload PDF successful',
                ['path' => $path]
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                $e->getMessage(),
            );
        }
    }

    public function downloadPdf($fileName)
    {
        try {
            $path = 'pdfs/' . $fileName;

            if (!Storage::disk('local')->exists($path)) {
                return $this->errorResponse(
                    404,
                    'Not Found',
                    'File Not Found'
                );
            }

            $absolutePath = Storage::disk('local')->path($path);
            return response()->download($absolutePath);
        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                $e->getMessage(),
            );
        }
    }
}
