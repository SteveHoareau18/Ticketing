# Comment effectuer des tests unitaires

Le répertoire distant GitHub lance automatiquement vos tests unitaires.<br>
Il aide ainsi le lead-dev à valider votre développement.

Il n'est pas obligatoire que tous vos tests unitaires fonctionnent.

Pour tester en local, vérifier que votre environnement de [test](web/.env.test) est bien configuré.

Puis lancer les commandes:

```shell
php bin/console c:c --env=test
```

```shell
php bin/phpunit 
```

Il est fortement recommandé d'accepter la génération de tests unitaires avec l'artisan et de la compléter !