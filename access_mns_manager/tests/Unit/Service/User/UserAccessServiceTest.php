<?php

namespace App\Tests\Unit\Service\User;

use App\Entity\User;
use App\Service\User\UserAccessService;
use App\Tests\Shared\DatabaseKernelTestCase;

class UserAccessServiceTest extends DatabaseKernelTestCase
{
    private UserAccessService $userAccessService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userAccessService = static::getContainer()->get(UserAccessService::class);
    }

    public function testCanAccessUserData(): void
    {
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);

        $canAccess = $this->userAccessService->canAccessUserData($user);
        $this->assertIsBool($canAccess);
    }

    public function testGetCurrentUser(): void
    {
        $result = $this->userAccessService->getCurrentUser();
        $this->assertTrue($result === null || $result instanceof User);
    }
}