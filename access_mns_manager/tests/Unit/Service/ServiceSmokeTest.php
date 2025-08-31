<?php

namespace App\Tests\Unit\Service;

use App\Service\Pointage\BadgeValidatorService;
use App\Service\Pointage\PointageService;
use App\Service\Pointage\WorkTimeCalculatorService;
use App\Service\Pointage\ZoneAccessService;
use App\Service\User\UserService;
use App\Service\Payment\StripeService;
use App\Tests\Shared\DatabaseKernelTestCase;

class ServiceSmokeTest extends DatabaseKernelTestCase
{
    public function testAllServicesCanBeInstantiated(): void
    {
        $container = static::getContainer();
        
        $services = [
            BadgeValidatorService::class,
            PointageService::class,
            WorkTimeCalculatorService::class,
            ZoneAccessService::class,
            UserService::class,
            StripeService::class
        ];
        
        foreach ($services as $serviceClass) {
            $service = $container->get($serviceClass);
            $this->assertInstanceOf($serviceClass, $service);
        }
    }
    
    public function testPointageServiceExists(): void
    {
        $pointageService = static::getContainer()->get(PointageService::class);
        $this->assertInstanceOf(PointageService::class, $pointageService);
    }
    
    public function testBadgeValidatorServiceExists(): void
    {
        $badgeValidatorService = static::getContainer()->get(BadgeValidatorService::class);
        $this->assertInstanceOf(BadgeValidatorService::class, $badgeValidatorService);
    }
    
    public function testWorkTimeCalculatorServiceExists(): void
    {
        $workTimeCalculatorService = static::getContainer()->get(WorkTimeCalculatorService::class);
        $this->assertInstanceOf(WorkTimeCalculatorService::class, $workTimeCalculatorService);
    }
    
    public function testZoneAccessServiceExists(): void
    {
        $zoneAccessService = static::getContainer()->get(ZoneAccessService::class);
        $this->assertInstanceOf(ZoneAccessService::class, $zoneAccessService);
    }
    
}