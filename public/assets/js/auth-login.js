(function () {
  document.querySelectorAll('.auth-toggle-pw[data-target]').forEach(function (toggleBtn) {
    const inputId = toggleBtn.getAttribute('data-target');
    const passwordInput = document.getElementById(inputId);
    if (!passwordInput) return;

    // One icon at a time: the eye while the password is hidden, the crossed eye while it shows
    // (auth-login.css, .auth-toggle-pw.is-showing).
    toggleBtn.addEventListener('click', function () {
      const isHidden = passwordInput.type === 'password';
      passwordInput.type = isHidden ? 'text' : 'password';
      toggleBtn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
      toggleBtn.classList.toggle('is-showing', isHidden);
    });
  });
})();
