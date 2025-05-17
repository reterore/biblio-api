<?php

namespace App\Controller;

use App\Entity\Livre;
use App\Form\LivreForm;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/livres')]
final class LivreController extends AbstractController
{
    #[Route('', name: 'livres_index', methods: ['GET'])]
    public function index(LivreRepository $livreRepository): JsonResponse
    {
        return $this->json($livreRepository->findAll(), 200, [], ['groups' => 'livre:read']);
    }

    #[Route('/{id}', name: 'livres_show', requirements: ['id' => '\d+'], methods: ['GET'])]  //requirements: ['id' => '\d+'] permet de s'assurer que l'id passer
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse       // est un nombre entier positif.
    {
        $livre = $entityManager->getRepository(\App\Entity\Livre::class)->find($id);
        if (!$livre) {
            return $this->json([
                'erreur' => 'Aucun livre avec cet ID dans la bibliothèque.'
            ], 404); // Code correct mais réponse propre
        }
        return $this->json($livre, 200, [], ['groups' => 'livre:read']);
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

        // Vérification de l'auteur en base
        $auteur = $em->getRepository(\App\Entity\Auteur::class)->find($auteurId);
        if (!$auteur) {
            $errors[] = 'Auteur introuvable';
        }

        // Vérification de la date
        $dateParution = \DateTime::createFromFormat('Y-m-d', $dateParutionStr);
        if (!$dateParution) {
            $errors[] = 'Date invalide. Format attendu : Y-m-d';
        }

        // Retour si erreurs secondaires
        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], 400);
        }

        // Création du livre
        $livre = new Livre();
        $livre->setTitre($titre);
        $livre->setIsbn($isbn);
        $livre->setDateParution($dateParution);
        $livre->addAuteur($auteur);

        $em->persist($livre);
        $em->flush();

        return $this->json($livre, 200, [], ['groups' => 'livre:read']);
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

        // Si aucun paramètre n’est fourni, on retourne simplement le livre tel quel
        if ($titre === null && $isbn === null && $dateParutionStr === null && $auteurId === null) {
            return $this->json($livre, 200, [], ['groups' => 'livre:read']);
        }

        // Application conditionnelle des modifications
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
                $livre->getAuteurs()->clear();  // si ManyToMany
                $livre->addAuteur($auteur);
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
            ], 404); // Code correct mais réponse propre
        }

        $entityManager->remove($livre);
        $entityManager->flush();

        return $this->json(null, 204); // Suppression OK, sans contenu
    }

    #[Route('/search', name: 'livres_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $titre = $request->query->get('titre');
        $isbn = $request->query->get('isbn');
        $auteurId = $request->query->get('auteur_id');

        $qb = $em->getRepository(Livre::class)->createQueryBuilder('l'); // l pour livre

        if ($titre !== null) {
            $qb->andWhere('LOWER(l.titre) LIKE LOWER(:titre)')
                ->setParameter('titre', '%' . $titre . '%');
        }

        if ($isbn !== null) {
            $qb->andWhere('l.isbn = :isbn')
                ->setParameter('isbn', $isbn);
        }

        if ($auteurId !== null) {
            if (!is_numeric($auteurId)) {
                return $this->json(['error' => 'auteur_id doit être un entier.'], 400);
            }

            $qb->join('l.auteurs', 'a')
                ->andWhere('a.id = :auteurId')
                ->setParameter('auteurId', $auteurId);
        }

        $livres = $qb->getQuery()->getResult();

        if (empty($livres)) {
            return $this->json(['message' => 'Aucun livre correspondant à la recherche.'], 404);
        }

        return $this->json($livres, 200, [], ['groups' => 'livre:read']);

    }

}
