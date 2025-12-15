/**
 * Bootstrap Carousel Initialization
 * Configures carousel with auto-rotation and proper settings
 */
document.addEventListener('DOMContentLoaded', function() {
    // Find all Bootstrap carousels
    const carousels = document.querySelectorAll('.carousel');
    
    carousels.forEach(carousel => {
        // Initialize Bootstrap carousel if it exists
        if (typeof bootstrap !== 'undefined' && bootstrap.Carousel) {
            new bootstrap.Carousel(carousel, {
                interval: 4000,
                ride: 'carousel',
                pause: 'hover',
                wrap: true
            });
        }
    });

    // Custom carousel implementation for the homepage categories carousel
    const customCarousel = document.getElementById('carouselTrack');
    if (customCarousel && !customCarousel.classList.contains('carousel')) {
        initializeCustomCarousel();
    }
});

function initializeCustomCarousel() {
    const carouselTrack = document.getElementById('carouselTrack');
    const carouselPrev = document.getElementById('carouselPrev');
    const carouselNext = document.getElementById('carouselNext');
    
    if (!carouselTrack) return;
    
    let currentSlide = 0;
    let isTransitioning = false;
    const autoPlayInterval = 4000;
    let autoPlayTimer = null;
    
    const categoryCards = carouselTrack.querySelectorAll('.category-card');
    const totalSlides = categoryCards.length;
    
    // Configure for infinite scroll with wrapping
    function updateCarousel() {
        if (isTransitioning) return;
        
        isTransitioning = true;
        
        // Calculate slide width dynamically
        const cardWidth = categoryCards[0]?.offsetWidth || 320;
        const gap = 32; // 2rem gap between cards
        const slideWidth = cardWidth + gap;
        
        // Get container width to determine how many slides to show
        const containerWidth = carouselTrack.parentElement.offsetWidth;
        const slidesToShow = Math.floor(containerWidth / slideWidth);
        
        // Ensure we don't go beyond bounds
        const maxSlide = Math.max(0, totalSlides - slidesToShow);
        
        // Wrap around for infinite effect
        if (currentSlide > maxSlide) {
            currentSlide = 0;
        } else if (currentSlide < 0) {
            currentSlide = maxSlide;
        }
        
        const translateX = -currentSlide * slideWidth;
        carouselTrack.style.transform = `translateX(${translateX}px)`;
        
        setTimeout(() => {
            isTransitioning = false;
        }, 500);
    }
    
    function nextSlide() {
        currentSlide++;
        updateCarousel();
    }
    
    function prevSlide() {
        currentSlide--;
        updateCarousel();
    }
    
    function startAutoPlay() {
        if (autoPlayTimer) clearInterval(autoPlayTimer);
        autoPlayTimer = setInterval(nextSlide, autoPlayInterval);
    }
    
    function pauseAutoPlay() {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
        }
    }
    
    // Event listeners
    if (carouselNext) {
        carouselNext.addEventListener('click', nextSlide);
    }
    
    if (carouselPrev) {
        carouselPrev.addEventListener('click', prevSlide);
    }
    
    // Pause on hover
    carouselTrack.addEventListener('mouseenter', pauseAutoPlay);
    carouselTrack.addEventListener('mouseleave', startAutoPlay);
    
    // Handle window resize
    window.addEventListener('resize', updateCarousel);
    
    // Initial setup
    updateCarousel();
    startAutoPlay();
    
    // Ensure clickable links work
    categoryCards.forEach(card => {
        card.style.pointerEvents = 'auto';
        card.style.cursor = 'pointer';
    });
}