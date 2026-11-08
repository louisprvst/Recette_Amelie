<?php
// Affichage d'une recette.

require_once __DIR__ . '/../src/layout.php';
exiger_connexion();

$r = trouver_recette((int)($_GET['id'] ?? 0));
if (!$r) {
    http_response_code(404);
    entete('Introuvable');
    echo '<p>Cette recette n\'existe pas. <a href="index.php">Retour</a></p>';
    pied();
    exit;
}

entete($r['titre']);
?>
<article>
    <header>
        <h1><?= e($r['titre']) ?></h1>
        <span class="meta">
            <?= e($r['categorie']) ?>
            <?= $r['temps'] ? ' · ' . (int)$r['temps'] . ' min' : '' ?>
            <?= $r['personnes'] ? ' · ' . (int)$r['personnes'] . ' pers.' : '' ?>
            <?= $r['auteur'] ? ' · par ' . e($r['auteur']) : '' ?>
            <?= $r['source'] ? ' · ' . e($r['source']) : '' ?>
        </span>
    </header>

    <h2>Ingrédients</h2>
    <ul>
        <?php foreach (lignes($r['ingredients']) as $ing): ?>
            <li><?= e($ing) ?></li>
        <?php endforeach; ?>
    </ul>

    <h2>Étapes</h2>
    <ol>
        <?php foreach (lignes($r['etapes']) as $etape): ?>
            <li><?= e($etape) ?></li>
        <?php endforeach; ?>
    </ol>

    <footer>
        <a href="edit.php?id=<?= $r['id'] ?>" role="button">Modifier</a>
        <form method="post" action="delete.php" style="display:inline"
              onsubmit="return confirm('Supprimer cette recette ?')">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button type="submit" class="secondary outline">Supprimer</button>
        </form>
    </footer>
</article>
<?php pied();
