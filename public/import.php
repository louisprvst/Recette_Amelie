<?php
// Import d'une recette collée en texte (par ex. une réponse de Claude).
// Le texte est envoyé à edit.php, qui pré-remplit le formulaire d'ajout pour relecture.

require_once __DIR__ . '/../src/layout.php';
exiger_connexion();

entete('Importer');
?>
<h1>Importer une recette</h1>
<p class="meta">Colle la recette ci-dessous. Le formulaire sera pré-rempli : tu pourras tout relire et corriger avant d'enregistrer.</p>

<form method="post" action="edit.php">
    <label>Recette
        <textarea name="texte_import" required style="min-height: 20rem" autofocus
                  placeholder="Titre : Tarte aux pommes&#10;Type : Dessert&#10;Temps : 1 h 15&#10;Personnes : 6&#10;Auteur : Claude&#10;Ingrédients :&#10;- 200 g de farine&#10;- 3 pommes&#10;Étapes :&#10;1. Préchauffer le four à 180 °C.&#10;2. …"></textarea>
    </label>
    <button type="submit">Pré-remplir le formulaire</button>
    <a href="index.php">Annuler</a>
</form>
<?php pied();
