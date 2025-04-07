document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('main-video');
    const movieId = video.dataset.movieId;
    let lastUpdate = 0;
    
    // Theo dõi thời gian xem
    video.addEventListener('timeupdate', function() {
        const currentTime = Math.floor(video.currentTime);
        
        // Gửi cập nhật mỗi 30 giây
        if (currentTime > 0 && currentTime % 30 === 0 && currentTime !== lastUpdate) {
            fetch('/api/watchtime.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    movie_id: movieId,
                    current_time: currentTime
                })
            });
            lastUpdate = currentTime;
        }
    });
    
    // Xử lý danh sách xem sau
    document.getElementById('add-to-watchlist').addEventListener('click', function() {
        fetch('/api/watchlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                movie_id: movieId,
                action: 'add'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Đã thêm vào danh sách xem sau');
            }
        });
    });
    
    // Xử lý bình luận
    document.getElementById('comment-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('movie_id', movieId);
        
        fetch('/api/comment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.reset();
                loadComments();
            }
        });
    });
    
    // Tải bình luận
    function loadComments() {
        fetch(`/api/comment.php?movie_id=${movieId}`)
            .then(response => response.json())
            .then(data => {
                const commentsList = document.getElementById('comments-list');
                commentsList.innerHTML = '';
                
                data.comments.forEach(comment => {
                    const commentElement = document.createElement('div');
                    commentElement.className = 'comment';
                    commentElement.innerHTML = `
                        <div class="comment-header">
                            <span>${comment.username}</span>
                            <span>${comment.created_at}</span>
                        </div>
                        <div class="comment-content">${comment.content}</div>
                    `;
                    commentsList.appendChild(commentElement);
                });
            });
    }
    
    // Tải bình luận ban đầu
    loadComments();
    
    // Làm mới bình luận mỗi 30 giây
    setInterval(loadComments, 30000);
});