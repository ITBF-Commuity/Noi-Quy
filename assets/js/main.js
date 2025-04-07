// Real-time Updates
document.addEventListener('DOMContentLoaded', () => {
    // Update Watch Time
    const video = document.getElementById('main-video');
    let lastSavedTime = 0;
    
    video.addEventListener('timeupdate', () => {
        if (Math.abs(video.currentTime - lastSavedTime) > 60) {
            fetch('api/update_watchtime.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    movieId: video.dataset.movieId,
                    currentTime: video.currentTime
                })
            });
            lastSavedTime = video.currentTime;
        }
    });

    // Comment System
    document.getElementById('comment-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        const response = await fetch('api/post_comment.php', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            e.target.reset();
        }
    });
});