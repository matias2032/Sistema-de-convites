document.addEventListener('DOMContentLoaded', () => {
    const toggleButtons = document.querySelectorAll('.toggle-senha');

    toggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Localiza o input relativo ao container do botão
            const inputGroup = button.closest('.input-group');
            const input = inputGroup ? inputGroup.querySelector('input') : null;
            const icon = button.querySelector('i');

            if (!input || !icon) return;

            // Alterna o tipo do input entre password e text
            const isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');

            // Alterna a classe do ícone Font Awesome
            icon.classList.toggle('fa-eye', !isPassword);
            icon.classList.toggle('fa-eye-slash', isPassword);
        });
    });
});