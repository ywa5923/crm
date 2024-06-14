<?php

namespace App\Test\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ClientControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/client/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Client::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Client index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'client[firstName]' => 'Testing',
            'client[lastName]' => 'Testing',
            'client[birthdate]' => 'Testing',
            'client[address]' => 'Testing',
            'client[email]' => 'Testing',
            'client[phone]' => 'Testing',
            'client[company]' => 'Testing',
            'client[workstation]' => 'Testing',
            'client[agent]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->getRepository()->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Client();
        $fixture->setFirstName('My Title');
        $fixture->setLastName('My Title');
        $fixture->setBirthdate('My Title');
        $fixture->setAddress('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPhone('My Title');
        $fixture->setCompany('My Title');
        $fixture->setWorkstation('My Title');
        $fixture->setAgent('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Client');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Client();
        $fixture->setFirstName('Value');
        $fixture->setLastName('Value');
        $fixture->setBirthdate('Value');
        $fixture->setAddress('Value');
        $fixture->setEmail('Value');
        $fixture->setPhone('Value');
        $fixture->setCompany('Value');
        $fixture->setWorkstation('Value');
        $fixture->setAgent('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'client[firstName]' => 'Something New',
            'client[lastName]' => 'Something New',
            'client[birthdate]' => 'Something New',
            'client[address]' => 'Something New',
            'client[email]' => 'Something New',
            'client[phone]' => 'Something New',
            'client[company]' => 'Something New',
            'client[workstation]' => 'Something New',
            'client[agent]' => 'Something New',
        ]);

        self::assertResponseRedirects('/client/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getFirstName());
        self::assertSame('Something New', $fixture[0]->getLastName());
        self::assertSame('Something New', $fixture[0]->getBirthdate());
        self::assertSame('Something New', $fixture[0]->getAddress());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getPhone());
        self::assertSame('Something New', $fixture[0]->getCompany());
        self::assertSame('Something New', $fixture[0]->getWorkstation());
        self::assertSame('Something New', $fixture[0]->getAgent());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Client();
        $fixture->setFirstName('Value');
        $fixture->setLastName('Value');
        $fixture->setBirthdate('Value');
        $fixture->setAddress('Value');
        $fixture->setEmail('Value');
        $fixture->setPhone('Value');
        $fixture->setCompany('Value');
        $fixture->setWorkstation('Value');
        $fixture->setAgent('Value');

        $this->manager->remove($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/client/');
        self::assertSame(0, $this->repository->count([]));
    }
}
