<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Location;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kiểm tra xem có location nào không
        $locations = Location::all();
        
        if ($locations->isEmpty()) {
            $this->command->error('Không có location nào trong hệ thống!');
            $this->command->warn('Vui lòng chạy LocationSeeder trước: php artisan db:seed --class=LocationSeeder');
            return;
        }

        $this->command->info('Tìm thấy ' . $locations->count() . ' locations. Bắt đầu tạo events...');

        $events = [
            // 1. Event UPCOMING - WAITING (Sắp diễn ra, chờ phê duyệt)
            [
                'title' => 'Hội thảo Trí tuệ Nhân tạo 2025',
                'description' => 'Khám phá xu hướng AI và Machine Learning mới nhất trong năm 2025',
                'location_id' => $locations->random()->_id,
                'start_date' => Carbon::now()->addDays(30),
                'end_date' => Carbon::now()->addDays(30)->addHours(8),
                'organizer' => 'Khoa Công nghệ Thông tin',
                'topic' => 'Artificial Intelligence',
                'capacity' => 200,
                'waiting_capacity' => 50,
                'image_url' => '/storage/events/ai-conference-2025.jpg',
                'speakers' => [
                    [
                        'name' => 'TS. Nguyễn Văn An',
                        'email' => 'nguyenvanan@university.edu.vn',
                        'phone' => '0901234567',
                        'organization' => 'Đại học Bách Khoa',
                    ],
                    [
                        'name' => 'PGS.TS. Trần Thị Bình',
                        'email' => 'tranthibinh@tech.edu.vn',
                        'phone' => '0912345678',
                        'organization' => 'Viện Nghiên cứu AI',
                    ],
                ],
                'status_history' => [
                    ['name' => 'UPCOMING', 'sequence' => 1, 'changed_at' => Carbon::now()]
                ],
                'approval_history' => [
                    ['name' => 'WAITING', 'sequence' => 1, 'changed_at' => Carbon::now()]
                ],
                'current_confirmed' => 0,
                'current_waiting' => 0,
            ],

            // 2. Event UPCOMING - APPROVED (Sắp diễn ra, đã phê duyệt)
            [
                'title' => 'Workshop Machine Learning cho Sinh viên',
                'description' => 'Workshop thực hành về Machine Learning cơ bản đến nâng cao',
                'location_id' => $locations->random()->_id,
                'start_date' => Carbon::now()->addDays(15),
                'end_date' => Carbon::now()->addDays(15)->addHours(6),
                'organizer' => 'CLB Lập trình',
                'topic' => 'Machine Learning',
                'capacity' => 100,
                'waiting_capacity' => 30,
                'image_url' => '/storage/events/ml-workshop.jpg',
                'speakers' => [
                    [
                        'name' => 'Lê Hoàng Nam',
                        'email' => 'lehoangnam@tech.com',
                        'phone' => '0923456789',
                        'organization' => 'Google Vietnam',
                    ],
                ],
                'status_history' => [
                    ['name' => 'UPCOMING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(5)]
                ],
                'approval_history' => [
                    ['name' => 'WAITING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(5)],
                    ['name' => 'APPROVED', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(3)]
                ],
                'current_confirmed' => 45,
                'current_waiting' => 10,
            ],

            // 3. Event OPEN - APPROVED (Đang mở đăng ký)
            [
                'title' => 'Hội thảo Blockchain và Cryptocurrency',
                'description' => 'Tìm hiểu về công nghệ Blockchain và ứng dụng trong tương lai',
                'location_id' => $locations->random()->_id,
                'start_date' => Carbon::now()->addDays(7),
                'end_date' => Carbon::now()->addDays(7)->addHours(5),
                'organizer' => 'Khoa Kinh tế',
                'topic' => 'Blockchain Technology',
                'capacity' => 150,
                'waiting_capacity' => 40,
                'image_url' => '/storage/events/blockchain-conference.jpg',
                'speakers' => [
                    [
                        'name' => 'Phạm Minh Tuấn',
                        'email' => 'phamminhtuan@blockchain.vn',
                        'phone' => '0934567890',
                        'organization' => 'Blockchain Vietnam',
                    ],
                    [
                        'name' => 'Đỗ Thu Hà',
                        'email' => 'dothuha@crypto.com',
                        'phone' => '0945678901',
                        'organization' => 'Crypto Exchange',
                    ],
                ],
                'status_history' => [
                    ['name' => 'UPCOMING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(10)],
                    ['name' => 'OPEN', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(2)]
                ],
                'approval_history' => [
                    ['name' => 'WAITING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(10)],
                    ['name' => 'APPROVED', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(8)]
                ],
                'current_confirmed' => 120,
                'current_waiting' => 25,
            ],

            // 4. Event ONGOING - APPROVED (Đang diễn ra)
            [
                'title' => 'Ngày hội Công nghệ Thông tin 2025',
                'description' => 'Sự kiện công nghệ lớn nhất năm với nhiều hoạt động hấp dẫn',
                'location_id' => $locations->random()->_id,
                'start_date' => Carbon::now()->subHours(2),
                'end_date' => Carbon::now()->addHours(6),
                'organizer' => 'Khoa CNTT',
                'topic' => 'Information Technology',
                'capacity' => 500,
                'waiting_capacity' => 100,
                'image_url' => '/storage/events/it-day-2025.jpg',
                'speakers' => [
                    [
                        'name' => 'GS.TS. Hoàng Văn Phương',
                        'email' => 'hoangvanphuong@edu.vn',
                        'phone' => '0956789012',
                        'organization' => 'Đại học Quốc gia',
                    ],
                    [
                        'name' => 'ThS. Vũ Thị Mai',
                        'email' => 'vuthimai@microsoft.com',
                        'phone' => '0967890123',
                        'organization' => 'Microsoft Vietnam',
                    ],
                ],
                'status_history' => [
                    ['name' => 'UPCOMING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(20)],
                    ['name' => 'OPEN', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(15)],
                    ['name' => 'ONGOING', 'sequence' => 3, 'changed_at' => Carbon::now()->subHours(2)]
                ],
                'approval_history' => [
                    ['name' => 'WAITING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(20)],
                    ['name' => 'APPROVED', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(18)]
                ],
                'current_confirmed' => 480,
                'current_waiting' => 85,
            ],

            // 5. Event ENDED - APPROVED (Đã kết thúc)
            [
                'title' => 'Hội thảo Khoa học Máy tính Quốc tế',
                'description' => 'Hội thảo khoa học quốc tế với sự tham gia của nhiều chuyên gia',
                'location_id' => $locations->random()->_id,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->subDays(5)->addHours(10),
                'organizer' => 'Ban Khoa học Công nghệ',
                'topic' => 'Computer Science',
                'capacity' => 300,
                'waiting_capacity' => 50,
                'image_url' => '/storage/events/international-cs-conference.jpg',
                'speakers' => [
                    [
                        'name' => 'Prof. John Smith',
                        'email' => 'john.smith@stanford.edu',
                        'phone' => '+1234567890',
                        'organization' => 'Stanford University',
                    ],
                    [
                        'name' => 'Dr. Nguyễn Thành Công',
                        'email' => 'nguyenthanhcong@edu.vn',
                        'phone' => '0978901234',
                        'organization' => 'ĐH Bách Khoa HCM',
                    ],
                ],
                'status_history' => [
                    ['name' => 'UPCOMING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(30)],
                    ['name' => 'OPEN', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(25)],
                    ['name' => 'ONGOING', 'sequence' => 3, 'changed_at' => Carbon::now()->subDays(5)],
                    ['name' => 'ENDED', 'sequence' => 4, 'changed_at' => Carbon::now()->subDays(5)->addHours(10)]
                ],
                'approval_history' => [
                    ['name' => 'WAITING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(30)],
                    ['name' => 'APPROVED', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(28)]
                ],
                'current_confirmed' => 285,
                'current_waiting' => 0,
            ],

            // 6. Event CANCELLED - APPROVED (Đã hủy)
            [
                'title' => 'Workshop Big Data Analytics',
                'description' => 'Workshop về phân tích dữ liệu lớn (ĐÃ HỦY)',
                'location_id' => $locations->random()->_id,
                'start_date' => Carbon::now()->addDays(20),
                'end_date' => Carbon::now()->addDays(20)->addHours(4),
                'organizer' => 'Khoa Toán - Tin',
                'topic' => 'Big Data',
                'capacity' => 80,
                'waiting_capacity' => 20,
                'image_url' => '/storage/events/bigdata-workshop.jpg',
                'speakers' => [
                    [
                        'name' => 'Trần Quốc Huy',
                        'email' => 'tranquochuy@data.vn',
                        'phone' => '0989012345',
                        'organization' => 'Data Analytics Co.',
                    ],
                ],
                'status_history' => [
                    ['name' => 'UPCOMING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(15)],
                    ['name' => 'OPEN', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(10)],
                    ['name' => 'CANCELLED', 'sequence' => 3, 'changed_at' => Carbon::now()->subDays(2)]
                ],
                'approval_history' => [
                    ['name' => 'WAITING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(15)],
                    ['name' => 'APPROVED', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(13)]
                ],
                'current_confirmed' => 35,
                'current_waiting' => 0,
            ],

            // 7. Event UPCOMING - REJECTED (Bị từ chối)
            [
                'title' => 'Seminar Lập trình Game với Unity',
                'description' => 'Seminar về phát triển game 3D với Unity Engine',
                'location_id' => $locations->random()->_id,
                'start_date' => Carbon::now()->addDays(25),
                'end_date' => Carbon::now()->addDays(25)->addHours(5),
                'organizer' => 'CLB Game Development',
                'topic' => 'Game Development',
                'capacity' => 120,
                'waiting_capacity' => 30,
                'image_url' => '/storage/events/unity-seminar.jpg',
                'speakers' => [
                    [
                        'name' => 'Lương Văn Kiên',
                        'email' => 'luongvankien@games.vn',
                        'phone' => '0990123456',
                        'organization' => 'VNG Games',
                    ],
                ],
                'status_history' => [
                    ['name' => 'UPCOMING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(7)]
                ],
                'approval_history' => [
                    ['name' => 'WAITING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(7)],
                    ['name' => 'REJECTED', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(5)]
                ],
                'current_confirmed' => 0,
                'current_waiting' => 0,
            ],

            // 8. Event OPEN - APPROVED với nhiều speakers
            [
                'title' => 'Diễn đàn Khởi nghiệp Công nghệ',
                'description' => 'Kết nối các startup công nghệ với nhà đầu tư và mentor',
                'location_id' => $locations->random()->_id,
                'start_date' => Carbon::now()->addDays(12),
                'end_date' => Carbon::now()->addDays(12)->addHours(8),
                'organizer' => 'Trung tâm Hỗ trợ Khởi nghiệp',
                'topic' => 'Startup & Innovation',
                'capacity' => 250,
                'waiting_capacity' => 60,
                'image_url' => '/storage/events/startup-forum.jpg',
                'speakers' => [
                    [
                        'name' => 'Nguyễn Hải Ninh',
                        'email' => 'nguyenhaininh@startup.vn',
                        'phone' => '0901234567',
                        'organization' => 'Techstars Vietnam',
                    ],
                    [
                        'name' => 'Lê Diệu Linh',
                        'email' => 'ledieulinh@vc.com',
                        'phone' => '0912345678',
                        'organization' => 'Vietnam Silicon Valley',
                    ],
                    [
                        'name' => 'Phạm Thanh Tùng',
                        'email' => 'phamthanhtung@investor.vn',
                        'phone' => '0923456789',
                        'organization' => 'VN Investment Group',
                    ],
                ],
                'status_history' => [
                    ['name' => 'UPCOMING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(8)],
                    ['name' => 'OPEN', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(3)]
                ],
                'approval_history' => [
                    ['name' => 'WAITING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(8)],
                    ['name' => 'APPROVED', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(6)]
                ],
                'current_confirmed' => 180,
                'current_waiting' => 45,
            ],

            // 9. Event ENDED - APPROVED (Full capacity)
            [
                'title' => 'Workshop Cybersecurity 101',
                'description' => 'Workshop cơ bản về bảo mật thông tin và an ninh mạng',
                'location_id' => $locations->random()->_id,
                'start_date' => Carbon::now()->subDays(3),
                'end_date' => Carbon::now()->subDays(3)->addHours(6),
                'organizer' => 'Phòng An ninh Thông tin',
                'topic' => 'Cybersecurity',
                'capacity' => 60,
                'waiting_capacity' => 15,
                'image_url' => '/storage/events/cybersecurity-workshop.jpg',
                'speakers' => [
                    [
                        'name' => 'Đặng Quang Minh',
                        'email' => 'dangquangminh@security.vn',
                        'phone' => '0934567890',
                        'organization' => 'BKAV Corporation',
                    ],
                ],
                'status_history' => [
                    ['name' => 'UPCOMING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(14)],
                    ['name' => 'OPEN', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(10)],
                    ['name' => 'ONGOING', 'sequence' => 3, 'changed_at' => Carbon::now()->subDays(3)],
                    ['name' => 'ENDED', 'sequence' => 4, 'changed_at' => Carbon::now()->subDays(3)->addHours(6)]
                ],
                'approval_history' => [
                    ['name' => 'WAITING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(14)],
                    ['name' => 'APPROVED', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(12)]
                ],
                'current_confirmed' => 60,
                'current_waiting' => 15,
            ],

            // 10. Event UPCOMING - APPROVED (No speakers)
            [
                'title' => 'Tọa đàm Nghề nghiệp IT',
                'description' => 'Tọa đàm về con đường sự nghiệp trong ngành IT',
                'location_id' => $locations->random()->_id,
                'start_date' => Carbon::now()->addDays(18),
                'end_date' => Carbon::now()->addDays(18)->addHours(3),
                'organizer' => 'Phòng Tư vấn Sinh viên',
                'topic' => 'Career Development',
                'capacity' => 150,
                'waiting_capacity' => 30,
                'image_url' => '/storage/events/career-talk.jpg',
                'speakers' => [],
                'status_history' => [
                    ['name' => 'UPCOMING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(4)]
                ],
                'approval_history' => [
                    ['name' => 'WAITING', 'sequence' => 1, 'changed_at' => Carbon::now()->subDays(4)],
                    ['name' => 'APPROVED', 'sequence' => 2, 'changed_at' => Carbon::now()->subDays(2)]
                ],
                'current_confirmed' => 25,
                'current_waiting' => 5,
            ],
        ];

        $createdCount = 0;
        foreach ($events as $eventData) {
            try {
                Event::create($eventData);
                $createdCount++;
                $this->command->info("Đã tạo: {$eventData['title']}");
            } catch (Exception $e) {
                $this->command->error("Lỗi khi tạo '{$eventData['title']}': " . $e->getMessage());
            }
        }
    }
}
