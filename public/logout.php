<?php
// Déconnexion de cet appareil.

require_once __DIR__ . '/../src/auth.php';

deconnecter();
header('Location: login.php');
exit;
