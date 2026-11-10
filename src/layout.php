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
        /* Même fond pour l'en-tête et le bas des cartes que pour leur contenu */
        article > header, article > footer { background: var(--pico-card-background-color); }
        .meta { color: var(--pico-muted-color); font-size: .9em; }
        textarea { min-height: 10rem; }
        /* Accueil : recettes en tuiles (autant de colonnes que la largeur le permet) */
        .tuiles { display: grid; grid-template-columns: repeat(auto-fill, minmax(13rem, 1fr)); gap: 1rem; }
        .tuile { display: flex; flex-direction: column; justify-content: space-between;
                 height: 100%; margin: 0; padding: 1rem; transition: transform .15s, box-shadow .15s; }
        a.lien-tuile { color: inherit; text-decoration: none; }
        a.lien-tuile:hover .tuile { transform: translateY(-2px); box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.12); }
        .tuile h3 { font-size: 1rem; margin-bottom: .75rem; }
        /* Bouton filtre : même fond que le champ de recherche, sans séparation visible,
           pour que l'icône semble faire partie du champ. */
        [role="search"] input[type="search"] { border-right-color: transparent; }
        .bouton-filtre, .bouton-filtre:hover, .bouton-filtre:focus, .bouton-filtre:active {
            background-color: var(--pico-form-element-background-color);
            border-color: var(--pico-form-element-border-color);
            border-left-color: transparent;
            color: var(--pico-muted-color);
            box-shadow: none;
        }
        .bouton-filtre:hover, .bouton-filtre.actif { color: var(--pico-color); }
        /* Boutons côte à côte (Modifier / Supprimer, Annuler / Confirmer) */
        .actions { display: flex; gap: .75rem; justify-content: flex-end; align-items: center; }
        .actions form { margin: 0; }
        .actions button, .actions [role="button"] { width: auto; margin: 0; }
        /* Bouton de suppression définitive en rouge */
        button.supprimer { background: #c0392b; border-color: #c0392b; color: #fff; }
        button.supprimer:hover { background: #a93226; border-color: #a93226; }
    </style>
</head>
<body>
<?php if ($avec_menu): ?>
<header class="container">
    <nav>
        <ul><li><a href="index.php"><strong>Recettes d'Amélie</strong></a></li></ul>
        <ul>
            <li><a href="import.php" role="button" class="secondary outline">Importer</a></li>
            <li><a href="edit.php" role="button">+ Nouvelle recette</a></li>
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
