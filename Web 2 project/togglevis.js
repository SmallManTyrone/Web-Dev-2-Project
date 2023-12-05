function togglePasswordVisibility(inputId) {
    var passwordInput = document.getElementById(inputId);
    var toggle = document.getElementById(inputId + '-toggle');

    // Toggle the type attribute of the password input
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggle.innerHTML = '<img src="eye-closed.png" alt="Toggle Password Visibility" width="20" height="20">';
    } else {
        passwordInput.type = "password";
        toggle.innerHTML = '<img src="eye-open.png" alt="Toggle Password Visibility" width="20" height="20">';
    }
}