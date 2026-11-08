<?php
// Formulaire d'ajout (sans ?id=) ou de modification (avec ?id=12).

require_once __DIR__ . '/../src/layout.php';
exiger_connexion();

$id = (int)($_GET['id'] ?? 0);
$recette = $id ? trouver_recette($id) : null;
if ($id && !$recette) {
    header('Location: index.php');
    exit;
}

// Valeurs par défaut du formulaire.
$r = $recette ?? [
    'titre' => '', 'categorie' => 'Plat', 'temps' => '', 'personnes' => 4,
    'ingredients' => '', 'etapes' => '', 'auteur' => '', 'source' => '',
];
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On récupère ce qui a été saisi.
    $r = [
        'titre'       => trim($_POST['titre'] ?? ''),
        'categorie'   => $_POST['categorie'] ?? 'Plat',
        'temps'       => $_POST['temps'] !== '' ? (int)$_POST['temps'] : null,
        'personnes'   => $_POST['personnes'] !== '' ? (int)$_POST['personnes'] : null,
        'ingredients' => trim($_POST['ingredients'] ?? ''),
        'etapes'      => trim($_POST['etapes'] ?? ''),
        'auteur'      => trim($_POST['auteur'] ?? ''),
        'source'      => trim($_POST['source'] ?? ''),
    ];

    if ($r['titre'] === '') $erreurs[] = 'Le titre est obligatoire.';
    if (!in_array($r['categorie'], CATEGORIES, true)) $erreurs[] = 'Catégorie invalide.';

    if (!$erreurs) {
        $valeurs = [$r['titre'], $r['categorie'], $r['temps'], $r['personnes'],
                    $r['ingredients'], $r['etapes'], $r['auteur'], $r['source']];
        if ($recette) {
            $stmt = db()->prepare('UPDATE recettes SET titre=?, categorie=?, temps=?, personnes=?,
                ingredients=?, etapes=?, auteur=?, source=?, modifie_le=CURRENT_TIMESTAMP WHERE id=?');
            $stmt->execute([...$valeurs, $id]);
        } else {
            $stmt = db()->prepare('INSERT INTO recettes (titre, categorie, temps, personnes,
                ingredients, etapes, auteur, source) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute($valeurs);
            $id = (int)db()->lastInsertId();
        }
        header("Location: recette.php?id=$id");
        exit;
    }
}

entete($recette ? 'Modifier' : 'Nouvelle recette');
?>
<h1><?= $recette ? 'Modifier « ' . e($recette['titre']) . ' »' : 'Nouvelle recette' ?></h1>

<?php foreach ($erreurs as $err): ?>
    <p style="color: var(--pico-del-color)"><?= e($err) ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Titre
        <input name="titre" value="<?= e($r['titre']) ?>" required>
    </label>

    <div class="grid">
        <label>Catégorie
            <select name="categorie">
                <?php foreach (CATEGORIES as $c): ?>
                    <option <?= $c === $r['categorie'] ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Temps (min)
            <input type="number" name="temps" min="0" value="<?= e((string)$r['temps']) ?>">
        </label>
        <label>Personnes
            <input type="number" name="personnes" min="1" value="<?= e((string)$r['personnes']) ?>">
        </label>
    </div>

    <label>Ingrédients <small>(un par ligne)</small>
        <textarea name="ingredients" placeholder="200 g de farine&#10;3 œufs"><?= e($r['ingredients']) ?></textarea>
    </label>

    <label>Étapes <small>(une par ligne)</small>
        <textarea name="etapes" placeholder="Mélanger la farine et les œufs.&#10;Ajouter le lait petit à petit."><?= e($r['etapes']) ?></textarea>
    </label>

    <!-- Les <datalist> proposent les valeurs déjà utilisées pendant la saisie,
         tout en permettant d'en taper une nouvelle. -->
    <div class="grid">
        <label>Auteur
            <input name="auteur" list="liste-auteurs" value="<?= e($r['auteur']) ?>" placeholder="Mamie, Amélie…">
        </label>
        <label>Source
            <input name="source" list="liste-sources" value="<?= e($r['source']) ?>" placeholder="Carnet de Mamie, HelloFresh…">
        </label>
    </div>
    <datalist id="liste-auteurs">
        <?php foreach (valeurs_distinctes('auteur') as $v): ?><option value="<?= e($v) ?>"><?php endforeach; ?>
    </datalist>
    <datalist id="liste-sources">
        <?php foreach (valeurs_distinctes('source') as $v): ?><option value="<?= e($v) ?>"><?php endforeach; ?>
    </datalist>

    <button type="submit">Enregistrer</button>
    <a href="<?= $recette ? 'recette.php?id=' . $id : 'index.php' ?>">Annuler</a>
</form>
<?php pied();
