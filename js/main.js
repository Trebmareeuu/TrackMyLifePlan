document.addEventListener('DOMContentLoaded', function() {
    // Animación para los botones
    const buttons = document.querySelectorAll('button, .btn');
    buttons.forEach(button => {
        button.addEventListener('mouseover', () => {
            button.style.transform = 'translateY(-2px)';
            button.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
        });
        button.addEventListener('mouseout', () => {
            button.style.transform = 'translateY(0)';
            button.style.boxShadow = 'none';
        });
    });

    // Cambio entre formularios de inicio de sesión y registro
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const showLoginBtn = document.getElementById('show-login-btn');
    const showRegisterBtn = document.getElementById('show-register-btn');

    if (showLoginBtn) {
        showLoginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
            showLoginBtn.classList.add('active');
            showRegisterBtn.classList.remove('active');
        });
    }

    if (showRegisterBtn) {
        showRegisterBtn.addEventListener('click', (e) => {
            e.preventDefault();
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
            showRegisterBtn.classList.add('active');
            showLoginBtn.classList.remove('active');
        });
    }

    // Validación del formulario de registro
    const registerFormEl = document.querySelector('form[action="index.php"] input[name="register"]');
    if (registerFormEl) {
        registerFormEl.closest('form').addEventListener('submit', function(event) {
            const password = document.getElementById('password').value;
            const confirm_password = document.getElementById('confirm_password').value;
            const errors = [];

            if (password.length < 8) {
                errors.push('La contraseña debe tener al menos 8 caracteres.');
            }

            if (password !== confirm_password) {
                errors.push('Las contraseñas no coinciden.');
            }

            if (errors.length > 0) {
                event.preventDefault();
                const errorContainer = this.querySelector('.errors');
                if (errorContainer) {
                    errorContainer.innerHTML = '';
                    errors.forEach(error => {
                        const p = document.createElement('p');
                        p.textContent = error;
                        errorContainer.appendChild(p);
                    });
                } else {
                    alert(errors.join('\n'));
                }
            }
        });
    }
});
