// Minimalist Task Interactivity
document.addEventListener('DOMContentLoaded', () => {
    // 1. Expand/Collapse Description in Add Card
    const toggleDescBtn = document.getElementById('toggle-details-btn');
    const expandableDetails = document.getElementById('expandable-details');
    
    if (toggleDescBtn && expandableDetails) {
        toggleDescBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const isOpen = expandableDetails.classList.toggle('open');
            toggleDescBtn.textContent = isOpen ? '- Sembunyikan catatan' : '+ Tambah catatan';
            if (isOpen) {
                const descInput = document.getElementById('input-desc');
                if (descInput) descInput.focus();
            }
        });
    }

    // 2. Edit Modal Logic
    const editModal = document.getElementById('edit-modal');
    const editForm = document.getElementById('edit-form');
    const editIdInput = document.getElementById('edit-id');
    const editTitleInput = document.getElementById('edit-title');
    const editDescInput = document.getElementById('edit-desc');
    const closeModalBtn = document.getElementById('btn-close-modal');
    const cancelModalBtn = document.getElementById('btn-cancel-modal');

    function openEditModal(task) {
        if (!editModal) return;
        editIdInput.value = task.id;
        editTitleInput.value = task.title;
        editDescInput.value = task.description || '';
        editModal.classList.add('open');
        editTitleInput.focus();
    }

    function closeEditModal() {
        if (!editModal) return;
        editModal.classList.remove('open');
    }

    document.querySelectorAll('.btn-edit-task').forEach(btn => {
        btn.addEventListener('click', () => {
            const taskData = {
                id: btn.dataset.id,
                title: btn.dataset.title,
                description: btn.dataset.description
            };
            openEditModal(taskData);
        });
    });

    if (closeModalBtn) closeModalBtn.addEventListener('click', closeEditModal);
    if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeEditModal);

    // Close on backdrop click
    if (editModal) {
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) {
                closeEditModal();
            }
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && editModal && editModal.classList.contains('open')) {
            closeEditModal();
        }
    });

    // 3. Auto-hide Toast after 3 seconds
    const toastNotice = document.querySelector('.toast-notice');
    if (toastNotice) {
        setTimeout(() => {
            toastNotice.style.transition = 'opacity 0.4s ease';
            toastNotice.style.opacity = '0';
            setTimeout(() => toastNotice.remove(), 400);
        }, 3200);
    }
});
