<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use ApiResponse;

    public function export()
    {
        try {
            $users = User::all();

            $exportData = [];
            foreach ($users as $user) {
                // Bỏ qua admin@gmail.com
                if ($user->email === 'admin@gmail.com') {
                    continue;
                }

                $exportData[] = [
                    'ID' => (string) $user->_id,
                    'Name' => $user->name,
                    'Email' => $user->email,
                    'Phone' => $user->phone ?? '',
                    'Is Active' => $user->is_active ? 'Yes' : 'No',
                    'Reputation Score' => $user->reputation_score ?? 70,
                    'Roles' => implode(', ', $user->roles ?? []),
                    'Created At' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '',
                ];
            }

            // Tạo CSV
            $filename = 'users_'.now()->format('Ymd_His').'.csv';
            $handle = fopen('php://temp', 'r+');

            // Thêm BOM để Excel hiển thị UTF-8 đúng
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($handle, array_keys($exportData[0] ?? []));

            // Data
            foreach ($exportData as $row) {
                fputcsv($handle, $row);
            }

            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);

            return response($csv, 200)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');

        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                'Lỗi khi export users: '.$e->getMessage()
            );
        }
    }

    public function import(Request $request)
    {
        try {
            // Validate file upload
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
            ], [
                'file.required' => 'File là bắt buộc',
                'file.mimes' => 'Chỉ chấp nhận file CSV hoặc Excel',
                'file.max' => 'Kích thước file không được vượt quá 5MB',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    422,
                    'Validation Error',
                    $validator->errors()->first()
                );
            }

            $file = $request->file('file');
            $path = $file->getRealPath();

            // Đọc file CSV
            $data = array_map(function ($line) {
                return str_getcsv($line, ',', '"', '\\');
            }, file($path));

            if (count($data) < 2) {
                return $this->errorResponse(
                    422,
                    'Validation Error',
                    'File không có dữ liệu để import'
                );
            }

            // Lấy header (dòng đầu tiên)
            $header = array_map('trim', $data[0]);

            // Kiểm tra các trường bắt buộc
            $requiredFields = ['Email'];
            foreach ($requiredFields as $field) {
                if (! in_array($field, $header)) {
                    return $this->errorResponse(
                        422,
                        'Validation Error',
                        "Thiếu trường bắt buộc: {$field}"
                    );
                }
            }

            $createdCount = 0;
            $skippedCount = 0;
            $errors = [];

            // Xử lý từng dòng dữ liệu (bỏ qua header)
            for ($i = 1; $i < count($data); $i++) {
                $row = $data[$i];

                // Bỏ qua dòng trống
                if (empty(array_filter($row))) {
                    $skippedCount++;

                    continue;
                }

                // Tạo array associative từ header và data
                $userData = array_combine($header, $row);

                // 1. Validate Email (bắt buộc)
                if (empty($userData['Email']) || trim($userData['Email']) === '') {
                    $errors[] = 'Dòng '.($i + 1).': Email là bắt buộc';
                    $skippedCount++;

                    continue;
                }

                $email = trim($userData['Email']);

                // 2. Kiểm tra email đã tồn tại → Bỏ qua
                $existingUser = User::where('email', $email)->first();
                if ($existingUser) {
                    $skippedCount++;

                    continue;
                }

                // 3. Validate email format
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Dòng '.($i + 1).': Email không hợp lệ';
                    $skippedCount++;

                    continue;
                }

                // 4. Xử lý Roles - Bỏ qua dòng nếu trống
                if (empty($userData['Roles']) || trim($userData['Roles']) === '') {
                    $errors[] = 'Dòng '.($i + 1).': Roles là bắt buộc';
                    $skippedCount++;

                    continue;
                }

                $rolesStr = trim($userData['Roles']);
                $roles = array_map('trim', explode(',', $rolesStr));

                // 5. Xử lý Is Active - Mặc định False nếu trống
                $isActive = false;
                if (isset($userData['Is Active']) && trim($userData['Is Active']) !== '') {
                    $isActiveValue = strtolower(trim($userData['Is Active']));
                    $isActive = in_array($isActiveValue, ['yes', '1', 'true']);
                }

                // 6. Xử lý Reputation Score - Mặc định 70 nếu trống
                $reputationScore = 70;
                if (isset($userData['Reputation Score']) && trim($userData['Reputation Score']) !== '') {
                    $reputationScore = (int) trim($userData['Reputation Score']);
                }

                // 7. Xử lý Name - Trống thì set rỗng
                $name = isset($userData['Name']) && trim($userData['Name']) !== ''
                    ? trim($userData['Name'])
                    : '';

                // 8. Xử lý Phone - Trống thì set rỗng
                $phone = isset($userData['Phone']) && trim($userData['Phone']) !== ''
                    ? trim($userData['Phone'])
                    : '';

                // Tạo user mới
                try {
                    User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => bcrypt('123456'), // Mật khẩu mặc định
                        'phone' => $phone,
                        'is_active' => $isActive,
                        'reputation_score' => $reputationScore,
                        'roles' => $roles,
                    ]);
                    $createdCount++;
                } catch (Exception $e) {
                    $errors[] = 'Dòng '.($i + 1).': '.$e->getMessage();
                    $skippedCount++;
                }
            }

            return $this->successResponse(
                200,
                'Import hoàn tất',
                [
                    'created_count' => $createdCount,
                    'skipped_count' => $skippedCount,
                    'total_rows' => count($data) - 1,
                    'errors' => $errors,
                ]
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                'Internal Server Error',
                'Lỗi khi import users: '.$e->getMessage()
            );
        }
    }
}
