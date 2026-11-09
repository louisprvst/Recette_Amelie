# Image utilisée par Railway pour faire tourner le site.
# L'image officielle PHP inclut déjà l'extension SQLite (pdo_sqlite).
FROM php:8.3-cli

WORKDIR /app
COPY . .

# Au démarrage :
#  1. si la base n'existe pas encore sur le volume, on y copie seed/recettes.sqlite
#     (une seule fois : ensuite la base du volume n'est plus jamais écrasée) ;
#  2. si la variable FUSIONNER_SEED vaut 1, on ajoute à la base les recettes du seed
#     qui n'y sont pas encore (voir bin/fusionner-seed.php) ;
#  3. on lance le serveur PHP sur le port fourni par Railway.
CMD ["sh", "-c", "if [ -n \"$DB_PATH\" ] && [ ! -f \"$DB_PATH\" ] && [ -f seed/recettes.sqlite ]; then mkdir -p \"$(dirname \"$DB_PATH\")\" && cp seed/recettes.sqlite \"$DB_PATH\" && echo 'Base initiale copiée sur le volume'; fi; if [ \"$FUSIONNER_SEED\" = \"1\" ]; then php bin/fusionner-seed.php; fi; exec php -S 0.0.0.0:${PORT:-8080} -t public"]
