<?php
// Page d'accueil : recettes en tuiles, avec une barre de recherche.

require_once __DIR__ . '/../src/layout.php';
exiger_connexion();

$q = trim($_GET['q'] ?? '');
$categorie = $_GET['categorie'] ?? '';
$auteur = $_GET['auteur'] ?? '';
if (!in_array($categorie, CATEGORIES, true)) $categorie = '';
$filtres_actifs = $categorie !== '' || $auteur !== '';

$conditions = [];
$params = [];
if ($q !== '') {
    // La recherche porte sur le titre, les ingrédients et l'auteur.
    $conditions[] = '(titre LIKE ? OR ingredients LIKE ? OR auteur LIKE ?)';
    array_push($params, "%$q%", "%$q%", "%$q%");
}
if ($categorie !== '') {
    $conditions[] = 'categorie = ?';
    $params[] = $categorie;
}
if ($auteur !== '') {
    $conditions[] = 'auteur = ?';
    $params[] = $auteur;
}

$sql = 'SELECT id, titre, categorie, temps, personnes, auteur FROM recettes';
if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY titre COLLATE NOCASE';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$recettes = $stmt->fetchAll();

entete('Accueil');
?>
<!-- role="search" : Pico place le champ et les boutons sur une même ligne -->
<form method="get" role="search" id="recherche">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Titre, ingrédient, auteur…" aria-label="Rechercher">
    <!-- Bouton entonnoir : affiche / masque les filtres -->
    <button type="button" class="bouton-filtre<?= $filtres_actifs ? ' actif' : '' ?>"
            aria-label="Filtres" title="Filtres" aria-controls="filtres"
            aria-expanded="<?= $filtres_actifs ? 'true' : 'false' ?>"
            onclick="const f = document.getElementById('filtres');
                     f.hidden = !f.hidden;
                     this.setAttribute('aria-expanded', !f.hidden);">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
        </svg>
    </button>
    <button type="submit">Rechercher</button>
</form>

<!-- Champs de filtre, rattachés au formulaire ci-dessus par l'attribut form="recherche".
     Ils sont repliés par défaut, sauf si un filtre est déjà actif. -->
<div id="filtres" class="grid" <?= $filtres_actifs ? '' : 'hidden' ?>>
    <label>Type
        <select name="categorie" form="recherche" onchange="this.form.requestSubmit()">
            <option value="">Tous</option>
            <?php foreach (CATEGORIES as $c): ?>
                <option <?= $c === $categorie ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Auteur
        <select name="auteur" form="recherche" onchange="this.form.requestSubmit()">
            <option value="">Tous</option>
            <?php foreach (valeurs_distinctes('auteur') as $a): ?>
                <option <?= $a === $auteur ? 'selected' : '' ?>><?= e($a) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
</div>

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
