// Animations and scroll effects

document.addEventListener('DOMContentLoaded', function() {
    // Parallax scrolling effect
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.parallax');
        
        parallaxElements.forEach(element => {
            const speed = element.getAttribute('data-speed') || 0.5;
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
        
        // Animate elements on scroll
        animateOnScroll();
    });
    
    // Initialize animations on load
    animateOnScroll();
    
    // Add hover effects to cards
    const cards = document.querySelectorAll('.project-card, .stat-card, .service-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
            this.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.1)';
        });
    });
    
    // Text reveal animation
    const textRevealElements = document.querySelectorAll('.text-reveal');
    textRevealElements.forEach(element => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        observer.observe(element);
    });
    
    // Staggered animation for lists
    const staggerItems = document.querySelectorAll('.stagger-item');
    staggerItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
    });
    
    // Animated gradient border
    const gradientBorderElements = document.querySelectorAll('.gradient-border');
    gradientBorderElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.borderImage = 'linear-gradient(45deg, #667eea, #764ba2, #f093fb, #f5576c) 1';
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.borderImage = 'linear-gradient(45deg, #667eea, #764ba2) 1';
        });
    });
    
    // Ripple effect for buttons
    const buttons = document.querySelectorAll('.btn-ripple');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const x = e.clientX - e.target.getBoundingClientRect().left;
            const y = e.clientY - e.target.getBoundingClientRect().top;
            
            const ripple = document.createElement('span');
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Scroll progress indicator
    const progressBar = document.createElement('div');
    progressBar.className = 'scroll-progress';
    progressBar.style.position = 'fixed';
    progressBar.style.top = '0';
    progressBar.style.left = '0';
    progressBar.style.height = '3px';
    progressBar.style.background = 'linear-gradient(90deg, #667eea, #764ba2)';
    progressBar.style.width = '0%';
    progressBar.style.zIndex = '1001';
    progressBar.style.transition = 'width 0.1s ease';
    
    document.body.appendChild(progressBar);
    
    window.addEventListener('scroll', () => {
        const windowHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrolled = (window.pageYOffset / windowHeight) * 100;
        progressBar.style.width = `${scrolled}%`;
    });
    
    // Animated skill bars
    const skillBars = document.querySelectorAll('.skill-bar');
    const skillObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const skillLevel = entry.target.querySelector('.skill-level');
                const width = skillLevel.style.width;
                skillLevel.style.width = '0';
                
                setTimeout(() => {
                    skillLevel.style.transition = 'width 2s ease-out';
                    skillLevel.style.width = width;
                }, 100);
                
                skillObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    skillBars.forEach(bar => skillObserver.observe(bar));
    
    // Floating elements animation
    const floatingElements = document.querySelectorAll('.float');
    floatingElements.forEach(element => {
        const duration = element.getAttribute('data-duration') || '3s';
        const delay = element.getAttribute('data-delay') || '0s';
        
        element.style.animationDuration = duration;
        element.style.animationDelay = delay;
    });
    
    // Page transition effect
    const links = document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="mailto:"]):not([href^="tel:"])');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.href && this.href.includes(window.location.hostname)) {
                e.preventDefault();
                
                // Add page transition
                document.body.classList.add('page-transition');
                
                setTimeout(() => {
                    window.location.href = this.href;
                }, 300);
            }
        });
    });
    
    // Add CSS for page transition
    const transitionCSS = `
        .page-transition {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .ripple {
            position: absolute;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        .text-reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease;
        }
        
        .text-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }
    `;
    
    const style = document.createElement('style');
    style.textContent = transitionCSS;
    document.head.appendChild(style);
});

// Animation on scroll function
function animateOnScroll() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const elementVisible = 150;
        
        if (elementTop < window.innerHeight - elementVisible) {
            element.classList.add('active');
        }
    });
}

// Intersection Observer for lazy loading images
const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            const src = img.getAttribute('data-src');
            
            if (src) {
                img.src = src;
                img.classList.add('loaded');
                imageObserver.unobserve(img);
            }
        }
    });
}, { rootMargin: '50px 0px' });

// Initialize lazy loading for images
document.querySelectorAll('img[data-src]').forEach(img => {
    imageObserver.observe(img);
});

// 3D tilt effect for cards
document.querySelectorAll('.tilt-card').forEach(card => {
    card.addEventListener('mousemove', function(e) {
        const cardRect = this.getBoundingClientRect();
        const x = e.clientX - cardRect.left;
        const y = e.clientY - cardRect.top;
        
        const centerX = cardRect.width / 2;
        const centerY = cardRect.height / 2;
        
        const rotateX = (y - centerY) / 20;
        const rotateY = (centerX - x) / 20;
        
        this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
    });
});

// Glitch effect on hover
document.querySelectorAll('.glitch-hover').forEach(element => {
    element.addEventListener('mouseenter', function() {
        this.classList.add('glitching');
        
        setTimeout(() => {
            this.classList.remove('glitching');
        }, 500);
    });
});

// Add glitch effect CSS
const glitchCSS = `
    .glitching {
        position: relative;
        animation: glitch 0.3s linear;
    }
    
    @keyframes glitch {
        0% {
            transform: translate(0);
        }
        20% {
            transform: translate(-2px, 2px);
        }
        40% {
            transform: translate(-2px, -2px);
        }
        60% {
            transform: translate(2px, 2px);
        }
        80% {
            transform: translate(2px, -2px);
        }
        100% {
            transform: translate(0);
        }
    }
`;

const glitchStyle = document.createElement('style');
glitchStyle.textContent = glitchCSS;
document.head.appendChild(glitchStyle);

// Confetti effect for celebrations
function createConfetti() {
    const colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe'];
    const confettiContainer = document.createElement('div');
    confettiContainer.style.position = 'fixed';
    confettiContainer.style.top = '0';
    confettiContainer.style.left = '0';
    confettiContainer.style.width = '100%';
    confettiContainer.style.height = '100%';
    confettiContainer.style.pointerEvents = 'none';
    confettiContainer.style.zIndex = '9999';
    
    document.body.appendChild(confettiContainer);
    
    for (let i = 0; i < 150; i++) {
        const confetti = document.createElement('div');
        confetti.style.position = 'absolute';
        confetti.style.width = '10px';
        confetti.style.height = '10px';
        confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.borderRadius = '50%';
        confetti.style.top = '-20px';
        confetti.style.left = `${Math.random() * 100}%`;
        confetti.style.opacity = Math.random() * 0.5 + 0.5;
        confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
        
        confettiContainer.appendChild(confetti);
        
        // Animation
        const animation = confetti.animate([
            { 
                transform: `translate(0, 0) rotate(0deg)`,
                opacity: 1 
            },
            { 
                transform: `translate(${Math.random() * 200 - 100}px, ${window.innerHeight + 100}px) rotate(${Math.random() * 720}deg)`,
                opacity: 0 
            }
        ], {
            duration: Math.random() * 3000 + 2000,
            easing: 'cubic-bezier(0.215, 0.610, 0.355, 1)'
        });
        
        animation.onfinish = () => confetti.remove();
    }
    
    // Remove container after animation
    setTimeout(() => {
        confettiContainer.remove();
    }, 5000);
}

// Add confetti to form submission success
const successMessages = document.querySelectorAll('.success-message');
successMessages.forEach(message => {
    if (message.querySelector('h3').textContent.includes('Successfully')) {
        setTimeout(createConfetti, 500);
    }
});

// Custom cursor (optional)
const customCursor = document.createElement('div');
customCursor.className = 'custom-cursor';
customCursor.style.position = 'fixed';
customCursor.style.width = '20px';
customCursor.style.height = '20px';
customCursor.style.border = '2px solid #667eea';
customCursor.style.borderRadius = '50%';
customCursor.style.pointerEvents = 'none';
customCursor.style.zIndex = '9999';
customCursor.style.transition = 'transform 0.1s ease';
customCursor.style.transform = 'translate(-50%, -50%)';

const cursorDot = document.createElement('div');
cursorDot.style.position = 'fixed';
cursorDot.style.width = '6px';
cursorDot.style.height = '6px';
cursorDot.style.background = '#667eea';
cursorDot.style.borderRadius = '50%';
cursorDot.style.pointerEvents = 'none';
cursorDot.style.zIndex = '9999';
cursorDot.style.transition = 'transform 0.05s ease';
cursorDot.style.transform = 'translate(-50%, -50%)';

// Optional: Uncomment to enable custom cursor
// document.body.appendChild(customCursor);
// document.body.appendChild(cursorDot);
// 
// document.addEventListener('mousemove', (e) => {
//     customCursor.style.left = `${e.clientX}px`;
//     customCursor.style.top = `${e.clientY}px`;
//     cursorDot.style.left = `${e.clientX}px`;
//     cursorDot.style.top = `${e.clientY}px`;
// });
// 
// document.addEventListener('mousedown', () => {
//     customCursor.style.transform = 'translate(-50%, -50%) scale(0.8)';
// });
// 
// document.addEventListener('mouseup', () => {
//     customCursor.style.transform = 'translate(-50%, -50%) scale(1)';
// });