(function () {
  document.querySelectorAll('.auth-toggle-pw[data-target]').forEach(function (toggleBtn) {
    const inputId = toggleBtn.getAttribute('data-target');
    const passwordInput = document.getElementById(inputId);
    if (!passwordInput) return;

    const eyeOpen = toggleBtn.querySelector('.icon-eye');
    const eyeClosed = toggleBtn.querySelector('.icon-eye-off');

    toggleBtn.addEventListener('click', function () {
      const isHidden = passwordInput.type === 'password';
      passwordInput.type = isHidden ? 'text' : 'password';
      toggleBtn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
      if (eyeOpen) eyeOpen.hidden = !isHidden;
      if (eyeClosed) eyeClosed.hidden = isHidden;
    });
  });
})();
