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

// $avec_menu = false pour une page sans barre de navigation (ex. : connexion).
function entete(string $titre, bool $avec_menu = true): void
{
    ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titre) ?> · Recettes d'Amélie</title>
    <!-- Variante « grey » de Pico : nuances de gris au lieu du bleu. -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2.0.6/css/pico.grey.min.css">
    <style>
        :root { --pico-border-radius: .75rem; } /* coins arrondis partout */
        .meta { color: var(--pico-muted-color); font-size: .9em; }
        textarea { min-height: 10rem; }
    </style>
</head>
<body>
<?php if ($avec_menu): ?>
<header class="container">
    <nav>
        <ul><li><a href="index.php"><strong>Recettes d'Amélie</strong></a></li></ul>
        <ul>
            <li><a href="edit.php" role="button">+ Nouvelle recette</a></li>
            <li><a href="logout.php" class="secondary">Se déconnecter</a></li>
        </ul>
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
