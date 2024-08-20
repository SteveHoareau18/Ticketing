<?php

namespace App\Tests\Controller;

use App\Entity\Service;
use App\Entity\Ticket;
use App\Entity\Treatment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class HistoricControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = $this->client->getContainer()->get('security.password_hasher');
    }

    private function createUserAndLogin(array $roles = ['ROLE_USER']): User
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $existingUser = $userRepository->findOneBy(['email' => 'test@example.com']);

        if ($existingUser) {
            $this->client->loginUser($existingUser);
            return $existingUser;
        }

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setUsername('testuser');
        $user->setName('Test');
        $user->setFirstname('User');
        $user->setActive(true);
        $user->setRoles($roles);

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'testpassword');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);

        return $user;
    }

    private function createService(): Service
    {
        $service = new Service();
        $service->setName('Test Service');
        $this->entityManager->persist($service);
        $this->entityManager->flush();
        return $service;
    }

    public function testHistoricByServiceAsAdmin()
    {
        $this->createUserAndLogin(['ROLE_ADMIN']);

        $service = $this->createService();
        $this->client->request('GET', '/admin/historic/by-service/' . $service->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testHistoricByServiceAsUser()
    {
        $this->createUserAndLogin();

        $service = $this->createService();
        $this->client->request('GET', '/admin/historic/by-service/' . $service->getId());
        $this->assertResponseRedirects('/login'); // Assume non-admins are redirected to login
    }

    public function testHistoricByServiceSeeTicketAsAdmin()
    {
        $this->createUserAndLogin(['ROLE_ADMIN']);

        $service = $this->createService();
        $ticket = new Ticket();
        $ticket->setService($service);
        $ticket->setProblem('Test Problem');
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        $this->client->request('GET', '/admin/historic/by-service/' . $service->getId() . '/see/' . $ticket->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testHistoricByServiceSeeTicketAsUser()
    {
        $this->createUserAndLogin();

        $service = $this->createService();
        $ticket = new Ticket();
        $ticket->setService($service);
        $ticket->setProblem('Test Problem');
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        $this->client->request('GET', '/admin/historic/by-service/' . $service->getId() . '/see/' . $ticket->getId());
        $this->assertResponseRedirects('/login'); // Assume non-admins are redirected to login
    }

    public function testHistoricRelanceTreatmentForTicketAsAdmin()
    {
        $this->createUserAndLogin(['ROLE_ADMIN']);

        $service = $this->createService();
        $ticket = new Ticket();
        $ticket->setService($service);
        $ticket->setProblem('Test Problem');
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        $treatment = new Treatment();
        $treatment->setTicket($ticket);
        $treatment->setStartDate(new \DateTime());
        $this->entityManager->persist($treatment);
        $this->entityManager->flush();

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('relance-treatment' . $treatment->getId())->getValue();

        $this->client->request('POST', '/admin/historic/by-service/' . $service->getId() . '/relance/' . $treatment->getId() . '/treatment/' . $treatment->getId(), [
            'relanceReason' => 'Test relance reason',
            '_csrf_token' => $csrfToken,
            'email' => 'test@example.com'
        ]);

        $this->assertResponseRedirects('/admin/historic/by-service/' . $service->getId());
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.flash-success', 'Une relance a été crée et envoyé par mail');
    }

    public function testHistoricRelanceTreatmentForTicketAsUser()
    {
        $this->createUserAndLogin();

        $service = $this->createService();
        $ticket = new Ticket();
        $ticket->setService($service);
        $ticket->setProblem('Test Problem');
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        $treatment = new Treatment();
        $treatment->setTicket($ticket);
        $treatment->setStartDate(new \DateTime());
        $this->entityManager->persist($treatment);
        $this->entityManager->flush();

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('relance-treatment' . $treatment->getId())->getValue();

        $this->client->request('POST', '/admin/historic/by-service/' . $service->getId() . '/relance/' . $treatment->getId() . '/treatment/' . $treatment->getId(), [
            'relanceReason' => 'Test relance reason',
            '_csrf_token' => $csrfToken,
            'email' => 'test@example.com'
        ]);

        $this->assertResponseRedirects('/login'); // Assume non-admins are redirected to login
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
