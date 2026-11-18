<?php
// Page d'accueil : barre de recherche, recette au hasard, catégories en dossiers
// et liste des recettes (ou résultats de la recherche).

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

// Accueil « nu » (sans recherche ni filtre) : on montre aussi le bandeau et les dossiers.
$accueil = $q === '' && !$filtres_actifs;
if ($accueil) {
    // Nombre de recettes par catégorie, dont celles ajoutées ces 7 derniers jours.
    $stats = db()->query("SELECT categorie, COUNT(*) AS total,
            SUM(cree_le >= datetime('now', '-7 days')) AS nouvelles
        FROM recettes GROUP BY categorie")->fetchAll(PDO::FETCH_UNIQUE);
    $au_hasard = db()->query('SELECT id FROM recettes ORDER BY RANDOM() LIMIT 1')->fetchColumn();
}

function pluriel(int $n, string $mot): string
{
    return $n . ' ' . $mot . ($n > 1 ? 's' : '');
}

entete('Accueil');
?>
<h1 class="titre-accueil">Qu'est-ce qu'on<br>mange ce soir ?</h1>

<form method="get" id="recherche" class="recherche" aria-label="Rechercher une recette">
    <div class="champ-recherche bloc">
        <?= icone('recherche') ?>
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Titre, ingrédient, auteur…" aria-label="Rechercher">
        <!-- Bouton entonnoir : affiche / masque les filtres -->
        <button type="button" class="bouton-filtre<?= $filtres_actifs ? ' actif' : '' ?>"
                aria-label="Filtres" title="Filtres" aria-controls="filtres"
                aria-expanded="<?= $filtres_actifs ? 'true' : 'false' ?>"
                onclick="const f = document.getElementById('filtres');
                         f.hidden = !f.hidden;
                         this.setAttribute('aria-expanded', !f.hidden);"><?= icone('filtre', 18) ?></button>
    </div>
    <!-- Bouton invisible : permet de lancer la recherche avec la touche Entrée -->
    <button type="submit" class="sr-only" tabindex="-1">Rechercher</button>
    <a href="edit.php" role="button" class="bouton-ajout" aria-label="Nouvelle recette" title="Nouvelle recette">+</a>
</form>

<!-- Champs de filtre, rattachés au formulaire ci-dessus par l'attribut form="recherche".
     Ils sont repliés par défaut, sauf si un filtre est déjà actif. -->
<div id="filtres" class="grid bloc" <?= $filtres_actifs ? '' : 'hidden' ?>>
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

<?php if ($accueil && $au_hasard): ?>
    <a href="recette.php?id=<?= (int)$au_hasard ?>" class="banniere bloc">
        <span class="pastille"><?= icone('de') ?></span>
        <span>
            <strong>Pas d'idée ?</strong>
            <small>Une recette au hasard parmi <?= count($recettes) ?></small>
        </span>
        <?= icone('chevron', 22) ?>
    </a>

    <h2>Catégories</h2>
    <div class="dossiers">
        <?php foreach (CATEGORIES as $c): ?>
            <?php if (empty($stats[$c]['total'])) continue; // on cache les catégories vides ?>
            <a class="dossier" href="?categorie=<?= urlencode($c) ?>" style="--c: <?= couleur_categorie($c) ?>">
                <span class="onglet"></span>
                <span class="corps bloc">
                    <span class="haut">
                        <span class="pastille"><?= icone('dossier') ?></span>
                        <span class="compte">
                            <?= pluriel((int)$stats[$c]['total'], 'recette') ?>
                            <?php if ($stats[$c]['nouvelles']): ?>
                                <span class="badge"><?= (int)$stats[$c]['nouvelles'] ?> nouv.</span>
                            <?php endif; ?>
                        </span>
                    </span>
                    <strong><?= e($c) ?></strong>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="entete-liste">
    <h2><?= $accueil ? 'Toutes les recettes' : pluriel(count($recettes), 'résultat') ?></h2>
    <?php if (!$accueil): ?><a href="index.php">Tout afficher</a><?php endif; ?>
</div>

<?php if (!$recettes): ?>
    <p>Aucune recette trouvée. <a href="edit.php">En ajouter une</a> ?</p>
<?php endif; ?>

<div class="tuiles">
    <?php foreach ($recettes as $r): ?>
        <!-- Toute la tuile est cliquable -->
        <a class="lien-tuile" href="recette.php?id=<?= $r['id'] ?>">
            <article class="tuile">
                <h3><?= e($r['titre']) ?></h3>
                <span>
                    <span class="etiquette" style="--c: <?= couleur_categorie($r['categorie']) ?>"><?= e($r['categorie']) ?></span>
                    <span class="meta">
                        <?= $r['temps'] ? ' · ' . (int)$r['temps'] . ' min' : '' ?>
                        <?= $r['personnes'] ? ' · ' . (int)$r['personnes'] . ' pers.' : '' ?>
                        <?php if ($r['auteur']): ?><br>par <?= e($r['auteur']) ?><?php endif; ?>
                    </span>
                </span>
            </article>
        </a>
    <?php endforeach; ?>
</div>
<?php pied();
