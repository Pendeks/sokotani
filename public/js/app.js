// Dropdown functionality
        function toggleDropdown() {
            const dropdown = document.getElementById('categoryDropdown');
            dropdown.classList.toggle('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdownContainer = document.querySelector('.category-dropdown-container');
            const dropdown = document.getElementById('categoryDropdown');

            if (!dropdownContainer.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });

        // Category dropdown items click handler
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const category = this.getAttribute('href').substring(1);
                const categoryName = this.querySelector('.dropdown-item-text').textContent;
                console.log('Selected category:', categoryName);

                // Close dropdown
                document.getElementById('categoryDropdown').classList.remove('active');

                // Here you would typically filter products or navigate to category page
                alert(`Navigating to ${categoryName} category`);
            });
        });

        // Hero carousel functionality
        const indicators = document.querySelectorAll('.indicator');
        let currentSlide = 0;

        function updateSlide() {
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === currentSlide);
            });
        }

        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                currentSlide = index;
                updateSlide();
            });
        });

        // Auto-advance slides
        setInterval(() => {
            currentSlide = (currentSlide + 1) % indicators.length;
            updateSlide();
        }, 5000);

        // Product navigation
        const navArrows = document.querySelectorAll('.nav-arrow');
        navArrows.forEach(arrow => {
            arrow.addEventListener('click', () => {
                // Add scroll functionality here
                console.log('Navigation clicked');
            });
        });

        // Search functionality
        const searchInput = document.querySelector('.search-input');
        const searchBtn = document.querySelector('.search-btn');

        searchBtn.addEventListener('click', () => {
            const searchTerm = searchInput.value;
            if (searchTerm) {
                console.log('Searching for:', searchTerm);
                // Add search functionality here
            }
        });

        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                searchBtn.click();
            }
        });

        // Category dropdown
        const categoryDropdown = document.querySelector('.category-dropdown');
        // Dropdown functionality is now handled by toggleDropdown() function above

        // Product card interactions
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            card.addEventListener('click', () => {
                console.log('Product clicked');
                // Add product detail functionality here
            });
        });

        // Category card interactions
        const categoryCards = document.querySelectorAll('.category-card');
        categoryCards.forEach(card => {
            card.addEventListener('click', () => {
                console.log('Category clicked');
                // Add category navigation functionality here
            });
        });

        // Hero section script
        document.addEventListener('DOMContentLoaded', function() {
            const backgrounds = [
                "/images/hero.png",
                "https://images.unsplash.com/photo-1464983953574-0892a716854b?w=800&h=600&fit=crop",
                "https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=800&h=600&fit=crop"
            ];
            const indicators = document.querySelectorAll('.hero-indicators .indicator');
            const heroSection = document.querySelector('.hero');
            let current = 0;
            let intervalId;

            function showSlide(idx) {
                heroSection.style.backgroundImage = `url('${backgrounds[idx]}')`;
                indicators.forEach((dot, i) => {
                    dot.classList.toggle('active', i === idx);
                });
                current = idx;
            }

            function nextSlide() {
                showSlide((current + 1) % backgrounds.length);
            }

            indicators.forEach((dot, i) => {
                dot.addEventListener('click', () => {
                    showSlide(i);
                    resetInterval();
                });
            });

            function resetInterval() {
                clearInterval(intervalId);
                intervalId = setInterval(nextSlide, 5000);
            }

            showSlide(0);
            intervalId = setInterval(nextSlide, 5000);
        });
