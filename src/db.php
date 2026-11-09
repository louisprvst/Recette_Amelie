<?php
// Connexion à la base SQLite (un simple fichier).
// En local : data/recettes.sqlite. En ligne (Railway) : le chemin donné par la
// variable d'environnement DB_PATH (ex. /data/recettes.sqlite sur le volume).
// La table est créée automatiquement au premier lancement.

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $chemin = getenv('DB_PATH') ?: __DIR__ . '/../data/recettes.sqlite';
        if (!is_dir(dirname($chemin))) {
            mkdir(dirname($chemin), 0775, true);
        }
        $pdo = new PDO('sqlite:' . $chemin);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $pdo->exec('CREATE TABLE IF NOT EXISTS recettes (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            titre       TEXT NOT NULL,
            categorie   TEXT NOT NULL DEFAULT \'Plat\',
            temps       INTEGER,             -- en minutes
            personnes   INTEGER,
            ingredients TEXT NOT NULL DEFAULT \'\', -- une ligne par ingrédient
            etapes      TEXT NOT NULL DEFAULT \'\', -- une ligne par étape
            auteur      TEXT,
            source      TEXT,                -- carnet de Mamie, HelloFresh…
            cree_le     TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            modifie_le  TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        // Migration : si la base a été créée avant l'ajout de la colonne « source »,
        // CREATE TABLE IF NOT EXISTS ne la rajoute pas, donc on l'ajoute ici.
        $colonnes = array_column($pdo->query('PRAGMA table_info(recettes)')->fetchAll(), 'name');
        if (!in_array('source', $colonnes, true)) {
            $pdo->exec('ALTER TABLE recettes ADD COLUMN source TEXT');
        }
    }
    return $pdo;
}

// Liste des valeurs différentes déjà utilisées dans une colonne (pour les filtres et suggestions).
function valeurs_distinctes(string $colonne): array
{
    if (!in_array($colonne, ['auteur', 'source'], true)) {
        throw new InvalidArgumentException('Colonne non autorisée');
    }
    return db()->query("SELECT DISTINCT $colonne FROM recettes
        WHERE $colonne IS NOT NULL AND $colonne <> '' ORDER BY $colonne COLLATE NOCASE")
        ->fetchAll(PDO::FETCH_COLUMN);
}

const CATEGORIES = ['Entrée', 'Plat', 'Dessert', 'Boisson', 'Autre'];

// Trouve une recette par son id (ou null si elle n'existe pas).
function trouver_recette(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM recettes WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
