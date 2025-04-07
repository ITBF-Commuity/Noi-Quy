<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/auth.php';

check_login();

$db = Database::getInstance()->getPDO();
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Gói VIP
$vip_packages = [
    1 => [
        'name' => 'Premium',
        'price' => 100000,
        'duration' => 30, // ngày
        'features' => [
            'Xem phim VIP 1',
            'Không quảng cáo',
            'Chất lượng Full HD'
        ]
    ],
    2 => [
        'name' => 'Super Premium',
        'price' => 200000,
        'duration' => 60, // ngày
        'features' => [
            'Xem tất cả phim',
            'Không quảng cáo',
            'Chất lượng 4K',
            'Tải xuống phim'
        ]
    ]
];

// Xử lý nâng cấp VIP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upgrade_vip'])) {
    $package_id = (int)$_POST['package_id'];
    
    if (!array_key_exists($package_id, $vip_packages)) {
        $error = 'Gói VIP không hợp lệ';
    } else {
        try {
            $package = $vip_packages[$package_id];
            $expiry_date = date('Y-m-d', strtotime("+{$package['duration']} days"));
            
            $db->beginTransaction();
            
            // Ghi nhận thanh toán (mô phỏng)
            $stmt = $db->prepare("INSERT INTO payments (user_id, amount, vip_type, transaction_id) 
                                VALUES (?, ?, ?, ?)");
            $transaction_id = 'TX' . time() . rand(100, 999);
            $stmt->execute([
                $user_id,
                $package['price'],
                $package_id,
                $transaction_id
            ]);
            
            // Cập nhật VIP cho user
            $db->prepare("UPDATE users SET vip_level = ?, vip_expiry = ? WHERE id = ?")
               ->execute([$package_id, $expiry_date, $user_id]);
            
            $db->commit();
            
            // Cập nhật session
            $_SESSION['vip_level'] = $package_id;
            $success = "Nâng cấp VIP {$package['name']} thành công! Hạn đến ngày " . date('d/m/Y', strtotime($expiry_date));
        } catch (PDOException $e) {
            $db->rollBack();
            $error = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}

// Lấy thông tin VIP hiện tại
$user_vip = $db->prepare("SELECT vip_level, vip_expiry FROM users WHERE id = ?");
$user_vip->execute([$user_id]);
$user_vip = $user_vip->fetch();

require __DIR__ . '/includes/header.php';
?>

<div class="vip-container">
    <h1>Nâng cấp tài khoản VIP</h1>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($user_vip['vip_level'] > 0): ?>
        <div class="current-vip">
            <h3>Tài khoản hiện tại: <span class="vip-badge vip-<?= $user_vip['vip_level'] ?>">
                VIP <?= $user_vip['vip_level'] ?> - <?= $vip_packages[$user_vip['vip_level']]['name'] ?>
            </span></h3>
            <p>Hạn sử dụng đến: <?= date('d/m/Y', strtotime($user_vip['vip_expiry'])) ?></p>
        </div>
    <?php else: ?>
        <div class="current-vip">
            <h3>Tài khoản hiện tại: <span class="vip-badge vip-0">Free</span></h3>
            <p>Bạn đang sử dụng tài khoản miễn phí với nhiều hạn chế</p>
        </div>
    <?php endif; ?>
    
    <div class="vip-packages">
        <?php foreach ($vip_packages as $id => $package): ?>
        <div class="vip-package">
            <div class="package-header">
                <h3><?= $package['name'] ?></h3>
                <div class="package-price"><?= number_format($package['price']) ?>đ</div>
                <small><?= $package['duration'] ?> ngày</small>
            </div>
            
            <div class="package-features">
                <ul>
                    <?php foreach ($package['features'] as $feature): ?>
                    <li><i class="fas fa-check"></i> <?= $feature ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <form method="POST">
                <input type="hidden" name="package_id" value="<?= $id ?>">
                <button type="submit" name="upgrade_vip" class="btn btn-vip">
                    <?= ($user_vip['vip_level'] >= $id) ? 'Gia hạn' : 'Nâng cấp ngay' ?>
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="vip-info">
        <h3>Lưu ý:</h3>
        <ul>
            <li>Thanh toán một lần, sử dụng đến hết hạn</li>
            <li>Có thể gia hạn bất cứ lúc nào</li>
            <li>Hỗ trợ nhiều phương thức thanh toán</li>
            <li>Hoàn tiền trong vòng 7 ngày nếu không hài lòng</li>
        </ul>
    </div>
</div>

<?php
require __DIR__ . '/includes/footer.php';