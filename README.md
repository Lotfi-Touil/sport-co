# SportCo

## Symfony Docker (PHP8 / Caddy / Postgresql)

Un installateur et environnement d'exécution basé sur [Docker](https://www.docker.com/) pour le framework web [Symfony](https://symfony.com), avec support complet pour [HTTP/2](https://symfony.com/doc/current/weblink.html), HTTP/3 et HTTPS.

### Démarrage rapide

1. Si ce n'est pas déjà fait, [installez Docker Compose](https://docs.docker.com/compose/install/).
2. Exécutez `docker compose build --pull --no-cache` pour construire des images fraîches.
3. Exécutez `docker compose up` (les logs seront affichés dans le terminal actuel) ou `docker compose up -d` pour exécuter en arrière-plan.
4. Ouvrez `https://localhost` dans votre navigateur web préféré et [acceptez le certificat TLS auto-généré](https://stackoverflow.com/a/15076602/1352334).
5. Exécutez `docker compose down --remove-orphans` pour arrêter les conteneurs Docker.
6. Exécutez `docker compose logs -f` pour afficher les logs actuels, `docker compose logs -f [CONTAINER_NAME]` pour afficher les logs d'un conteneur spécifique.

### Commandes utiles

- **Liste des commandes:** `docker compose exec php bin/console`
- **Création de fichier vierge:** Controller `docker compose exec php bin/console make:controller`, etc.
- **Debug:** Supprimer le cache `docker compose exec php bin/console cache:clear`, voir les routes actives `docker compose exec php bin/console debug:router`, etc.
- **Gestion des routes:** [Documentation](https://symfony.com/doc/current/routing.html)
- **Autowiring & ParamConverter:** [Documentation](https://symfony.com/doc/current/service_container/autowiring.html)
- **Gestion de base de données:** Commandes pour la création d'entités, la mise à jour de la base de données, etc.

### Contributeurs et Fonctionnalités

Ce projet est le fruit du travail collaboratif de plusieurs développeurs talentueux, chacun apportant sa pierre à l'édifice :

- **Annaël Moussa** - [@annaelmoussa](https://github.com/annaelmoussa)
    - Suivi des Paiements et Gestion des Statuts de Paiement
    - Envoi par mail de liens de paiements
    - Tableau de bord administrateur
    - Génération de Rapports Financiers
- **Lotfi Touil** - [@Lotfi-Touil](https://github.com/Lotfi-Touil)
    - Création, Modification et Suppression de Devis
    - Création, Modification et Suppression de Factures
    - Envoi par mail de Devis et de Factures au client
    - Gestion des Clients
    - Gestion des Utilisateurs, des Rôles et des Droits d'Accès
- **Jason Afonso** - [@JasonAfs](https://github.com/JasonAfs)
- **Raouf Abdoumsa** - [@](https://github.com/)
      

### Lien vers le projet en production

[Accéder à SportCo en production](https://littleyarns.org/)

### Lien GitHub

[Repository GitHub de SportCo](https://github.com/Lotfi-Touil/sport-co)
