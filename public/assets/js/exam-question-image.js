(function () {
  if (window.__examQuestionImageReady) return;
  window.__examQuestionImageReady = true;

  let overlay = null;
  let overlayImg = null;

  function ensureOverlay() {
    if (overlay) return;
    overlay = document.createElement('div');
    overlay.className = 'exam-image-lightbox';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.innerHTML = [
      '<button type="button" class="exam-image-lightbox-close" aria-label="إغلاق">&times;</button>',
      '<img alt="صورة السؤال المكبرة">',
    ].join('');
    document.body.appendChild(overlay);
    overlayImg = overlay.querySelector('img');

    overlay.addEventListener('click', function (e) {
      if (e.target === overlay || e.target.classList.contains('exam-image-lightbox-close')) {
        closeLightbox();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeLightbox();
    });
  }

  function openLightbox(url) {
    ensureOverlay();
    overlayImg.src = url;
    overlay.classList.add('is-open');
    document.body.classList.add('exam-image-lightbox-open');
  }

  function closeLightbox() {
    if (!overlay) return;
    overlay.classList.remove('is-open');
    document.body.classList.remove('exam-image-lightbox-open');
    overlayImg.removeAttribute('src');
  }

  document.addEventListener('click', function (e) {
    const trigger = e.target.closest('[data-exam-image]');
    if (!trigger) return;
    e.preventDefault();
    openLightbox(trigger.getAttribute('data-exam-image'));
  });
})();
