document.addEventListener("DOMContentLoaded", function () {

  const loginError = document.getElementById("error-msg");

  if (loginError) {
    const params = new URLSearchParams(window.location.search);

    if (params.get("error") === "1") {
      loginError.textContent = "Email ou mot de passe incorrect";
      loginError.style.display = "block";
    }
  }

  // PASSWORD TOGGLE (login page)
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