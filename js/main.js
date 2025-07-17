    // Navbar scroll effect
    window.addEventListener('scroll', function () {
      const navbar = document.getElementById('navbar');
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });

    // Hero slider
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const totalSlides = slides.length;

    function showSlide(n) {
      slides.forEach(slide => slide.classList.remove('active'));
      slides[n].classList.add('active');
    }

    function nextSlide() {
      currentSlide = (currentSlide + 1) % totalSlides;
      showSlide(currentSlide);
    }

    // Auto-slide every 5 seconds
    setInterval(nextSlide, 5000);

    // Testimonials slider
    let currentTestimonialIndex = 0;
    const testimonialSlides = document.querySelectorAll('.testimonial-slide');
    const dots = document.querySelectorAll('.dot');

    function showTestimonial(n) {
      testimonialSlides.forEach(slide => slide.classList.remove('active'));
      dots.forEach(dot => dot.classList.remove('active'));

      testimonialSlides[n].classList.add('active');
      dots[n].classList.add('active');
    }

    function changeTestimonial(direction) {
      currentTestimonialIndex += direction;
      if (currentTestimonialIndex >= testimonialSlides.length) {
        currentTestimonialIndex = 0;
      } else if (currentTestimonialIndex < 0) {
        currentTestimonialIndex = testimonialSlides.length - 1;
      }
      showTestimonial(currentTestimonialIndex);
    }

    function currentTestimonial(n) {
      currentTestimonialIndex = n - 1;
      showTestimonial(currentTestimonialIndex);
    }

    // Smooth scrolling for navigation links (only for anchor links)
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
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

    // Intersection Observer for animations
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.animation = 'fadeInUp 0.8s ease-out forwards';
        }
      });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.company-text, .company-image, .feature-item').forEach(el => {
      observer.observe(el);
    });
