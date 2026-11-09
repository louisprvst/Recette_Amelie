<?php
// Page d'accueil : recettes en tuiles, avec une barre de recherche.

require_once __DIR__ . '/../src/layout.php';
exiger_connexion();

$q = trim($_GET['q'] ?? '');

$sql = 'SELECT id, titre, categorie, temps, personnes, auteur FROM recettes';
$params = [];
if ($q !== '') {
    // La recherche porte sur le titre, les ingrédients et l'auteur.
    $sql .= ' WHERE titre LIKE ? OR ingredients LIKE ? OR auteur LIKE ?';
    $params = ["%$q%", "%$q%", "%$q%"];
}
$sql .= ' ORDER BY titre COLLATE NOCASE';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$recettes = $stmt->fetchAll();

entete('Accueil');
?>
<!-- role="search" : Pico place le champ et le bouton sur une même ligne -->
<form method="get" role="search">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Titre, ingrédient, auteur…" aria-label="Rechercher">
    <button type="submit">Rechercher</button>
</form>

<p class="meta"><?= count($recettes) ?> recette<?= count($recettes) > 1 ? 's' : '' ?></p>

<?php if (!$recettes): ?>
    <p>Aucune recette trouvée. <a href="edit.php">En ajouter une</a> ?</p>
<?php endif; ?>

<div class="tuiles">
    <?php foreach ($recettes as $r): ?>
        <!-- Toute la tuile est cliquable -->
        <a class="lien-tuile" href="recette.php?id=<?= $r['id'] ?>">
            <article class="tuile">
                <h3><?= e($r['titre']) ?></h3>
                <span class="meta">
                    <?= e($r['categorie']) ?>
                    <?= $r['temps'] ? ' · ' . (int)$r['temps'] . ' min' : '' ?>
                    <?= $r['personnes'] ? ' · ' . (int)$r['personnes'] . ' pers.' : '' ?>
                    <?php if ($r['auteur']): ?><br>par <?= e($r['auteur']) ?><?php endif; ?>
                </span>
            </article>
        </a>
    <?php endforeach; ?>
</div>
<?php pied();
