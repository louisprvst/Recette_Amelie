<?php
// Page de connexion avec le mot de passe familial.

require_once __DIR__ . '/../src/layout.php';

// Page où renvoyer après connexion. On n'accepte qu'une adresse interne au site
// (commençant par « / » mais pas « // ») pour éviter de rediriger vers un autre site.
$retour = $_GET['retour'] ?? '/';
if (!str_starts_with($retour, '/') || str_starts_with($retour, '//')) {
    $retour = '/';
}

if (est_connecte()) {
    header('Location: ' . $retour);
    exit;
}

$erreur = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (connecter($_POST['mot_de_passe'] ?? '')) {
        header('Location: ' . $retour);
        exit;
    }
    $erreur = true;
}

entete('Connexion', false);
?>
<article class="connexion">
    <span class="logo"><img src="img/apple-touch-icon.png" alt=""></span>
    <h1>Bienvenue</h1>

    <?php if ($erreur): ?>
        <p class="erreur">Mot de passe incorrect.</p>
    <?php endif; ?>

    <form method="post">
        <label>Mot de passe
            <input type="password" name="mot_de_passe" required autofocus
                   <?= $erreur ? 'aria-invalid="true"' : '' ?>>
        </label>
        <button type="submit">Entrer</button>
    </form>
</article>
<?php pied();
