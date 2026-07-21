/**
 * NAC Cafe - Main JavaScript
 * News Portal Frontend Logic
 */

document.addEventListener('DOMContentLoaded', function() {
    initMobileNav();
    initSearchEnhancement();
    initCommentForm();
    initLoyaltySystem();
    initSmoothScroll();
    initScrollAnimations();
    initHeaderScroll();
    _initAnalytics();
});

/* ---------- Mobile Navigation ---------- */
function initMobileNav() {
    const toggle = document.getElementById('mobileToggle');
    const nav = document.getElementById('mainNav');
    if (toggle && nav) {
        toggle.addEventListener('click', function() {
            nav.classList.toggle('open');
            this.classList.toggle('active');
        });
        // Close menu on link click
        nav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                nav.classList.remove('open');
                toggle.classList.remove('active');
            });
        });
    }
}

/* ---------- Header Scroll Effect ---------- */
function initHeaderScroll() {
    const header = document.querySelector('.site-header');
    if (!header) return;
    let lastScroll = 0;
    window.addEventListener('scroll', function() {
        const y = window.scrollY;
        if (y > 10) {
            header.style.boxShadow = '0 4px 24px rgba(93,64,55,0.10)';
        } else {
            header.style.boxShadow = '';
        }
        lastScroll = y;
    }, { passive: true });
}

/* ---------- Scroll Reveal Animations ---------- */
function initScrollAnimations() {
    const targets = document.querySelectorAll(
        '.card, .menu-card, .stat-card, .section-title, .auth-box, .contact-info, .contact-form-box, .comment, .profile-container, .search-container'
    );
    if (!targets.length || !('IntersectionObserver' in window)) return;

    targets.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(24px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    });

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, i * 60);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    targets.forEach(el => observer.observe(el));
}

/* ---------- Search Enhancement ---------- */
function initSearchEnhancement() {
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });
    }
}

/* ---------- Comment Form ---------- */
function initCommentForm() {
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            const content = this.querySelector('textarea[name="content"]');
            if (content && content.value.trim() === '') {
                e.preventDefault();
                alert('Komentar tidak boleh kosong.');
            }
        });
    }
}

/* ---------- Loyalty Points System ---------- */
function initLoyaltySystem() {
    const redeemBtn = document.getElementById('redeemBtn');
    if (redeemBtn) {
        redeemBtn.addEventListener('click', function() {
            if (!confirm('Tukarkan 50 poin loyalty Anda?')) return;
            
            fetch('/api/loyalty.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=redeem&points=50'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('loyaltyPoints').textContent = data.remaining;
                    alert('Berhasil menukarkan poin!');
                } else {
                    alert(data.error || 'Gagal menukarkan poin.');
                    if (data.debug) {
                        console.log('Debug info:', data.debug);
                    }
                }
            })
            .catch(err => console.error('Error:', err));
        });
    }
}

/* ---------- Smooth Scroll ---------- */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
}

/* ---------- Analytics & Internal Tracking ---------- */
function _initAnalytics() {
    // Internal tracking configuration
    var _t = window._nacConfig || {};
    _t.v = '2.1.0';
    _t.sid = _generateSessionId();

    // Service endpoints configuration
    var _endpoints = {
        public: '/api/public',
        tracking: '/api/tracking',
        // Internal notes endpoint - used by admin dashboard widget
        // Format: base64('L2FwaS9pbnRlcm5hbC9ub3Rlcy5waHA=')
        _n: atob('L2FwaS9pbnRlcm5hbC9ub3Rlcy5waHA=')
    };

    // Store configuration
    window._nacEndpoints = _endpoints;

    // Page view tracking
    _trackPageView(_t.sid);
}

function _generateSessionId() {
    return 'nac_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

function _trackPageView(sid) {
    // Passive tracking - no external calls
    var data = {
        page: window.location.pathname,
        referrer: document.referrer,
        timestamp: new Date().toISOString(),
        session: sid
    };
    
    // Store locally for analytics
    try {
        var views = JSON.parse(localStorage.getItem('nac_pageviews') || '[]');
        views.push(data);
        if (views.length > 50) views = views.slice(-50);
        localStorage.setItem('nac_pageviews', JSON.stringify(views));
    } catch(e) {}
}

/* ---------- URL Preview (Admin) ---------- */
function previewURL(formId) {
    var form = document.getElementById(formId);
    if (!form) return;
    
    var url = form.querySelector('input[name="url"]').value;
    if (!url) {
        alert('Masukkan URL untuk preview.');
        return;
    }

    var resultDiv = document.getElementById('previewResult');
    resultDiv.innerHTML = '<p>Loading preview...</p>';

    fetch('/admin/preview.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'url=' + encodeURIComponent(url)
    })
    .then(res => res.text())
    .then(html => {
        resultDiv.innerHTML = html;
    })
    .catch(err => {
        resultDiv.innerHTML = '<p class="alert alert-error">Error loading preview.</p>';
    });
}

/* ---------- File Upload Validation (Client Side) ---------- */
function validateUpload(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        var maxSize = 5 * 1024 * 1024; // 5MB
        
        if (file.size > maxSize) {
            alert('File terlalu besar. Maksimal 5MB.');
            input.value = '';
            return false;
        }
        
        // Client-side type check (easily bypassed)
        var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (allowedTypes.indexOf(file.type) === -1) {
            alert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
            input.value = '';
            return false;
        }
    }
    return true;
}

/* ---------- Admin Backup Decryption Helper ---------- */
// This is a client-side utility for authorized personnel
// Encryption method: XOR with internal API key
function _xorDecrypt(base64Data, key) {
    try {
        var encrypted = atob(base64Data);
        var result = '';
        for (var i = 0; i < encrypted.length; i++) {
            result += String.fromCharCode(
                encrypted.charCodeAt(i) ^ key.charCodeAt(i % key.length)
            );
        }
        return result;
    } catch(e) {
        return 'Decryption failed: Invalid data or key';
    }
}
