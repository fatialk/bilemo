# Bilemo
Project 7 du parcours "développeur d'applications PHP/Symfony" chez Openclassrooms.

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/353e58fd13864a4491b2589c1be5550e)](https://app.codacy.com/gh/fatialk/bilemo/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

# Description
Bilemo fournit à ses clients l’accès à un catalogue de produits via une API. elle leur permet aussi d'ajouter, supprimer et mettre à jour des utilisateurs.

Seuls les clients authentifiés via JWT peuvent accéder à l'API.

# Documentation
L'API a été documentée avec le bundle : nelmio/api-doc-bundle. Aussi des annotations ont été ajoutées pour personnaliser la documentation et faciliter l'utilisation de l'API.

   - route de la documentation: /api/doc
   - exemple : http://bilemo/api/doc

# Stack technique
   - Symfony 6.4
   - PHP 8

# Installation

1. Cloner le projet depuis le repository:

   - https://github.com/fatialk/bilemo.git

2. Installer les dépendances:

   - composer install

3. Modifier la connexion à la base de données dans le fichier .env :

   - DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/bilemo_api

4. Créer la base de données:

   - php bin/console doctrine:database:create

5. Créer la structure de la base de données et générer les fixtures:

   - php bin/console make:migration
   - php bin/console doctrine:migrations:migrate
   - php bin/console doctrine:fixtures:load

6. générer la clé privée et public de JWT :

   - lancer la commande suivante dans le dossier config/jwt:
   - openssl req -x509 -nodes -days 365 -newkey rsa:4096 -keyout private.pem -out public.pem -passout pass:bilemo123

# Tester les routes

   - pour se connecter en tant que client : email = client1@gmail.com / password = 1234
   - pour se connecter en tant qu'admin: email = admin@bilemo.com / password = password




