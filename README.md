
## Mise en ligne sur Railway

1. Pousser le projet sur GitHub, puis sur Railway : **New Project → Deploy from GitHub repo**.
   Railway détecte le `Dockerfile` tout seul.
2. Dans le service : **Add Volume**, point de montage `/data`.
3. Dans **Variables** :
   - `DB_PATH` = `/data/recettes.sqlite`
   - `MOT_DE_PASSE_HASH` = le hash qui se trouve dans `src/config.php` (la chaîne `$2y$...`)
4. **Settings → Networking → Generate Domain** pour obtenir l'adresse du site.

Au premier démarrage, `seed/recettes.sqlite` est copiée sur le volume.
Ensuite la base du volume n'est plus jamais écrasée par un redéploiement.
Une fois le site en ligne et vérifié, le dossier `seed/` peut être supprimé du dépôt.
