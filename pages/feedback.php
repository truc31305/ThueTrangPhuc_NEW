<?php
require_once __DIR__ . '/../inc/storage.php';
// Xử lý form gửi feedback và lưu file JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $content = htmlspecialchars(trim($_POST['content'] ?? ''));
    if ($name && $email && $content) {
        $path = __DIR__ . '/../data/feedback.json';
        $list = readJsonFile($path);
        $list[] = [
            'id' => uniqid('fb_', true),
            'name' => $name,
            'email' => $email,
            'content' => $content,
            'createdAt' => date('c')
        ];
        writeJsonFile($path, $list);
        $success_message = "Cảm ơn bạn đã gửi phản hồi! Chúng tôi sẽ xem xét và phản hồi sớm nhất.";
    }
}

// Lấy danh sách feedback mẫu (hiển thị) + đã lưu
$static_feedbacks = [
    [
        'avatar' => 'https://i.pravatar.cc/150?img=1',
        'name' => 'Ngọc Trâm',
        'service' => 'Thuê áo dài chụp kỷ yếu',
        'content' => 'Đồ rất đẹp và thơm, nhân viên hỗ trợ nhiệt tình. Sẽ quay lại lần sau ❤️',
        'rating' => 5
    ],
    [
        'avatar' => 'https://i.pravatar.cc/150?img=12',
        'name' => 'Thanh Phong',
        'service' => 'Thuê vest cưới',
        'content' => 'Vest rất vừa, lên ảnh cực kỳ đẹp. Giá hợp lý và giao đồ đúng hẹn.',
        'rating' => 5
    ],
    [
        'avatar' => 'https://i.pravatar.cc/150?img=5',
        'name' => 'Mỹ Duyên',
        'service' => 'Thuê trang phục cosplay',
        'content' => 'Nhiều mẫu mới và sạch sẽ. Mình được tư vấn chọn rất nhiệt tình 💕',
        'rating' => 5
    ],
    [
        'avatar' => 'https://i.pravatar.cc/150?img=8',
        'name' => 'Minh Khôi',
        'service' => 'Thuê đồ dạ hội',
        'content' => 'Chất lượng tốt, giá cả phải chăng. Rất hài lòng với dịch vụ!',
        'rating' => 5
    ],
    [
        'avatar' => 'https://i.pravatar.cc/150?img=9',
        'name' => 'Thu Hà',
        'service' => 'Thuê áo dài truyền thống',
        'content' => 'Áo dài rất đẹp và sang trọng. Phù hợp cho sự kiện quan trọng.',
        'rating' => 5
    ],
    [
        'avatar' => 'https://i.pravatar.cc/150?img=15',
        'name' => 'Đức Anh',
        'service' => 'Thuê suit công sở',
        'content' => 'Chuyên nghiệp, đúng size. Sẽ giới thiệu cho bạn bè!',
        'rating' => 5
    ]
];

$stored_feedbacks = readJsonFile(__DIR__ . '/../data/feedback.json');
$feedbacks = array_merge($stored_feedbacks, $static_feedbacks);
?>

<div class="feedback-page">
     Hero Section 
    <div class="feedback-hero">
        <div class="feedback-pattern"></div>
        <div style="position: relative; z-index: 1; text-align: center; padding: 48px 24px;">
            <h1 style="margin: 0 0 12px 0; font-size: 36px; font-weight: 800; color: #be185d;">
                💖 Phản hồi khách hàng
            </h1>
            <p style="color: #9d174d; font-size: 16px; margin: 0;">
                Cảm ơn bạn đã tin tưởng và lựa chọn SAPAQT cho những khoảnh khắc đáng nhớ
            </p>
        </div>
    </div>

     Success Message 
    <?php if (isset($success_message)): ?>
    <div class="alert alert-success" style="margin: 24px 0;">
        <?= $success_message ?>
    </div>
    <?php endif; ?>

     Feedback List Section 
    <section style="margin: 32px 0;">
        <h2 style="text-align: center; margin-bottom: 24px; font-weight: 700; color: #be185d;">
            Khách hàng nói gì về chúng tôi
        </h2>
        
        <div class="feedback-grid">
            <?php foreach ($feedbacks as $feedback): ?>
            <div class="feedback-card">
                <div class="feedback-avatar">
                    <img src="<?= $feedback['avatar'] ?>" alt="<?= $feedback['name'] ?>" />
                </div>
                <div class="feedback-content">
                    <h3 class="feedback-name"><?= $feedback['name'] ?></h3>
                    <p class="feedback-service"><?= $feedback['service'] ?></p>
                    <div class="feedback-rating">
                        <?php for ($i = 0; $i < $feedback['rating']; $i++): ?>
                            <span style="color: #fbbf24;">★</span>
                        <?php endfor; ?>
                    </div>
                    <p class="feedback-text">"<?= $feedback['content'] ?>"</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

     Submit Feedback Form Section 
    <section class="feedback-form-section">
        <div class="feedback-form-container">
            <h2 style="text-align: center; margin-bottom: 18px; font-weight: 700; color: #be185d;">
                Gửi phản hồi của bạn
            </h2>
            <p style="text-align: center; color: var(--muted); margin-bottom: 24px;">
                Ý kiến của bạn giúp chúng tôi phục vụ tốt hơn mỗi ngày
            </p>
            
            <form method="POST" class="feedback-form">
                <div class="form-row">
                    <label for="name">Họ và tên *</label>
                    <input type="text" id="name" name="name" required placeholder="Nhập họ tên của bạn">
                </div>
                
                <div class="form-row">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required placeholder="email@example.com">
                </div>
                
                <div class="form-row">
                    <label for="content">Nội dung phản hồi *</label>
                    <textarea id="content" name="content" rows="5" required placeholder="Chia sẻ trải nghiệm của bạn với SAPAQT..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="submit_feedback" class="btn btn-primary">
                        Gửi phản hồi
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>

<style>
/* Feedback page specific styles */
.feedback-page {
    position: relative;
}

.feedback-hero {
    position: relative;
    border: 1px solid var(--border);
    border-radius: 24px;
    overflow: hidden;
    background: linear-gradient(135deg, #ffe4ec, #fff7fb);
    margin-bottom: 32px;
}

.feedback-pattern {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image: radial-gradient(#ffd1e8 1px, transparent 1px), radial-gradient(#fde68a 1px, transparent 1px);
    background-position: 0 0, 20px 20px;
    background-size: 40px 40px;
    opacity: 0.35;
}

.feedback-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.feedback-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(236, 72, 153, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.feedback-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(236, 72, 153, 0.15);
}

.feedback-avatar {
    text-align: center;
    margin-bottom: 12px;
}

.feedback-avatar img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid #ffe4ec;
    object-fit: cover;
}

.feedback-content {
    text-align: center;
}

.feedback-name {
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 4px 0;
}

.feedback-service {
    font-size: 13px;
    color: var(--muted);
    margin: 0 0 8px 0;
}

.feedback-rating {
    margin-bottom: 12px;
    font-size: 18px;
}

.feedback-text {
    font-style: italic;
    color: #374151;
    line-height: 1.6;
    margin: 0;
}

.feedback-form-section {
    background: linear-gradient(135deg, #fff0f6, #fff7fb);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 48px 24px;
    margin: 32px 0;
}

.feedback-form-container {
    max-width: 600px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.feedback-form .form-row {
    margin-bottom: 18px;
}

.feedback-form label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    color: #374151;
    margin-bottom: 6px;
}

.feedback-form input,
.feedback-form textarea {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: #fff;
    font-family: inherit;
    font-size: 15px;
}

.feedback-form input:focus,
.feedback-form textarea:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.18);
    border-color: #f0b3cf;
}

.feedback-form textarea {
    resize: vertical;
    min-height: 120px;
}

.feedback-form .form-actions {
    display: flex;
    justify-content: center;
    margin-top: 24px;
}

@media (max-width: 900px) {
    .feedback-grid {
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 16px;
    }
    
    .feedback-hero {
        border-radius: 18px;
    }
    
    .feedback-form-section {
        border-radius: 18px;
        padding: 32px 16px;
    }
    
    .feedback-form-container {
        padding: 24px 18px;
    }
}

@media (max-width: 600px) {
    .feedback-grid {
        grid-template-columns: 1fr;
    }
    
    .feedback-hero div[style*="padding"] {
        padding: 32px 18px !important;
    }
    
    .feedback-hero h1 {
        font-size: 28px !important;
    }
}
</style>
