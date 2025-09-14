# PHP Backend Integration - R&G

Ce document explique l'intégration du backend PHP dans la boutique R&G.

## Structure du Projet

```
/
├── public/                 # DocumentRoot (seul dossier accessible publiquement)
│   ├── index.php          # Page d'accueil (identique à l'original)
│   ├── login.php          # Page de connexion
│   ├── register.php       # Page d'inscription
│   ├── logout.php         # Déconnexion
│   ├── api.php            # API REST pour le frontend
│   ├── assets/            # Images, logos
│   ├── styles/            # Fichiers CSS (inchangés)
│   ├── scripts/           # Fichiers JavaScript (inchangés)
│   └── pages/             # Pages HTML (inchangées)
├── src/                   # Backend PHP (non accessible publiquement)
│   ├── config.php.sample  # Configuration exemple
│   ├── db.php             # Connexion base de données
│   ├── auth.php           # Système d'authentification
│   ├── csrf.php           # Protection CSRF
│   └── functions.php      # Fonctions métier
├── admin/                 # Interface d'administration
│   └── products/          # Gestion des produits
└── database.sql           # Schéma de base de données
```

## Configuration

1. Copiez `src/config.php.sample` vers `src/config.php`
2. Configurez vos paramètres de base de données
3. Importez le schéma : `mysql -u user -p database < database.sql`

## API Endpoints

- `GET /api.php?action=products` - Liste des produits
- `POST /api.php?action=cart_add` - Ajouter au panier
- `GET /api.php?action=cart_get` - Contenu du panier
- `POST /api.php?action=checkout` - Finaliser commande

## Authentification

- `/login.php` - Connexion utilisateur
- `/register.php` - Inscription utilisateur
- `/logout.php` - Déconnexion

## Administration

- `/admin/products/` - Gestion des produits (accès admin requis)

## Préservation du Design

Le design original a été intégralement préservé :
- Tous les fichiers CSS/JS sont inchangés
- Le HTML de `index.php` est identique à `index.html`
- Aucune modification de l'interface utilisateur

## Sécurité

- Protection CSRF sur tous les formulaires
- Hashage sécurisé des mots de passe
- Requêtes préparées PDO
- Sessions sécurisées