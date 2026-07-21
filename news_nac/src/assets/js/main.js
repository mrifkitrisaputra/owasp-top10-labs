/**
 * Nac News Portal - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {

    // Mobile Navigation Toggle
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
            }
        });
    }

    // Comment Form Submission
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('/comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Comment submitted successfully! It will appear after moderation.', 'success');
                    commentForm.reset();
                } else {
                    showAlert(data.error || 'Failed to submit comment.', 'error');
                }
            })
            .catch(err => {
                showAlert('An error occurred. Please try again.', 'error');
            });
        });
    }

    // Search Autocomplete (simple debounce)
    const searchInput = document.querySelector('.search-form input');
    if (searchInput) {
        let timeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                // Could add autocomplete suggestions here
            }, 300);
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Reading progress bar for articles
    const articleContent = document.querySelector('.article-content');
    if (articleContent) {
        const progressBar = document.createElement('div');
        progressBar.style.cssText = 'position:fixed;top:0;left:0;height:3px;background:var(--primary,#c62828);z-index:9999;transition:width 0.1s;width:0';
        document.body.prepend(progressBar);

        window.addEventListener('scroll', function() {
            const rect = articleContent.getBoundingClientRect();
            const total = articleContent.offsetHeight;
            const current = Math.max(0, -rect.top);
            const progress = Math.min(100, (current / total) * 100);
            progressBar.style.width = progress + '%';
        });
    }

    // Lazy load images
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(function(img) {
            imageObserver.observe(img);
        });
    }

    // Increment article view count via AJAX
    const articleId = document.querySelector('[data-article-id]');
    if (articleId) {
        // View count already handled server-side
    }

    // File upload preview
    const fileInput = document.getElementById('fileUpload');
    const previewArea = document.getElementById('uploadPreview');
    if (fileInput && previewArea) {
        fileInput.addEventListener('change', function() {
            previewArea.innerHTML = '';
            if (this.files[0]) {
                const file = this.files[0];
                const info = document.createElement('p');
                info.textContent = `Selected: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                previewArea.appendChild(info);

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.style.maxWidth = '200px';
                    img.style.marginTop = '10px';
                    img.style.borderRadius = '8px';
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                    previewArea.appendChild(img);
                }
            }
        });
    }

    // Newsletter form
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            showAlert('Thank you for subscribing to our newsletter!', 'success');
            this.reset();
        });
    }
});

/**
 * Show alert message
 */
function showAlert(message, type) {
    const existing = document.querySelector('.js-alert');
    if (existing) existing.remove();

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} js-alert`;
    alert.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
    
    const main = document.querySelector('.main-content .container') || document.querySelector('.main-content');
    if (main) {
        main.prepend(alert);
        setTimeout(() => alert.remove(), 5000);
    }
}

/**
 * Confirm action
 */
function confirmAction(message) {
    return confirm(message || 'Are you sure?');
}
