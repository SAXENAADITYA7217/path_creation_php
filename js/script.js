document.addEventListener('DOMContentLoaded', function () {
  const carousel = document.getElementById('myCarousel22');
  const inner = document.querySelector('.carousel22-inner22');
  const dotsContainer = document.querySelector('.carousel22-hots22');
  const dots = Array.from(inner.children).map((_, index) => {
    const dot = document.createElement('div');
    dot.classList.add('hot22');
    dot.addEventListener('click', () => {
      goToSlide(index);
    });
    dotsContainer.appendChild(dot);
    return dot;
  });

  let currentIndex = 0;

  function updateDots() {
    dots.forEach((dot, index) => {
      dot.classList.toggle('active', index === currentIndex);
    });
  }

  function goToSlide(index) {
    currentIndex = index;
    updateDots();
    const transformValue = -100 * index + '%';
    inner.style.transform = 'translateX(' + transformValue + ')';
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % dots.length;
    updateDots();
    const transformValue = -100 * currentIndex + '%';
    inner.style.transform = 'translateX(' + transformValue + ')';
  }

  function startCarousel() {
    setInterval(nextSlide, 3000); // Change slide every 3 seconds
  }

  updateDots(); // Initial dot setup
  startCarousel(); // Start automatic carousel
});





// js card
