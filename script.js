function togglePassword(id) {
    var input = document.getElementById(id);
    if (!input) return;
    var toggle = input.parentElement.querySelector('.toggle-password');
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    if (toggle) toggle.textContent = show ? 'Hide' : 'Show';
}

function validateRegister() {
    var pass = document.getElementById('reg_password').value;
    var confirm = document.getElementById('confirm_password').value;
    if (pass.length < 6) {
        alert('Password must be at least 6 characters.');
        return false;
    }
    if (pass !== confirm) {
        alert('Passwords do not match.');
        return false;
    }
    return true;
}
