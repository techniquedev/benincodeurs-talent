/**
 * Petits comportements du site, sans bibliothèque :
 * - menu mobile (délégation d'événements, compatible avec wire:navigate) ;
 * - repli des images absentes (le fond du conteneur reste visible) ;
 * - barre de navigation de l'accueil et défilement narratif (progression du défilement sur ordinateur,
 *   IntersectionObserver sur mobile).
 */

function setMenuOpen(menu, open) {
    const toggle = document.querySelector(`[data-menu-toggle="${menu.id}"]`);
    menu.hidden = !open;
    document.documentElement.classList.toggle('overflow-hidden', open);
    if (toggle) {
        toggle.setAttribute('aria-expanded', String(open));
    }
}

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-menu-toggle]');
    if (toggle) {
        const menu = document.getElementById(toggle.dataset.menuToggle);
        if (menu) {
            setMenuOpen(menu, menu.hidden);
        }
        return;
    }

    const close = event.target.closest('[data-menu-close]');
    if (close) {
        const menu = close.closest('[data-menu]');
        if (menu) {
            setMenuOpen(menu, false);
        }
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }
    document.querySelectorAll('[data-menu]:not([hidden])').forEach((menu) => setMenuOpen(menu, false));
});

/* Images de décor : si le fichier manque, on masque l'image pour laisser voir le fond de repli. */
function hideBrokenImage(image) {
    image.style.visibility = 'hidden';
}

document.addEventListener('error', (event) => {
    if (event.target instanceof HTMLImageElement && event.target.hasAttribute('data-fallback')) {
        hideBrokenImage(event.target);
    }
}, true);

function checkImages() {
    document.querySelectorAll('img[data-fallback]').forEach((image) => {
        if (image.complete && image.naturalWidth === 0) {
            hideBrokenImage(image);
        }
    });
}

/* Barre de navigation de l'accueil : transparente sur le hero, pleine dès qu'on défile. */
function initSiteHeader() {
    const header = document.querySelector('[data-site-header][data-overlay]');
    if (!header) {
        return;
    }

    const update = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
    window.addEventListener('scroll', update, { passive: true });
    update();
}

/*
 * Défilement narratif (« Pensé pour la communauté ») : le bloc collant reste visible pendant toute la zone ;
 * la progression du défilement dans la zone choisit le point actif (1er point actif d'emblée).
 */
function initStory() {
    const story = document.querySelector('[data-story]');
    if (!story) {
        return;
    }

    const steps = [...story.querySelectorAll('[data-story-step]')];
    const photos = [...story.querySelectorAll('[data-story-photo]')];
    const sticky = story.firstElementChild;
    let current = -1;
    let ticking = false;

    const activate = (index) => {
        if (index === current) {
            return;
        }
        current = index;
        steps.forEach((step, i) => {
            step.classList.toggle('is-active', i === index);
            if (i === index) {
                step.setAttribute('aria-current', 'step');
            } else {
                step.removeAttribute('aria-current');
            }
        });
        photos.forEach((photo, i) => photo.classList.toggle('is-active', i === index));
    };

    const update = () => {
        ticking = false;
        const rect = story.getBoundingClientRect();
        const stickyTop = parseFloat(getComputedStyle(sticky).top) || 0;
        const range = rect.height - sticky.offsetHeight;
        const progress = range > 0 ? Math.min(1, Math.max(0, (stickyTop - rect.top) / range)) : 0;
        activate(Math.min(steps.length - 1, Math.floor(progress * steps.length)));
    };

    const requestUpdate = () => {
        if (!ticking) {
            ticking = true;
            window.requestAnimationFrame(update);
        }
    };

    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', requestUpdate);
    update();
}

/*
 * Défilement narratif sur mobile : la photo reste collée en haut, les cartes défilent dessous.
 * La carte qui traverse la bande située aux deux tiers de l'écran devient active : photo (fondu) et point de progression.
 */
function initMobileStory() {
    const story = document.querySelector('[data-story-mobile]');
    if (!story || !('IntersectionObserver' in window)) {
        return;
    }

    const cards = [...story.querySelectorAll('[data-story-card]')];
    const photos = [...story.querySelectorAll('[data-story-photo]')];
    const dots = [...story.querySelectorAll('[data-story-dot]')];

    const activate = (index) => {
        photos.forEach((photo, i) => photo.classList.toggle('is-active', i === index));
        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === index));
        cards.forEach((card, i) => card.classList.toggle('is-active', i === index));
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                activate(cards.indexOf(entry.target));
            }
        });
    }, { rootMargin: '-65% 0px -30% 0px' });

    cards.forEach((card) => observer.observe(card));
    activate(0);
}

function init() {
    checkImages();
    initSiteHeader();
    initStory();
    initMobileStory();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

document.addEventListener('livewire:navigated', () => {
    document.documentElement.classList.remove('overflow-hidden');
    checkImages();
});
