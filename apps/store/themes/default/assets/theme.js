document.addEventListener('DOMContentLoaded', function () {
  // Mobile menu toggle
  const menuToggle = document.querySelector('.mobile-menu-toggle');
  const menuDrawer = document.querySelector('.mobile-menu-drawer');
  const menuClose = document.querySelector('.mobile-menu-close');

  if (menuToggle && menuDrawer && menuClose) {
    menuToggle.addEventListener('click', function () {
      menuDrawer.setAttribute('aria-hidden', 'false');
      menuDrawer.style.display = 'block';
    });

    menuClose.addEventListener('click', function () {
      menuDrawer.setAttribute('aria-hidden', 'true');
      menuDrawer.style.display = 'none';
    });
  }

  // Sticky header
  const header = document.querySelector('.site-header.sticky');
  if (header) {
    const stickyOffset = header.offsetTop;
    window.addEventListener('scroll', function () {
      if (window.pageYOffset > stickyOffset) {
        header.classList.add('is-sticky');
      } else {
        header.classList.remove('is-sticky');
      }
    });
  }

  // Search button toggle
  const searchButton = document.querySelector('.search-button');
  const searchInput = document.getElementById('site-search');
  if (searchButton && searchInput) {
    searchButton.addEventListener('click', function () {
      searchInput.focus();
    });
  }
});
