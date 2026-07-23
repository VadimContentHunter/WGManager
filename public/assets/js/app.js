document.addEventListener('DOMContentLoaded', () => {

    const settings = document.getElementById('settings-button');
    const modal = document.getElementById('settings-modal');
    const close = document.querySelector('.close-modal');

    settings.addEventListener('click', () => {
        modal.classList.remove('hidden');
    });

    close.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

});