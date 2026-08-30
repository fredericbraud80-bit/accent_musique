<?php
use Core\Security;
?>

<?php 
$username = \Core\Security::sanitize(\Core\Session::get('user_name'));
$user['license_expires_at'] = Core\Session::get('user_license_expires_at');
// Renommage des catégories
$displayNames = [
    'cours'        => 'Cours & Leçons de Guitare',
    'harmonie'     => 'Harmonie Musicale',
    'composition'  => 'Techniques de Composition',
    'mixage'       => 'Techniques de Mixage',
    'livres'       => 'Bibliothèque',
    'reparations'  => 'Réparations & Entretien'
];

// Ordre personnalisé
$ordre = ['cours', 'livres', 'composition', 'mixage','harmonie' , 'reparations'];

// Tri AVANT l'affichage
usort($categories, function($a, $b) use ($ordre) {
    return array_search($a['slug'], $ordre) <=> array_search($b['slug'], $ordre);
});
?>

<div class="main-container">

    <!-- En-tête -->
<div class="page-header">
    <h1 class="page-title">Accueil</h1>
    <p class="page-subtitle">
        👋 Bonjour <?= $username; ?> !
    </p>
    <p class="page-subtitle">
        🎫 Licence valable jusqu'au :
        <strong><?= date('d/m/Y', strtotime($user['license_expires_at'])) ?></strong>
    </p>
</div>

   
    <!-- Grille des catégories -->
    <div class="course-grid">

        <?php foreach ($categories as $category): ?>

            <a href="<?= BASE_URL ?>/categorie/<?= $category['slug'] ?>" style="text-decoration:none;">

                <div class="course-card" style="cursor:pointer; transition:transform .2s ease;">
                    
                    <!-- Icône -->
                    <div style="font-size:2.5rem; margin-bottom:1rem;">
                        <?php
                            $icons = [
                                'cours' => '📚',
                                'harmonie' => '🎵',
                                'composition' => '✍️',
                                'mixage' => '🎚️',
                                'livres' => '📖',
                                'reparations' => '🔧'
                            ];
                            echo $icons[$category['slug']] ?? '📚';
                        ?>
                    </div>

                    <!-- Nom de la catégorie -->
                    <h2 class="course-card-title">
                        <?= $displayNames[$category['slug']] ?? Security::sanitize($category['name']) ?>
                    </h2>


                    <!-- Footer de la carte -->
                    <div style="
                        margin-top:1rem;
                        padding-top:1rem;
                        border-top:1px solid var(--border-color);
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                    ">
                        <span class="course-desc" style="font-size:0.85rem;">
                            <?php 
                                $count = $category['course_count'] ?? 0;
                                echo $count . ' cours';
                            ?>
                        </span>

                        <span style="color:var(--primary); font-size:1.2rem; transition:transform .2s ease;">
                            →
                        </span>
                    </div>

                </div>

            </a>

        <?php endforeach; ?>

    </div>

</div>
