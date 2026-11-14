// ============================================
// Main JavaScript File — Neumorphism Version
// ============================================

document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // 🍔 MOBILE NAVIGATION TOGGLE (FIXED)
    // ============================================
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (navToggle && navMenu) {

        // Toggle menu on hamburger click
        navToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();   // 👈 KEY FIX: prevents instant close
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });

        // Close menu when a link is clicked
        navMenu.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            });
        });

        // Close menu when clicking outside (but NOT the toggle)
        document.addEventListener('click', function(event) {
            const clickedInsideNav = event.target.closest('.nav-container');
            if (!clickedInsideNav && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            }
        });

        // Close menu on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            }
        });
    }

    // ============================================
    // Navbar scroll effect
    // ============================================
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    });

    // ============================================
    // Smooth scrolling for anchor links
    // ============================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();

            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                if (navMenu) navMenu.classList.remove('active');
                if (navToggle) navToggle.classList.remove('active');

                window.scrollTo({
                    top: targetElement.offsetTop - 70,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ============================================
    // Contact form validation
    // ============================================
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = this.querySelectorAll('input[required], textarea[required]');

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.boxShadow =
                        'inset 5px 5px 10px #b8bcc2, inset -5px -5px 10px #ffffff, 0 0 0 2px #dc3545';
                } else {
                    input.style.boxShadow =
                        'inset 5px 5px 10px #b8bcc2, inset -5px -5px 10px #ffffff, 0 0 0 2px #28a745';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    }

    // ============================================
    // Project filter functionality
    // ============================================
    const filterButtons = document.querySelectorAll('.filter-btn');
    const projectItems = document.querySelectorAll('.project-item');

    if (filterButtons.length > 0 && projectItems.length > 0) {
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                const filterValue = this.getAttribute('data-filter');

                projectItems.forEach(item => {
                    const category = item.getAttribute('data-category');

                    if (filterValue === 'all' || filterValue === category) {
                        item.style.display = 'block';
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, 100);
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(20px)';
                        setTimeout(() => {
                            item.style.display = 'none';
                        }, 300);
                    }
                });
            });
        });
    }

    // ============================================
    // Project modal functionality
    // ============================================
    const projectModal = document.querySelector('.project-modal');
    const closeModal = document.querySelector('.close-modal');

    if (projectModal && closeModal) {
        closeModal.addEventListener('click', function() {
            projectModal.style.display = 'none';
        });

        window.addEventListener('click', function(e) {
            if (e.target === projectModal) {
                projectModal.style.display = 'none';
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && projectModal.style.display === 'block') {
                projectModal.style.display = 'none';
            }
        });
    }

    // ============================================
    // Animated counter for statistics
    // ============================================
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200;

    const animateCounters = () => {
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-count');
                const count = +counter.innerText;
                const increment = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(updateCount, 1);
                } else {
                    counter.innerText = target;
                }
            };
            updateCount();
        });
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    const statsSection = document.querySelector('.about-preview');
    if (statsSection) {
        observer.observe(statsSection);
    }

    // ============================================
    // Form input animations
    // ============================================
    const formInputs = document.querySelectorAll('.form-group input, .form-group textarea');

    formInputs.forEach(input => {
        const label = input.previousElementSibling;
        if (label && label.tagName === 'LABEL') {
            input.addEventListener('focus', () => {
                label.style.color = '#4a6fa5';
            });
            input.addEventListener('blur', () => {
                if (!input.value) {
                    label.style.color = '';
                }
            });
        }

        if (input.tagName === 'TEXTAREA' && input.maxLength > 0) {
            const charCount = document.createElement('div');
            charCount.className = 'char-count';
            charCount.style.textAlign = 'right';
            charCount.style.fontSize = '12px';
            charCount.style.color = '#5a6b8a';
            charCount.style.marginTop = '5px';
            charCount.textContent = `0/${input.maxLength}`;
            input.parentNode.appendChild(charCount);

            input.addEventListener('input', () => {
                const remaining = input.maxLength - input.value.length;
                charCount.textContent = `${input.value.length}/${input.maxLength}`;
                if (remaining < 50) {
                    charCount.style.color = remaining < 20 ? '#dc3545' : '#ffc107';
                } else {
                    charCount.style.color = '#5a6b8a';
                }
            });
        }
    });

    // ============================================
    // Dark/Light mode toggle
    // ============================================
    const themeToggle = document.createElement('button');
    themeToggle.className = 'theme-toggle';
    themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    themeToggle.style.position = 'fixed';
    themeToggle.style.bottom = '20px';
    themeToggle.style.right = '20px';
    themeToggle.style.zIndex = '1000';
    themeToggle.style.width = '52px';
    themeToggle.style.height = '52px';
    themeToggle.style.borderRadius = '50%';
    themeToggle.style.background = 'var(--bg, #e0e5ec)';
    themeToggle.style.color = 'var(--accent, #4a6fa5)';
    themeToggle.style.border = 'none';
    themeToggle.style.cursor = 'pointer';
    themeToggle.style.fontSize = '18px';
    themeToggle.style.boxShadow =
        '5px 5px 10px var(--shadow-dark, #b8bcc2), -5px -5px 10px var(--shadow-light, #ffffff)';
    themeToggle.style.transition = 'all 0.2s ease';

    themeToggle.addEventListener('mouseenter', () => {
        themeToggle.style.transform = 'scale(1.05)';
    });
    themeToggle.addEventListener('mouseleave', () => {
        themeToggle.style.transform = 'scale(1)';
    });

    const currentTheme = localStorage.getItem('theme') || 'light';
    if (currentTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }

    themeToggle.addEventListener('click', () => {
        const theme = document.documentElement.getAttribute('data-theme');
        if (theme === 'dark') {
            document.documentElement.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
            themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
    });

    document.body.appendChild(themeToggle);

    // ============================================
    // Back to top button
    // ============================================
    const backToTop = document.createElement('button');
    backToTop.className = 'back-to-top';
    backToTop.innerHTML = '<i class="fas fa-chevron-up"></i>';
    backToTop.style.position = 'fixed';
    backToTop.style.bottom = '85px';
    backToTop.style.right = '20px';
    backToTop.style.zIndex = '999';
    backToTop.style.width = '52px';
    backToTop.style.height = '52px';
    backToTop.style.borderRadius = '50%';
    backToTop.style.background = 'var(--bg, #e0e5ec)';
    backToTop.style.color = 'var(--accent, #4a6fa5)';
    backToTop.style.border = 'none';
    backToTop.style.cursor = 'pointer';
    backToTop.style.fontSize = '18px';
    backToTop.style.boxShadow =
        '5px 5px 10px var(--shadow-dark, #b8bcc2), -5px -5px 10px var(--shadow-light, #ffffff)';
    backToTop.style.opacity = '0';
    backToTop.style.visibility = 'hidden';
    backToTop.style.transition = 'all 0.3s ease';

    backToTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    backToTop.addEventListener('mouseenter', () => {
        backToTop.style.transform = 'translateY(-3px)';
    });
    backToTop.addEventListener('mouseleave', () => {
        backToTop.style.transform = 'translateY(0)';
    });

    document.body.appendChild(backToTop);

    window.addEventListener('scroll', () => {
        if (window.scrollY > 500) {
            backToTop.style.opacity = '1';
            backToTop.style.visibility = 'visible';
        } else {
            backToTop.style.opacity = '0';
            backToTop.style.visibility = 'hidden';
        }
    });

    // ============================================
    // Loading animation
    // ============================================
    window.addEventListener('load', () => {
        document.body.classList.add('loaded');

        const loadingSpinner = document.querySelector('.loading-spinner');
        if (loadingSpinner) {
            setTimeout(() => {
                loadingSpinner.style.opacity = '0';
                setTimeout(() => {
                    loadingSpinner.style.display = 'none';
                }, 300);
            }, 500);
        }
    });

    // ============================================
    // Tooltip functionality
    // ============================================
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = element.getAttribute('data-tooltip');
        tooltip.style.position = 'absolute';
        tooltip.style.background = 'var(--bg, #e0e5ec)';
        tooltip.style.color = 'var(--text, #2d3e5f)';
        tooltip.style.padding = '5px 10px';
        tooltip.style.borderRadius = '6px';
        tooltip.style.fontSize = '12px';
        tooltip.style.whiteSpace = 'nowrap';
        tooltip.style.zIndex = '10000';
        tooltip.style.pointerEvents = 'none';
        tooltip.style.opacity = '0';
        tooltip.style.transition = 'opacity 0.3s ease';
        tooltip.style.transform = 'translateY(10px)';
        tooltip.style.boxShadow =
            '3px 3px 6px var(--shadow-dark, #b8bcc2), -3px -3px 6px var(--shadow-light, #ffffff)';

        document.body.appendChild(tooltip);

        element.addEventListener('mouseenter', (e) => {
            const rect = element.getBoundingClientRect();
            tooltip.style.left = `${rect.left + rect.width / 2}px`;
            tooltip.style.top = `${rect.top - 10}px`;
            tooltip.style.transform = 'translate(-50%, -100%)';
            tooltip.style.opacity = '1';
        });

        element.addEventListener('mouseleave', () => {
            tooltip.style.opacity = '0';
            tooltip.style.transform = 'translate(-50%, -90%)';
        });
    });

    // ============================================
    // Initialize particles if element exists
    // ============================================
    if (document.getElementById('particles-js')) {
        initializeParticles();
    }

    // ============================================
    // Typewriter effect
    // ============================================
    const typewriterElement = document.querySelector('.hero-subtitle .typewriter-text');
    if (typewriterElement) {
        const words = ["IT Student & Aspiring Developer", "Web Developer", "Problem Solver", "Tech Enthusiast"];
        const wait = 2500;
        new Typewriter(typewriterElement.parentElement, words, wait);
    }

});

// ============================================
// Particles initialization
// ============================================
function initializeParticles() {
    const particles = document.getElementById('particles-js');
    if (!particles) return;

    for (let i = 0; i < 40; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';

        const size = Math.random() * 5 + 2;
        const posX = Math.random() * 100;
        const posY = Math.random() * 100;
        const duration = Math.random() * 20 + 10;
        const delay = Math.random() * 5;
        const opacity = Math.random() * 0.5 + 0.1;

        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${posX}%`;
        particle.style.top = `${posY}%`;
        particle.style.opacity = opacity;
        particle.style.animationDuration = `${duration}s`;
        particle.style.animationDelay = `${delay}s`;

        particles.appendChild(particle);
    }
}

// ============================================
// Typewriter class
// ============================================
class Typewriter {
    constructor(element, words, wait = 3000) {
        this.element = element;
        this.words = words;
        this.txt = '';
        this.wordIndex = 0;
        this.wait = parseInt(wait, 10);
        this.isDeleting = false;
        this.type();
    }

    type() {
        const current = this.wordIndex % this.words.length;
        const fullTxt = this.words[current];

        if (this.isDeleting) {
            this.txt = fullTxt.substring(0, this.txt.length - 1);
        } else {
            this.txt = fullTxt.substring(0, this.txt.length + 1);
        }

        this.element.innerHTML =
            `<span class="typewriter-text">${this.txt}</span><span class="cursor">|</span>`;

        let typeSpeed = 100;
        if (this.isDeleting) typeSpeed /= 2;

        if (!this.isDeleting && this.txt === fullTxt) {
            typeSpeed = this.wait;
            this.isDeleting = true;
        } else if (this.isDeleting && this.txt === '') {
            this.isDeleting = false;
            this.wordIndex++;
            typeSpeed = 500;
        }

        setTimeout(() => this.type(), typeSpeed);
    }
}