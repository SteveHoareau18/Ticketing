# Plateforme Ticketing
La plateforme de tickets permettant aux services des entreprises de créer et traiter des tâches par le biais de ticket.

# Logiciels requis
- [DockerDesktop](https://docs.docker.com/desktop/install/windows-install/)
- [GitScm](https://git-scm.com/)
- [PhpStorm](https://www.jetbrains.com/phpstorm/)
- [HeidiSql](https://www.heidisql.com/download.php)

# Installation du projet
- Récuperer le projet:
```sh
git clone <repo>
```
Remplacer le `<repo>` par l'url du projet GitHub
- Se placer dans le répertoire du projet

- Build l'environnement:
```sh
docker compose build
```
- Démarrer l'environnement
```sh
docker compose up -d
```
(Éteindre l'environnement si nécessaire: `docker compose down`)

# Commencer le développment
- Ouvrir PhpStorm
- Se connecter en remote développement en utilisant les informations `symfony@localhost` et le mot de passe `symfony`
- Vérifier que le serveur développement de symfony est ouvert en allant sur `http://localhost:8080`
- Vérifier que la base de donnée de développement est accessible en se connectant avec `HeidiSql`et les informations `ticketing:ticketing@localhost:3306/ticketing`
- Créer ou accéder à votre branche de développement

# Après un développement
- Tester le bon fonctionnement de votre code
- Créer des Tests Unitaires
(Vous pouvez utiliser les informations `root:rootpassword@localhost:3306` pour créer une base de donnée de test)
- Créer un version de migration lorsque des entitées ont été crées ou modifiées `php bin/console make:migration` (et n'oubliez pas de les compléter)
- Engager vos modifications
- Pousser le contenu de votre branche sur le répertoire distant GitHub
- Créer une Pull Request allant vers master (attention à toujours être à jour par rapport à master et d'ajouter dans la description de votre PR l'url de la branche parente)
