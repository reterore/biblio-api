<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Repository\AuteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/auteurs')]
final class AuteurController extends AbstractController
{
    #[Route('', name: 'auteurs_index', methods: ['GET'])]
    public function index(AuteurRepository $auteurRepository): JsonResponse
    {
        return $this->json($auteurRepository->findAll(), 200, [], ['groups' => 'auteur:read']);
    }

    #[Route('/{id}', name: 'auteurs_show', requirements: ['id' => '\d+'], methods: ['GET'])]  //requirements: ['id' => '\d+'] permet de s'assurer que l'id passé
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse        // est un nombre entier (positif)
    {
        $auteur = $entityManager->getRepository(\App\Entity\Auteur::class)->find($id);
        if (!$auteur) {
            return $this->json([
                'erreur' => 'Aucun auteur avec cet ID dans la bibliothèque.'], 404);
        }
        return $this->json($auteur, 200, [], ['groups' => 'auteur:read']);
    }

    #[Route('/create', name: 'auteurs_create', methods: ['GET'])]
    public function create(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $errors = [];

        $nom = $request->query->get('nom');
        $prenom = $request->query->get('prenom');
        $dateNaissance = $request->query->get('date_naissance');
        $dateMort = $request->query->get('date_mort');

        // Vérifications des champs
        if (!$nom) {
            $errors[] = 'Paramètre nom manquant';
        }

        if (!$prenom) {
            $errors[] = 'Paramètre prenom manquant';
        }

        if (!$dateNaissance) {
            $errors[] = 'Paramètre date de Naissance manquant';
        }

        if (!$dateMort) {
            $errors[] = 'Paramètre date de Mort manquant';
        }

        // Vérification format date de naissance
        if ($dateNaissance) {
            $parsedDateNaissance = \DateTime::createFromFormat('Y-m-d', $dateNaissance);
            if (!$parsedDateNaissance) {
                $errors[] = 'Date de naissance invalide. Format attendu : Y-m-d';
            }
        } else {
            $parsedDateNaissance = null;
        }

        // Vérification format date de mort (optionnelle)
        if ($dateMort) {
            $parsedDateMort = \DateTime::createFromFormat('Y-m-d', $dateMort);
            if (!$parsedDateMort) {
                $errors[] = 'Date de mort invalide. Format attendu : Y-m-d';
            }
        } else {
            $parsedDateMort = null;
        }


        // Retour listes des éventuelles erreurs
        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], 400);
        }

        $auteur = new Auteur();
        $auteur->setNom($nom);
        $auteur->setPrenom($prenom);
        $auteur->setDateNaissance($parsedDateNaissance);
        $auteur->setDateMort($parsedDateMort);

        $em->persist($auteur);
        $em->flush();

        return $this->json($auteur, 200, [], ['groups' => 'auteur:read']);
    }

    #[Route('/{id}', name: 'auteurs_edit', methods: ['PUT'])]
    public function edit(
        Request $request,
        Auteur $auteur,
        EntityManagerInterface $em
    ): JsonResponse {
        $errors = [];

        $nom = $request->query->get('nom');
        $prenom = $request->query->get('prenom');
        $dateNaissance = $request->query->get('date_naissance');
        $dateMort = $request->query->get('date_mort');

        // Si aucun paramètre n’est fourni, on retourne simplement l'auteur tel quel
        if ($prenom === null && $nom === null && $dateNaissance === null && $dateMort === null) {
            return $this->json($auteur, 200, [], ['groups' => 'auteur:read']);
        }

        // Application conditionnelle des modifications
        if ($nom !== null) {
            $auteur->setNom($nom);
        }

        if ($prenom !== null) {
            $auteur->setPrenom($prenom);
        }

        if ($dateNaissance !== null) {
            $dateNaissance = \DateTime::createFromFormat('Y-m-d', $dateNaissance);
            if (!$dateNaissance) {
                $errors[] = 'Date invalide. Format attendu : Y-m-d';
            } else {
                $auteur->setDateNaissance($dateNaissance);
            }
        }

        if ($dateMort !== null) {
            $dateMort = \DateTime::createFromFormat('Y-m-d', $dateMort);
            if (!$dateMort) {
                $errors[] = 'Date invalide. Format attendu : Y-m-d';
            } else {
                $auteur->setDateMort($dateMort);
            }
        }

        if (count($errors) > 0) {
            return $this->json(['erreurs' => $errors], 400);
        }

        $em->flush();

        return $this->json($auteur, 200, [], ['groups' => 'auteur:read']);
    }

    #[Route('/{id}', name: 'auteurs_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $auteur = $entityManager->getRepository(\App\Entity\Auteur::class)->find($id);

        if (!$auteur) {
            return $this->json([
                'erreur' => 'Aucun auteur avec cet ID dans la bibliothèque.'
            ], 404);
        }

        $entityManager->remove($auteur);
        $entityManager->flush();

        return $this->json(null, 204); // Suppression réalisé avec succès
    }

    #[Route('/search', name: 'auteurs_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $nom = $request->query->get('nom');
        $prenom = $request->query->get('prenom');
        $livreId = $request->query->get('livre_id');
        $dateNaissanceStr = $request->query->get('date_naissance');

        $qb = $em->getRepository(Auteur::class)->createQueryBuilder('a');

        if ($nom !== null) {
            $qb->andWhere('LOWER(a.nom) LIKE LOWER(:nom)')
                ->setParameter('nom', '%' . $nom . '%');
        }

        if ($prenom !== null) {
            $qb->andWhere('LOWER(a.prenom) LIKE LOWER(:prenom)')
                ->setParameter('prenom', '%' . $prenom . '%');
        }

        if ($dateNaissanceStr !== null) {
            $dateNaissance = \DateTime::createFromFormat('Y-m-d', $dateNaissanceStr);
            if (!$dateNaissance) {
                return $this->json(['error' => 'date_naissance invalide. Format attendu : Y-m-d'], 400);
            }

            $qb->andWhere('a.date_naissance = :dateNaissance')
                ->setParameter('dateNaissance', $dateNaissance->format('Y-m-d'));
        }

        if ($livreId !== null) {
            if (!is_numeric($livreId)) {
                return $this->json(['error' => 'livre_id doit être un entier positif.'], 400);
            }

            $qb->join('a.livres', 'l')
                ->andWhere('l.id = :livreId')
                ->setParameter('livreId', $livreId);
        }

        $auteurs = $qb->getQuery()->getResult();

        if (empty($auteurs)) {
            return $this->json(['message' => 'Aucun auteur correspondant à la recherche.'], 404);
        }

        return $this->json($auteurs, 200, [], ['groups' => 'auteur:read']);
    }
}
