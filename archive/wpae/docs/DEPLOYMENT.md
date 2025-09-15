# Guide de Déploiement VPS - R&G

## Vue d'ensemble

Ce guide décrit le déploiement de la boutique R&G sur un VPS avec la stack Nginx + PHP-FPM + MySQL/MariaDB.

## Prérequis

- VPS avec Ubuntu 20.04+ ou Debian 11+
- Accès root ou sudo
- Nom de domaine pointant vers votre VPS

## Installation des Composants

### 1. Mise à jour du système

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Installation de Nginx

```bash
sudo apt install nginx -y
sudo systemctl enable nginx
sudo systemctl start nginx
```

### 3. Installation de PHP-FPM

```bash
sudo apt install php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-intl -y
sudo systemctl enable php8.1-fpm
sudo systemctl start php8.1-fpm
```

### 4. Installation de MySQL/MariaDB

```bash
sudo apt install mariadb-server -y
sudo systemctl enable mariadb
sudo systemctl start mariadb
sudo mysql_secure_installation
```

## Configuration de la Base de Données

### 1. Création de la base et de l'utilisateur

```sql
sudo mysql -u root -p

CREATE DATABASE rg_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rg_user'@'localhost' IDENTIFIED BY 'votre_mot_de_passe_securise';
GRANT ALL PRIVILEGES ON rg_shop.* TO 'rg_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. Import du schéma

```bash
mysql -u rg_user -p rg_shop < /path/to/database.sql
```

## Déploiement de l'Application

### 1. Structure des dossiers

```bash
sudo mkdir -p /var/www/rg-boutique
sudo chown -R www-data:www-data /var/www/rg-boutique
```

### 2. Upload des fichiers

Uploadez tous les fichiers du projet dans `/var/www/rg-boutique/`:

```
/var/www/rg-boutique/
├── public/           # DocumentRoot (seul dossier accessible publiquement)
│   ├── index.php
│   ├── assets/
│   ├── styles/
│   ├── scripts/
│   └── pages/
├── src/             # Backend PHP (non accessible publiquement)
│   ├── config.php
│   ├── db.php
│   ├── auth.php
│   ├── csrf.php
│   └── functions.php
├── admin/           # Interface d'administration
│   └── products/
└── database.sql
```

### 3. Configuration de l'application

```bash
cd /var/www/rg-boutique
cp src/config.php.sample src/config.php
```

Éditez `src/config.php` avec vos paramètres de base de données :

```php
<?php
return [
  'db' => [
    'dsn' => 'mysql:host=127.0.0.1;dbname=rg_shop;charset=utf8mb4',
    'user' => 'rg_user',
    'pass' => 'votre_mot_de_passe_securise',
    'options' => [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ],
  ],
  'app' => [
    'base_url' => '',  // Laisser vide pour un vhost dédié
  ],
];
```

### 4. Permissions

```bash
sudo chown -R www-data:www-data /var/www/rg-boutique
sudo chmod -R 755 /var/www/rg-boutique
sudo chmod 600 /var/www/rg-boutique/src/config.php
```

## Configuration Nginx

### 1. Configuration du Virtual Host

Créez `/etc/nginx/sites-available/rg-boutique`:

```nginx
server {
    listen 80;
    server_name votre-domaine.com www.votre-domaine.com;
    root /var/www/rg-boutique/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handling
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location /src/ {
        deny all;
    }

    # Admin protection (optionnel - ajoutez une authentification HTTP)
    location /admin/ {
        # auth_basic "Administration";
        # auth_basic_user_file /etc/nginx/.htpasswd;
        
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
}
```

### 2. Activation du site

```bash
sudo ln -s /etc/nginx/sites-available/rg-boutique /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## Configuration SSL avec Let's Encrypt

### 1. Installation de Certbot

```bash
sudo apt install certbot python3-certbot-nginx -y
```

### 2. Génération du certificat

```bash
sudo certbot --nginx -d votre-domaine.com -d www.votre-domaine.com
```

### 3. Renouvellement automatique

```bash
sudo crontab -e
# Ajouter cette ligne :
0 12 * * * /usr/bin/certbot renew --quiet
```

## Optimisations PHP

### 1. Configuration PHP-FPM

Éditez `/etc/php/8.1/fpm/pool.d/www.conf` :

```ini
; Ajustez selon vos ressources
pm.max_children = 20
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
```

### 2. Configuration PHP

Éditez `/etc/php/8.1/fpm/php.ini` :

```ini
; Sécurité
expose_php = Off
display_errors = Off
log_errors = On

; Performance
memory_limit = 256M
max_execution_time = 30
upload_max_filesize = 10M
post_max_size = 10M

; Sessions
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

Redémarrez PHP-FPM :

```bash
sudo systemctl restart php8.1-fmp
```

## Sécurité

### 1. Firewall

```bash
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
```

### 2. Protection contre les attaques

Ajoutez dans la configuration Nginx :

```nginx
# Rate limiting
limit_req_zone $binary_remote_addr zone=login:10m rate=1r/s;

location /login.php {
    limit_req zone=login burst=5 nodelay;
    # ... rest of PHP configuration
}
```

### 3. Sauvegarde automatique

Créez un script de sauvegarde `/home/backup-rg.sh` :

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/backups"
mkdir -p $BACKUP_DIR

# Sauvegarde base de données
mysqldump -u rg_user -p'votre_mot_de_passe' rg_shop > $BACKUP_DIR/rg_shop_$DATE.sql

# Sauvegarde fichiers
tar -czf $BACKUP_DIR/rg_files_$DATE.tar.gz -C /var/www rg-boutique

# Nettoyage (garder 7 jours)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

Ajoutez au cron :

```bash
sudo crontab -e
# Sauvegarde quotidienne à 2h du matin
0 2 * * * /home/backup-rg.sh
```

## Monitoring

### 1. Logs à surveiller

```bash
# Logs Nginx
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log

# Logs PHP
sudo tail -f /var/log/php8.1-fpm.log

# Logs MySQL
sudo tail -f /var/log/mysql/error.log
```

### 2. Surveillance des ressources

```bash
# Usage CPU/RAM
htop

# Espace disque
df -h

# Connexions réseau
netstat -tulpn
```

## Maintenance

### 1. Mises à jour régulières

```bash
# Système
sudo apt update && sudo apt upgrade -y

# Base de données
sudo mysql_upgrade -u root -p
```

### 2. Optimisation base de données

```sql
-- Exécuter périodiquement
OPTIMIZE TABLE users, products, orders, order_items, categories;
```

## Dépannage

### Erreurs courantes

1. **Erreur 500** : Vérifiez les logs PHP et Nginx
2. **Connexion base refusée** : Vérifiez les paramètres dans `config.php`
3. **Permissions** : Vérifiez que www-data possède les bons droits

### Tests de fonctionnement

```bash
# Test PHP
php -v

# Test base de données
mysql -u rg_user -p rg_shop -e "SELECT COUNT(*) FROM products;"

# Test Nginx
sudo nginx -t
```

## Support

Pour toute question ou problème :

- Vérifiez les logs en premier
- Consultez la documentation officielle de chaque composant
- Contactez le support technique : tech@rg-boutique.fr

---

*Ce guide a été testé sur Ubuntu 20.04 LTS avec Nginx 1.18, PHP 8.1 et MariaDB 10.3*