<?php
// Définit (ou change) le mot de passe
// Utilisation, depuis le dossier du projet : php bin/mot-de-passe.php

if (PHP_SAPI !== 'cli') {
    exit("Ce script se lance dans un terminal.\n");
}

function demander(string $question): string
{
    echo $question;
    system('stty -echo');          // masque la saisie
    $reponse = rtrim((string)fgets(STDIN), "\r\n");
    system('stty echo');
    echo "\n";
    return $reponse;
}

$mdp = demander('Nouveau mot de passe : ');
if (strlen($mdp) < 6) {
    exit("Trop court (6 caractères minimum).\n");
}
if (demander('Confirme le mot de passe : ') !== $mdp) {
    exit("Les deux mots de passe ne correspondent pas.\n");
}

$hash = password_hash($mdp, PASSWORD_DEFAULT);
$contenu = "<?php\n// Généré par bin/mot-de-passe.php — ne pas envoyer sur git.\n\n"
         . 'const MOT_DE_PASSE_HASH = ' . var_export($hash, true) . ";\n";

$fichier = __DIR__ . '/../src/config.php';
file_put_contents($fichier, $contenu);
echo "Mot de passe enregistré dans src/config.php\n";
echo "(Tous les appareils déjà connectés devront se reconnecter.)\n";