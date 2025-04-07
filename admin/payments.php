<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

check_admin();

$db = Database::getInstance()->getPDO();

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE u.username LIKE :search OR u.email LIKE :search OR p.transaction_id LIKE :search";
    $params = [':search' => "%$search%"];
}

// Lấy danh sách thanh toán
$payments = $db->prepare("SELECT p.*, u.username, u.email 
                         FROM payments p
                         JOIN users u ON p.user_id = u.id
                         $where
                         ORDER BY p.payment_date DESC");
$payments->execute($params);
$payments = $payments->fetchAll();

// Thống kê
$stats = $db->query("SELECT 
                    SUM(amount) as total_amount,
                    COUNT(*) as total_payments,
                    SUM(CASE WHEN vip_type = 1 THEN amount ELSE 0 END) as premium_total,
                    SUM(CASE WHEN vip_type = 2 THEN amount ELSE 0 END) as super_premium_total
                    FROM payments")->fetch();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-container">
    <h1>Quản lý thanh toán</h1>
    
    <div class="admin-row">
        <div class="admin-col-md-8">
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Lịch sử thanh toán</h3>
                    <form method="GET" class="form-inline">
                        <input type="text" name="search" placeholder="Tìm kiếm..." value="<?= $search ?>">
                        <button type="submit" class="btn btn-primary">Tìm</button>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Thành viên</th>
                                <th>Gói VIP</th>
                                <th>Số tiền</th>
                                <th>Ngày thanh toán</th>
                                <th>Mã giao dịch</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= $payment['id'] ?></td>
                                <td>
                                    <div><?= $payment['username'] ?></div>
                                    <small><?= $payment['email'] ?></small>
                                </td>
                                <td>
                                    <?php 
                                    $vip_types = [
                                        0 => 'Free',
                                        1 => 'Premium',
                                        2 => 'Super Premium'
                                    ];
                                    echo $vip_types[$payment['vip_type']] ?? 'Unknown';
                                    ?>
                                </td>
                                <td><?= number_format($payment['amount']) ?>đ</td>
                                <td><?= date('d/m/Y H:i', strtotime($payment['payment_date'])) ?></td>
                                <td><?= $payment['transaction_id'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="admin-col-md-4">
            <div class="admin-card">
                <h3>Thống kê doanh thu</h3>
                
                <div class="stats-summary">
                    <div class="stat-item">
                        <span class="stat-label">Tổng thanh toán:</span>
                        <span class="stat-value"><?= number_format($stats['total_amount']) ?>đ</span>
                    </div>
                    
                    <div class="stat-item">
                        <span class="stat-label">Số giao dịch:</span>
                        <span class="stat-value"><?= number_format($stats['total_payments']) ?></span>
                    </div>
                    
                    <div class="stat-item">
                        <span class="stat-label">Premium:</span>
                        <span class="stat-value"><?= number_format($stats['premium_total']) ?>đ</span>
                    </div>
                    
                    <div class="stat-item">
                        <span class="stat-label">Super Premium:</span>
                        <span class="stat-value"><?= number_format($stats['super_premium_total']) ?>đ</span>
                    </div>
                </div>
                
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Biểu đồ doanh thu
const ctx = document.getElementById('revenueChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Premium', 'Super Premium'],
        datasets: [{
            data: [<?= $stats['premium_total'] ?>, <?= $stats['super_premium_total'] ?>],
            backgroundColor: [
                '#4e73df',
                '#1cc88a'
            ],
            hoverBackgroundColor: [
                '#2e59d9',
                '#17a673'
            ]
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php
require_once __DIR__ . '/../includes/admin_footer.php';