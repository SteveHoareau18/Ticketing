<?php

namespace App\Tests\Controller;

use App\Entity\Treatment;
use App\Entity\User;
use App\Entity\Service;
use App\Entity\Ticket;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class TicketControllerTest extends WebTestCase
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

    private function createUserAndLogin(): User
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
        $user->setRoles(['ROLE_USER']);

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
        // Add other necessary properties for the Service

        $this->entityManager->persist($service);
        $this->entityManager->flush();

        return $service;
    }

    public function testCreateTicket()
    {
        $user = $this->createUserAndLogin();
        $service = $this->createService();
        $user->setService($service);
        $this->entityManager->flush();

        // Request the ticket creation page
        $crawler = $this->client->request('GET', '/ticket/new');
        $this->assertResponseIsSuccessful();

        // Submit the form to create a new ticket
        $form = $crawler->selectButton('Créer')->form([
            'ticket[problem]' => 'Problème de test',
            'ticket[service]' => $service->getId(),
        ]);

        $this->client->submit($form);

        // Assert redirection and verify the ticket was created
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        $ticket = $this->entityManager->getRepository(Ticket::class)->findOneBy(['problem' => 'Problème de test']);
        $this->assertNotNull($ticket);
        $this->assertEquals($user->getId(), $ticket->getCreator()->getId());
        $this->assertEquals($service->getId(), $ticket->getService()->getId());
        $this->assertFalse($ticket->isTransfered());
        $this->assertEquals("EN ATTENTE", $ticket->getStatus());
    }

    public function testViewTicket()
    {
        $user = $this->createUserAndLogin();
        $service = $this->createService();
        $user->setService($service);
        $this->entityManager->flush();

        $ticket = new Ticket();
        $ticket->setCreator($user);
        $ticket->setService($service);
        $ticket->setProblem('Problème de test');
        $ticket->setCreateDate(new \DateTime());
        $ticket->setTransfered(false);
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        // Request the ticket view page
        $crawler = $this->client->request('GET', '/ticket/see/' . $ticket->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testOpenTicketWithTreatment()
    {
        $user = $this->createUserAndLogin();
        $service = $this->createService();
        $user->setService($service);
        $this->entityManager->flush();

        $ticket = new Ticket();
        $ticket->setCreator($user);
        $ticket->setService($service);
        $ticket->setProblem('Problème de test');
        $ticket->setCreateDate(new \DateTime());
        $ticket->setTransfered(false);
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        // Request the ticket opening page
        $crawler = $this->client->request('GET', '/ticket/open/' . $ticket->getId());
        $this->assertResponseIsSuccessful();

        // Get the CSRF token
        $form = $crawler->selectButton('OUVRIR')->form();
        $csrfToken = $form->get('treatment[_token]')->getValue();

        // Submit the form to open the ticket and create a treatment
        $this->client->request('POST', '/ticket/open/' . $ticket->getId(), [
            'treatment[observations]' => 'First Treatment',
            '_token' => $csrfToken
        ]);

        // Assert redirection and verify the treatment was created
        $user = new User();
        $user->setEmail('another-test@example.com');
        $user->setUsername('anotheruser');
        $user->setName('Test');
        $user->setFirstname('User');
        $user->setActive(true);
        $user->setRoles(['ROLE_USER']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'testpassword');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->loginUser($user);

        $this->assertResponseRedirects('/ticket/see/' . $ticket->getId());
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Ticket Details'); // Adjust as necessary

        $treatments = $this->entityManager->getRepository(Treatment::class)->findBy(['ticket' => $ticket]);
        $this->assertCount(1, $treatments);
        $this->assertEquals('First Treatment', $treatments[0]->getObservations());

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function testCloseTicket()
    {
        $user = $this->createUserAndLogin();
        $service = $this->createService();
        $user->setService($service);
        $this->entityManager->flush();

        $ticket = new Ticket();
        $ticket->setCreator($user);
        $ticket->setService($service);
        $ticket->setProblem('Problème de test');
        $ticket->setCreateDate(new \DateTime());
        $ticket->setTransfered(false);
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        $userRepository = $this->entityManager->getRepository(User::class);

        $user = $userRepository->findOneBy(['email' => 'another-test@example.com']);

        if (!$user) {
            $user = new User();
            $user->setEmail('another-test@example.com');
            $user->setUsername('anotheruser');
            $user->setName('Test');
            $user->setFirstname('User');
            $user->setActive(true);
            $user->setRoles(['ROLE_USER']);

            $hashedPassword = $this->passwordHasher->hashPassword($user, 'testpassword');
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $this->client->loginUser($user);

        $treatment = new Treatment();
        $treatment->setTicket($ticket);
        $treatment->setUser($user);
        $treatment->setStartDate(new \DateTime());
        $treatment->setStatus('EN COURS');
        $treatment->setObservations("Oui il y a un probleme");
        $this->entityManager->persist($treatment);
        $this->entityManager->flush();

        // Request the ticket view page to get the CSRF token for closing
        $crawler = $this->client->request('GET', '/ticket/see/' . $ticket->getId());
        $this->assertResponseIsSuccessful();
        $html = $crawler->html();
        $this->assertStringContainsString('<input type="hidden" name="_csrf_token"', $html);

        // Find the hidden input field for the CSRF token
        $csrfTokenElements = $crawler->filter('input[name="_csrf_token"]');
        if ($csrfTokenElements->count() === 0) {
            throw new \Exception('CSRF token input field not found on the page.');
        }

        // Get the CSRF token value
        $csrfToken = $csrfTokenElements->attr('value');

        // Submit the form to close the ticket
        $this->client->request('POST', '/ticket/close/' . $ticket->getId(), [
            'closeReason' => 'Resolved Issue',
            '_csrf_token' => $csrfToken
        ]);

        // Assert redirection and verify the ticket was closed
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();// Adjust as necessary

        $this->entityManager->refresh($ticket);
        $this->assertEquals('Fermé', $ticket->getStatus());
        $this->assertNotNull($ticket->getResult());
        $this->assertNotNull($ticket->getResultDate());

        // Ensure the treatment status is closed
        $treatment = $this->entityManager->getRepository(Treatment::class)->findOneBy(['ticket' => $ticket]);
        $this->assertNotNull($treatment);
        $this->assertEquals('Fermé', $treatment->getStatus());
        $this->assertNotNull($treatment->getEndDate());

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
