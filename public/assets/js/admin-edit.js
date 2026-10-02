// admin-edit.js

(function () {
  'use strict';

  function togglePasswordVisibility() {
    var passwordField = document.getElementById("password");
    var icon = document.querySelector("#showPassword i");
    if (!passwordField || !icon) return;
    if (passwordField.type === "password") {
      passwordField.type = "text";
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
    } else {
      passwordField.type = "password";
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
    }
  }

  var sp = document.getElementById("showPassword");
  if (sp) sp.addEventListener("click", togglePasswordVisibility);

  // Notification handling using window.App.notificationMessage set by server
  try {
    var nm = (window.App && window.App.notificationMessage) ? window.App.notificationMessage : '';
    if (nm && nm.indexOf('✅') !== -1) {
      alert(nm);
      var redirectBase = (window.App && window.App.base) ? window.App.base : '';
      window.location.href = redirectBase + '/admin/account';
    } else if (nm) {
      alert(nm);
    }
  } catch (e) {
    console.error('admin-edit.js error', e);
  }
})();
