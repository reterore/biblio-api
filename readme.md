#  Biblio-API

##  Objectif du projet

J’ai choisi de ne pas utiliser API Platform, malgré sa rapidité d’implémentation, car le but de ce projet était de tester mon niveau et de garder la maîtrise complète de chaque étape de la création d’une API REST avec Symfony.

---

##  Commande d’installation

```bash
symfony new biblio-api --webapp
```

> Cette commande installe la distribution complète de Symfony, incluant beaucoup plus que nécessaire pour une API REST simple.

---

## Composants symfony utilisés dans le projet

| Composant               | Raison de l'utiliser                                  |
|------------------------|--------------------------------------------------------|
| `framework-bundle`     | Base indispensable de Symfony                         |
| `routing`              | Définir les endpoints REST manuellement               |
| `maker-bundle`         | Générer entités, contrôleurs                          |
| `orm-pack`             | Manipulation des entités Doctrine                     |
| `validator`            | Validation des données entrantes                      |
| `serializer`           | Sérialisation / désérialisation JSON ↔ PHP            |
| `http-client`          | Appel API externe (Google Books)                      |
| `test-pack`            | Création de tests unitaires et fonctionnels           |

---

##  Base de données

- Utilisation de **MySQL** :
    - Parce que je le connais bien
    - adapté à la structure de ce projet
    - Utilisation de clés étrangères pour assurer l'intégrité des données

### Tables requises :

- `livres`
- `auteurs`
- `emprunts`

### Tables ajoutées :

- `clients` : pour associer les emprunts
- `genres` : table de référence pour classer les livres


- MCD:
  ![MCD](/img/MCD_db.png)

- MLD:
  ![MLD](/img/MLD_db.png)

---

## Modélisation

### MCD (Modèle Conceptuel de Données)
> *(Diagramme UML non inclus ici)*

### MLD (Modèle Logique de Données)
- 5 tables au total
- 2 tables de liaison (`emprunts`, `livres_genres`)

---

## Connexion de la base de données

1. Modifier `.env` :
   ```env
   DATABASE_URL="mysql://user:password@127.0.0.1:3306/biblio"
   ```

2. Créer les entités via Symfony CLI :

- ex:
   ```bash
   php bin/console make:entity Livre
   php bin/console make:entity Auteur
   ```

3. Appliquer les migrations :
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

---

## Sérialisation manuelle

Symfony ne sérialise pas les entités automatiquement. Il faut ajouter des annotations de groupes dans chaque entité :

- ex:

```php
use Symfony\Component\Serializer\Annotation\Groups;

#[Groups(['livre:read'])]
private $title;
```

- `read` → pour les champs exposés dans les réponses `GET`
- `write` → pour les champs acceptés en `POST` / `PUT`

> Ces groupes permettent un bon contrôle sur ce qui est accessible en read ou en write via l’API (plus de sécurité).

---

## CRUD avec Symfony

1. Installer MakerBundle si besoin :
   ```bash
   composer require symfony/maker-bundle --dev
   ```

2. Générer les CRUDs :
   ```bash
   php bin/console make:crud Livre
   ```

> Les CRUDs générés respectent automatiquement les conventions Symfony.
>
> Cependant je ne les ai pas utilisés pour mon projet car je voulais faire par moi même pour avoir plus de contrôle
> sur mes endpoints et pouvoir mieux debugué.


---

## Améliorations futures possibles

- Ajouter une authentification (JWT ou token)
- Ajouter des indexes dans la base pour optimiser les recherches (titre, disponibilité, etc.)
-

---

## Endpoints de l’API

### Endpoints de l'entité **Livre**

| Méthode | Endpoint                                       | Description                                |
|---------|------------------------------------------------|--------------------------------------------|
| GET     | `/livres`                                      | Liste tous les livres                      |
| GET     | `/livres/{id}`                                 | Affiche un livre par ID                    |
| GET     | `/livres/create`                               | Crée un livre         |
| PUT     | `/livres/{id}`                                 | Met à jour un livre                        |
| DELETE  | `/livres/{id}`                                 | Supprime un livre                          |
| GET     | `/livres/search`                               | Recherche par titre, auteur, genre         |
| POST    | `/livres/{livreId}/emprunter/{clientId}`       | Emprunt d’un livre                         |
| POST    | `/livres/{livreId}/rendre`                     | Rendre un livre                            |
| GET     | `/livres/disponibles`                          | Liste des livres actuellement disponibles  |

### Endpoints de l'entité **Auteur**

| Méthode | Endpoint                                 | Description                                    |
|---------|------------------------------------------|------------------------------------------------|
| GET     | `/auteurs`                               | Liste tous les auteurs                         |
| GET     | `/auteurs/{id}`                          | Affiche un auteur par ID                       |
| GET     | `/auteurs/create`                        | Crée un nouvel auteur                          |
| PUT     | `/auteurs/{id}`                          | Met à jour un auteur                           |
| DELETE  | `/auteurs/{id}`                          | Supprime un auteur                             |
| GET     | `/auteurs/search`                        | Recherche d’auteurs (nom, prénom, date, livre) |

### Endpoints de l'entité **Client**

| Méthode | Endpoint                         | Description                                          |
|---------|----------------------------------|------------------------------------------------------|
| GET     | `/clients`                       | Liste tous les clients                              |
| GET     | `/clients/{id}`                  | Affiche un client par ID                            |
| GET     | `/clients/create`                | Crée un nouveau client       |
| PUT     | `/clients/{id}`                  | Met à jour un client (tout champ modifiable)        |
| DELETE  | `/clients/{id}`                  | Supprime un client par son ID                       |
| GET     | `/clients/search`                | Recherche de clients selon plusieurs critères       |


### Endpoints de l'entité **Emprunt**

| Méthode | Endpoint                             | Description                                            |
|---------|--------------------------------------|--------------------------------------------------------|
| GET     | `/emprunts`                          | Liste tous les emprunts                               |
| GET     | `/emprunts/{id}`                     | Affiche un emprunt par ID                             |
| GET     | `/emprunts/create`                   | Crée un nouvel emprunt (client, livre, date optionnelle) |
| PUT     | `/emprunts/{id}`                     | Met à jour un emprunt (retour, client, livre, etc.)   |
| DELETE  | `/emprunts/{id}`                     | Supprime un emprunt par son ID                        |
| GET     | `/emprunts/search`                   | Recherche d'emprunts (client, livre, en cours, etc.)  |

---

## Tests unitaires

### Préparation

- Activer les extensions `pdo_sqlite` et `sqlite3`
- Ajouter un fichier `.env.test.local` avec :
  ```env
  DATABASE_URL="sqlite:///%kernel.project_dir%/var/data_test.sqlite"
  ```

### Création des tests

- Créer un fichier `tests/LivreTest.php`
- Ajouter des requêtes pour **tester tous les endpoints**

---

## Appels asynchrones avec Messenger

Pour mon projet j'ai uniquement fait appel à l'API pour enrichir le JSON de sortie. Cependant,
il aurait été possible d'utilisé Messenger pour le faire de manière asynchrone (pas les mêmes
notions de récurrences qu'avec JS). Ici on attends la réponse de la requête donc pas utile de
faire la requête à l'API en arrière plan, mais peut être quand même interessant si on veut faire
un create un peu complexe pour avoir accès au terminal pendant que la requête à l'API externe se
poursuis.

1. Créer un **message Symfony** (classe DTO)
2. Créer un **handler** (`MessageHandlerInterface`)
3. Appeler le message depuis `create()` ou `edit()` pour lancer des traitements asynchrones (ex. : enrichissement avec Google Books)

