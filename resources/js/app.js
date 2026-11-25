/**
 * Petits comportements du site, sans bibliothèque :
 * - menu mobile (délégation d'événements, compatible avec wire:navigate) ;
 * - repli des images absentes (le fond du conteneur reste visible) ;
 * - défilement narratif de l'accueil (IntersectionObserver).
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

/* Défilement narratif : le repère qui passe au milieu de l'écran active son étape et fait apparaître sa photo. */
function initStory() {
    const story = document.querySelector('[data-story]');
    if (!story || !('IntersectionObserver' in window)) {
        return;
    }

    const steps = [...story.querySelectorAll('[data-story-step]')];
    const photos = [...story.querySelectorAll('[data-story-photo]')];
    const triggers = [...story.querySelectorAll('[data-story-trigger]')];

    const activate = (index) => {
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

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                activate(triggers.indexOf(entry.target));
            }
        });
    }, { rootMargin: '-45% 0px -50% 0px' });

    triggers.forEach((trigger) => observer.observe(trigger));
    activate(0);
}

function init() {
    checkImages();
    initStory();
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
