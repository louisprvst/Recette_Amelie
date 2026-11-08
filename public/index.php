<?php
// Page d'accueil : liste des recettes, avec recherche et filtres
// (texte libre, type, auteur, source). Les filtres se cumulent.

require_once __DIR__ . '/../src/layout.php';
exiger_connexion();

$q      = trim($_GET['q'] ?? '');
$cat    = $_GET['cat'] ?? '';
$auteur = $_GET['auteur'] ?? '';
$source = $_GET['source'] ?? '';

$auteurs = valeurs_distinctes('auteur');
$sources = valeurs_distinctes('source');

$sql = 'SELECT id, titre, categorie, temps, personnes, auteur, source FROM recettes WHERE 1';
$params = [];
if ($q !== '') {
    // Le texte libre cherche partout : titre, ingrédients, auteur, source.
    $sql .= ' AND (titre LIKE ? OR ingredients LIKE ? OR auteur LIKE ? OR source LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
}
if (in_array($cat, CATEGORIES, true)) {
    $sql .= ' AND categorie = ?';
    $params[] = $cat;
}
if ($auteur !== '') {
    $sql .= ' AND auteur = ?';
    $params[] = $auteur;
}
if ($source !== '') {
    $sql .= ' AND source = ?';
    $params[] = $source;
}
$sql .= ' ORDER BY titre COLLATE NOCASE';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$recettes = $stmt->fetchAll();

// Affiche un <select> de filtre avec une option « Tous » vide.
function filtre(string $nom, string $libelle, array $options, string $choisi): void
{
    echo '<select name="' . e($nom) . '" aria-label="' . e($libelle) . '">';
    echo '<option value="">' . e($libelle) . ' : tous</option>';
    foreach ($options as $o) {
        $sel = $o === $choisi ? ' selected' : '';
        echo '<option value="' . e($o) . '"' . $sel . '>' . e($o) . '</option>';
    }
    echo '</select>';
}

entete('Accueil');
?>
<form method="get">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Titre, ingrédient, auteur, source…">
    <div class="grid">
        <?php filtre('cat', 'Type', CATEGORIES, $cat); ?>
        <?php filtre('auteur', 'Auteur', $auteurs, $auteur); ?>
        <?php filtre('source', 'Source', $sources, $source); ?>
    </div>
    <button type="submit">Chercher</button>
    <?php if ($q !== '' || $cat !== '' || $auteur !== '' || $source !== ''): ?>
        <a href="index.php">Réinitialiser</a>
    <?php endif; ?>
</form>

<p class="meta"><?= count($recettes) ?> recette<?= count($recettes) > 1 ? 's' : '' ?></p>

<?php if (!$recettes): ?>
    <p>Aucune recette trouvée. <a href="edit.php">En ajouter une</a> ?</p>
<?php endif; ?>

<?php foreach ($recettes as $r): ?>
    <article>
        <h3><a href="recette.php?id=<?= $r['id'] ?>"><?= e($r['titre']) ?></a></h3>
        <span class="meta">
            <?= e($r['categorie']) ?>
            <?= $r['temps'] ? ' · ' . (int)$r['temps'] . ' min' : '' ?>
            <?= $r['personnes'] ? ' · ' . (int)$r['personnes'] . ' pers.' : '' ?>
            <?= $r['auteur'] ? ' · par ' . e($r['auteur']) : '' ?>
            <?= $r['source'] ? ' · ' . e($r['source']) : '' ?>
        </span>
    </article>
<?php endforeach; ?>
<?php pied();
