<?php

use Core\Security;
?>

<div class="main-container legal-container">

    <!-- En-tête de la page -->
    <div class="legal-page-header">
        <div class="legal-badge-draft">
            <span>📝 Mentions légales</span>
        </div>
        <h1 class="page-title">Mentions Légales</h1>
        <p class="page-subtitle">Informations juridiques, éditeur du site et politique de confidentialité.</p>

        <!-- Onglets de navigation Légale -->
        <div class="legal-nav-tabs">
            <a href="<?= BASE_URL ?>/mentions-legales" class="legal-tab-btn active">
                <span>⚖️</span> Mentions Légales
            </a>
            <a href="<?= BASE_URL ?>/cgu" class="legal-tab-btn">
                <span>📄</span> Conditions Générales d'Utilisation (CGU)
            </a>
        </div>
    </div>



    <!-- 1. IDENTIFICATION ET ÉDITEUR DU SITE -->
    <div class="legal-card">
        <h2><span>🏢</span> 1. Éditeur du Site</h2>
        <p>Le site Internet <strong>Accent Musique</strong> (accessible à l'adresse <a href="<?= BASE_URL ?>"><?= BASE_URL ?></a>) est édité par l'association régie par la loi du 1er juillet 1901 :</p>

        <ul style="list-style: none; padding-left: 0;">
            <li><strong>Dénomination sociale :</strong> Association Accent Musique</li>

            <li><strong>Forme juridique :</strong> Association loi 1901 à but non lucratif</li>

            <li><strong>Siège social :</strong> 2 Avenue du Camp Grand, 66340 Osséja, France</li>

            <li><strong>Numéro RNA (Répertoire National des Associations) :</strong>
                W663001796</span>
            </li>

            <li><strong>Numéro SIRET / SIREN :</strong> 822 082 186 00013</li>

            <li><strong>Responsable de la publication :</strong>
                Frédéric Braud</span>
            </li>

            <li><strong>Téléphone :</strong>
                06 95 12 52 11</a>
            </li>

            <li><strong>Email de contact :</strong>
                <a href="mailto:contact@accentmusique.fr">contact@accentmusique.fr</a>
            </li>

            <li><strong>Site web :</strong>
                <a href="https://accentmusique.fr" target="_blank">accentmusique.fr</a>
            </li>

            <li><strong>Horaires d’ouverture :</strong><br>
                Mardi–Samedi : 16h00 – 18h30<br>
                Lundi & Dimanche : Fermé
            </li>

            <li><strong>Activité :</strong>
                Enseignement musical, cours de guitare et instruments, studio d'enregistrement,
                atelier de réparation/entretien, vente d'instruments de musique, mise à disposition
                de ressources pédagogiques en ligne.
            </li>
        </ul>

    </div>

    <!-- 2. HÉBERGEMENT DU SITE -->
<div class="legal-card">
    <h2><span>🌐</span> 2. Hébergement du Site</h2>
    <p>Le site web et sa base de données sont hébergés par :</p>
    <ul style="list-style: none; padding-left: 0;">
        <li><strong>Hébergeur :</strong> o2switch</li>
        <li><strong>Forme juridique :</strong> Société à responsabilité limitée (SARL)</li>
        <li><strong>Capital social :</strong> 100 000 €</li>
        <li><strong>SIRET :</strong> 510 909 807 00024</li>
        <li><strong>Adresse du siège :</strong> 222 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, France</li>
        <li><strong>Site Internet :</strong> 
            <a href="https://www.o2switch.fr" target="_blank">https://www.o2switch.fr</a>
        </li>
        <li><strong>Téléphone :</strong> 
            <a href="tel:+33444446040">04 44 44 60 40</a>
        </li>
    </ul>
</div>


    <!-- 3. PROPRIÉTÉ INTELLECTUELLE -->
    <div class="legal-card">
        <h2><span>⚖️</span> 3. Propriété Intellectuelle</h2>
        <p>
            L'ensemble des contenus présents sur le site <strong>Accent Musique</strong> — incluant, sans limitation, les méthodes pédagogiques, livres interactifs, partitions, tablatures, cours vidéo, grilles d'accords, textes, logos, visuels graphiques, photographies et sons — sont protégés par le Code de la propriété intellectuelle et par le droit d'auteur.
        </p>
        <p>
            Ces éléments sont la propriété exclusive de l'association <strong>Accent Musique</strong> et de leurs auteurs respectifs, sauf mention contraire explicite.
        </p>
        <p>
            Toute reproduction, représentation, diffusion, commercialisation, adaptation ou exploitation, totale ou partielle, par quelque procédé que ce soit, sans autorisation écrite expresse et préalable de l'association Accent Musique, est strictement interdite et constituerait une contrefaçon sanctionnée par les articles L.335-2 et suivants du Code de la propriété intellectuelle.
        </p>
        <p>
            Les élèves et utilisateurs inscrits bénéficient d'un droit d'accès et de consultation strictly privé, personnel et non transférable dans le cadre de leur apprentissage individuel.
        </p>
    </div>

    <!-- 4. DONNÉES PERSONNELLES & RGPD -->
    <div class="legal-card">
        <h2><span>🔒</span> 4. Protection des Données Personnelles (RGPD)</h2>
        <p>
            L'association <strong>Accent Musique</strong> s'engage à ce que la collecte et le traitement de vos données soient conformes au <em>Règlement Général sur la Protection des Données (RGPD 2016/679)</em> et à la loi <em>« Informatique et Libertés » du 6 janvier 1978 modifiée</em>.
        </p>

        <h3>4.1. Responsable du traitement</h3>
        <p>Le responsable du traitement des données est l'association Accent Musique, joignable à l'adresse e-mail : <a href="mailto:contact@accentmusique.fr">contact@accentmusique.fr</a>.</p>

        <h3>4.2. Données collectées et finalités</h3>
        <p>Dans le cadre de l'utilisation de l'espace membre et de l'apprentissage en ligne, nous collectons uniquement les données strictement nécessaires :</p>
        <ul>
            <li><strong>Identité :</strong> Nom, prénom, adresse email (utilisés pour la création du compte, l'accès sécurisé et la communication).</li>
            <li><strong>Sécurité :</strong> Mot de passe chiffré (aucun mot de passe n'est stocké en clair).</li>
            <li><strong>Pédagogie :</strong> Suivi des favoris et des cours consultés afin de personnaliser l'expérience d'apprentissage.</li>
        </ul>

        <h3>4.3. Base légale et conservation</h3>
        <p>Le traitement des données repose sur l'exécution des prestations pédagogiques et associatives (adhésion et accès élève). Les données sont conservées pendant toute la durée active de l'adhésion ou du compte élève, puis archivées conformément aux délais de prescription légaux.</p>

        <h3>4.4. Droits des utilisateurs</h3>
        <p>Conformément à la réglementation, vous disposez des droits suivants concernant vos données à caractère personnel :</p>
        <ul>
            <li>Droit d'accès et de rectification de vos informations.</li>
            <li>Droit à l'effacement de votre compte (« droit à l'oubli »).</li>
            <li>Droit à la limitation du traitement et à la portabilité des données.</li>
            <li>Droit d'opposition pour motif légitime.</li>
        </ul>
        <p>Pour exercer l'un de ces droits, vous pouvez contacter l'association à tout moment par email à : <a href="mailto:contact@accentmusique.fr">contact@accentmusique.fr</a> ou par voie postale au siège de l'association.</p>
    </div>

    <!-- 5. COOKIES ET TRACEURS -->
    <div class="legal-card">
        <h2><span>🍪</span> 5. Cookies et Traceurs</h2>
        <p>
            Le site <strong>Accent Musique</strong> utilise exclusivement des <strong>cookies techniques essentiels</strong> indispensables au bon fonctionnement de la plateforme :
        </p>
        <ul>
            <li><strong>Session utilisateur :</strong> Maintien de votre connexion sécurisée à l'espace élève.</li>
            <li><strong>Sécurité CSRF :</strong> Protection contre les attaques et soumissions de formulaires frauduleuses.</li>
            <li><strong>Préférence de thème :</strong> Sauvegarde de votre choix d'affichage (mode sombre ou clair).</li>
        </ul>
        <p>
            Notre site n'utilise aucun cookie publicitaire tiers ni outil de pistage invasif. Conformément aux recommandations de la CNIL, ces cookies techniques indispensables sont exemptés de consentement préalable.
        </p>
    </div>

    <!-- 6. LIMITATION DE RESPONSABILITÉ -->
    <div class="legal-card">
        <h2><span>⚠️</span> 6. Limitation de Responsabilité</h2>
        <p>
            L'association Accent Musique met en œuvre tous les moyens raisonnables pour fournir des informations exactes et un service disponible 24h/24 et 7j/7. Toutefois, l'association ne saurait être tenue pour responsable en cas de force majeure, d'indisponibilité momentanée des réseaux de télécommunication ou de difficultés techniques indépendantes de sa volonté.
        </p>
        <p>
            Le site peut contenir des liens vers des ressources ou plateformes externes (ex : YouTube, Google Maps). Accent Musique n'exerce aucun contrôle sur le contenu de ces sites tiers et décline toute responsabilité quant à leur accessibilité ou leur contenu.
        </p>
    </div>

    <!-- 7. CONTACT & JURIDICTION -->
    <div class="legal-card">
        <h2><span>📬</span> 7. Contact & Droit Applicable</h2>
        <p>Pour toute question relative aux présentes mentions légales ou au fonctionnement du site, vous pouvez nous joindre :</p>
        <ul style="list-style: none; padding-left: 0;">
            <li><strong>Par e-mail :</strong> <a href="mailto:contact@accentmusique.fr">contact@accentmusique.fr</a></li>
            <li><strong>Par courrier :</strong> Accent Musique, 2 Avenue du Camp Grand, 66340 Osséja, France</li>
            <li><strong>Au magasin :</strong> Du mardi au samedi de 16h00 à 18h30</li>
        </ul>
        <p>Les présentes mentions légales sont soumises au droit français. En cas de litige, les tribunaux français compétents du ressort de Perpignan seront seuls habilités.</p>
    </div>


    <div class="legal-card">
    <h2><span>🎼</span> 8. Œuvres Musicales et Contenus Protégés</h2>
    <p>
        Certains contenus pédagogiques proposés sur le site peuvent inclure des extraits de morceaux existants 
        protégés par le droit d'auteur. Ces extraits sont utilisés exclusivement dans un cadre pédagogique et 
        conformément aux exceptions prévues par le Code de la propriété intellectuelle.
    </p>
    <p>
        Toute reproduction, diffusion ou réutilisation de ces extraits en dehors de la plateforme est strictement interdite.
    </p>
</div>

<div class="legal-card">
    <h2><span>🤝</span> 9. Médiation de la Consommation</h2>
    <p>
        Conformément à l'article L.612-1 du Code de la consommation, tout consommateur a le droit de recourir 
        gratuitement à un médiateur de la consommation en vue de la résolution amiable d'un litige.
    </p>
    <p>
        L'association Accent Musique n'est pas encore affiliée à un médiateur agréé. Dans l'attente, toute demande 
        de médiation peut être adressée à la plateforme européenne de règlement des litiges :
        <a href="https://ec.europa.eu/consumers/odr" target="_blank">https://ec.europa.eu/consumers/odr</a>.
    </p>
</div>

<div class="legal-card">
    <h2><span>🌍</span> 10. Règlement des Litiges en Ligne (RLL)</h2>
    <p>
        Conformément au règlement européen n°524/2013, les utilisateurs peuvent recourir à la plateforme de 
        règlement en ligne des litiges (RLL) accessible à l'adresse suivante :
        <a href="https://ec.europa.eu/consumers/odr" target="_blank">https://ec.europa.eu/consumers/odr</a>.
    </p>
</div>

<div class="legal-card">
    <h2><span>🔐</span> 11. Sécurité et Disponibilité du Site</h2>
    <p>
        Accent Musique met en œuvre des mesures techniques raisonnables pour assurer la sécurité du site et 
        la protection des données. Toutefois, l'association ne peut garantir une disponibilité continue et 
        décline toute responsabilité en cas d'interruption temporaire liée à la maintenance ou à des contraintes techniques.
    </p>
</div>

<div class="legal-card">
    <h2><span>🏷️</span> 12. Marques et Identité Visuelle</h2>
    <p>
        Le nom <strong>Accent Musique</strong>, son logo et son identité visuelle sont des éléments protégés. 
        Toute utilisation non autorisée est strictement interdite.
    </p>
</div>



    <!-- Actions du bas -->
    <div class="legal-actions-bar">
        <a href="<?= BASE_URL ?>/" class="btn btn-outline">
            &larr; Retour à l'accueil
        </a>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            <button onclick="window.print()" class="btn btn-outline" style="cursor:pointer;">
                🖨️ Imprimer cette page
            </button>
            <a href="<?= BASE_URL ?>/cgu" class="btn btn-primary">
                Consulter les CGU &rarr;
            </a>
        </div>
    </div>

</div>