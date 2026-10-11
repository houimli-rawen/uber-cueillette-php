function getById(id) { return document.getElementById(id); }

function showAlert(message, type, containerId) {
    if (!containerId) containerId = 'alert-container';
    var container = getById(containerId);
    if (!container) return;
    var icon = '';
    if (type === 'success') icon = '&#9989;';
    else if (type === 'danger') icon = '&#10060;';
    else if (type === 'warning') icon = '&#9888;';
    else if (type === 'info') icon = '&#8505;';
    container.innerHTML = '<div class="alert alert-' + type + ' fade-in">' + icon + ' ' + message + '</div>';
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function confirmAction(message, onConfirm) {
    var overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.innerHTML = '<div class="modal">' +
        '<div class="modal-header">Confirmation</div>' +
        '<div class="modal-body"><p>' + message + '</p></div>' +
        '<div class="modal-footer">' +
            '<button class="btn btn-outline" id="cancel-confirm">Annuler</button>' +
            '<button class="btn btn-danger" id="ok-confirm">Confirmer</button>' +
        '</div>' +
    '</div>';
    document.body.appendChild(overlay);
    getById('cancel-confirm').addEventListener('click', function() { overlay.remove(); });
    getById('ok-confirm').addEventListener('click', function() { overlay.remove(); onConfirm(); });
    overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
}

function validateField(input, rules) {
    var errorEl = getById(input.id + '-error');
    var valid = true;
    var message = '';
    var val = input.value;
    var i;

    for (i = 0; i < rules.length; i++) {
        var rule = rules[i];
        if (rule === 'required') {
            if (val.trim() === '') { valid = false; message = 'Ce champ est obligatoire.'; }
        } else if (rule === 'cin') {
            if (!/^\d{8}$/.test(val.trim())) { valid = false; message = 'Le CIN doit contenir exactement 8 chiffres.'; }
        } else if (rule === 'pseudo') {
            if (!/^[a-zA-Z]+$/.test(val.trim())) { valid = false; message = 'Le pseudo doit contenir uniquement des lettres.'; }
        } else if (rule === 'password') {
            if (!/^[a-zA-Z0-9]{7,}[$#]$/.test(val)) { valid = false; message = 'Le mot de passe doit avoir au moins 8 caracteres et finir par $ ou #.'; }
        } else if (rule === 'email') {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim())) { valid = false; message = 'Adresse email invalide.'; }
        } else if (rule === 'positiveInt') {
            var numInt = parseInt(val);
            if (isNaN(numInt) || numInt <= 0) { valid = false; message = 'Doit etre un entier positif.'; }
        } else if (rule === 'positiveFloat') {
            var numFloat = parseFloat(val);
            if (isNaN(numFloat) || numFloat <= 0) { valid = false; message = 'Doit etre un nombre positif.'; }
        }
        if (!valid) break;
    }

    if (errorEl) {
        errorEl.textContent = message;
        if (!valid) errorEl.classList.add('show'); else errorEl.classList.remove('show');
    }
    if (!valid) { input.classList.add('error'); input.classList.remove('success'); }
    else if (val.trim() !== '') { input.classList.remove('error'); input.classList.add('success'); }
    else { input.classList.remove('error'); input.classList.remove('success'); }
    return valid;
}

function validateDateRange(startId, endId, limitId) {
    var start = getById(startId);
    var end = getById(endId);
    var limit = limitId ? getById(limitId) : null;
    var valid = true;

    if (start && end && start.value && end.value) {
        var startDate = new Date(start.value);
        var endDate = new Date(end.value);
        var endErr = getById(endId + '-error');
        if (endDate <= startDate) {
            if (endErr) { endErr.textContent = 'La date de fin doit etre apres la date de debut.'; endErr.classList.add('show'); }
            end.classList.add('error');
            valid = false;
        } else {
            if (endErr) endErr.classList.remove('show');
            end.classList.remove('error');
            end.classList.add('success');
        }
    }

    if (limit && start && limit.value && start.value) {
        var startDate2 = new Date(start.value);
        var limitDate = new Date(limit.value);
        var limitErr = getById(limitId + '-error');
        if (limitDate >= startDate2) {
            if (limitErr) { limitErr.textContent = 'La date limite doit etre avant la date de debut.'; limitErr.classList.add('show'); }
            limit.classList.add('error');
            valid = false;
        } else {
            if (limitErr) limitErr.classList.remove('show');
            limit.classList.remove('error');
            limit.classList.add('success');
        }
    }
    return valid;
}

function validatePasswordConfirm(passId, confirmId) {
    var pass = getById(passId);
    var confirm = getById(confirmId);
    var errorEl = getById(confirmId + '-error');
    var valid = false;
    if (pass && confirm) valid = pass.value === confirm.value;
    if (errorEl) {
        errorEl.textContent = 'Les mots de passe ne correspondent pas.';
        if (!valid) errorEl.classList.add('show'); else errorEl.classList.remove('show');
    }
    if (!valid) { confirm.classList.add('error'); confirm.classList.remove('success'); }
    else if (confirm.value !== '') { confirm.classList.remove('error'); confirm.classList.add('success'); }
    return valid;
}

function setupRealTimeValidation(formConfig) {
    var fieldIds = Object.keys(formConfig);
    var i;
    for (i = 0; i < fieldIds.length; i++) {
        var fieldId = fieldIds[i];
        var input = getById(fieldId);
        if (!input) continue;
        input.addEventListener('blur', (function(id, rules) {
            return function() { validateField(getById(id), rules); };
        })(fieldId, formConfig[fieldId]));
        input.addEventListener('input', (function(id, rules) {
            return function() {
                var el = getById(id);
                if (el.className.indexOf('error') !== -1) validateField(el, rules);
            };
        })(fieldId, formConfig[fieldId]));
    }
}

function initAgriculteurRegister() {
    var form = getById('form-agriculteur');
    if (!form) return;
    setupRealTimeValidation({
        'nom': ['required'], 'prenom': ['required'], 'cin': ['required', 'cin'],
        'email': ['required', 'email'], 'adresse': ['required'], 'pseudo': ['required', 'pseudo'],
        'password': ['required', 'password']
    });
    var confirmInput = getById('confirm_password');
    if (confirmInput) confirmInput.addEventListener('blur', function() { validatePasswordConfirm('password', 'confirm_password'); });
    form.addEventListener('submit', function(e) {
        var fields = { nom: ['required'], prenom: ['required'], cin: ['required','cin'], email: ['required','email'], adresse: ['required'], pseudo: ['required','pseudo'], password: ['required','password'] };
        var valid = true;
        var ids = Object.keys(fields);
        var i;
        for (i = 0; i < ids.length; i++) {
            var input = getById(ids[i]);
            if (input && !validateField(input, fields[ids[i]])) valid = false;
        }
        if (!validatePasswordConfirm('password', 'confirm_password')) valid = false;
        if (!valid) e.preventDefault();
    });
}

function initOuvrierRegister() {
    var form = getById('form-ouvrier');
    if (!form) return;
    setupRealTimeValidation({
        'nom': ['required'], 'prenom': ['required'], 'cin': ['required', 'cin'],
        'email': ['required', 'email'], 'pseudo': ['required', 'pseudo'], 'password': ['required', 'password']
    });
    var confirmInput = getById('confirm_password');
    if (confirmInput) confirmInput.addEventListener('blur', function() { validatePasswordConfirm('password', 'confirm_password'); });
    form.addEventListener('submit', function(e) {
        var fields = { nom: ['required'], prenom: ['required'], cin: ['required','cin'], email: ['required','email'], pseudo: ['required','pseudo'], password: ['required','password'] };
        var valid = true;
        var ids = Object.keys(fields);
        var i;
        for (i = 0; i < ids.length; i++) {
            var input = getById(ids[i]);
            if (input && !validateField(input, fields[ids[i]])) valid = false;
        }
        if (!validatePasswordConfirm('password', 'confirm_password')) valid = false;
        var photo = getById('photo');
        if (photo && photo.files.length === 0) {
            var err = getById('photo-error');
            if (err) { err.textContent = 'La photo est requise.'; err.classList.add('show'); }
            valid = false;
        }
        if (!valid) e.preventDefault();
    });
}

function initPhotoPreview() {
    var photoInput = getById('photo');
    if (!photoInput) return;
    photoInput.addEventListener('change', function() {
        var preview = getById('photo-preview');
        var wrap = getById('photo-preview-wrap');
        if (photoInput.files && photoInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                if (preview) preview.src = e.target.result;
                if (wrap) wrap.style.display = 'block';
                if (preview && !wrap) preview.style.display = 'block';
            };
            reader.readAsDataURL(photoInput.files[0]);
        }
    });
}

function initOffreForm() {
    var form = getById('form-offre');
    if (!form) return;
    setupRealTimeValidation({ 'adresse': ['required'], 'nombre_ouvriers': ['required', 'positiveInt'], 'prix_journee': ['required', 'positiveFloat'] });
    var dateDebut = getById('date_debut');
    var dateFin = getById('date_fin');
    var dateLimite = getById('date_limite');
    if (dateFin) dateFin.addEventListener('change', function() { validateDateRange('date_debut','date_fin','date_limite'); });
    if (dateLimite) dateLimite.addEventListener('change', function() { validateDateRange('date_debut','date_fin','date_limite'); });
    if (dateDebut) dateDebut.addEventListener('change', function() { if (dateFin && dateFin.value) validateDateRange('date_debut','date_fin','date_limite'); });
    form.addEventListener('submit', function(e) {
        var valid = true;
        var selectIds = ['type_fruit', 'gouvernorat'];
        var i;
        for (i = 0; i < selectIds.length; i++) {
            var sel = getById(selectIds[i]);
            if (sel && sel.value === '') {
                var err = getById(selectIds[i] + '-error');
                if (err) { err.textContent = 'Veuillez choisir une option.'; err.classList.add('show'); }
                sel.classList.add('error');
                valid = false;
            }
        }
        var textFields = { adresse: ['required'], nombre_ouvriers: ['required','positiveInt'], prix_journee: ['required','positiveFloat'] };
        var textIds = Object.keys(textFields);
        for (i = 0; i < textIds.length; i++) {
            var input = getById(textIds[i]);
            if (input && !validateField(input, textFields[textIds[i]])) valid = false;
        }
        if (!validateDateRange('date_debut','date_fin','date_limite')) valid = false;
        if (!valid) e.preventDefault();
    });
}

function initLoginForm() {
    var form = getById('form-login');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        var valid = true;
        var ids = ['pseudo', 'password'];
        var i;
        for (i = 0; i < ids.length; i++) {
            var input = getById(ids[i]);
            if (input && !validateField(input, ['required'])) valid = false;
        }
        if (!valid) e.preventDefault();
    });
}

function initTogglePassword() {
    var toggleBtn = getById('toggle-password-btn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            var p = getById('password');
            var i = getById('eye-icon');
            if (p.type === 'password') { p.type = 'text'; i.classList.remove('fa-eye'); i.classList.add('fa-eye-slash'); }
            else { p.type = 'password'; i.classList.remove('fa-eye-slash'); i.classList.add('fa-eye'); }
        });
    }
}

function initEvalStars() {
    var inputs = document.querySelectorAll('input[data-stars]');
    var i;
    for (i = 0; i < inputs.length; i++) {
        inputs[i].addEventListener('input', function() {
            var id = this.getAttribute('data-stars');
            var n = parseInt(this.value) || 0;
            if (n < 0) n = 0;
            if (n > 10) n = 10;
            var stars = '';
            var s;
            for (s = 1; s <= 10; s++) stars += s <= n ? '★' : '☆';
            var starEl = getById('stars-' + id);
            if (starEl) starEl.textContent = stars;
        });
    }
}

function initDeleteButtons() {
    var buttons = document.querySelectorAll('.btn-delete-offre');
    var i;
    for (i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener('click', function() {
            var url = this.getAttribute('data-url');
            confirmAction('Supprimer cette offre definitivement ?', function() { window.location = url; });
        });
    }
}

function initPostulerButtons() {
    var buttons = document.querySelectorAll('.btn-postuler');
    var i;
    for (i = 0; i < buttons.length; i++) {
        buttons[i].addEventListener('click', function(e) {
            e.preventDefault();
            var url = this.getAttribute('href');
            var msg = this.getAttribute('data-confirm');
            confirmAction(msg, function() { window.location = url; });
        });
    }
}

function initDecisionButtons() {
    var acceptBtns = document.querySelectorAll('.btn-accept-cand');
    var refuseBtns = document.querySelectorAll('.btn-refuse-cand');
    var i;
    for (i = 0; i < acceptBtns.length; i++) {
        acceptBtns[i].addEventListener('click', function(e) {
            e.preventDefault();
            var url = this.getAttribute('href');
            confirmAction('Accepter ce postulant ?', function() { window.location = url; });
        });
    }
    for (i = 0; i < refuseBtns.length; i++) {
        refuseBtns[i].addEventListener('click', function(e) {
            e.preventDefault();
            var url = this.getAttribute('href');
            confirmAction('Refuser ce postulant ?', function() { window.location = url; });
        });
    }
}

function autoHideAlerts() {
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        var i;
        for (i = 0; i < alerts.length; i++) {
            alerts[i].style.transition = 'opacity 0.5s';
            alerts[i].style.opacity = '0';
            setTimeout((function(el) { return function() { el.remove(); }; })(alerts[i]), 500);
        }
    }, 5000);
}

function initAll() {
    initAgriculteurRegister();
    initOuvrierRegister();
    initOffreForm();
    initLoginForm();
    initTogglePassword();
    initPhotoPreview();
    initEvalStars();
    initDeleteButtons();
    initPostulerButtons();
    initDecisionButtons();
    autoHideAlerts();
}

document.addEventListener('DOMContentLoaded', initAll);
