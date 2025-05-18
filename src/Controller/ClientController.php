<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clients')]
final class ClientController extends AbstractController
{
    #[Route('', name: 'clients_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository): JsonResponse
    {
        return $this->json($clientRepository->findAll(), 200, [], ['groups' => 'client:read']);
    }

    #[Route('/{id}', name: 'clients_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $client = $entityManager->getRepository(Client::class)->find($id);
        if (!$client) {
            return $this->json(['erreur' => 'Aucun client avec cet ID dans la bibliothèque.'], 404);
        }
        return $this->json($client, 200, [], ['groups' => 'client:read']);
    }

    #[Route('/create', name: 'clients_create', methods: ['GET'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $errors = [];

        $nom = $request->query->get('nom');
        $prenom = $request->query->get('prenom');
        $email = $request->query->get('email');
        $tel = $request->query->get('tel');
        $adresse = $request->query->get('adresse');
        $dateNaissance = $request->query->get('date_naissance');

        if (!$nom) $errors[] = 'Paramètre nom manquant';
        if (!$prenom) $errors[] = 'Paramètre prenom manquant';
        if (!$email) $errors[] = 'Paramètre email manquant';
        if (!$tel) $errors[] = 'Paramètre tel manquant';
        if (!$adresse) $errors[] = 'Paramètre adresse manquant';
        if (!$dateNaissance) $errors[] = 'Paramètre date_naissance manquant';

        $parsedDateNaissance = $dateNaissance ? \DateTime::createFromFormat('Y-m-d', $dateNaissance) : null;
        if (!$parsedDateNaissance) $errors[] = 'date_naissance invalide. Format attendu : Y-m-d';

        if (count($errors) > 0) {
            return $this->json(['errors' => $errors], 400);
        }

        $client = new Client();
        $client->setNom($nom);
        $client->setPrenom($prenom);
        $client->setEmail($email);
        $client->setTel($tel);
        $client->setAdresse($adresse);
        $client->setDateNaissance($parsedDateNaissance);

        $em->persist($client);
        $em->flush();

        return $this->json($client, 200, [], ['groups' => 'client:read']);
    }

    #[Route('/{id}', name: 'clients_edit', methods: ['PUT'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $em): JsonResponse
    {
        $errors = [];

        $nom = $request->query->get('nom');
        $prenom = $request->query->get('prenom');
        $email = $request->query->get('email');
        $tel = $request->query->get('tel');
        $adresse = $request->query->get('adresse');
        $dateNaissance = $request->query->get('date_naissance');

        if ($nom !== null) $client->setNom($nom);
        if ($prenom !== null) $client->setPrenom($prenom);
        if ($email !== null) $client->setEmail($email);
        if ($tel !== null) $client->setTel($tel);
        if ($adresse !== null) $client->setAdresse($adresse);

        if ($dateNaissance !== null) {
            $d = \DateTime::createFromFormat('Y-m-d', $dateNaissance);
            if (!$d) $errors[] = 'date_naissance invalide';
            else $client->setDateNaissance($d);
        }

        if (count($errors) > 0) return $this->json(['errors' => $errors], 400);

        $em->flush();

        return $this->json($client, 200, [], ['groups' => 'client:read']);
    }

    #[Route('/{id}', name: 'clients_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $client = $em->getRepository(Client::class)->find($id);
        if (!$client) {
            return $this->json(['erreur' => 'Aucun client avec cet ID.'], 404);
        }
        $em->remove($client);
        $em->flush();

        return $this->json(null, 204);
    }

    #[Route('/search', name: 'clients_search', methods: ['GET'])]
    public function search(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $nom = $request->query->get('nom');
        $prenom = $request->query->get('prenom');
        $dateNaissanceStr = $request->query->get('date_naissance');
        $email = $request->query->get('email');
        $tel = $request->query->get('tel');
        $adresse = $request->query->get('adresse');
        $dateInscriptionStr = $request->query->get('date_inscription');

        $qb = $em->getRepository(Client::class)->createQueryBuilder('c');

        if ($nom !== null) {
            $qb->andWhere('LOWER(c.nom) LIKE LOWER(:nom)')
                ->setParameter('nom', '%' . $nom . '%');
        }

        if ($prenom !== null) {
            $qb->andWhere('LOWER(c.prenom) LIKE LOWER(:prenom)')
                ->setParameter('prenom', '%' . $prenom . '%');
        }

        if ($email !== null) {
            $qb->andWhere('LOWER(c.email) LIKE LOWER(:email)')
                ->setParameter('email', '%' . $email . '%');
        }

        if ($tel !== null) {
            $qb->andWhere('c.tel LIKE :tel')
                ->setParameter('tel', '%' . $tel . '%');
        }

        if ($adresse !== null) {
            $qb->andWhere('LOWER(c.adresse) LIKE LOWER(:adresse)')
                ->setParameter('adresse', '%' . $adresse . '%');
        }

        if ($dateNaissanceStr !== null) {
            $dn = \DateTime::createFromFormat('Y-m-d', $dateNaissanceStr);
            if (!$dn) return $this->json(['error' => 'date_naissance invalide'], 400);
            $qb->andWhere('c.date_naissance = :dn')
                ->setParameter('dn', $dn->format('Y-m-d'));
        }

        if ($dateInscriptionStr !== null) {
            $di = \DateTime::createFromFormat('Y-m-d', $dateInscriptionStr);
            if (!$di) return $this->json(['error' => 'date_inscription invalide'], 400);
            $qb->andWhere('c.date_inscription = :di')
                ->setParameter('di', $di->format('Y-m-d'));
        }

        $clients = $qb->getQuery()->getResult();

        if (empty($clients)) {
            return $this->json(['message' => 'Aucun client correspondant.'], 404);
        }

        return $this->json($clients, 200, [], ['groups' => 'client:read']);
    }
}
