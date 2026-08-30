<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accent Musique – Cours de Musique, Studio, Réparations & Cours en Ligne</title>
    <meta name="description" content="Accent Musique : cours de guitare, basse, batterie, studio d'enregistrement, lutherie & réparations, et plateforme de cours de musique en ligne.">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/favicon.svg">

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/landingstyle.css">
</head>

<body>

    <!-- NAVIGATION NAVBAR -->
    <header class="navbar" id="navbar">
        <div class="nav-container">

            <a href="<?= BASE_URL ?>/" class="nav-brand">
                <span class="brand-accent">ACCENT</span><span class="brand-sub">MUSIQUE</span>
            </a>

            <button class="burger" id="burgerBtn" aria-label="Menu principal">
                <span></span><span></span><span></span>
            </button>

            <nav class="nav-links" id="navLinks">
                <a href="#magasin" class="nav-link">Magasin</a>
                <a href="#app" class="nav-link">Application Web</a>
                <a href="#tarifs" class="nav-link">Tarifs</a>
                <a href="#contact" class="nav-link">Contact</a>

                <div class="nav-auth-group">
                    <?php if (\Core\Session::has('user_id')): ?>
                        <a href="<?= BASE_URL ?>/accueil" class="btn btn-sm btn-primary">Mon Espace</a>
                        <?php if (\Core\Session::get('user_role') === 'admin'): ?>
                            <a href="<?= BASE_URL ?>/admin" class="btn btn-sm btn-admin">Admin</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login" class="nav-link nav-link-login">Connexion</a>
                        <a href="<?= BASE_URL ?>/register" class="btn btn-sm btn-primary">S'inscrire</a>
                    <?php endif; ?>
                </div>
            </nav>

        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero" id="hero">
        <img src="<?= BASE_URL ?>/assets/images/hero.JPG" alt="Accent Musique" class="hero-img">
        <div class="hero-backdrop"></div>

        <div class="hero-overlay">
            <!-- <span class="hero-badge">🎵 Association loi 1901</span> -->
            <h1>Accent <span>Musique</span></h1>
            <p class="hero-tagline">Cours de Guitare, Studio d'enregistrement, Réparations & cours en ligne.</p>

            <div class="hero-cta-group">
                <a href="#magasin" class="btn btn-primary btn-lg">Découvrir le Magasin</a>
                <a href="#tarifs" class="btn btn-secondary btn-lg">Consulter les Tarifs</a>
                <a href="#app" class="btn btn-secondary btn-lg">Notre Appli</a>
            </div>
        </div>
    </section>

    <!-- SECTION MAGASIN (AVEC CARROUSEL DE PHOTOS ET CARTES SERVICES) -->
    <section id="magasin" class="section section-magasin">
        <div class="section-header">
            <span class="section-badge">🏪 Magasin & Atelier</span>
            <h2>Le Magasin Accent Musique</h2>
            <p class="section-subtitle">Découvrez notre association de partage musical à Osséja : cours, studio, atelier et boutique.</p>
        </div>

        <!-- CARROUSEL DE PHOTOS DU MAGASIN -->
        <div class="carousel-container" id="magasinCarouselWrapper">
            <div class="carousel-viewport" id="magasinSlider">
                <div class="carousel-track" id="sliderTrack">

                    <!-- Slide Photo 1 : Magasin -->
                    <div class="carousel-slide">
                        <div class="slide-photo-card">
                            <div class="slide-photo-media">
                                <img src="<?= BASE_URL ?>/assets/images/hero.jpg" alt="Le Magasin Accent Musique" class="slide-photo-img">
                                <div class="slide-photo-gradient"></div>
                                <span class="slide-photo-pill">🏪 Magasin & Espace Accueil</span>
                            </div>
                            <div class="slide-photo-info">
                                <span class="slide-tag-sub">En Cerdagne à Osséja</span>
                                <h3 class="slide-title">Bienvenue dans notre Magasin</h3>
                                <p class="slide-desc">Vente de guitares, cordes, médiators, câbles, capodastres et recevez les conseils avisés de musiciens passionnés.</p>
                                <div class="slide-feature-tags">
                                    <span>Cordes & Accessoires</span>
                                    <span>Ambiance Conviviale</span>
                                    <span>Conseils</span>
                                </div>
                                <a href="#contact" class="btn btn-outline btn-sm">Nous Rendre Visite &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <!-- Slide Photo 2 : Cours -->
                    <div class="carousel-slide">
                        <div class="slide-photo-card">
                            <div class="slide-photo-media">
                                <img src="<?= BASE_URL ?>/assets/images/videos.png" alt="Cours de Guitare et Musique" class="slide-photo-img">
                                <div class="slide-photo-gradient"></div>
                                <span class="slide-photo-pill">🎸 Cours de Musique</span>
                            </div>
                            <div class="slide-photo-info">
                                <span class="slide-tag-sub">Guitare, Basse & Batterie</span>
                                <h3 class="slide-title">Cours de Guitare Tous Niveaux</h3>
                                <p class="slide-desc">Des leçons individuelles adaptées à votre rythme et à vos envies musicales : Rock, Blues, Acoustique, etc... Progressez rapidement avec un suivi personnalisé.</p>
                                <div class="slide-feature-tags">
                                    <span>20€ à l'unité</span>
                                    <span>15€ avec adhésion</span>

                                </div>
                                <a href="#tarifs" class="btn btn-outline btn-sm">Voir le Tarif des Cours &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <!-- Slide Photo 3 : Studio -->
                    <div class="carousel-slide">
                        <div class="slide-photo-card">
                            <div class="slide-photo-media">
                                <img src="<?= BASE_URL ?>/assets/images/studio.JPG" alt="Studio" class="slide-photo-img">
                                <div class="slide-photo-gradient"></div>
                                <span class="slide-photo-pill">🎙️ Studio & Production</span>
                            </div>
                            <div class="slide-photo-info">
                                <span class="slide-tag-sub">Prise de Son & Mixage</span>
                                <h3 class="slide-title">Studio d'Enregistrement Pro</h3>
                                <p class="slide-desc">Enregistrez vos voix, instruments et maquettes dans les meilleures conditions acoustiques avec Cubase Pro. Mixage multipiste et mastering adapté à vos projets.</p>
                                <div class="slide-feature-tags">
                                    <span>30€ / heure</span>
                                    <span>Cubase Pro</span>
                                    <span>Mixage & Mastering</span>
                                </div>
                                <a href="#tarifs" class="btn btn-outline btn-sm">Découvrir le Studio &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <!-- Slide Photo 4 : Lutherie -->
                    <div class="carousel-slide">
                        <div class="slide-photo-card">
                            <div class="slide-photo-media">
                                <img src="<?= BASE_URL ?>/assets/images/atelier.JPG" alt="Réparations & Réglages" class="slide-photo-img">
                                <div class="slide-photo-gradient"></div>
                                <span class="slide-photo-pill">🛠️ Atelier de Réparations & Réglages</span>
                            </div>
                            <div class="slide-photo-info">
                                <span class="slide-tag-sub">Réparations & Réglages</span>
                                <h3 class="slide-title">Atelier de réparations & Maintenance</h3>
                                <p class="slide-desc">Optimisez le confort de jeu de vos instruments : réglage complet d'action, courbure du manche (truss-rod), intonation, blindage électronique et refrettage.</p>
                                <div class="slide-feature-tags">
                                    <span>Refrettage</span>
                                    <span>Électronique</span>
                                    <span>Réglages Guitares</span>
                                </div>
                                <a href="#contact" class="btn btn-outline btn-sm">Voir l'Atelier &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <!-- Slide Photo 5 : Application Web -->
                    <div class="carousel-slide">
                        <div class="slide-photo-card">
                            <div class="slide-photo-media">
                                <img src="<?= BASE_URL ?>/assets/images/app.png" alt="Application Web de Cours" class="slide-photo-img">
                                <div class="slide-photo-gradient"></div>
                                <span class="slide-photo-pill">💻 Plateforme en Ligne</span>
                            </div>
                            <div class="slide-photo-info">
                                <span class="slide-tag-sub">Espace Membre 24/7</span>
                                <h3 class="slide-title">Ressources & Cours Numériques</h3>
                                <p class="slide-desc">Retrouvez chez vous tous vos supports de cours : livres PDF avec liseuse intégrée, partitions interactives, exercices guidés et vidéos de morceaux.</p>
                                <div class="slide-feature-tags">
                                    <span>Livres PDF</span>
                                    <span>Vidéos Pas à Pas</span>
                                    <span>Accès Illimité</span>
                                </div>
                                <a href="#app" class="btn btn-outline btn-sm">Accéder à l'Espace Web &rarr;</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Flèches de navigation du carrousel -->
            <button class="carousel-arrow carousel-arrow-prev" id="sliderPrev" aria-label="Photo précédente">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </button>
            <button class="carousel-arrow carousel-arrow-next" id="sliderNext" aria-label="Photo suivante">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M9 18l6-6-6-6" />
                </svg>
            </button>

            <!-- Points indicateurs (dots) -->
            <div class="carousel-dots" id="sliderDots"></div>
        </div>
    </section>

    <!-- SECTION APPLICATION WEB (CARTES PHOTOS DES OUTILS NUMERIQUES) -->
    <section id="app" class="section section-app">
        <div class="section-header">
            <span class="section-badge">💻 Nouveau ! l'application Web</span>
            <h2>La Plateforme Numérique</h2>
            <p class="section-subtitle">Accédez à votre espace membre complet et sécurisé pour apprendre et progresser depuis chez vous.</p>
        </div>

        <div class="cards-grid">
            <div class="photo-card">
                <div class="photo-card-top photo-card-top-sm">
                    <img src="<?= BASE_URL ?>/assets/images/livre.png" alt="Livres PDF et Partitions" class="photo-card-img">
                    <div class="photo-card-overlay"></div>
                    <span class="photo-card-badge">Bibliothèque</span>
                    <h3 class="photo-card-overlay-title">Livres & Méthodes PDF</h3>
                </div>
                <div class="photo-card-bottom">
                    <p class="photo-card-text">Liseuse PDF interactive intégrée avec méthodes, partitions, tablatures et exemples vidéo synchronisés.</p>
                    <div class="photo-card-footer">
                        <a href="<?= BASE_URL ?>/login" class="btn btn-outline btn-block">Consulter les Livres</a>
                    </div>
                </div>
            </div>

            <div class="photo-card">
                <div class="photo-card-top photo-card-top-sm">
                    <img src="<?= BASE_URL ?>/assets/images/videos.png" alt="Vidéos Explicatives de Morceaux" class="photo-card-img">
                    <div class="photo-card-overlay"></div>
                    <span class="photo-card-badge">Tutoriels</span>
                    <h3 class="photo-card-overlay-title">Vidéos de Morceaux</h3>
                </div>
                <div class="photo-card-bottom">
                    <p class="photo-card-text">Décorticage pas à pas des plus grands morceaux en vidéo avec schémas de doigtés et astuces de jeu.</p>
                    <div class="photo-card-footer">
                        <a href="<?= BASE_URL ?>/login" class="btn btn-outline btn-block">Voir les Vidéos</a>
                    </div>
                </div>
            </div>

            <div class="photo-card">
                <div class="photo-card-top photo-card-top-sm">
                    <img src="<?= BASE_URL ?>/assets/images/harmonie.jpg" alt="Harmonie et Composition" class="photo-card-img">
                    <div class="photo-card-overlay"></div>
                    <span class="photo-card-badge">Théorie & Création</span>
                    <h3 class="photo-card-overlay-title">Harmonie & Composition</h3>
                </div>
                <div class="photo-card-bottom">
                    <p class="photo-card-text">Modules pour comprendre les grilles d'accords, les gammes, l'improvisation et la composition.</p>
                    <div class="photo-card-footer">
                        <a href="<?= BASE_URL ?>/login" class="btn btn-outline btn-block">Étudier l'Harmonie</a>
                    </div>
                </div>
            </div>

            <div class="photo-card">
                <div class="photo-card-top photo-card-top-sm">
                    <img src="<?= BASE_URL ?>/assets/images/atelier1.jpg" alt="Tutoriels Entretien Guitare" class="photo-card-img">
                    <div class="photo-card-overlay"></div>
                    <span class="photo-card-badge">Atelier Maison</span>
                    <h3 class="photo-card-overlay-title">Tutos Entretien Guitare</h3>
                </div>
                <div class="photo-card-bottom">
                    <p class="photo-card-text">Guides pratiques et conseils pour entretenir votre instrument à la maison sans l'endommager.</p>
                    <div class="photo-card-footer">
                        <a href="<?= BASE_URL ?>/login" class="btn btn-outline btn-block">Accéder aux Tutos</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION TARIFS RECAPITULATIF (MODERNE ET SIMPLE) -->
    <section id="tarifs" class="section section-tarifs">
        <div class="section-header">
            <span class="section-badge">🏷️ Formules & Services</span>
            <h2>Grille Tarifaire Complète</h2>
            <p class="section-subtitle">Des tarifs clairs et accessibles pour tous les musiciens. Choisissez la formule qui correspond à votre pratique.</p>
        </div>

        <div class="pricing-grid">

            <!-- CARTE TARIF 1 : COURS SANS ADHESION -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <span class="pricing-pill">À la séance</span>
                    <h3 class="pricing-name">Cours à l'Unité</h3>
                    <p class="pricing-target">Sans engagement</p>
                    <div class="pricing-price-box">
                        <div class="price-main">
                            <span class="price-value">20</span>
                            <span class="price-unit">€</span>
                        </div>
                        <span class="price-period">/ séance (60 min)</span>
                    </div>
                    <p class="pricing-note">Guitare, Basse, Batterie...</p>
                </div>

                <div class="pricing-body">
                    <ul class="pricing-features">
                        <li><span class="pricing-check">✓</span> <strong>60 minutes</strong> de cours individuel</li>
                        <li><span class="pricing-check">✓</span> Tous niveaux & tous styles</li>
                        <li><span class="pricing-check">✓</span> Partitions et supports inclus</li>
                        <li><span class="pricing-check">✓</span> Sans engagement à l'année</li>
                    </ul>
                </div>

                <div class="pricing-footer">
                    <a href="#contact" class="btn btn-outline btn-block">Réserver une séance</a>
                </div>
            </div>

            <!-- CARTE TARIF 2 : FORMULE ANNUELLE (FEATURED) -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <span class="pricing-pill pill-gold">Avec Adhésion</span>
                    <h3 class="pricing-name">A l'année</h3>
                    <p class="pricing-target">Suivi & Avantages</p>
                    <div class="pricing-price-box">
                        <div class="price-main">
                            <span class="price-value">95</span>
                            <span class="price-unit">€</span>
                        </div>
                        <span class="price-period">/ pour l'année</span>
                    </div>
                    <p class="pricing-note">Cours de 45 min</p>
                </div>

                <div class="pricing-body">
                    <ul class="pricing-features">
                        <li><span class="pricing-check">✓</span> <strong>6 cours compris</strong> avec l'adhésion</li>
                        <li><span class="pricing-check">✓</span> <strong>15 €</strong> à partir du 7ème cours</li>
                        <li><span class="pricing-check">✓</span> Réglage ou prêt d'une guitare inclus</li>
                        <li><span class="pricing-check">✓</span> Accès à l'application web à 25€ par an.</li>
                    </ul>
                </div>

                <div class="pricing-footer">
                    <a href="#contact" class="btn btn-primary btn-block">S'inscrire à l'année</a>
                </div>
            </div>

            <!-- CARTE TARIF 3 : STUDIO D'ENREGISTREMENT -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <span class="pricing-pill">Production</span>
                    <h3 class="pricing-name">Studio & Mixage</h3>
                    <p class="pricing-target">Enregistrement</p>
                    <div class="pricing-price-box">
                        <div class="price-main">
                            <span class="price-value">30</span>
                            <span class="price-unit">€</span>
                        </div>
                        <span class="price-period">/ heure</span>
                    </div>
                    <p class="pricing-note">Envoi de pistes possible</p>
                </div>

                <div class="pricing-body">
                    <ul class="pricing-features">
                        <li><span class="pricing-check">✓</span> <strong>Prise de son</strong> voix & instruments</li>
                        <li><span class="pricing-check">✓</span> Mixage & arrangements personnalisés</li>
                        <li><span class="pricing-check">✓</span> Mastering audio professionnel</li>
                        <li><span class="pricing-check">✓</span> Exportation haute fidélité</li>
                    </ul>
                </div>

                <div class="pricing-footer">
                    <a href="#contact" class="btn btn-outline btn-block">Prix ajustable selon projet</a>
                </div>
            </div>

            <!-- CARTE TARIF 4 : APPLICATION WEB -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <span class="pricing-pill">En Ligne</span>
                    <h3 class="pricing-name">Appli Web</h3>
                    <p class="pricing-target">Espace Pédagogique</p>
                    <div class="pricing-price-box">
                        <div class="price-main">
                            <span class="price-value">49</span>
                            <span class="price-unit">€</span>
                        </div>
                        <span class="price-period">pour une année</span>
                    </div>
                    <p class="pricing-note">formule 100% web</p>
                </div>

                <div class="pricing-body">
                    <ul class="pricing-features">
                        <li><span class="pricing-check">✓</span> <strong>Accès illimité 24h/24 et 7j/7</strong></li>
                        <li><span class="pricing-check">✓</span> Vidéos explicatives de morceaux</li>
                        <li><span class="pricing-check">✓</span> Livres et méthodes interactives PDF</li>
                        <li><span class="pricing-check">✓</span> Suivi favoris & partitions</li>
                    </ul>
                </div>

                <div class="pricing-footer">
                    <?php if (\Core\Session::has('user_id')): ?>
                        <a href="<?= BASE_URL ?>/accueil" class="btn btn-outline btn-block">Accéder à mes cours</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/register" class="btn btn-outline btn-block">Demander un accès</a>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="pricing-notice">
            <p>💡 <em>Les tarifs sont indiqués à titre associatif. N'hésitez pas à nous contacter pour tout renseignement ou devis sur-mesure.</em></p>
        </div>
    </section>

    <!-- SECTION CONTACT & ACCES -->
    <section id="contact" class="section section-contact">
        <div class="section-header">
            <span class="section-badge">📍 Nous Trouver</span>
            <h2>Contact & Localisation</h2>
            <p class="section-subtitle">Venez nous rencontrer au magasin ou contactez l'équipe Accent Musique.</p>
        </div>

        <div class="contact-grid">

            <!-- Carte Infos Contact -->
            <div class="contact-card contact-info-card">
                <h3 class="contact-card-title">Coordonnées de l'Association</h3>
                <p class="contact-card-intro">Une question sur les cours, le studio ou une réparation ? Contactez-nous facilement :</p>

                <div class="contact-details-list">
                    <div class="contact-item">
                        <div class="contact-item-icon">📍</div>
                        <div class="contact-item-content">
                            <strong>Adresse</strong>
                            <p>2 Av. du Camp Grand, 66340 Osséja</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item-icon">✉️</div>
                        <div class="contact-item-content">
                            <strong>E-mail</strong>
                            <p><a href="mailto:contact@accentmusique.fr">contact@accentmusique.fr</a></p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item-icon">🕒</div>
                        <div class="contact-item-content">
                            <strong>Horaires</strong>
                            <p>Du Mardi au Samedi – de 16:00 à 18:30</p>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-item-icon">🎸</div>
                        <div class="contact-item-content">
                            <strong>Association</strong>
                            <p>Accent Musique – Espace d'apprentissage & de création</p>
                        </div>
                    </div>
                </div>

                <div class="contact-actions">
                    <a href="https://maps.google.com/?q=2+Av.+du+Camp+Grand,+66340+Oss%C3%A9ja" target="_blank" rel="noopener" class="btn btn-outline">🗺️ Voir l'itinéraire</a>
                </div>
            </div>

            <!-- Carte Google Maps -->
            <div class="contact-card contact-map-card">
                <h3 class="contact-card-title">Plan de Situation</h3>
                <div class="map-container">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d5890.66142683111!2d1.9687169999999998!3d42.420695!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12a57b21fa6962f7%3A0x9dc718f18a016e79!2s2%20Av.%20du%20Camp%20Grand%2C%2066340%20Oss%C3%A9ja!5e0!3m2!1sfr!2sfr!4v1787496590945!5m2!1sfr!2sfr"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        title="Plan Accent Musique Osséja">
                    </iframe>
                </div>
                <p class="map-caption">📍 2 Av. du Camp Grand, 66340 Osséja – Stationnement à proximité.</p>
            </div>

        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand-col">
                <a href="<?= BASE_URL ?>/" class="footer-logo">
                    <span class="brand-accent">ACCENT</span><span class="brand-sub">MUSIQUE</span>
                </a>
                <p class="footer-desc">Association musicale à Osséja. Cours d'instruments, studio d'enregistrement, réparations et plateforme d'apprentissage en ligne.</p>
            </div>

            <div class="footer-links-col">
                <h4 class="footer-heading">Navigation</h4>
                <nav class="footer-nav">
                    <a href="#magasin">Magasin & Atelier</a>
                    <a href="#app">Application Web</a>
                    <a href="#tarifs">Tarifs & Formules</a>
                    <a href="#contact">Contact & Accès</a>
                </nav>
            </div>

            <div class="footer-links-col">
                <h4 class="footer-heading">Espace Membre</h4>
                <nav class="footer-nav">
                    <?php if (\Core\Session::has('user_id')): ?>
                        <a href="<?= BASE_URL ?>/accueil">Mon Espace</a>
                        <a href="<?= BASE_URL ?>/favorites">Mes Favoris</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login">Connexion</a>
                        <a href="<?= BASE_URL ?>/register">Créer un compte</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/mentions-legales">Mentions légales</a>
                    <a href="<?= BASE_URL ?>/cgu">CGU</a>
                </nav>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Accent‑Musique – Tous droits réservés.</p>
            <div class="footer-legal-links">
                <a href="<?= BASE_URL ?>/mentions-legales">Mentions légales</a>
                <span>•</span>
                <a href="<?= BASE_URL ?>/cgu">Conditions Générales d'Utilisation</a>
            </div>
            <p class="footer-subtext">Espace d'apprentissage & de création musicale sécurisé.</p>
        </div>
    </footer>

    <script src="<?= BASE_URL ?>/assets/js/landing.js"></script>

</body>

</html>
