# Déploiement sur LWS (hébergement mutualisé)

## Upload
- Déposez tout le contenu du dépôt dans `~/htdocs` (racine du site LWS).
- Les dossiers `assets/`, `styles/`, `scripts/`, `pages/` restent sous `public/`.

## Configuration base de données
- Créez votre base et votre utilisateur depuis le manager LWS si ce n'est pas déjà fait.
- Copiez `src/config.php.sample` en `src/config.php` et renseignez:
  - HOST (ex: `mysql4201.lwspanel.com`),
  - DBNAME,
  - USER,
  - PASSWORD.

## .htaccess
- Le `.htaccess` à la racine redirige automatiquement les requêtes vers `public/` sans modifier l'URL.
- `src/` est protégé contre l'accès direct.

## phpMyAdmin
- Utilisez l'URL fournie par LWS pour accéder à phpMyAdmin et importer `database.sql` si besoin.

## Tests
- Page d'accueil: `https://votre-domaine/` (même DA)
- Auth: `/login.php`, `/register.php`, `/logout.php`
- Admin produits: `/admin/products` (nécessite un compte avec rôle `admin`)
- Panier: `/cart.php`

## Versions de PHP
- Si les scripts PHP se téléchargent au lieu de s'exécuter, vérifiez la version de PHP dans votre manager LWS et forcez-la.
- Dans certains environnements, un handler peut être requis dans `.htaccess` (consultez la doc LWS), p.ex.:

```
AddHandler application/x-httpd-php82 .php
```

> Ne committez jamais `src/config.php` avec vos identifiants.