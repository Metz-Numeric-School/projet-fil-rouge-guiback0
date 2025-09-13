<?php

namespace App\Tests\Unit\Service\User\GDPR;

use App\Entity\User;
use App\Service\User\GDPR\AccountDeactivationService;
use App\Tests\Shared\DatabaseKernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountDeactivationServiceTest extends DatabaseKernelTestCase
{
    private AccountDeactivationService $accountDeactivationService;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountDeactivationService = static::getContainer()->get(AccountDeactivationService::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testDeactivateAccount(): void
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);
        
        $user->setCompteActif(true);
        $this->em->flush();
        $this->assertTrue($user->isCompteActif());

        $result = $this->accountDeactivationService->deactivateAccount($user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('date_suppression_prevue', $result);
        $this->assertFalse($user->isCompteActif());
        $this->assertNotNull($user->getDateSuppressionPrevue());
        
        $this->assertGreaterThan(new \DateTime(), $user->getDateSuppressionPrevue());
        
        if ($result['date_suppression_prevue']) {
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result['date_suppression_prevue']);
        }
    }

    public function testDeactivateAccountMultipleTimes(): void
    {
        $user = new User();
        $user->setEmail('deactivate-multi-test@example.com');
        $user->setNom('TestDeactivateMulti');
        $user->setPrenom('User');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setCompteActif(true);
        $this->em->persist($user);
        $this->em->flush();

        $result1 = $this->accountDeactivationService->deactivateAccount($user);
        $dateSuppressionPrevue1 = $user->getDateSuppressionPrevue();
        
        $result2 = $this->accountDeactivationService->deactivateAccount($user);
        $dateSuppressionPrevue2 = $user->getDateSuppressionPrevue();

        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertFalse($user->isCompteActif());
        
        $this->assertLessThan(10, abs($dateSuppressionPrevue1->getTimestamp() - $dateSuppressionPrevue2->getTimestamp()));
    }
}