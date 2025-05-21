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
        $dateLimiteRetourStr = $request->query->get('date_limite_retour'); // optionnel sinon 1 mois après création de l'emprunt

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
                $errors[] = 'Ce livre est déjà emprunté actuellement.';
            }
        }

        if (count($errors) > 0) {
            return $this->json(['erreurs' => $errors], 400);
        }

        // Date d'emprunt = maintenant
        $dateEmprunt = new \DateTime();

        // Détermination de la date limite
        if ($dateLimiteRetourStr !== null) {
            $dateLimiteRetour = \DateTime::createFromFormat('Y-m-d', $dateLimiteRetourStr);
            if (!$dateLimiteRetour) {
                return $this->json(['erreur' => 'Format de date_limite_retour invalide. Format attendu : Y-m-d.'], 400);
            }
        } else {
            $dateLimiteRetour = (clone $dateEmprunt)->modify('+1 month');
        }

        $emprunt = new Emprunt();
        $emprunt->setClient($client);
        $emprunt->setLivre($livre);
        $emprunt->setDateEmprunt($dateEmprunt);
        $emprunt->setDateLimiteRetour($dateLimiteRetour);

        $em->persist($emprunt);
        $em->flush();

        return $this->json($emprunt, 201, [], ['groups' => 'emprunt:read']);
    }


    #[Route('/{id}', name: 'emprunts_update', methods: ['PUT'])]
    public function update(Request $request, int $id, EntityManagerInterface $em): JsonResponse
    {
        $emprunt = $em->getRepository(Emprunt::class)->find($id);

        if (!$emprunt) {
            return $this->json(['erreur' => "Aucun emprunt trouvé avec l'ID $id."], 404);
        }

        $errors = [];

        // Récupération des données de requête
        $dateRetourStr = $request->query->get('date_retour');
        $dateLimiteRetourStr = $request->query->get('date_limite_retour');
        $clientId = $request->query->get('client_id');
        $livreId = $request->query->get('id_livre');
        // Aucun paramètre fourni
        if ($dateRetourStr === null && $dateLimiteRetourStr === null && $clientId === null && $livreId === null) {
            return $this->json(['message' => 'Aucune donnée fournie pour la mise à jour.'], 400);
        }

        // Date de retour réelle
        if ($dateRetourStr !== null) {
            $dateRetour = \DateTime::createFromFormat('Y-m-d', $dateRetourStr);
            if (!$dateRetour) {
                $errors[] = "Format de date_retour invalide. Format attendu : Y-m-d.";
            } else {
                $emprunt->setDateRetour($dateRetour);
            }
        }

        // Date limite de retour
        if ($dateLimiteRetourStr !== null) {
            $dateLimite = \DateTime::createFromFormat('Y-m-d', $dateLimiteRetourStr);
            if (!$dateLimite) {
                $errors[] = "Format de date_limite_retour invalide. Format attendu : Y-m-d.";
            } else {
                $emprunt->setDateLimiteRetour($dateLimite);
            }
        }

        // Client
        if ($clientId !== null) {
            $client = $em->getRepository(\App\Entity\Client::class)->find($clientId);
            if (!$client) {
                $errors[] = "Client introuvable avec l'ID $clientId.";
            } else {
                $emprunt->setClient($client);
            }
        }

        // Livre
        if ($livreId !== null) {
            $livre = $em->getRepository(\App\Entity\Livre::class)->find($livreId);
            if (!$livre) {
                $errors[] = "Livre introuvable avec l'ID $livreId.";
            } else {
                $emprunt->setLivre($livre);
            }
        }

        // Erreurs ?
        if (!empty($errors)) {
            return $this->json(['erreurs' => $errors], 400);
        }

        $em->flush();

        return $this->json($emprunt, 200, [], ['groups' => 'emprunt:read']);
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
        $livreId = $request->query->get('livre_id') ?? $request->query->get('id_livre');
        $enCours = $request->query->get('en_cours'); // "true" ou "false"
        $nomClient = $request->query->get('nom_client');
        $prenomClient = $request->query->get('prenom_client');
        $titreLivre = $request->query->get('titre_livre');

        $qb = $em->getRepository(Emprunt::class)->createQueryBuilder('e')
            ->leftJoin('e.client', 'c')
            ->leftJoin('e.livre', 'l');

        if ($clientId !== null) {
            $qb->andWhere('c.id = :clientId')
                ->setParameter('clientId', $clientId);
        }

        if ($livreId !== null) {
            $qb->andWhere('l.id = :livreId')
                ->setParameter('livreId', $livreId);
        }

        if (!empty($nomClient)) {
            $qb->andWhere('LOWER(c.nom) LIKE :nomClient')
                ->setParameter('nomClient', '%' . strtolower($nomClient) . '%');
        }

        if (!empty($prenomClient)) {
            $qb->andWhere('LOWER(c.prenom) LIKE :prenomClient')
                ->setParameter('prenomClient', '%' . strtolower($prenomClient) . '%');
        }

        if (!empty($titreLivre)) {
            $qb->andWhere('LOWER(l.titre) LIKE :titreLivre')
                ->setParameter('titreLivre', '%' . strtolower($titreLivre) . '%');
        }

        if ($enCours === 'true') {
            $qb->andWhere('e.date_retour IS NULL');
        } elseif ($enCours === 'false') {
            $qb->andWhere('e.date_retour IS NOT NULL');
        }

        $results = $qb->getQuery()->getResult();

        if (empty($results)) {
            return $this->json(['message' => 'Rien ne correspond à votre recherche.'], 404);
        }

        return $this->json($results, 200, [], ['groups' => 'emprunt:read']);
    }

}
