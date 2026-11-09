<?php
// Protection du site par un mot de passe unique partagé par la famille.
//
// Principe : après une connexion réussie, on dépose dans le navigateur un cookie
// qui contient une date d'expiration + une signature. La signature est calculée avec
// un secret connu seulement du serveur (le hash du mot de passe) : impossible de
// fabriquer un faux cookie sans le connaître. Si tu changes le mot de passe,
// tous les appareils sont automatiquement déconnectés.

// Le hash du mot de passe vient soit de src/config.php (en local),
// soit de la variable d'environnement MOT_DE_PASSE_HASH (en ligne, sur Railway).
if (is_file(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} elseif (getenv('MOT_DE_PASSE_HASH')) {
    define('MOT_DE_PASSE_HASH', getenv('MOT_DE_PASSE_HASH'));
} else {
    http_response_code(500);
    exit('Mot de passe non défini : crée src/config.php (en local) ou définis la variable MOT_DE_PASSE_HASH (en ligne). Voir README.');
}

// Vrai si la page est servie en HTTPS, y compris derrière le proxy de Railway.
function en_https(): bool
{
    return !empty($_SERVER['HTTPS'])
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

const COOKIE_AUTH = 'recettes_famille';
const DUREE_CONNEXION = 60 * 24 * 3600; // 60 jours, en secondes

function signature(string $expiration): string
{
    return hash_hmac('sha256', $expiration, MOT_DE_PASSE_HASH);
}

function est_connecte(): bool
{
    $cookie = $_COOKIE[COOKIE_AUTH] ?? '';
    $morceaux = explode('.', $cookie, 2);
    if (count($morceaux) !== 2) return false;
    [$expiration, $sig] = $morceaux;
    return ctype_digit($expiration)
        && (int)$expiration > time()
        && hash_equals(signature($expiration), $sig); // comparaison sûre
}

function poser_cookie(string $valeur, int $expiration): void
{
    setcookie(COOKIE_AUTH, $valeur, [
        'expires'  => $expiration,
        'path'     => '/',
        'httponly' => true,   // inaccessible au JavaScript
        'samesite' => 'Lax',  // pas envoyé par les formulaires d'autres sites
        'secure'   => en_https(), // HTTPS uniquement quand il est actif
    ]);
}

// Vérifie le mot de passe ; si c'est le bon, connecte l'appareil.
function connecter(string $mot_de_passe): bool
{
    if (!password_verify($mot_de_passe, MOT_DE_PASSE_HASH)) {
        sleep(1); // ralentit ceux qui essaieraient de deviner le mot de passe
        return false;
    }
    $expiration = (string)(time() + DUREE_CONNEXION);
    poser_cookie($expiration . '.' . signature($expiration), (int)$expiration);
    return true;
}

function deconnecter(): void
{
    poser_cookie('', time() - 3600); // date passée = le navigateur supprime le cookie
}

// À appeler en haut de chaque page protégée.
function exiger_connexion(): void
{
    if (!est_connecte()) {
        header('Location: login.php?retour=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
        exit;
    }
}
