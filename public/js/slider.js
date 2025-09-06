document.addEventListener("DOMContentLoaded", function () {
    const navbar = document.querySelector(".custom_nav-container");
    window.addEventListener("scroll", function () {
      if (window.scrollY > 50) {
        navbar.classList.add("scrolled-navbar");
      } else {
        navbar.classList.remove("scrolled-navbar");
      }
    });
});


//Manual Slide
document.querySelector('.carousel-control-next').addEventListener('click', () => {
  const hero = document.querySelector('.hero_area');
  const currentBg = getComputedStyle(hero).getPropertyValue('--hero-bg').trim();

  if (currentBg === '#111111') {
    hero.style.setProperty('--hero-bg', '#bebdbd');
    hero.style.setProperty('--hero-before-bg', '#111111');
    hero.style.setProperty('--read-more', '#000000');
  } else {
    hero.style.setProperty('--hero-bg', '#111111');          // Original background
    hero.style.setProperty('--hero-before-bg', '##bebdbd');   // Original ::before background
    hero.style.setProperty('--read-more', '#ffffff');
  }
});

document.querySelector('.carousel-control-prev').addEventListener('click', () => {
  const hero = document.querySelector('.hero_area');
  const currentBg = getComputedStyle(hero).getPropertyValue('--hero-bg').trim();

  if (currentBg === '#111111') {
    hero.style.setProperty('--hero-bg', '#bebdbd');
    hero.style.setProperty('--hero-before-bg', '#111111');
    hero.style.setProperty('--read-more', '#000000');
  } else {
    hero.style.setProperty('--hero-bg', '#111111');          // Original background
    hero.style.setProperty('--hero-before-bg', '#bebdbd');   // Original ::before background
    hero.style.setProperty('--read-more', '#ffffff');
  }
});

//Auto Slide
document.addEventListener('DOMContentLoaded', function () {
  const carousel = document.querySelector('#carouselExampleIndicators');
  const hero = document.querySelector('.hero_area');

  function updateBackground() {
      const activeSlide = carousel.querySelector('.carousel-item.active');
      const newBg = activeSlide.getAttribute('data-bg');
      const newRBg = activeSlide.getAttribute('data-bg-right');
      const newMainText = activeSlide.getAttribute('data-main-text');
      const newSliderPage = activeSlide.getAttribute('data-slider-page');
      const newReadMore = activeSlide.getAttribute('data-read-more');

      // Update CSS variables (or inline style)
      if (newBg) {
        hero.style.setProperty('--hero-bg', newBg);
      }

      if(newRBg) {
        hero.style.setProperty('--hero-before-bg', newRBg);
      }

      if (newMainText) {
        hero.style.setProperty('--main-text', newMainText);
      }

      if (newReadMore) {
        hero.style.setProperty('--read-more', newReadMore);
      }
  }

  // Listen to Bootstrap's built-in carousel slide event
  $('#carouselExampleIndicators').on('slid.bs.carousel', function () {
      updateBackground();
  });

  // Trigger once on load
  updateBackground();
  });


window.addEventListener('scroll', function () {
  const header = document.querySelector('.header_section');
  if (window.scrollY > 50) {
    header.classList.add('scrolled');
  } else {
    header.classList.remove('scrolled');
  }
});