import './bootstrap';

const themeButton = document.querySelector('.theme-toggle');
const storedTheme = localStorage.getItem('portfolio-theme');
if (storedTheme === 'light') document.documentElement.classList.add('theme-light');
themeButton?.addEventListener('click', () => {
    const isLight = document.documentElement.classList.toggle('theme-light');
    localStorage.setItem('portfolio-theme', isLight ? 'light' : 'dark');
});
