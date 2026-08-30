/**
 * Accent Musique - Landing Page Scripts
 * - Burger Menu Toggle & Auto-close
 * - Magasin Slider / Carousel (Touch Swipe, Auto-play, Dots, Keyboard)
 * - Smooth Scrolling Offset for Sticky Navbar
 */

document.addEventListener('DOMContentLoaded', () => {
    /* -------------------------------------------------------------------------
     * 1. NAVIGATION BURGER & MOBILE MENU
     * ---------------------------------------------------------------------- */
    const burger = document.getElementById('burgerBtn');
    const navLinks = document.getElementById('navLinks');

    if (burger && navLinks) {
        burger.addEventListener('click', (e) => {
            e.stopPropagation();
            burger.classList.toggle('open');
            navLinks.classList.toggle('open');
            burger.setAttribute('aria-expanded', burger.classList.contains('open'));
        });

        // Close menu on link click
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                burger.classList.remove('open');
                navLinks.classList.remove('open');
                burger.setAttribute('aria-expanded', 'false');
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navLinks.contains(e.target) && !burger.contains(e.target)) {
                burger.classList.remove('open');
                navLinks.classList.remove('open');
                burger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* -------------------------------------------------------------------------
     * 2. MAGASIN SLIDER / CAROUSEL
     * ---------------------------------------------------------------------- */
    const sliderContainer = document.getElementById('magasinSlider');
    const sliderTrack = document.getElementById('sliderTrack');
    const btnPrev = document.getElementById('sliderPrev');
    const btnNext = document.getElementById('sliderNext');
    const dotsContainer = document.getElementById('sliderDots');

    if (sliderTrack && sliderContainer) {
        const slides = sliderTrack.querySelectorAll('.carousel-slide, .slide');
        const totalSlides = slides.length;
        let currentIndex = 0;
        let autoPlayTimer = null;
        const autoPlayInterval = 6000; // 6 seconds

        // Create pagination dots
        if (dotsContainer) {
            dotsContainer.innerHTML = '';
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('button');
                dot.classList.add('dot');
                dot.setAttribute('aria-label', `Aller à la slide ${i + 1}`);
                if (i === 0) dot.classList.add('active');
                dot.addEventListener('click', () => {
                    goToSlide(i);
                    resetAutoPlay();
                });
                dotsContainer.appendChild(dot);
            }
        }

        const updateSlider = () => {
            sliderTrack.style.transform = `translateX(-${currentIndex * 100}%)`;

            // Update dots
            if (dotsContainer) {
                const dots = dotsContainer.querySelectorAll('.dot');
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentIndex);
                });
            }
        };

        const goToSlide = (index) => {
            if (index < 0) {
                currentIndex = totalSlides - 1;
            } else if (index >= totalSlides) {
                currentIndex = 0;
            } else {
                currentIndex = index;
            }
            updateSlider();
        };

        const nextSlide = () => {
            goToSlide(currentIndex + 1);
        };

        const prevSlide = () => {
            goToSlide(currentIndex - 1);
        };

        if (btnNext) {
            btnNext.addEventListener('click', () => {
                nextSlide();
                resetAutoPlay();
            });
        }

        if (btnPrev) {
            btnPrev.addEventListener('click', () => {
                prevSlide();
                resetAutoPlay();
            });
        }

        // Auto play function
        const startAutoPlay = () => {
            stopAutoPlay();
            autoPlayTimer = setInterval(nextSlide, autoPlayInterval);
        };

        const stopAutoPlay = () => {
            if (autoPlayTimer) {
                clearInterval(autoPlayTimer);
                autoPlayTimer = null;
            }
        };

        const resetAutoPlay = () => {
            stopAutoPlay();
            startAutoPlay();
        };

        // Pause on mouse hover & focus
        sliderContainer.addEventListener('mouseenter', stopAutoPlay);
        sliderContainer.addEventListener('mouseleave', startAutoPlay);
        sliderContainer.addEventListener('focusin', stopAutoPlay);
        sliderContainer.addEventListener('focusout', startAutoPlay);

        // Keyboard navigation when hovering
        sliderContainer.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                prevSlide();
                resetAutoPlay();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
                resetAutoPlay();
            }
        });

        // Touch Swipe Support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        const minSwipeDistance = 45;

        sliderContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            stopAutoPlay();
        }, { passive: true });

        sliderContainer.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            startAutoPlay();
        }, { passive: true });

        const handleSwipe = () => {
            const distance = touchEndX - touchStartX;
            if (Math.abs(distance) > minSwipeDistance) {
                if (distance < 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
            }
        };

        // Initial setup
        updateSlider();
        startAutoPlay();
    }
});
