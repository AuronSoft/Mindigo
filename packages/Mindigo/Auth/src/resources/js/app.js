import '../../../../Core/src/resources/js/mindigo-ui.js';
import '../css/app.css';
import './mindigo-id';

const authMessages = window.__authMessages || {};
const pwToggle = document.getElementById('pwToggle');
const pwInput = document.getElementById('password');
const eyeIcon = document.getElementById('eyeIcon');

const onboarding = document.querySelector('[data-login-onboarding]');

if (onboarding) {
    const slides = [...onboarding.querySelectorAll('[data-onboarding-slide]')];
    const dots = [...onboarding.querySelectorAll('[data-onboarding-dot]')];
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let activeSlide = 0;
    let autoplay;
    let isTransitioning = false;

    const showSlide = index => {
        const nextSlide = (index + slides.length) % slides.length;
        if (nextSlide === activeSlide || isTransitioning) return;

        const currentElement = slides[activeSlide];
        const nextElement = slides[nextSlide];
        const transitionDelay = reduceMotion ? 0 : 720;
        isTransitioning = true;
        currentElement.classList.add('is-leaving');

        dots.forEach((dot, dotIndex) => {
            const isActive = dotIndex === nextSlide;
            dot.classList.toggle('is-active', isActive);
            dot.setAttribute('aria-selected', String(isActive));
        });

        window.setTimeout(() => {
            currentElement.classList.remove('is-active', 'is-leaving');
            currentElement.setAttribute('aria-hidden', 'true');
            activeSlide = nextSlide;
            nextElement.classList.add('is-active', 'is-entering');
            nextElement.setAttribute('aria-hidden', 'false');

            window.setTimeout(() => {
                nextElement.classList.remove('is-entering');
                isTransitioning = false;
                startAutoplay();
            }, reduceMotion ? 0 : 900);
        }, transitionDelay);
    };

    const startAutoplay = () => {
        if (reduceMotion) return;
        window.clearTimeout(autoplay);
        autoplay = window.setTimeout(() => showSlide(activeSlide + 1), 1500);
    };

    dots.forEach((dot, index) => dot.addEventListener('click', () => {
        window.clearTimeout(autoplay);
        if (index === activeSlide) {
            startAutoplay();
        } else {
            showSlide(index);
        }
    }));
    onboarding.addEventListener('mouseenter', () => window.clearTimeout(autoplay));
    onboarding.addEventListener('mouseleave', startAutoplay);
    document.addEventListener('visibilitychange', () => document.hidden ? window.clearTimeout(autoplay) : startAutoplay());
    startAutoplay();
}

pwToggle?.addEventListener('click', () => {
    const isText = pwInput.type === 'text';
    pwInput.type = isText ? 'password' : 'text';
    eyeIcon.innerHTML = isText
        ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'
        : '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
});

document.getElementById('loginForm')?.addEventListener('submit', () => {
    const btn = document.getElementById('loginBtn');
    btn?.classList.add('loading');
    if (btn) btn.disabled = true;
});

if (window.__loginSuccess) {
    MindigoToast(authMessages.login_success || 'Login successful! Welcome back.', 'success');
}

if (window.__logoutSuccess) {
    MindigoToast(authMessages.logout_success || 'Logged out successfully. See you again!', 'success');
}

if (window.__loginError) {
    MindigoToast(window.__loginError, 'error', 4500);
}

document.querySelectorAll('[data-logout]').forEach(btn => {
    btn.addEventListener('click', async (event) => {
        event.preventDefault();

        const confirmed = await MindigoConfirm({
            title: authMessages.logout_title || 'Logout',
            message: authMessages.logout_message || 'Are you sure you want to logout?',
            confirmText: authMessages.logout_confirm || 'Logout',
            cancelText: authMessages.logout_cancel || 'Cancel',
            type: 'warning',
        });

        if (!confirmed) return;

        MindigoToast(authMessages.logging_out || 'Logging out...', 'info', 1500);
        setTimeout(() => document.getElementById('logoutForm')?.submit(), 1000);
    });
});

(() => {
    const canvas = document.getElementById('connectorCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const cardIds = ['fc1', 'fc2', 'fc3', 'fc4', 'fc5', 'fc6', 'fc7'];
    const colors = ['#60A5FA', '#22c55e', '#A78BFA', '#FBBF24', '#F87171', '#34D399', '#60A5FA'];
    const dots = cardIds.map((id, index) => ({
        id,
        color: colors[index],
        progress: Math.random(),
        speed: 0.003 + Math.random() * 0.002,
    }));

    function getCenter(element, container) {
        const rect = element.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();
        return {
            x: rect.left - containerRect.left + rect.width / 2,
            y: rect.top - containerRect.top + rect.height / 2,
        };
    }

    function resize() {
        const parent = canvas.parentElement;
        canvas.width = parent.offsetWidth;
        canvas.height = parent.offsetHeight;
    }

    function draw() {
        resize();
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        const container = canvas.parentElement;
        const logo = document.getElementById('centerLogo');
        if (!logo) {
            requestAnimationFrame(draw);
            return;
        }

        const center = getCenter(logo, container);

        dots.forEach(dot => {
            const card = document.getElementById(dot.id);
            if (!card) return;

            const cardCenter = getCenter(card, container);
            const hex = dot.color.replace('#', '');
            const red = parseInt(hex.substring(0, 2), 16);
            const green = parseInt(hex.substring(2, 4), 16);
            const blue = parseInt(hex.substring(4, 6), 16);

            ctx.beginPath();
            ctx.moveTo(cardCenter.x, cardCenter.y);
            ctx.lineTo(center.x, center.y);
            ctx.strokeStyle = `rgba(${red},${green},${blue},0.15)`;
            ctx.lineWidth = 1;
            ctx.setLineDash([4, 6]);
            ctx.stroke();
            ctx.setLineDash([]);

            dot.progress += dot.speed;
            if (dot.progress > 1) dot.progress = 0;

            const px = cardCenter.x + (center.x - cardCenter.x) * dot.progress;
            const py = cardCenter.y + (center.y - cardCenter.y) * dot.progress;
            const gradient = ctx.createRadialGradient(px, py, 0, px, py, 6);
            gradient.addColorStop(0, `rgba(${red},${green},${blue},0.9)`);
            gradient.addColorStop(1, `rgba(${red},${green},${blue},0)`);

            ctx.beginPath();
            ctx.arc(px, py, 4, 0, Math.PI * 2);
            ctx.fillStyle = gradient;
            ctx.fill();

            ctx.beginPath();
            ctx.arc(px, py, 2.5, 0, Math.PI * 2);
            ctx.fillStyle = dot.color;
            ctx.fill();
        });

        requestAnimationFrame(draw);
    }

    setTimeout(draw, 300);
    window.addEventListener('resize', resize);
})();
