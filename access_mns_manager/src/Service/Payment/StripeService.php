<?php

namespace App\Service\Payment;

use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class StripeService
{
   private StripeClient $stripeClient;

   public function __construct(
    #[Autowire('%env(STRIPE_API_KEY)%')] string $stripeApiKey
   )
   {
        $this->stripeClient = new StripeClient($stripeApiKey);
   }

   
   public function getCoffees(): array
   {
        return $this
        ->stripeClient
        ->products
        ->all(['active' => true])
        ->data;
   }

   public function getPrice(string $priceId): ?\Stripe\Price
   {
        try {
            return $this->stripeClient->prices->retrieve($priceId);
        } catch (\Exception) {
            return null;
        }
   }

   public function createCheckoutSession(string $priceId, string $successUrl, string $cancelUrl): ?\Stripe\Checkout\Session
   {
        try {
            // Essayer d'abord en mode payment (pour les achats uniques)
            return $this->stripeClient->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'type' => 'buy_me_coffee'
                ]
            ]);
        } catch (\Exception $e) {
            // Si ça échoue car c'est un prix récurrent, essayer en mode subscription
            if (strpos($e->getMessage(), 'recurring price') !== false) {
                try {
                    return $this->stripeClient->checkout->sessions->create([
                        'payment_method_types' => ['card'],
                        'line_items' => [[
                            'price' => $priceId,
                            'quantity' => 1,
                        ]],
                        'mode' => 'subscription',
                        'success_url' => $successUrl,
                        'cancel_url' => $cancelUrl,
                        'metadata' => [
                            'type' => 'buy_me_coffee'
                        ]
                    ]);
                } catch (\Exception) {
                    return null;
                }
            }
            return null;
        }
   }

   // Code spécifique au service Stripe
}