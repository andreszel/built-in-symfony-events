<?php

namespace App\Test\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/project/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Project::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Project index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $currentDate = new DateTime();
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'project[name]' => 'Testing',
            'project[slug]' => 'Testing',
            'project[description]' => 'Testing',
            'project[createdAt]' => $currentDate,
            'project[updatedAt]' => $currentDate,
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $currentDate = new DateTime();
        $this->markTestIncomplete();
        $fixture = new Project();
        $fixture->setName('My Title');
        $fixture->setSlug('My Title');
        $fixture->setDescription('My Title');
        $fixture->setCreatedAt($currentDate);
        $fixture->setUpdatedAt($currentDate);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Project');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $currentDate = new DateTime();
        $this->markTestIncomplete();
        $fixture = new Project();
        $fixture->setName('Value');
        $fixture->setSlug('Value');
        $fixture->setDescription('Value');
        $fixture->setCreatedAt($currentDate);
        $fixture->setUpdatedAt($currentDate);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'project[name]' => 'Something New',
            'project[slug]' => 'Something New',
            'project[description]' => 'Something New',
            'project[createdAt]' => $currentDate,
            'project[updatedAt]' => $currentDate,
        ]);

        self::assertResponseRedirects('/project/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getSlug());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame($currentDate, $fixture[0]->getCreatedAt());
        self::assertSame($currentDate, $fixture[0]->getUpdatedAt());
    }

    public function testRemove(): void
    {
        $currentDate = new DateTime();
        $this->markTestIncomplete();
        $fixture = new Project();
        $fixture->setName('Value');
        $fixture->setSlug('Value');
        $fixture->setDescription('Value');
        $fixture->setCreatedAt($currentDate);
        $fixture->setUpdatedAt($currentDate);

        $this->manager->remove($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/project/');
        self::assertSame(0, $this->repository->count([]));
    }
}
