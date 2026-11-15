'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('sidebar');
  const toggle = document.getElementById('menuToggle');

  if (sidebar && toggle) {
    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('sidebar-open');
      toggle.setAttribute('aria-expanded', sidebar.classList.contains('sidebar-open') ? 'true' : 'false');
    });

    document.addEventListener('click', (event) => {
      if (window.innerWidth <= 900 && sidebar.classList.contains('sidebar-open')
          && !sidebar.contains(event.target) && !toggle.contains(event.target)) {
        sidebar.classList.remove('sidebar-open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('click', (event) => {
      if (!window.confirm(element.dataset.confirm || 'آیا مطمئن هستید؟')) {
        event.preventDefault();
      }
    });
  });
});
