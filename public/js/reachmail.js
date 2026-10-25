/**
 * ReachMail Core Helper & Toast Notification System
 */
window.ReachMail = {
    toast: function(message, type = 'success') {
        const container = document.getElementById('rm-toast-container');
        if (!container) return;

        const toastEl = document.createElement('div');
        const toastClass = type === 'danger' || type === 'error' ? 'rm-toast-error' : 'rm-toast-success';
        toastEl.className = `toast ${toastClass} align-items-center mb-2`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        toastEl.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="toast-body d-flex align-items-center gap-2 py-2 px-3 fw-medium" style="font-size: 0.88rem;">
                    <i class="bi ${type === 'danger' || type === 'error' ? 'bi-exclamation-octagon' : 'bi-check2-circle'} fs-5 text-white"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        container.appendChild(toastEl);
        const bsToast = new bootstrap.Toast(toastEl, { delay: 4000 });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    },

    confirmAction: function(formSelector, message = 'Are you sure you want to proceed with this action?') {
        if (confirm(message)) {
            document.querySelector(formSelector).submit();
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Mobile sidebar toggle handler
    const sidebarToggle = document.getElementById('rm-sidebar-toggle');
    const sidebar = document.querySelector('.rm-sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
    }
});
