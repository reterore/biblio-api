<?php

namespace App\Tests\Controller;

use App\Entity\Auteur;
use App\Entity\Genre;
use App\Entity\Livre;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LivreControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get(EntityManagerInterface::class);

        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $tool = new SchemaTool($this->em);
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
        }
    }

    private function createAuteurGenre(): array
    {
        $auteur = new Auteur();
        $auteur->setNom('Tolkien');
        $auteur->setPrenom('J.R.R.');
        $auteur->setDateNaissance(new \DateTime('1892-01-03'));

        $genre = new Genre();
        $genre->setNom('Fantasy');

        $this->em->persist($auteur);
        $this->em->persist($genre);
        $this->em->flush();

        return [$auteur, $genre];
    }

    private function createLivre(): Livre
    {
        [$auteur, $genre] = $this->createAuteurGenre();

        $livre = new Livre();
        $livre->setTitre('Le Seigneur des Anneaux');
        $livre->setIsbn('9782070612884');
        $livre->setDateParution(new \DateTime('1964-07-29'));
        $livre->addAuteur($auteur);
        $livre->setGenre($genre);

        $this->em->persist($livre);
        $this->em->flush();

        return $livre;
    }

    public function testLivreInexistant(): void
    {
        $this->client->request('GET', '/livres/9999');
        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateLivre(): void
    {
        [$auteur, $genre] = $this->createAuteurGenre();

        $params = http_build_query([
            'titre' => 'Le Seigneur des Anneaux',
            'isbn' => '9782070612884',
            'date_parution' => '1964-07-29',
            'auteur_id' => $auteur->getId(),
            'genre_id' => $genre->getId(),
        ]);

        $this->client->request('GET', '/livres/create?' . $params);
        self::assertResponseStatusCodeSame(201);
    }

    public function testLivreExistant(): void
    {
        $livre = $this->createLivre();
        $this->client->request('GET', '/livres/' . $livre->getId());
        self::assertResponseStatusCodeSame(200);
    }

    public function testSearchLivreByTitre(): void
    {
        $this->createLivre();
        $this->client->request('GET', '/livres/search?titre=Seigneur');
        self::assertResponseStatusCodeSame(200);
    }

    public function testDeleteLivre(): void
    {
        $livre = $this->createLivre();
        $this->client->request('DELETE', '/livres/' . $livre->getId());
        self::assertResponseStatusCodeSame(204);
    }

    public function testSearchNotFound(): void
    {
        $this->client->request('GET', '/livres/search?titre=inexistant');
        self::assertResponseStatusCodeSame(404);
    }
}
