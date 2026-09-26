// Finance ERP — LMS JavaScript
'use strict';

document.addEventListener('DOMContentLoaded', function () {

    // ── Sidebar Toggle ────────────────────────────────────────────────
    const sidebar        = document.getElementById('sidebar');
    const sidebarToggle  = document.getElementById('sidebarToggle');
    const sidebarClose   = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar?.classList.add('open');
        sidebarOverlay?.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar?.classList.remove('open');
        sidebarOverlay?.classList.remove('open');
        document.body.style.overflow = '';
    }

    sidebarToggle?.addEventListener('click', openSidebar);
    sidebarClose?.addEventListener('click', closeSidebar);
    sidebarOverlay?.addEventListener('click', closeSidebar);

    // Close on escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeSidebar();
    });

    // ── Auto-dismiss alerts ───────────────────────────────────────────
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert?.close();
        }, 5000);
    });

    // ── Confirm delete buttons ────────────────────────────────────────
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const message = this.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // ── CSRF token for all AJAX requests ─────────────────────────────
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    window.lmsAjax = function(url, method = 'POST', data = {}) {
        return fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: method !== 'GET' ? JSON.stringify(data) : undefined,
        }).then(r => r.json());
    };

    // ── Number formatter (Indian format) ─────────────────────────────
    window.formatINR = function(amount) {
        if (amount === null || amount === undefined) return '₹0.00';
        return '₹' + parseFloat(amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    // ── Table row link clicks ─────────────────────────────────────────
    document.querySelectorAll('tr[data-href]').forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(e) {
            if (!e.target.closest('a, button, .btn, .btn-icon')) {
                window.location.href = this.dataset.href;
            }
        });
    });

    // ── Tooltip init ──────────────────────────────────────────────────
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el, { trigger: 'hover' });
    });

    // ── Animate stat cards ────────────────────────────────────────────
    function animateCounter(el) {
        const raw    = el.getAttribute('data-count');
        const target = parseFloat(raw?.replace(/[^0-9.]/g, '') || 0);
        const prefix = raw?.startsWith('₹') ? '₹' : '';
        const isCurrency = prefix === '₹';
        const duration = 1200;
        const start    = Date.now();

        function tick() {
            const progress = Math.min((Date.now() - start) / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3);
            const current = target * ease;

            el.textContent = isCurrency
                ? formatINR(current)
                : Math.round(current).toLocaleString('en-IN');

            if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    document.querySelectorAll('.stat-value[data-count]').forEach(el => {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(el);
                    observer.disconnect();
                }
            });
        });
        observer.observe(el);
    });

});
