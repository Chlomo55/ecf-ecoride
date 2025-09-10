// Gestion du menu burger
document.addEventListener('DOMContentLoaded', () => {
    const burger = document.querySelector('.burger');
    const nav = document.querySelector('.nav-container ul');

    burger.addEventListener('click', () => {
        nav.classList.toggle('active');
    });
});