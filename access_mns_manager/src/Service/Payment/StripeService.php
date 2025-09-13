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

   
   protected function getStripeClient(): StripeClient
   {
        return $this->stripeClient;
   }

   public function getCoffees(): array
   {
        return $this
        ->getStripeClient()
        ->products
        ->all(['active' => true])
        ->data;
   }

   public function getPrice(string $priceId): ?\Stripe\Price
   {
        try {
            return $this->getStripeClient()->prices->retrieve($priceId);
        } catch (\Exception) {
            return null;
        }
   }

   public function createCheckoutSession(string $priceId, string $successUrl, string $cancelUrl): ?\Stripe\Checkout\Session
   {
        try {
            // Essayer d'abord en mode payment (pour les achats uniques)
            return $this->getStripeClient()->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => rtrim($successUrl, '/') . '?success=true&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => rtrim($cancelUrl, '/') . '?canceled=true',
                'metadata' => [
                    'type' => 'buy_me_coffee'
                ]
            ]);
        } catch (\Exception $e) {
            // Si ça échoue car c'est un prix récurrent, essayer en mode subscription
            if (strpos($e->getMessage(), 'recurring price') !== false) {
                try {
                    return $this->getStripeClient()->checkout->sessions->create([
                        'payment_method_types' => ['card'],
                        'line_items' => [[
                            'price' => $priceId,
                            'quantity' => 1,
                        ]],
                        'mode' => 'subscription',
                        'success_url' => rtrim($successUrl, '/') . '?success=true&session_id={CHECKOUT_SESSION_ID}',
                        'cancel_url' => rtrim($cancelUrl, '/') . '?canceled=true',
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

   public function verifySession(string $sessionId): ?array
   {
        try {
            $session = $this->getStripeClient()->checkout->sessions->retrieve($sessionId, []);
            return [
                'id' => $session->id,
                'payment_status' => $session->payment_status,
                'amount_total' => $session->amount_total,
                'currency' => $session->currency,
                'paid' => $session->payment_status === 'paid'
            ];
        } catch (\Exception) {
            return null;
        }
   }

   // Code spécifique au service Stripe
}