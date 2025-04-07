document.addEventListener('DOMContentLoaded', function() {
    // Xử lý confirm xóa
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Bạn chắc chắn muốn xóa?')) {
                e.preventDefault();
            }
        });
    });

    // Cập nhật role user
    document.querySelectorAll('.role-select').forEach(select => {
        select.addEventListener('change', function() {
            const userId = this.dataset.user;
            const newRole = this.value;
            
            fetch('/admin/update_role.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    user_id: userId,
                    new_role: newRole
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Cập nhật thành công!', 'success');
                } else {
                    showToast('Lỗi: ' + data.message, 'error');
                    this.value = this.dataset.originalValue;
                }
            });
        });
    });

    // Xử lý biểu đồ
    if (document.getElementById('revenueChart')) {
        new Chart(document.getElementById('revenueChart'), {
            type: 'doughnut',
            data: {
                labels: ['Premium', 'Super Premium'],
                datasets: [{
                    data: [
                        document.getElementById('premiumTotal').value,
                        document.getElementById('superPremiumTotal').value
                    ],
                    backgroundColor: ['#4e73df', '#1cc88a']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 3000);
    }
});