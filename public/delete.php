<?php
// Suppression d'une recette (uniquement via un formulaire POST, jamais par un simple lien).

require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
exiger_connexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare('DELETE FROM recettes WHERE id = ?');
    $stmt->execute([(int)($_POST['id'] ?? 0)]);
}
header('Location: index.php');
exit;
