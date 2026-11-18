<?php
// Petites fonctions partagées par toutes les pages.

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Échappe un texte avant de l'afficher (protection contre les failles XSS).
// À utiliser pour TOUT ce qui vient de la base ou de l'utilisateur.
function e(?string $texte): string
{
    return htmlspecialchars($texte ?? '', ENT_QUOTES, 'UTF-8');
}

// Découpe un texte multi-lignes en tableau, en ignorant les lignes vides.
function lignes(string $texte): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/', $texte))));
}

// Couleur de chaque catégorie (onglets des dossiers, étiquettes). Voir public/style.css.
const COULEURS_CATEGORIES = [
    'Entrée' => 'var(--orange)', 'Plat' => 'var(--bleu)', 'Dessert' => 'var(--jaune)',
    'Boisson' => 'var(--rouge)', 'Autre' => 'var(--gris)',
];

function couleur_categorie(string $categorie): string
{
    return COULEURS_CATEGORIES[$categorie] ?? 'var(--gris)';
}

// Petites icônes au trait (dessins repris du jeu d'icônes libre Lucide).
function icone(string $nom, int $taille = 20): string
{
    $dessins = [
        'dossier'     => '<path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>',
        'importer'    => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M9 15h6"/><path d="M12 18v-6"/>',
        'deconnexion' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
        'recherche'   => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'filtre'      => '<path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>',
        'de'          => '<rect width="18" height="18" x="3" y="3" rx="3"/><circle cx="8.5" cy="8.5" r="1" fill="currentColor"/><circle cx="15.5" cy="8.5" r="1" fill="currentColor"/><circle cx="12" cy="12" r="1" fill="currentColor"/><circle cx="8.5" cy="15.5" r="1" fill="currentColor"/><circle cx="15.5" cy="15.5" r="1" fill="currentColor"/>',
        'chevron'     => '<path d="m9 18 6-6-6-6"/>',
    ];
    return '<svg width="' . $taille . '" height="' . $taille . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        . ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . $dessins[$nom] . '</svg>';
}

// $avec_menu = false pour une page sans barre de navigation (ex. : connexion).
function entete(string $titre, bool $avec_menu = true): void
{
    ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#121214">
    <title><?= e($titre) ?> · Recettes d'Amélie</title>
    <!-- Icône de l'app (Bob) : onglet du navigateur, écran d'accueil iPhone et Android.
         Versions réduites de img/bob.png. -->
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32.png">
    <link rel="icon" type="image/png" sizes="512x512" href="img/icone-512.png">
    <link rel="apple-touch-icon" href="img/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-title" content="Recettes">
    <!-- Police « Outfit » (Google Fonts) et thème sombre du site -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/../public/style.css') ?>">
</head>
<body>
<?php if ($avec_menu): ?>
<header class="container barre-haut">
    <a href="index.php" class="logo" aria-label="Accueil"><img src="img/apple-touch-icon.png" alt=""></a>
    <nav>
        <a href="import.php" class="icone-bouton" aria-label="Importer une recette" title="Importer une recette"><?= icone('importer') ?></a>
        <a href="logout.php" class="icone-bouton" aria-label="Se déconnecter" title="Se déconnecter"><?= icone('deconnexion') ?></a>
    </nav>
</header>
<?php endif; ?>
<main class="container">
    <?php
}

function pied(): void
{
    ?>
</main>
</body>
</html>
    <?php
}
