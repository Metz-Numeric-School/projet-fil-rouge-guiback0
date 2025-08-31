<?php

namespace App\Tests\Unit\Service\Payment;

use App\Service\Payment\StripeService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Stripe\StripeClient;
use Stripe\Collection;
use PHPUnit\Framework\Assert;

class StripeServiceTest extends KernelTestCase
{
    private StripeService $stripeService;
    private StripeClient&MockObject $stripeClient;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->stripeClient = $this->createMock(StripeClient::class);
        $this->stripeService = new class('test_key') extends StripeService {
            private StripeClient $mockClient;
            public function __construct(string $key) { 
                parent::__construct($key);
            }
            public function setMockClient(StripeClient $client): void {
                $this->mockClient = $client;
            }
            protected function getStripeClient(): StripeClient { 
                return $this->mockClient ?? parent::getStripeClient(); 
            }
        };
        $this->stripeService->setMockClient($this->stripeClient);
    }

    public function testGetCoffeesReturnsProductsData(): void
    {
        $productService = $this->getMockBuilder(\stdClass::class)->addMethods(['all'])->getMock();
        $collection = new Collection();
        $p1 = new \stdClass(); $p1->id='prod_coffee1'; $p1->name='Espresso';
        $p2 = new \stdClass(); $p2->id='prod_coffee2'; $p2->name='Cappuccino';
        $collection->data = [$p1,$p2];
        $productService->expects($this->once())->method('all')->with(['active'=>true])->willReturn($collection);
        $this->stripeClient->products = $productService;
        $res = $this->stripeService->getCoffees();
        $this->assertSame([$p1,$p2], $res);
    }

    public function testGetCoffeesReturnsEmptyArrayWhenNoProducts(): void
    {
        $productService = $this->getMockBuilder(\stdClass::class)->addMethods(['all'])->getMock();
        $collection = new Collection();
        $collection->data = [];
        $productService->expects($this->once())->method('all')->willReturn($collection);
        $this->stripeClient->products = $productService;
        $this->assertSame([], $this->stripeService->getCoffees());
    }

    public function testGetPriceReturnsPrice(): void
    {
        $priceService = $this->getMockBuilder(\stdClass::class)->addMethods(['retrieve'])->getMock();
        $priceObj = $this->createMock(\Stripe\Price::class);
        $priceObj->method('__get')->with('id')->willReturn('price_123');
        $priceService->expects($this->once())->method('retrieve')->with('price_123')->willReturn($priceObj);
        $this->stripeClient->prices = $priceService;
        $this->assertSame($priceObj, $this->stripeService->getPrice('price_123'));
    }

    public function testGetPriceReturnsNullOnException(): void
    {
        $priceService = $this->getMockBuilder(\stdClass::class)->addMethods(['retrieve'])->getMock();
        $priceService->expects($this->once())->method('retrieve')->willThrowException(new \Exception());
        $this->stripeClient->prices = $priceService;
        $this->assertNull($this->stripeService->getPrice('bad'));
    }

    public function testCreateCheckoutSessionSuccess(): void
    {
        $sessionService = $this->getMockBuilder(\stdClass::class)->addMethods(['create'])->getMock();
        $session = $this->createMock(\Stripe\Checkout\Session::class);
        $session->method('__get')->with('id')->willReturn('cs_123');
        $sessionService->expects($this->once())->method('create')->willReturn($session);
        $checkout = new \stdClass(); $checkout->sessions = $sessionService; $this->stripeClient->checkout = $checkout;
        $res = $this->stripeService->createCheckoutSession('price_x','https://ok/s','https://ok/c');
        $this->assertSame($session,$res);
    }

    public function testCreateCheckoutSessionFallbackSubscription(): void
    {
        $sessionService = $this->getMockBuilder(\stdClass::class)->addMethods(['create'])->getMock();
        $session = $this->createMock(\Stripe\Checkout\Session::class);
        $session->method('__get')->with('id')->willReturn('cs_sub');
        $sessionService->expects($this->exactly(2))->method('create')->willReturnCallback(function($params) use ($session){
            if($params['mode']==='payment'){ throw new \Exception('recurring price'); }
            return $session;
        });
        $checkout = new \stdClass(); $checkout->sessions=$sessionService; $this->stripeClient->checkout=$checkout;
        $res = $this->stripeService->createCheckoutSession('price_r','https://ok/s','https://ok/c');
        $this->assertSame($session,$res);
    }

    public function testCreateCheckoutSessionFailure(): void
    {
        $sessionService = $this->getMockBuilder(\stdClass::class)->addMethods(['create'])->getMock();
        $sessionService->expects($this->once())->method('create')->willThrowException(new \Exception('boom'));
        $checkout = new \stdClass(); $checkout->sessions=$sessionService; $this->stripeClient->checkout=$checkout;
        $this->assertNull($this->stripeService->createCheckoutSession('price_x','https://ok/s','https://ok/c'));
    }

    public function testVerifySession(): void
    {
        $sessionService = $this->getMockBuilder(\stdClass::class)->addMethods(['retrieve'])->getMock();
        $session = new \stdClass(); $session->id='cs_ver'; $session->payment_status='paid'; $session->amount_total=500; $session->currency='eur';
        $sessionService->expects($this->once())->method('retrieve')->willReturn($session);
        $checkout = new \stdClass(); $checkout->sessions=$sessionService; $this->stripeClient->checkout=$checkout;
        $data = $this->stripeService->verifySession('cs_ver');
        $this->assertEquals(['id'=>'cs_ver','payment_status'=>'paid','amount_total'=>500,'currency'=>'eur','paid'=>true], $data);
    }
}