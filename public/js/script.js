// ========== VISUALISATION MOT DE PASSE - SOLUTION UNIVERSELLE CORRIGÉE ==========
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initialisation du toggle password...');

    // Trouver TOUS les boutons toggle password qui existent déjà
    const existingToggles = document.querySelectorAll('.password-toggle');

    existingToggles.forEach(toggleBtn => {
        const targetId = toggleBtn.getAttribute('data-target');
        const input = targetId ? document.getElementById(targetId) : toggleBtn.closest('.password-container')?.querySelector('input[type="password"]');

        if (input) {
            console.log('Configuration du toggle pour:', input.id || input.name);
            setupPasswordToggle(toggleBtn, input);
        }
    });

    // Trouver les champs password qui n'ont PAS encore de toggle
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    console.log('Champs password détectés:', passwordInputs.length);

    passwordInputs.forEach((input) => {
        // Vérifier si le champ a déjà un toggle configuré
        const container = input.closest('.password-container');
        if (container && container.querySelector('.password-toggle')) {
            return;
        }

        console.log('Création automatique du conteneur pour:', input.id || input.name);

        let wrapper = container;
        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'password-container';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
        }

        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'password-toggle';
        toggleBtn.innerHTML = `
            <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
            <svg class="eye-slash-icon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
        `;
        wrapper.appendChild(toggleBtn);

        setupPasswordToggle(toggleBtn, input);
    });

    console.log('Initialisation du toggle password terminée.');
});

// Fonction pour configurer un toggle
function setupPasswordToggle(toggleBtn, input) {
    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const eyeIcon = this.querySelector('.eye-icon');
        const eyeSlashIcon = this.querySelector('.eye-slash-icon');
        const isPassword = input.type === 'password';

        input.type = isPassword ? 'text' : 'password';

        if (eyeIcon && eyeSlashIcon) {
            if (isPassword) {
                // Mot de passe visible : afficher l'œil barré
                eyeIcon.style.display = 'none';
                eyeSlashIcon.style.display = 'block';
            } else {
                // Mot de passe masqué : afficher l'œil normal
                eyeIcon.style.display = 'block';
                eyeSlashIcon.style.display = 'none';
            }
        }
    });
}

// ========== RESTE DU CODE ==========
document.addEventListener('DOMContentLoaded', function() {

    // ========== MENU MOBILE BURGER ==========
    const burger = document.querySelector('.burger');
    const navMenu = document.querySelector('.nav-menu');

    if (burger && navMenu) {
        burger.addEventListener('click', function() {
            burger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        const navLinks = document.querySelectorAll('.nav-menu a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
                burger.classList.remove('active');
            });
        });

        document.addEventListener('click', function(event) {
            const isClickInsideNav = burger.contains(event.target) || navMenu.contains(event.target);
            if (!isClickInsideNav && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                burger.classList.remove('active');
            }
        });
    }

    // ========== GESTION DU CARROUSEL ==========
    initCarousels();

    // ========== GESTION DES ALERTES ==========
    initAlerts();

    // ========== GESTION DES FORMULAIRES ==========
    initForms();

    // ========== COMPTEUR DE CARACTÈRES (COMMENTAIRES) ==========
    initCharCounters();

    // ========== BOUTONS RÉPONDRE (COMMENTAIRES) ==========
    initCommentReplyButtons();

    // ========== NOTATION PAR ÉTOILES ==========
    initStarRatings();

    // ========== ANIMATIONS SCROLL ==========
    initScrollAnimations();

    // ========== SMOOTH SCROLL ==========
    initSmoothScroll();

    // ========== GESTION DU MOYEN DE PAIEMENT ==========
    initPaymentMethod();

    // ========== NAVIGATION ACTIVE ==========
    initActiveNav();

    // ========== SYNCHRONISATION DATE/HEURE ATELIER ==========
    initDateTimeSync();

    // ========== ACTIONS INDIVIDUELLES AVIS (sans formulaires imbriqués) ==========
    // CORRECTION : les boutons .btn-action-avis remplacent les <form> imbriqués du template Twig.
    // Au clic, on crée dynamiquement un formulaire hors du DOM pour éviter tout conflit CSRF.
    document.querySelectorAll('.btn-action-avis').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm(this.dataset.confirm)) return;

            const form = document.createElement('form');
            form.method = 'post';
            form.action = this.dataset.action;

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = this.dataset.token;

            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        });
    });
});

// ========== FONCTION: CARROUSEL ==========
function initCarousels() {
    const carousels = document.querySelectorAll('.carousel');

    carousels.forEach(carousel => {
        const items = carousel.querySelectorAll('.carousel-item');
        const indicators = carousel.querySelectorAll('.carousel-indicators button');
        const prevBtn = carousel.querySelector('.carousel-control-prev');
        const nextBtn = carousel.querySelector('.carousel-control-next');

        if (items.length === 0) return;

        let currentIndex = 0;
        let autoplayInterval;

        function showSlide(index) {
            items.forEach(item => item.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));

            if (index >= items.length) {
                currentIndex = 0;
            } else if (index < 0) {
                currentIndex = items.length - 1;
            } else {
                currentIndex = index;
            }

            items[currentIndex].classList.add('active');
            if (indicators[currentIndex]) {
                indicators[currentIndex].classList.add('active');
            }
        }

        function nextSlide() {
            showSlide(currentIndex + 1);
        }

        function prevSlide() {
            showSlide(currentIndex - 1);
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                nextSlide();
                resetAutoplay();
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                prevSlide();
                resetAutoplay();
            });
        }

        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                showSlide(index);
                resetAutoplay();
            });
        });

        function startAutoplay() {
            if (carousel.hasAttribute('data-autoplay')) {
                autoplayInterval = setInterval(nextSlide, 10000);
            }
        }

        function resetAutoplay() {
            clearInterval(autoplayInterval);
            startAutoplay();
        }

        startAutoplay();

        carousel.addEventListener('mouseenter', () => clearInterval(autoplayInterval));
        carousel.addEventListener('mouseleave', startAutoplay);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                prevSlide();
                resetAutoplay();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
                resetAutoplay();
            }
        });

        let touchStartX = 0;
        let touchEndX = 0;

        carousel.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });

        carousel.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            if (touchEndX < touchStartX - 50) {
                nextSlide();
                resetAutoplay();
            }
            if (touchEndX > touchStartX + 50) {
                prevSlide();
                resetAutoplay();
            }
        }
    });
}

// ========== FONCTION: ALERTES ==========
function initAlerts() {
    const closeButtons = document.querySelectorAll('.btn-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            if (alert) {
                fadeOut(alert);
            }
        });
    });

    const successAlerts = document.querySelectorAll('.alert-success');
    successAlerts.forEach(alert => {
        setTimeout(() => {
            fadeOut(alert);
        }, 5000);
    });
}

// ========== FONCTION: FORMULAIRES ==========
// CORRECTION : on exclut #bulk-form pour ne pas interférer avec la soumission groupée
function initForms() {
    const forms = document.querySelectorAll('form:not(#bulk-form)');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    field.style.borderColor = '#dc3545';

                    field.addEventListener('input', function() {
                        this.classList.remove('error');
                        this.style.borderColor = '#ced4da';
                        removeFieldError(this);
                    }, { once: true });
                }
            });

            if (!isValid) {
                e.preventDefault();

                const firstInvalid = form.querySelector('.error');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }

                showNotification('Veuillez remplir tous les champs obligatoires.', 'danger');
            }
        });

        const emailInputs = form.querySelectorAll('input[type="email"]');
        emailInputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value && !isValidEmail(this.value)) {
                    this.classList.add('error');
                    showFieldError(this, 'Format d\'email invalide');
                } else {
                    this.classList.remove('error');
                    removeFieldError(this);
                }
            });
        });
    });
}

// ========== FONCTION: COMPTEUR DE CARACTÈRES ==========
function initCharCounters() {
    console.log('🔍 Initialisation des compteurs...');

    // Utiliser un setTimeout pour s'assurer que le DOM Symfony est bien chargé
    setTimeout(() => {
        // L'ID réel généré par Symfony avec underscore (pas tiret)
        const mainTextarea = document.getElementById('commentaire_contenu');
        console.log('📝 Textarea principal trouvé:', mainTextarea);

        if (mainTextarea) {
            // Le span du compteur est dans le nextElementSibling (div suivant)
            const counterDiv = mainTextarea.nextElementSibling;
            const counterSpan = counterDiv ? counterDiv.querySelector('small:last-child span') : null;

            console.log('🎯 Compteur span trouvé:', counterSpan);

            if (counterSpan) {
                // Fonction de mise à jour
                const updateMainCounter = () => {
                    const length = mainTextarea.value.length;
                    counterSpan.textContent = length;

                    // Couleur selon le nombre de caractères
                    if (length > 450) {
                        counterSpan.style.color = '#c72c48'; // Rouge si proche de 500
                        counterSpan.style.fontWeight = 'bold';
                    } else {
                        counterSpan.style.color = '#4c1d95'; // Violet
                        counterSpan.style.fontWeight = 'bold';
                    }
                };

                // Mise à jour initiale
                updateMainCounter();

                // Mise à jour à chaque saisie
                mainTextarea.addEventListener('input', updateMainCounter);

                console.log('✅ Compteur principal activé !');
            } else {
                console.error('❌ Span compteur non trouvé');
            }
        } else {
            console.warn('⚠️ Textarea commentaire_contenu non trouvé');
        }

        // Compteurs pour les formulaires de réponse
        attachReplyCounters();

    }, 200);
}

// Attacher les compteurs aux formulaires de réponse
function attachReplyCounters() {
    document.querySelectorAll('form[id^="form-reponse-"] textarea').forEach(textarea => {
        const parentDiv = textarea.parentElement;
        const spans = parentDiv.querySelectorAll('span');
        const counter = spans[0];

        if (counter) {
            const updateCounter = () => {
                const length = textarea.value.length;
                counter.textContent = length;

                if (length > 450) {
                    counter.style.color = '#c72c48';
                } else {
                    counter.style.color = '#888';
                }
            };

            updateCounter();
            textarea.addEventListener('input', updateCounter);
        }
    });
}

// ========== FONCTION: BOUTONS RÉPONDRE ==========
function initCommentReplyButtons() {
    // Gérer tous les boutons Répondre
    document.querySelectorAll('[class*="btn-repondre-"]').forEach(btn => {
        btn.addEventListener('click', function() {
            const commentaireId = this.dataset.commentaireId;
            const form = document.getElementById(`form-reponse-${commentaireId}`);

            // Masquer tous les autres formulaires ouverts
            document.querySelectorAll('form[id^="form-reponse-"]').forEach(f => {
                f.style.display = 'none';
            });

            // Réafficher tous les boutons Répondre
            document.querySelectorAll('[class*="btn-repondre-"]').forEach(b => {
                b.style.display = 'inline-block';
            });

            // Cacher CE bouton Répondre
            this.style.display = 'none';

            // Afficher CE formulaire
            form.style.display = 'block';

            // Attacher le compteur au nouveau formulaire visible
            attachReplyCounters();
        });
    });

    // Gérer tous les boutons Annuler
    document.querySelectorAll('.btn-annuler').forEach(btn => {
        btn.addEventListener('click', function() {
            const commentaireId = this.dataset.commentaireId;
            const form = document.getElementById(`form-reponse-${commentaireId}`);
            const btnRepondre = document.querySelector(`.btn-repondre-${commentaireId}`);

            form.style.display = 'none';

            if (btnRepondre) {
                btnRepondre.style.display = 'inline-block';
            }

            form.reset();
            const counter = form.querySelector('span');
            if (counter) {
                counter.textContent = '0';
            }
        });
    });
}

// ========== FONCTION: NOTATION PAR ÉTOILES ==========
function initStarRatings() {
    const ratingInputs = document.querySelectorAll('input[type="number"][name*="note"]');

    ratingInputs.forEach(input => {
        const container = input.parentElement;
        const value = input.value || 0;

        const starsContainer = document.createElement('div');
        starsContainer.className = 'stars-rating';
        starsContainer.innerHTML = generateStars(value);

        input.style.display = 'none';
        container.appendChild(starsContainer);

        attachStarEvents(starsContainer, input);
    });
}

function generateStars(rating) {
    let html = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            html += '<span class="star filled">★</span>';
        } else {
            html += '<span class="star">☆</span>';
        }
    }
    return html;
}

function attachStarEvents(container, input) {
    const stars = container.querySelectorAll('.star');

    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = index + 1;
            input.value = rating;
            container.innerHTML = generateStars(rating);
            attachStarEvents(container, input);
        });

        star.addEventListener('mouseenter', function() {
            highlightStars(container, index + 1);
        });
    });

    container.addEventListener('mouseleave', function() {
        highlightStars(container, input.value || 0);
    });
}

function highlightStars(container, count) {
    const stars = container.querySelectorAll('.star');
    stars.forEach((star, index) => {
        if (index < count) {
            star.classList.add('filled');
            star.textContent = '★';
        } else {
            star.classList.remove('filled');
            star.textContent = '☆';
        }
    });
}

// ========== FONCTION: ANIMATIONS SCROLL ==========
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const elementsToAnimate = document.querySelectorAll('.avis-card, .formulaire-avis');
    elementsToAnimate.forEach((element, index) => {
        element.style.opacity = '1';
        element.style.transform = 'translateY(0)';
        element.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
    });
}

// ========== FONCTION: SMOOTH SCROLL ==========
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#' && targetId !== '#!') {
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
}

// ========== FONCTION: GESTION DU MOYEN DE PAIEMENT ==========
function initPaymentMethod() {
    const paymentRadios = document.querySelectorAll('input[name*="moyenPaiement"]');
    const helloAssoLink = document.getElementById('helloasso-link');

    if (helloAssoLink && paymentRadios.length > 0) {
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'cb') {
                    helloAssoLink.style.display = 'block';
                    helloAssoLink.innerHTML = '<strong>Important :</strong> Après avoir cliqué sur "Valider l\'inscription", vous serez automatiquement redirigé vers HelloAsso pour finaliser votre paiement par carte bancaire.';

                    helloAssoLink.style.opacity = '0';
                    setTimeout(() => {
                        helloAssoLink.style.transition = 'opacity 0.3s ease';
                        helloAssoLink.style.opacity = '1';
                    }, 10);
                } else {
                    helloAssoLink.style.display = 'none';
                }
            });
        });
    }
}

// ========== FONCTION: NAVIGATION ACTIVE ==========
function initActiveNav() {
    const navLinks = document.querySelectorAll('.nav-menu .nav-item a');
    const currentPath = window.location.pathname;

    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }

        link.addEventListener('click', function() {
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// ========== FONCTIONS UTILITAIRES ==========

function fadeOut(element) {
    element.style.transition = 'opacity 0.5s ease, transform 0.3s ease';
    element.style.opacity = '0';
    element.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        element.remove();
    }, 500);
}

function showFieldError(field, message) {
    removeFieldError(field);
    const error = document.createElement('div');
    error.className = 'field-error';
    error.textContent = message;
    field.parentElement.appendChild(error);
}

function removeFieldError(field) {
    const error = field.parentElement.querySelector('.field-error');
    if (error) {
        error.remove();
    }
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function isValidPhone(phone) {
    const re = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
    return re.test(phone.replace(/\s/g, ''));
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(400px)';
    notification.style.transition = 'all 0.3s ease';
    notification.innerHTML = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 10);

    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// ========== FONCTION: SYNCHRONISATION DATE/HEURE ATELIER ==========
function initDateTimeSync() {
    const dateInput = document.getElementById('date_input');
    const heureDebutInput = document.getElementById('heure_debut_input');
    const heureFinInput = document.getElementById('heure_fin_input');

    const dateAtelierHidden = document.getElementById('dateAtelier_hidden');
    const heureDebutHidden = document.getElementById('heureDebut_hidden');
    const heureFinHidden = document.getElementById('heureFin_hidden');

    // Si les éléments n'existent pas, on sort
    if (!dateInput || !heureDebutInput || !heureFinInput) {
        return;
    }

    console.log('🔍 Initialisation de la synchronisation date/heure');

    function syncDate() {
        if (dateInput && dateInput.value && dateAtelierHidden) {
            dateAtelierHidden.value = dateInput.value;
            console.log('✅ Date synchronisée:', dateInput.value);
        }
    }

    function syncHeureDebut() {
        if (heureDebutInput && heureDebutInput.value && heureDebutHidden) {
            heureDebutHidden.value = heureDebutInput.value;
            console.log('✅ Heure début synchronisée:', heureDebutInput.value);
        }
    }

    function syncHeureFin() {
        if (heureFinInput && heureFinInput.value && heureFinHidden) {
            heureFinHidden.value = heureFinInput.value;
            console.log('✅ Heure fin synchronisée:', heureFinInput.value);
        }
    }

    if (dateAtelierHidden && dateAtelierHidden.value) {
        dateInput.value = dateAtelierHidden.value;
    }
    if (heureDebutHidden && heureDebutHidden.value) {
        heureDebutInput.value = heureDebutHidden.value;
    }
    if (heureFinHidden && heureFinHidden.value) {
        heureFinInput.value = heureFinHidden.value;
    }

    if (dateInput) {
        dateInput.addEventListener('change', syncDate);
        dateInput.addEventListener('input', syncDate);
    }

    if (heureDebutInput) {
        heureDebutInput.addEventListener('change', syncHeureDebut);
        heureDebutInput.addEventListener('input', syncHeureDebut);
    }

    if (heureFinInput) {
        heureFinInput.addEventListener('change', syncHeureFin);
        heureFinInput.addEventListener('input', syncHeureFin);
    }

    const form = document.querySelector('form[name="atelier"]');
    if (form) {
        console.log('📝 Formulaire trouvé, ajout du listener submit');
        form.addEventListener('submit', function(e) {
            console.log('🚀 Soumission du formulaire - synchronisation...');
            syncDate();
            syncHeureDebut();
            syncHeureFin();
        });
    }
}

// ========== CASE A COCHER ET SUPPRESSION GROUPÉE ==========

document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const btnDelete = document.getElementById('btn-delete-selected');
    const bulkActions = document.getElementById('bulk-actions');
    const selectedCount = document.getElementById('selected-count');

    if (!selectAll || !btnDelete || !bulkActions || !selectedCount) return;

    function updateBulkActions() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const count = checked.length;
        selectedCount.textContent = count + ' sélectionné(s)';
        btnDelete.disabled = count === 0;
        bulkActions.style.display = count > 0 ? 'flex' : 'none';
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActions();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateBulkActions));

    // CORRECTION : le bouton est maintenant type="submit" dans le formulaire,
    // mais on garde la confirmation ici via un listener sur le formulaire
    const bulkForm = document.getElementById('bulk-form');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function (e) {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            if (checked.length === 0) {
                e.preventDefault();
                return;
            }
            if (!confirm('Supprimer les ' + checked.length + ' utilisateur(s) sélectionné(s) ?')) {
                e.preventDefault();
            }
        });
    }

    // ========== SUPPRESSION INDIVIDUELLE (sans formulaire imbriqué) ==========
    // CORRECTION : on crée dynamiquement un formulaire hors du tableau pour éviter l'imbrication
    document.querySelectorAll('.btn-delete-single').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) return;

            const form = document.createElement('form');
            form.method = 'post';
            form.action = this.dataset.action;

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = this.dataset.token;

            form.appendChild(token);
            document.body.appendChild(form);
            form.submit();
        });
    });
});

function setupBulkActions(selectAllClass, checkboxClass, bulkActionsId, countId, btnId, formId) {
    const selectAll = document.querySelector('.' + selectAllClass);
    const checkboxes = document.querySelectorAll('.' + checkboxClass);
    const bulkActions = document.getElementById(bulkActionsId);
    const countEl = document.getElementById(countId);
    const btn = document.getElementById(btnId);
    const form = document.getElementById(formId);

    if (!selectAll) return;

    function update() {
        const checked = document.querySelectorAll('.' + checkboxClass + ':checked');
        countEl.textContent = checked.length + ' sélectionné(s)';
        bulkActions.style.display = checked.length > 0 ? 'flex' : 'none';
    }

    selectAll.addEventListener('change', function () {
        checkboxes.forEach(cb => cb.checked = this.checked);
        update();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', update));

    btn.addEventListener('click', function () {
        const checked = document.querySelectorAll('.' + checkboxClass + ':checked');
        if (checked.length === 0) return;
        if (confirm('Supprimer les ' + checked.length + ' avis sélectionné(s) ?')) {
            form.submit();
        }
    });
}

setupBulkActions('select-all-attente', 'cb-attente', 'bulk-actions-attente', 'count-attente', 'btn-delete-attente', 'form-attente');
setupBulkActions('select-all-valides', 'cb-valides', 'bulk-actions-valides', 'count-valides', 'btn-delete-valides', 'form-valides');


// ========== MODAL VALIDATION AVIS ==========
document.addEventListener('DOMContentLoaded', function () {
    const modalAvis = document.getElementById('modalAvis');
    if (!modalAvis) return;

    // On écoute le clic sur les boutons "Valider" pour mémoriser les données
    document.querySelectorAll('[data-bs-target="#modalAvis"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('modal-nom').textContent = this.dataset.nom || '';
            document.getElementById('modal-commentaire').textContent = this.dataset.commentaire || '';
            const note = parseInt(this.dataset.note) || 0;
            document.getElementById('modal-note').textContent = '🍓'.repeat(note);
            const form = document.getElementById('modal-form-valider');
            form.action = this.dataset.action;
            document.getElementById('modal-token').value = this.dataset.token;
        });
    });
});
