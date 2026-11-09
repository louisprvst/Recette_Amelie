<?php
// Ajoute dans la base du site les recettes de seed/recettes.sqlite qui n'y sont pas encore.
// Une recette est considérée comme « déjà présente » si une recette porte le même titre.
// Rien n'est modifié ni supprimé : les recettes existantes (et leurs modifications) restent intactes.
//
// Utilisation (en local ou sur Railway via « railway ssh ») :
//     php bin/fusionner-seed.php

if (PHP_SAPI !== 'cli') {
    exit("Ce script se lance dans un terminal.\n");
}

require_once __DIR__ . '/../src/db.php';

$seed_chemin = __DIR__ . '/../seed/recettes.sqlite';
if (!is_file($seed_chemin)) {
    exit("Fichier introuvable : seed/recettes.sqlite\n");
}

$seed = new PDO('sqlite:' . $seed_chemin);
$seed->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$seed->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$base = db(); // la base du site (DB_PATH sur Railway, data/ en local)

$existe = $base->prepare('SELECT 1 FROM recettes WHERE titre = ?');
$ajout  = $base->prepare('INSERT INTO recettes (titre, categorie, temps, personnes, ingredients, etapes, auteur)
                          VALUES (?, ?, ?, ?, ?, ?, ?)');

$ajoutees = 0;
$base->beginTransaction();
foreach ($seed->query('SELECT * FROM recettes ORDER BY id') as $r) {
    $existe->execute([$r['titre']]);
    if ($existe->fetchColumn()) {
        continue;
    }
    $ajout->execute([$r['titre'], $r['categorie'], $r['temps'], $r['personnes'],
                     $r['ingredients'], $r['etapes'], $r['auteur']]);
    echo "+ {$r['titre']}\n";
    $ajoutees++;
}
$base->commit();

$total = $base->query('SELECT COUNT(*) FROM recettes')->fetchColumn();
echo "\n$ajoutees recette(s) ajoutée(s) — $total au total dans la base.\n";
