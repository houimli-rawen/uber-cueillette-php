# Uber-Cueillette

Site web dynamique mettant en relation des **agriculteurs** et des **ouvriers** pour les saisons de récolte (olives, agrumes, tomates).

Mini-projet de Programmation Web 2 (ISG Tunis, 2025/2026).

## Fonctionnalités

### Agriculteur
- Inscription et connexion (CIN à 8 chiffres, pseudo en lettres, mot de passe d'au moins 8 caractères finissant par `$` ou `#`)
- Tableau de bord avec statistiques (offres publiées, offres actives, ouvriers acceptés)
- Publication d'offres : type de fruit, gouvernorat, adresse, dates, nombre d'ouvriers, prix par jour, date limite
- Consultation des postulants (photo, description, moyenne des notes) puis acceptation ou refus
- Clôture automatique de l'offre (date limite dépassée ou nombre d'ouvriers atteint)
- Suppression d'une offre sans postulant
- Notation (0 à 10), commentaire et rémunération de chaque ouvrier après le chantier
- Consultation et modification du profil

### Ouvrier
- Inscription avec photo d'identité et description
- Consultation des offres ouvertes, filtres (fruit, gouvernorat) et tri par prix
- Candidature en un clic avec confirmation
- Suivi des candidatures (en attente, acceptées, refusées)
- Historique des chantiers terminés : notes, commentaires et gains

## Technologies
- **Client :** HTML, CSS, JavaScript (contrôle de saisie, confirmations)
- **Serveur :** PHP (sessions, mots de passe hachés avec `password_hash`)
- **Base de données :** MySQL (phpMyAdmin)

## Structure du projet
- `index.php`, `login.php`, `logout.php`, `register_agriculteur.php`, `register_ouvrier.php` : accueil et authentification
- `dashboard.php`, `mes_offres.php`, `ajouter_offre.php`, `postulants.php`, `evaluer.php`, `profil.php` : espace agriculteur
- `dashboardO.php`, `offres.php`, `mes_candidatures.php`, `historique.php` : espace ouvrier
- `config.php`, `functions.php`, `header.php`, `navbar.php`, `footer.php`, `photo_ouvrier.php` : fichiers communs
- `style.css`, `script.js` : mise en forme et scripts côté client
- `database.sql` : base de données

## Lancer le projet
1. Installer XAMPP ou WAMP
2. Copier le dossier du projet dans `htdocs`
3. Importer `database.sql` dans phpMyAdmin (base `uber_cueillette`)
4. Vérifier les paramètres de connexion dans `config.php`
5. Ouvrir `http://localhost/` suivi du nom du dossier

## Auteure
Rawen Houimli
