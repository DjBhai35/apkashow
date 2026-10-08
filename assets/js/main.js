/**
 * CinemaVault - Interactive Frontend Logic & Animations
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Navbar Glass Effect on Scroll
    const navbar = document.querySelector('.glass-nav');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 40) {
                navbar.classList.add('shadow-lg');
                navbar.style.background = 'rgba(7, 9, 14, 0.98)';
            } else {
                navbar.classList.remove('shadow-lg');
                navbar.style.background = 'rgba(7, 9, 14, 0.88)';
            }
        });
    }

    // 2. Initialize Bootstrap Carousel Auto-sliding
    const heroCarousel = document.getElementById('heroCarousel');
    if (heroCarousel && typeof bootstrap !== 'undefined') {
        new bootstrap.Carousel(heroCarousel, {
            interval: 5000,
            ride: 'carousel',
            pause: 'hover',
            wrap: true
        });
    }

    // 3. Image Lazy Load & Error Fallback
    const moviePosters = document.querySelectorAll('.movie-poster-img, .hero-slide-item');
    moviePosters.forEach(img => {
        if (img.tagName === 'IMG') {
            img.onerror = function() {
                this.src = 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=600&q=80';
            };
        }
    });

    // 4. Quick Live Search Suggestions (Optional AJAX dropdown)
    const searchInput = document.getElementById('siteSearchInput');
    const searchDropdown = document.getElementById('searchSuggestBox');
    
    if (searchInput && searchDropdown) {
        let debounceTimer;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();
            if (query.length < 2) {
                searchDropdown.classList.add('d-none');
                return;
            }
            debounceTimer = setTimeout(() => {
                fetch(`${window.BASE_URL || ''}/api/search-suggest.php?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            searchDropdown.innerHTML = data.map(item => `
                                <a href="${window.BASE_URL || ''}/movie.php?slug=${item.slug}" class="list-group-item list-group-item-action bg-dark text-light border-secondary d-flex align-items-center gap-2 py-2">
                                    <img src="${item.poster}" class="rounded" style="width: 32px; height: 44px; object-fit: cover;">
                                    <div class="overflow-hidden">
                                        <div class="text-truncate fw-semibold">${item.title}</div>
                                        <small class="text-muted">${item.release_year} &bull; ${item.genre}</small>
                                    </div>
                                </a>
                            `).join('');
                            searchDropdown.classList.remove('d-none');
                        } else {
                            searchDropdown.classList.add('d-none');
                        }
                    })
                    .catch(() => {
                        searchDropdown.classList.add('d-none');
                    });
            }, 300);
        });

        // Close search suggest when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.classList.add('d-none');
            }
        });
    }

    // 5. Video Player Modal / Stream Trigger
    const streamButtons = document.querySelectorAll('[data-bs-target="#videoPlayerModal"]');
    const videoIframe = document.getElementById('modalVideoIframe');
    if (streamButtons.length > 0 && videoIframe) {
        streamButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const videoSrc = btn.getAttribute('data-video-url');
                if (videoSrc) {
                    videoIframe.src = videoSrc;
                }
            });
        });

        const modalEl = document.getElementById('videoPlayerModal');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', () => {
                videoIframe.src = '';
            });
        }
    }
});
