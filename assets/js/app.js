/**
 * RZDK Store - Main JavaScript
 * 
 * Features:
 * - Dark/Light mode persistence (localStorage)
 * - CSRF token helper
 * - Alert auto-dismiss
 * - Copy to clipboard
 * - Form utilities
 * - Countdown timer
 */

(function() {
    'use strict';

    // ============================================================
    // THEME MANAGEMENT
    // ============================================================
    
    const ThemeManager = {
        STORAGE_KEY: 'theme',
        
        init() {
            // Set initial theme based on localStorage (default: dark)
            const saved = localStorage.getItem(this.STORAGE_KEY);
            if (!saved) {
                localStorage.setItem(this.STORAGE_KEY, 'dark');
            }
        },

        isDark() {
            return localStorage.getItem(this.STORAGE_KEY) !== 'light';
        },

        toggle() {
            const newTheme = this.isDark() ? 'light' : 'dark';
            localStorage.setItem(this.STORAGE_KEY, newTheme);
            document.documentElement.classList.toggle('dark', newTheme === 'dark');
        }
    };

    // ============================================================
    // ALERT AUTO-DISMISS
    // ============================================================

    const AlertManager = {
        init() {
            // Auto-dismiss flash alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('[data-auto-dismiss]');
                alerts.forEach(alert => {
                    alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-8px)';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 5000);
        }
    };

    // ============================================================
    // CLIPBOARD
    // ============================================================

    const Clipboard = {
        /**
         * Copy text to clipboard and show feedback
         */
        async copy(text, feedbackEl = null) {
            try {
                await navigator.clipboard.writeText(text);
                if (feedbackEl) {
                    const original = feedbackEl.textContent;
                    feedbackEl.textContent = 'Copied!';
                    feedbackEl.classList.add('text-primary');
                    setTimeout(() => {
                        feedbackEl.textContent = original;
                        feedbackEl.classList.remove('text-primary');
                    }, 2000);
                }
                return true;
            } catch (err) {
                // Fallback for older browsers
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                return true;
            }
        }
    };

    // ============================================================
    // FORM UTILITIES
    // ============================================================

    const FormUtils = {
        /**
         * Prevent double form submission
         */
        initSubmitProtection() {
            document.querySelectorAll('form[data-protect-submit]').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const btn = form.querySelector('[type="submit"]');
                    if (btn && btn.dataset.submitting === 'true') {
                        e.preventDefault();
                        return false;
                    }
                    if (btn) {
                        btn.dataset.submitting = 'true';
                        btn.disabled = true;
                        const originalText = btn.innerHTML;
                        btn.innerHTML = '<svg class="animate-spin h-4 w-4 inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Memproses...';
                        
                        // Re-enable after 10 seconds (in case of error)
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                            btn.dataset.submitting = 'false';
                        }, 10000);
                    }
                });
            });
        },

        /**
         * Toggle password visibility
         */
        initPasswordToggle() {
            document.querySelectorAll('[data-toggle-password]').forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetId = btn.dataset.togglePassword;
                    const input = document.getElementById(targetId);
                    if (input) {
                        const isPassword = input.type === 'password';
                        input.type = isPassword ? 'text' : 'password';
                        // Toggle icon
                        const showIcon = btn.querySelector('.icon-show');
                        const hideIcon = btn.querySelector('.icon-hide');
                        if (showIcon) showIcon.style.display = isPassword ? 'none' : 'block';
                        if (hideIcon) hideIcon.style.display = isPassword ? 'block' : 'none';
                    }
                });
            });
        }
    };

    // ============================================================
    // COUNTDOWN TIMER
    // ============================================================

    const Countdown = {
        /**
         * Initialize countdown timers on page
         * Elements with data-countdown="YYYY-MM-DD HH:MM:SS" attribute
         */
        init() {
            const elements = document.querySelectorAll('[data-countdown]');
            if (elements.length === 0) return;

            const update = () => {
                elements.forEach(el => {
                    const target = new Date(el.dataset.countdown).getTime();
                    const now = new Date().getTime();
                    const diff = target - now;

                    if (diff <= 0) {
                        el.textContent = 'Expired';
                        el.classList.add('text-red-500');
                        return;
                    }

                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    el.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                });
            };

            update();
            setInterval(update, 1000);
        }
    };

    // ============================================================
    // FORMAT HELPERS
    // ============================================================

    const Format = {
        /**
         * Format number as Indonesian Rupiah
         */
        rupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        },

        /**
         * Format number input in real-time (add thousand separators)
         */
        initPriceInputs() {
            document.querySelectorAll('[data-format-price]').forEach(input => {
                input.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value) {
                        e.target.value = new Intl.NumberFormat('id-ID').format(parseInt(value));
                    }
                });
            });
        }
    };

    // ============================================================
    // CONFIRMATION DIALOG
    // ============================================================

    const Confirm = {
        /**
         * Initialize confirmation dialogs for delete/destructive actions
         */
        init() {
            document.querySelectorAll('[data-confirm]').forEach(el => {
                el.addEventListener('click', function(e) {
                    const message = el.dataset.confirm || 'Apakah Anda yakin?';
                    if (!confirm(message)) {
                        e.preventDefault();
                        return false;
                    }
                });
            });
        }
    };

    // ============================================================
    // INITIALIZE
    // ============================================================

    document.addEventListener('DOMContentLoaded', function() {
        ThemeManager.init();
        AlertManager.init();
        FormUtils.initSubmitProtection();
        FormUtils.initPasswordToggle();
        Countdown.init();
        Format.initPriceInputs();
        Confirm.init();
    });

    // Expose utilities to global scope for inline usage
    window.RZDKStore = {
        Theme: ThemeManager,
        Clipboard: Clipboard,
        Format: Format,
        Countdown: Countdown,
    };

})();
