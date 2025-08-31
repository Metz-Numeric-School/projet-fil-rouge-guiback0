<?php

namespace App\Tests\Integration\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Shared\DatabaseKernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRepositoryTest extends DatabaseKernelTestCase
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        parent::setUp();
        $container = static::getContainer();
        $this->userRepository = $container->get(UserRepository::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
    }

    public function testFindByEmail(): void
    {
        $user = (new User())
            ->setEmail('test@repository.com')
            ->setNom('Repository')
            ->setPrenom('Test')
            ->setPassword($this->passwordHasher->hashPassword(new User(), 'password123'));
        $this->em->persist($user);
        $this->em->flush();

        $foundUser = $this->userRepository->findOneBy(['email' => 'test@repository.com']);
        $this->assertNotNull($foundUser);
        $this->assertSame('test@repository.com', $foundUser->getEmail());
    }

    public function testUpgradePassword(): void
    {
        $user = (new User())
            ->setEmail('password@test.com')
            ->setNom('Password')
            ->setPrenom('Test')
            ->setPassword($this->passwordHasher->hashPassword(new User(), 'oldpassword'));
        $this->em->persist($user);
        $this->em->flush();

        $old = $user->getPassword();
        $new = $this->passwordHasher->hashPassword($user, 'newpassword');
        $this->userRepository->upgradePassword($user, $new);
        $this->assertNotSame($old, $user->getPassword());
    }

    public function testCountUsers(): void
    {
        $initial = $this->userRepository->count([]);
        $u1 = (new User())->setEmail('c1@test.com')->setNom('C1')->setPrenom('U')->setPassword('x');
        $u2 = (new User())->setEmail('c2@test.com')->setNom('C2')->setPrenom('U')->setPassword('y');
        $this->em->persist($u1); $this->em->persist($u2); $this->em->flush();
        $this->assertSame($initial + 2, $this->userRepository->count([]));
    }
}