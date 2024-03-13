function togglePasswordVisibility(fieldId, buttonId) {
  var passwordInput = document.getElementById(fieldId);
  var passwordVisibilityToggle = document.getElementById(buttonId);

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    passwordVisibilityToggle.textContent = "Ocultar contraseña";
  } else {
    passwordInput.type = "password";
    passwordVisibilityToggle.textContent = "Mostrar contraseña";
  }
}