document.addEventListener("DOMContentLoaded", function () {

  // =========================
  // SIGNUP VALIDATION
  // =========================
  const signupForm = document.getElementById("signup-form");

  if (signupForm) {
    const errorMsg = document.getElementById("error-msg");

    signupForm.addEventListener("submit", function (e) {
      const password = document.getElementById("password").value.trim();
      const confirm = document.getElementById("password2").value.trim();

      // reset message
      errorMsg.textContent = "";
      errorMsg.style.display = "none";

      // check password length
      if (password.length < 8) {
        e.preventDefault();
        errorMsg.textContent = "Le mot de passe doit contenir au moins 8 caractères ";
        errorMsg.style.display = "block";
        return;
      }

      // check match
      if (password !== confirm) {
        e.preventDefault();
        errorMsg.textContent = "Les mots de passe ne correspondent pas";
        errorMsg.style.display = "block";
        return;
      }
    });
  }

  // =========================
  // LOGIN ERROR DISPLAY
  // =========================
  const loginError = document.getElementById("error-msg");

  if (loginError) {
    const params = new URLSearchParams(window.location.search);

    if (params.get("error") === "1") {
      loginError.textContent = "Email ou mot de passe incorrect";
      loginError.style.display = "block";
    }
  }

});


// =========================
// PASSWORD TOGGLE (🙈 / 🐵)
// =========================
function togglePassword(inputId, icon) {
  const input = document.getElementById(inputId);

  if (input.type === "password") {
    input.type = "text";
    icon.textContent = "🐱";
  } else {
    input.type = "password";
    icon.textContent = "🙀";
  }
}
