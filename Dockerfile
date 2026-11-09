# Image utilisée par Railway pour faire tourner le site.
# L'image officielle PHP inclut déjà l'extension SQLite (pdo_sqlite).
FROM php:8.3-cli

WORKDIR /app
COPY . .

# Lance le serveur PHP sur le port fourni par Railway.
# La base vit sur le volume (variable DB_PATH), elle n'est jamais touchée par un redéploiement.
CMD ["sh", "-c", "exec php -S 0.0.0.0:${PORT:-8080} -t public"]
