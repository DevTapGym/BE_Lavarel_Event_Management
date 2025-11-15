<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Paper;
use App\Models\Event;

class PaperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kiểm tra xem có event nào không
        $events = Event::all();
        
        if ($events->isEmpty()) {
            $this->command->error('Không có event nào trong hệ thống!');
            $this->command->warn('Vui lòng chạy EventSeeder trước: php artisan db:seed --class=EventSeeder');
            return;
        }

        $this->command->info('Tìm thấy ' . $events->count() . ' events. Bắt đầu tạo papers...');

        // Lấy một số event để gán papers
        $approvedEvents = Event::whereNotNull('approval_history')
            ->get()
            ->filter(function ($event) {
                $approvalHistory = $event->approval_history ?? [];
                $lastApproval = end($approvalHistory);
                return $lastApproval && $lastApproval['name'] === 'APPROVED';
            });

        if ($approvedEvents->isEmpty()) {
            $this->command->warn('Không có event APPROVED nào. Sẽ sử dụng event bất kỳ...');
            $targetEvents = $events->take(2);
        } else {
            $targetEvents = $approvedEvents->take(2);
        }

        if ($targetEvents->isEmpty()) {
            $this->command->error('Không thể tìm thấy event phù hợp để gán papers!');
            return;
        }

        $papers = [
            [
                'title' => 'Deep Learning Applications in Medical Image Analysis',
                'abstract' => 'This paper presents a comprehensive study on the application of deep learning techniques in medical image analysis. We explore various convolutional neural network architectures including ResNet, VGG, and DenseNet for automated disease detection from X-ray and MRI scans. Our experimental results demonstrate that deep learning models can achieve diagnostic accuracy comparable to experienced radiologists, with potential applications in early disease detection and treatment planning.',
                'author' => ['Dr. Nguyen Van Anh', 'Dr. Tran Thi Binh', 'Prof. Le Hoang Nam'],
                'event_id' => (string) $targetEvents->first()->_id,
                'file_url' => 'https://example.com/papers/deep-learning-medical-imaging.pdf',
                'view' => 245,
                'download' => 89,
                'category' => 'Artificial Intelligence',
                'language' => 'English',
                'keywords' => ['Deep Learning', 'Medical Imaging', 'CNN', 'Disease Detection', 'Healthcare AI'],
            ],
            [
                'title' => 'Blockchain Technology for Supply Chain Management: A Case Study',
                'abstract' => 'Nghiên cứu này trình bày việc ứng dụng công nghệ Blockchain trong quản lý chuỗi cung ứng tại Việt Nam. Chúng tôi đề xuất một kiến trúc hệ thống dựa trên Ethereum smart contracts để theo dõi và xác thực nguồn gốc sản phẩm. Kết quả triển khai thí điểm cho thấy hệ thống giúp tăng độ minh bạch, giảm chi phí vận hành và nâng cao niềm tin của người tiêu dùng.',
                'author' => ['TS. Pham Minh Tuan', 'ThS. Do Thu Ha'],
                'event_id' => (string) $targetEvents->first()->_id,
                'file_url' => 'https://example.com/papers/blockchain-supply-chain.pdf',
                'view' => 182,
                'download' => 67,
                'category' => 'Blockchain',
                'language' => 'Tiếng Việt',
                'keywords' => ['Blockchain', 'Supply Chain', 'Smart Contracts', 'Ethereum', 'Traceability'],
            ],
            [
                'title' => 'Machine Learning Approaches for Natural Language Processing in Vietnamese',
                'abstract' => 'Bài báo này nghiên cứu các phương pháp Machine Learning cho xử lý ngôn ngữ tự nhiên tiếng Việt. Chúng tôi so sánh hiệu quả của các mô hình BERT, PhoBERT và mBERT trong các tác vụ phân loại văn bản, nhận dạng thực thể có tên, và phân tích cảm xúc. Kết quả thực nghiệm cho thấy PhoBERT đạt độ chính xác cao nhất với F1-score 94.2% trong tác vụ phân loại văn bản tiếng Việt.',
                'author' => ['GS.TS. Hoang Van Phuong', 'TS. Vu Thi Mai', 'ThS. Nguyen Thanh Cong'],
                'event_id' => (string) $targetEvents->last()->_id,
                'file_url' => 'https://example.com/papers/ml-nlp-vietnamese.pdf',
                'view' => 312,
                'download' => 145,
                'category' => 'Natural Language Processing',
                'language' => 'Tiếng Việt',
                'keywords' => ['Machine Learning', 'NLP', 'Vietnamese', 'BERT', 'PhoBERT', 'Text Classification'],
            ],
            [
                'title' => 'Cybersecurity Threats and Countermeasures in IoT Systems',
                'abstract' => 'The rapid growth of Internet of Things (IoT) devices has introduced new security challenges. This paper analyzes common cybersecurity threats in IoT ecosystems including DDoS attacks, data breaches, and unauthorized access. We propose a multi-layered security framework incorporating encryption, authentication protocols, and anomaly detection using machine learning. Our evaluation shows that the proposed framework can detect 98.5% of known attacks while maintaining low false positive rates.',
                'author' => ['Dr. Dang Quang Minh', 'Prof. Tran Quoc Huy'],
                'event_id' => (string) $targetEvents->last()->_id,
                'file_url' => 'https://example.com/papers/iot-cybersecurity.pdf',
                'view' => 198,
                'download' => 76,
                'category' => 'Cybersecurity',
                'language' => 'English',
                'keywords' => ['IoT', 'Cybersecurity', 'Network Security', 'Machine Learning', 'Threat Detection'],
            ],
        ];

        $createdCount = 0;
        foreach ($papers as $paperData) {
            try {
                Paper::create($paperData);
                $createdCount++;
                $this->command->info("Đã tạo: {$paperData['title']}");
            } catch (\Exception $e) {
                $this->command->error("Lỗi khi tạo '{$paperData['title']}': " . $e->getMessage());
            }
        }
    }
}
