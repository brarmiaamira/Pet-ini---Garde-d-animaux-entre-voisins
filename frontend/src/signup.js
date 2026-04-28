document.addEventListener("DOMContentLoaded", function () {

  const signupForm = document.getElementById("signup-form");

  if (signupForm) {
    const errorMsg = document.getElementById("error-msg");

    signupForm.addEventListener("submit", function (e) {
      const password = document.getElementById("password").value.trim();
      const confirm = document.getElementById("password2").value.trim();

      // reset message
      errorMsg.textContent = "";
      errorMsg.style.display = "none";

      // longueur
      if (password.length < 8) {
        e.preventDefault();
        errorMsg.textContent = "Le mot de passe doit contenir au moins 8 caractères";
        errorMsg.style.display = "block";
        return;
      }

      // correspondance
      if (password !== confirm) {
        e.preventDefault();
        errorMsg.textContent = "Les mots de passe ne correspondent pas";
        errorMsg.style.display = "block";
        return;
      }
    });
  }

  // PASSWORD TOGGLE (signup page)
  window.togglePassword = function (inputId, icon) {
    const input = document.getElementById(inputId);

    if (input.type === "password") {
      input.type = "text";
      icon.textContent = "🐱";
    } else {
      input.type = "password";
      icon.textContent = "🙀";
    }
  };

});