<footer id="contact" class="bg-neutral-900 text-white py-6 px-4 sm:px-6 md:px-12">
  <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 md:gap-8">
    <!-- Left: Copyright -->
    <div class="text-xs sm:text-sm md:text-base font-sans text-white text-center md:text-left">
      © 2005—2025 Motto® | NYC | DAL | LDN
    </div>

    <!-- Center: Social Media Icons -->
    <div class="flex items-center gap-3 sm:gap-4">
      <!-- LinkedIn -->
      <a href="https://www.linkedin.com" target="_blank" rel="noopener noreferrer" class="text-white hover:text-neutral-300 transition-colors duration-200" aria-label="LinkedIn">
        <svg width="20" height="20" class="sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
          <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
        </svg>
      </a>
      
      <!-- Instagram -->
      <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" class="text-white hover:text-neutral-300 transition-colors duration-200" aria-label="Instagram">
        <svg width="20" height="20" class="sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
          <circle cx="12" cy="12" r="3"/>
          <path d="M17.5 6.5h.01"/>
        </svg>
      </a>
      
      <!-- YouTube -->
      <a href="https://www.youtube.com" target="_blank" rel="noopener noreferrer" class="text-white hover:text-neutral-300 transition-colors duration-200" aria-label="YouTube">
        <svg width="20" height="20" class="sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="currentColor">
          <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-9.5 13.5v-9l6.5 4.5-6.5 4.5z"/>
        </svg>
      </a>
    </div>

    <!-- Right: Back to Top -->
    <div>
      <button id="back-to-top" onclick="scrollToTop()" class="text-xs sm:text-sm md:text-base font-sans text-white hover:text-neutral-300 transition-colors duration-200 flex items-center gap-1 cursor-pointer">
        {{ __('app.footer.back_to_top') }} 
        <span class="text-base sm:text-lg">↑</span>
      </button>
    </div>
  </div>
</footer>

<script>
  function scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }

  // Show/hide back-to-top button based on scroll position
  window.addEventListener('scroll', function() {
    const backToTopButton = document.getElementById('back-to-top');
    if (backToTopButton) {
      if (window.pageYOffset > 300) {
        backToTopButton.style.opacity = '1';
        backToTopButton.style.pointerEvents = 'auto';
      } else {
        backToTopButton.style.opacity = '0.7';
        backToTopButton.style.pointerEvents = 'auto';
      }
    }
  });
</script>
