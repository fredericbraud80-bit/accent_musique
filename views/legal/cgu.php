<?php
use Core\Security;
?>

<div class="main-container legal-container">

    <!-- En-tête de la page -->
    <div class="legal-page-header">
        <div class="legal-badge-draft">
            <span>📝 CGU – CGV </span>
        </div>
        <h1 class="page-title">Conditions Générales d'Utilisation (CGU)</h1>
        <p class="page-subtitle">Règles d'accès, d'utilisation des services et des ressources pédagogiques Accent Musique.</p>

        <!-- Onglets de navigation Légale -->
        <div class="legal-nav-tabs">
            <a href="<?= BASE_URL ?>/mentions-legales" class="legal-tab-btn">
                <span>⚖️</span> Mentions Légales
            </a>
            <a href="<?= BASE_URL ?>/cgu" class="legal-tab-btn active">
                <span>📄</span> Conditions Générales d'Utilisation (CGU)
            </a>
        </div>
    </div>

    

    <!-- ARTICLE 1 : OBJET -->
    <div class="legal-card">
        <h2><span>📌</span> Article 1 – Objet et Champ d'Application</h2>
        <p>
            Les présentes Conditions Générales d'Utilisation (ci-après désignées « <strong>CGU</strong> ») ont pour objet d'encadrer les conditions juridiques d'accès et d'utilisation de la plateforme numérique <strong>Accent Musique</strong> (accessible à l'adresse <a href="<?= BASE_URL ?>"><?= BASE_URL ?></a>), éditée par l'association <strong>Accent Musique</strong>, sise au 2 Avenue du Camp Grand, 66340 Osséja.
        </p>
        <p>
            La plateforme propose un espace d'apprentissage musical en ligne combinant liseuse de méthodes et partitions PDF, tutoriels vidéo, grilles harmoniques, fiches techniques et espace adhérents.
        </p>
    </div>

    <!-- ARTICLE 2 : ACCEPTATION DES CGU -->
    <div class="legal-card">
        <h2><span>🤝</span> Article 2 – Acceptation et Opposabilité</h2>
        <p>
            Toute consultation du site ou création de compte au sein de l'espace membre implique l'acceptation expresse, pleine et sans réserve des présentes CGU par l'Utilisateur.
        </p>
        <p>
            Lors de son inscription sur le site, l'Utilisateur confirme avoir pris connaissance des présentes CGU. L'association se réserve le droit de modifier les CGU à tout moment ; les conditions applicables sont celles en vigueur à la date de connexion de l'utilisateur.
        </p>
    </div>

    <!-- ARTICLE 3 : INSCRIPTION ET GESTION DU COMPTE -->
    <div class="legal-card">
        <h2><span>👤</span> Article 3 – Inscription, Validation et Accès à l'Espace Membre</h2>
        
        <h3>3.1. Création de compte</h3>
        <p>
            L'accès aux fonctionnalités complètes (cours, bibliothèque PDF, vidéos) nécessite la création d'un compte personnel. L'utilisateur s'engage à fournir des informations véridiques, complètes et à jour (nom complet, adresse e-mail valide).
        </p>

        <h3>3.2. Validation par l'administrateur</h3>
        <p>
            La création d'un compte élève fait l'objet d'une <strong>validation préalable par les administrateurs</strong> ou enseignants de l'association Accent Musique, garantissant l'accès aux seuls membres et élèves inscrits.
        </p>

        <h3>3.3. Confidentialité des identifiants</h3>
        <p>
            Les identifiants et mots de passe sont strictement personnels et confidentiels. L'utilisateur est seul responsable de toute activité effectuée depuis son compte. Tout prêt, cession, partage ou mutualisation d'identifiants à des tiers est formellement interdit.
        </p>

        <h3>3.4. Durée de validité de l'accès</h3>
        <p>
            L'accès aux ressources est octroyé pour la durée de l'adhésion ou de la formule pédagogique souscrite auprès de l'association, renouvelable chaque année.
        </p>
    </div>

    <!-- ARTICLE 4 : DESCRIPTION DES SERVICES -->
    <div class="legal-card">
        <h2><span>🎵</span> Article 4 – Services et Ressources Pédagogiques</h2>
        <p>Accent Musique met à la disposition de ses adhérents validés un ensemble d'outils et de supports pédagogiques, notamment :</p>
        <ul>
            <li><strong>Liseuse interactive de méthodes :</strong> Consultation en ligne fluide de livrets PDF de cours, partitions, tablatures et schémas d'harmonie.</li>
            <li><strong>Vidéos pédagogiques pas à pas :</strong> Accès aux explications vidéo de morceaux, plans de jeu et théorie musicale.</li>
            <li><strong>Modules d'harmonie et de composition :</strong> Fiches et grilles d'accords pour l'apprentissage du rythme, du mixage et de l'arrangement.</li>
            <li><strong>Tutoriels et fiches d'atelier :</strong> Conseils de réglage, entretien et réparation d'instruments.</li>
            <li><strong>Système de favoris :</strong> Sauvegarde personnalisée des cours et morceaux préférés.</li>
        </ul>
        <p>L'association se réserve le droit de faire évoluer, d'enrichir ou de modifier les contenus mis à disposition à tout moment.</p>
    </div>

    <!-- ARTICLE 5 : PROPRIÉTÉ INTELLECTUELLE ET LICENCE D'USAGE -->
    <div class="legal-card">
        <h2><span>🛡️</span> Article 5 – Propriété Intellectuelle et Licence d'Usage</h2>
        <p>
            Tous les contenus hébergés ou rendus accessibles via la plateforme (textes, vidéos, fichiers PDF, partitions, méthodes, pistes audio, marques et logos) sont protégés par les lois internationales et françaises sur le droit d'auteur et la propriété intellectuelle.
        </p>
        <p>
            L'association Accent Musique concède à l'élève une <strong>licence d'utilisation personnelle, privée, non exclusive et non transférable</strong> pour la durée de son inscription.
        </p>
        <p><strong>Il est strictement interdit de :</strong></p>
        <ul>
            <li>Diffuser, copier, reproduire ou partager tout ou partie des supports pédagogiques sur Internet (réseaux sociaux, plateformes de partage, forums, etc.).</li>
            <li>Vendre, revendre ou louer les documents et vidéos fournis par l'association.</li>
            <li>Extraire de manière automatisée (scraping, aspiration de site) les fichiers ou données de la plateforme.</li>
            <li>Contourner les dispositifs techniques de protection des contenus ou de gestion des droits numériques.</li>
        </ul>
    </div>

    <!-- ARTICLE 6 : OBLIGATIONS ET COMPORTEMENT DE L'UTILISATEUR -->
    <div class="legal-card">
        <h2><span>🧭</span> Article 6 – Engagements de l'Utilisateur</h2>
        <p>L'Utilisateur s'engage à utiliser le site et ses fonctionnalités dans le respect des lois et règlements en vigueur. Il s'interdit notamment de :</p>
        <ul>
            <li>Porter atteinte à la sécurité, à l'intégrité ou à la disponibilité des serveurs et réseaux de la plateforme.</li>
            <li>Introduire des virus, chevaux de Troie ou tout autre code malveillant.</li>
            <li>Tenter d'accéder sans droit aux comptes d'autres membres ou aux données réservées à l'administration.</li>
            <li>Usurper l'identité d'un autre utilisateur ou d'un intervenant de l'association.</li>
        </ul>
    </div>

    <!-- ARTICLE 7 : DISPONIBILITÉ ET MAINTENANCE -->
    <div class="legal-card">
        <h2><span>⚡</span> Article 7 – Disponibilité du Service et Maintenance</h2>
        <p>
            Accent Musique s'efforce de maintenir la plateforme accessible 7 jours sur 7 et 24 heures sur 24.
        </p>
        <p>
            Toutefois, l'accès peut être suspendu de manière temporaire et sans préavis pour des opérations de maintenance, de mise à niveau de sécurité ou d'amélioration technique. Accent Musique ne saurait être tenue pour responsable des éventuelles interruptions de service ni des conséquences qui pourraient en résulter pour l'utilisateur.
        </p>
    </div>

    <!-- ARTICLE 8 : SUSPENSION ET CLÔTURE DE COMPTE -->
    <div class="legal-card">
        <h2><span>🛑</span> Article 8 – Suspension et Résiliation</h2>
        <p>
            En cas de non-respect des présentes CGU (notamment en cas de partage frauduleux de compte, de piratage ou de diffusion illicite de contenus pédagogiques), Accent Musique se réserve le droit de <strong>suspendre ou de clôturer définitivement le compte</strong> de l'utilisateur fautif, de plein droit et sans mise en demeure préalable, sans remboursement de l'adhésion et sans préjudice de poursuites judiciaires.
        </p>
    </div>

    <!-- ARTICLE 9 : DONNÉES PERSONNELLES -->
    <div class="legal-card">
        <h2><span>🔒</span> Article 9 – Protection des Données Personnelles</h2>
        <p>
            Les données personnelles collectées lors de l'inscription et de l'utilisation du site sont traitées conformément à la réglementation RGPD en vigueur. Pour plus de détails sur la nature des données traitées, les durées de conservation et vos droits, veuillez vous référer à nos <a href="<?= BASE_URL ?>/mentions-legales">Mentions Légales & Politique de Confidentialité</a>.
        </p>
    </div>

    <!-- ARTICLE 10 : ÉVOLUTION DES CGU -->
    <div class="legal-card">
        <h2><span>🔄</span> Article 10 – Modification des CGU</h2>
        <p>
            Accent Musique se réserve la faculté de modifier à tout moment les termes des présentes Conditions Générales d'Utilisation. Les utilisateurs seront avertis de toute modification substantielle lors de leur prochaine connexion.
        </p>
    </div>

    <!-- ARTICLE 11 : DROIT APPLICABLE ET LITIGES -->
    <div class="legal-card">
        <h2><span>⚖️</span> Article 11 – Droit Applicable et Résolution des Différends</h2>
        <p>
            Les présentes CGU sont régies et interprétées conformément au <strong>droit français</strong>.
        </p>
        <p>
            En cas de contestation ou de litige relatif à l'interprétation ou à l'exécution des présentes, les parties s'engagent à rechercher préalablement une conciliation amiable. À défaut d'accord amiable, compétence expresse est attribuée aux tribunaux compétents du ressort de Perpignan (Pyrénées-Orientales).
        </p>
    </div>

    <!-- ARTICLE 12 : UTILISATION DE MORCEAUX ET ŒUVRES MUSICALES PROTÉGÉES -->
<div class="legal-card">
    <h2><span>🎼</span> Article 12 – Utilisation de Morceaux et Œuvres Musicales Protégées</h2>
    <p>
        Dans le cadre de ses activités pédagogiques, la plateforme Accent Musique propose des contenus incluant 
        l’étude, l’analyse, l’interprétation ou la démonstration de morceaux existants protégés par le droit d’auteur.
    </p>

    <p>
        Les extraits musicaux présents dans les vidéos, partitions, tutoriels ou documents pédagogiques sont utilisés 
        exclusivement à des fins d’enseignement, conformément aux exceptions prévues par le Code de la propriété 
        intellectuelle. Lorsque des œuvres sont interprétées, rejouées ou réarrangées par les enseignants, ces 
        enregistrements constituent des créations pédagogiques originales réalisées par l’association.
    </p>

    <p><strong>L’Utilisateur reconnaît que :</strong></p>
    <ul>
        <li>les œuvres originales restent la propriété exclusive de leurs auteurs et ayants droit ;</li>
        <li>les extraits utilisés sur la plateforme ne peuvent être réexploités, copiés, enregistrés, rediffusés ou publiés en dehors de l’espace membre ;</li>
        <li>toute extraction, reproduction ou diffusion des contenus pédagogiques (vidéos, audio, partitions, méthodes, analyses) est strictement interdite ;</li>
        <li>les interprétations réalisées par les enseignants sont protégées et appartiennent à l’association Accent Musique.</li>
    </ul>

    <p>
        L’association Accent Musique s’engage à n’utiliser que des extraits strictement nécessaires à l’enseignement 
        et à respecter les règles applicables en matière de droit d’auteur et de propriété intellectuelle.
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
            <a href="<?= BASE_URL ?>/mentions-legales" class="btn btn-primary">
                Consulter les Mentions Légales &rarr;
            </a>
        </div>
    </div>

</div>
