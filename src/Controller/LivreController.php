<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/livres')]
final class LivreController extends AbstractController
{
    // client HTTP pour appeler des APIs externes, et le serializer qui transforme les objets PHP en JSON
    private HttpClientInterface $client;
    private SerializerInterface $serializer;

    // Constructeur pour HttpClient et Serializer
    public function __construct(HttpClientInterface $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'livres_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        // Récupère tous les livres de la base de données
        $livres = $em->getRepository(Livre::class)->findAll();
        $results = [];

        foreach ($livres as $livre) {
            // Sérialise l'entité Livre en tableau selon le groupe 'livre:read'
            $data = $this->serializer->normalize($livre, null, ['groups' => 'livre:read']);


            /*
               normalize(
               $object,              // L’objet PHP à transformer
               string|null $format,  // Format cible (ici: null = tableau PHP)
               array $context = []   // groupes de sérialisation
                )
             */

            $isbn = $livre->getIsbn();

            if ($isbn) {
                $isbnKey = 'ISBN:' . $isbn;

                try {
                    // Requête GET vers OpenLibrary API pour chercher avec ISBN
                    $response = $this->client->request('GET', 'https://openlibrary.org/api/books', [
                        'query' => [
                            'bibkeys' => $isbnKey,    // clé ISBN au format attendu par l'API
                            'format' => 'json',       // réponse en JSON
                            'jscmd' => 'data',        // données enrichies (titre, nb pages)
                        ],
                        'timeout' => 2.5,
                    ]);

                    // Si réponse HTTP 200, on parse et on récupère les données
                    if ($response->getStatusCode() === 200) {
                        $externalData = $response->toArray(false);

                        // Extrait le nombre de pages si disponible, sinon 'inconnu'
                        $data['nombre_pages'] = $externalData[$isbnKey]['number_of_pages'] ?? 'inconnu';
                    } else {
                        $data['nombre_pages'] = 'Non disponible';
                    }

                } catch (\Throwable) {
                    // En cas d’erreur réseau ou parsing JSON => valeur par défaut
                    $data['nombre_pages'] = 'Non disponible';
                }
            } else {
                $data['nombre_pages'] = 'Non disponible';
            }

            $results[] = $data;
        }

        return new JsonResponse($results);
    }


    #[Route('/{id}', name: 'livres_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(Request $request, int $id, EntityManagerInterface $em): JsonResponse
    {
        // Récupère un livre par son identifiant
        $livre = $em->getRepository(Livre::class)->find($id);

        if (!$livre) {
            // Si aucun livre ne correspond à l'ID, retourne une erreur 404
            return $this->json([
                'erreur' => 'Aucun livre avec cet ID dans la bibliothèque.'
            ], 404);
        }

        // Sérialisation de l'entité Livre en tableau associatif
        $data = $this->serializer->normalize($livre, null, ['groups' => 'livre:read']);

        // Vérifie si un ISBN est présent pour ce livre
        if ($livre->getIsbn()) {
            $isbnKey = 'ISBN:' . $livre->getIsbn();

            try {
                // Requête GET vers OpenLibrary API pour enrichir le livre
                $response = $this->client->request('GET', 'https://openlibrary.org/api/books', [
                    'query' => [
                        'bibkeys' => $isbnKey,
                        'format' => 'json',
                        'jscmd' => 'data',
                    ],
                    'timeout' => 2.5,
                ]);

                // Récupère les données si la réponse est correcte
                if ($response->getStatusCode() === 200) {
                    $externalData = $response->toArray(false);
                    $data['nombre_pages'] = $externalData[$isbnKey]['number_of_pages'] ?? 'inconnu';
                    $data['image_couverture'] = $externalData[$isbnKey]['cover']['large']
                        ?? $externalData[$isbnKey]['cover']['medium']
                        ?? $externalData[$isbnKey]['cover']['small']
                        ?? null;
                } else {
                    $data['nombre_pages'] = 'Non disponible';
                    $data['image_couverture'] = 'Non disponible';
                }

            } catch (\Throwable) {
                // En cas d’erreur réseau/parsing JSON => valeur par défaut
                $data['nombre_pages'] = 'Non disponible';
                $data['image_couverture'] = 'Non disponible';
            }
        } else {
            // Aucun ISBN => pas de requête API
            $data['nombre_pages'] = 'Non disponible';
            $data['image_couverture'] = 'Non disponible';
        }

        // Retourne le livre enrichi au format JSON
        return new JsonResponse($data);
    }


    #[Route('/create', name: 'livres_create', methods: ['GET'])]
    public function create(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $errors = [];

        $titre = $request->query->get('titre');
        $isbn = $request->query->get('isbn');
        $dateParutionStr = $request->query->get('date_parution');
        $auteurId = $request->query->get('auteur_id');
        $genreId = $request->query->get('genre_id');

        // Vérifications des champs
        if (!$titre) {
            $errors[] = 'Paramètre titre manquant';
        }

        if (!$isbn) {
            $errors[] = 'Paramètre isbn manquant';
        }

        if (!$dateParutionStr) {
            $errors[] = 'Paramètre date de parution manquant';
        }

        if (!$auteurId) {
            $errors[] = 'Paramètre auteur_id manquant';
        }

        if (!$genreId) {
            $errors[] = 'Paramètre genre_id manquant';
        }

        // Chargement des objets liés
        $auteur = $auteurId ? $em->getRepository(\App\Entity\Auteur::class)->find($auteurId) : null;
        if (!$auteur) {
            $errors[] = 'Auteur introuvable';
        }

        $genre = $genreId ? $em->getRepository(\App\Entity\Genre::class)->find($genreId) : null;
        if (!$genre) {
            $errors[] = 'Genre introuvable';
        }

        $dateParution = \DateTime::createFromFormat('Y-m-d', $dateParutionStr);
        if (!$dateParution) {
            $errors[] = 'Date invalide. Format attendu : Y-m-d';
        }

        // Retour si erreurs
        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], 400);
        }

        // Création du livre
        $livre = new Livre();
        $livre->setTitre($titre);
        $livre->setIsbn($isbn);
        $livre->setDateParution($dateParution);
        $livre->addAuteur($auteur);
        $livre->setGenre($genre);

        $em->persist($livre);
        $em->flush();

        return $this->json($livre, 201, [], ['groups' => 'livre:read']);
    }


    #[Route('/{id}', name: 'livres_edit', methods: ['PUT'])]
    public function edit(
        Request $request,
        Livre $livre,
        EntityManagerInterface $em
    ): JsonResponse {
        $errors = [];

        $titre = $request->query->get('titre');
        $isbn = $request->query->get('isbn');
        $dateParutionStr = $request->query->get('date_parution');
        $auteurId = $request->query->get('auteur_id');
        $genreId = $request->query->get('genre_id');

        // Si aucun paramètre n’est fourni, on retourne simplement le livre tel quel
        if ($titre === null && $isbn === null && $dateParutionStr === null && $auteurId === null && $genreId === null) {
            return $this->json($livre, 204, [], ['groups' => 'livre:read']);
        }

        if ($titre !== null) {
            $livre->setTitre($titre);
        }

        if ($isbn !== null) {
            $livre->setIsbn($isbn);
        }

        if ($dateParutionStr !== null) {
            $dateParution = \DateTime::createFromFormat('Y-m-d', $dateParutionStr);
            if (!$dateParution) {
                $errors[] = 'Date invalide. Format attendu : Y-m-d';
            } else {
                $livre->setDateParution($dateParution);
            }
        }

        if ($auteurId !== null) {
            $auteur = $em->getRepository(\App\Entity\Auteur::class)->find($auteurId);
            if (!$auteur) {
                $errors[] = 'Auteur introuvable';
            } else {
                $livre->getAuteurs()->clear();
                $livre->addAuteur($auteur);
            }
        }

        if ($genreId !== null) {
            $genre = $em->getRepository(\App\Entity\Genre::class)->find($genreId);
            if (!$genre) {
                $errors[] = 'Genre introuvable';
            } else {
                $livre->setGenre($genre);
            }
        }

        // En cas d’erreurs
        if (count($errors) > 0) {
            return $this->json(['erreurs' => $errors], 400);
        }

        $em->flush();

        return $this->json($livre, 200, [], ['groups' => 'livre:read']);
    }


    #[Route('/{id}', name: 'livres_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $livre = $entityManager->getRepository(\App\Entity\Livre::class)->find($id);

        if (!$livre) {
            return $this->json([
                'erreur' => 'Aucun livre avec cet ID dans la bibliothèque.'
            ], 404);
        }

        $entityManager->remove($livre);
        $entityManager->flush();

        return $this->json(null, 204);
    }

    #[Route('/search', name: 'livres_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $titre = $request->query->get('titre');
        $isbn = $request->query->get('isbn');
        $auteurNom = $request->query->get('auteur'); // nom ou prénom dans auteur pour la recherche (pas les 2)
        $genreId = $request->query->get('genre_id');

        $qb = $em->getRepository(Livre::class)->createQueryBuilder('l');

        if ($titre !== null) {
            $qb->andWhere('LOWER(l.titre) LIKE LOWER(:titre)')
                ->setParameter('titre', '%' . $titre . '%');
        }

        if ($isbn !== null) {
            $qb->andWhere('l.isbn = :isbn')
                ->setParameter('isbn', $isbn);
        }

        if ($auteurNom !== null) {
            $qb->join('l.auteurs', 'a')
                ->andWhere('LOWER(a.nom) LIKE LOWER(:auteurNom) OR LOWER(a.prenom) LIKE LOWER(:auteurNom)')
                ->setParameter('auteurNom', '%' . $auteurNom . '%');
        }

        if ($genreId !== null) {
            if (!is_numeric($genreId)) {
                return $this->json(['error' => 'genre_id doit être un entier.'], 400);
            }
            $qb->andWhere('l.genre = :genreId')
                ->setParameter('genreId', $genreId);
        }

        $livres = $qb->getQuery()->getResult();

        if (empty($livres)) {
            return $this->json(['message' => 'Aucun livre correspondant à la recherche.'], 404);
        }

        return $this->json($livres, 200, [], ['groups' => 'livre:read']);
    }

    #[Route('/{livreId}/emprunter/{clientId}', name: 'livres_emprunter', methods: ['POST'])]
    public function emprunter(int $livreId, int $clientId, EntityManagerInterface $em): JsonResponse
    {
        $livre = $em->getRepository(\App\Entity\Livre::class)->find($livreId);
        $client = $em->getRepository(\App\Entity\Client::class)->find($clientId);

        $errors = [];

        if (!$livre) {
            $errors[] = "aucun livre trouvé avec l'ID $livreId.";
        }

        if (!$client) {
            $errors[] = "aucun client trouvé avec l'ID $clientId.";
        }

        if ($livre && $client) {
            $empruntActif = $em->getRepository(\App\Entity\Emprunt::class) // Vérifie si le livre est actuellement emprunté
                ->createQueryBuilder('e')
                ->where('e.livre = :livre')
                ->andWhere('e.date_retour IS NULL')
                ->setParameter('livre', $livre)
                ->getQuery()
                ->getOneOrNullResult();

            if ($empruntActif) {
                $errors[] = "Ce livre est actuellement emprunté et n'est donc pas disponible.";
            }
        }

        if (!empty($errors)) {
            return $this->json(['erreurs' => $errors], 400);
        }

        $dateEmprunt = new \DateTime();
        $dateLimite = (clone $dateEmprunt)->modify('+1 month');

        $emprunt = new \App\Entity\Emprunt();
        $emprunt->setClient($client);
        $emprunt->setLivre($livre);
        $emprunt->setDateEmprunt($dateEmprunt);
        $emprunt->setDateLimiteRetour($dateLimite);

        $em->persist($emprunt);
        $em->flush();

        return $this->json($emprunt, 201, [], ['groups' => 'emprunt:read']);
    }

    #[Route('/{livreId}/rendre', name: 'livres_rendre', methods: ['POST'])]
    public function rendre(int $livreId, EntityManagerInterface $em): JsonResponse
    {
        $livre = $em->getRepository(\App\Entity\Livre::class)->find($livreId);

        if (!$livre) {
            return $this->json(['erreurs' => 'aucun livre trouvé avec l\'ID $livreId.'], 400);
        } else {
            $empruntActif = $em->getRepository(\App\Entity\Emprunt::class) // Vérifie si le livre est actuellement emprunté
            ->createQueryBuilder('e')
                ->where('e.livre = :livre')
                ->andWhere('e.date_retour IS NULL')
                ->setParameter('livre', $livre)
                ->getQuery()
                ->getOneOrNullResult();

            if ($empruntActif) {
                $dateRetour = new \DateTime();
                $empruntActif->setDateRetour($dateRetour);
                $em->persist($empruntActif);
                $em->flush();
                return $this->json($empruntActif, 201, [], ['groups' => 'emprunt:read']);
            } else {
                return $this->json(['erreurs' => 'le livre n\'est pas emprunter pour le moment'], 400);
            }
        }
    }
    #[Route('/disponibles', name: 'livres_disponibles', methods: ['GET'])]
    public function disponibles(EntityManagerInterface $em): JsonResponse
    {
        // Sous-requête : identifie les IDs des livres actuellement empruntés et non rendus
        $empruntsActifs = $em->getRepository(\App\Entity\Emprunt::class)
            ->createQueryBuilder('e')
            ->select('IDENTITY(e.livre)')
            ->where('e.date_retour IS NULL')
            ->getQuery()
            ->getSingleColumnResult();

        // Récupère tous les livres qui ne sont pas actuellement empruntés
        $qb = $em->getRepository(Livre::class)->createQueryBuilder('l');
        if (!empty($empruntsActifs)) {
            $qb->where($qb->expr()->notIn('l.id', ':ids'))
                ->setParameter('ids', $empruntsActifs);
        }

        $livresDisponibles = $qb->getQuery()->getResult();

        if (empty($livresDisponibles)) {
            return $this->json(['message' => 'Aucun livre disponible pour le moment.'], 404);
        }

        return $this->json($livresDisponibles, 200, [], ['groups' => 'livre:read']);
    }

}
