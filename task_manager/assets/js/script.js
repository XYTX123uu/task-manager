document.addEventListener('DOMContentLoaded', function() {
    const deleteBtns = document.querySelectorAll('.btn-delete');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('确定要删除这个任务吗？')) {
                e.preventDefault(); 
            }
        });
    });
});