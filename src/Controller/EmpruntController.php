<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Repository\EmpruntRepository;
use App\Entity\Client;
use App\Entity\Livre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/emprunts')]
final class EmpruntController extends AbstractController
{
    #[Route('', name: 'emprunts_index', methods: ['GET'])]
    public function index(EmpruntRepository $empruntRepository): JsonResponse
    {
        return $this->json($empruntRepository->findAll(), 200, [], ['groups' => 'emprunt:read']);
    }

    #[Route('/{id}', name: 'emprunts_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): JsonResponse
    {
        $emprunt = $em->getRepository(Emprunt::class)->find($id);
        if (!$emprunt) {
            return $this->json(['erreur' => 'Aucun emprunt avec cet ID.'], 404);
        }
        return $this->json($emprunt, 200, [], ['groups' => 'emprunt:read']);
    }

    #[Route('/create', name: 'emprunts_create', methods: ['GET'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $clientId = $request->query->get('client_id');
        $livreId = $request->query->get('livre_id');

        if (!$clientId || !$livreId) {
            return $this->json(['erreur' => 'Champs obligatoires manquants (client_id, livre_id)'], 400);
        }

        $errors = [];

        $client = $em->getRepository(Client::class)->find($clientId);
        $livre = $em->getRepository(Livre::class)->find($livreId);
        if (!$client) $errors[] = 'Client introuvable';
        if (!$livre) $errors[] = 'Livre introuvable';

        // Vérification que le livre n'est pas déjà emprunté
        if ($livre) {
            $empruntEnCours = $em->getRepository(Emprunt::class)->createQueryBuilder('e')
                ->where('e.livre = :livre')
                ->andWhere('e.date_retour IS NULL')
                ->setParameter('livre', $livre)
                ->getQuery()
                ->getOneOrNullResult();

            if ($empruntEnCours) {
                $errors[] = 'Ce livre est déjà emprunte et non encore retourne.';
            }
        }

        if (count($errors) > 0) {
            return $this->json(['erreurs' => $errors], 400);
        }

        // Utilise la date actuelle pour l'emprunt
        $dateEmprunt = new \DateTime(); // équivalent à NOW() sur php
        $dateLimite = (clone $dateEmprunt)->modify('+1 month');

        $emprunt = new Emprunt();
        $emprunt->setClient($client);
        $emprunt->setLivre($livre);
        $emprunt->setDateEmprunt($dateEmprunt);
        $emprunt->setDateLimiteRetour($dateLimite);

        $em->persist($emprunt);
        $em->flush();

        return $this->json($emprunt, 201, [], ['groups' => 'emprunt:read']);
    }



    #[Route('/{id}', name: 'emprunts_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $emprunt = $em->getRepository(Emprunt::class)->find($id);
        if (!$emprunt) {
            return $this->json(['erreur' => 'Aucun emprunt avec cet ID.'], 404);
        }

        $em->remove($emprunt);
        $em->flush();

        return $this->json(null, 204);
    }

    #[Route('/search', name: 'emprunts_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $clientId = $request->query->get('client_id');
        $livreId = $request->query->get('livre_id');

        $qb = $em->getRepository(Emprunt::class)->createQueryBuilder('e');

        if ($clientId !== null) {
            $qb->andWhere('e.client = :client')->setParameter('client', $clientId);
        }

        if ($livreId !== null) {
            $qb->andWhere('e.livre = :livre')->setParameter('livre', $livreId);
        }

        $results = $qb->getQuery()->getResult();

        if (empty($results)) {
            return $this->json(['message' => 'Aucun emprunt trouvé.'], 404);
        }

        return $this->json($results, 200, [], ['groups' => 'emprunt:read']);
    }
}
