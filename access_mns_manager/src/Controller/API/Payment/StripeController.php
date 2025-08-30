<?php

namespace App\Controller\API\Payment;

use App\Service\Payment\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/stripe', name: 'api_stripe_')]
class StripeController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService
    ) {}

    /**
     * Récupération des produits café disponibles
     */
    #[Route('/coffees', name: 'coffees', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getCoffees(): JsonResponse
    {
        try {
            $products = $this->stripeService->getCoffees();

            $formattedProducts = [];
            foreach ($products as $product) {
                // Récupération du prix par défaut
                $defaultPrice = null;
                if (!empty($product->default_price)) {
                    $priceId = is_string($product->default_price) ? $product->default_price : $product->default_price->id;
                    $price = $this->stripeService->getPrice($priceId);
                    if ($price) {
                        $defaultPrice = [
                            'id' => $price->id,
                            'amount' => $price->unit_amount,
                            'currency' => $price->currency,
                            'formatted_amount' => number_format($price->unit_amount / 100, 2, ',', ' ') . ' €'
                        ];
                    }
                }

                $formattedProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'images' => $product->images,
                    'price' => $defaultPrice,
                    'created' => date('d M', $product->created)
                ];
            }

            return new JsonResponse([
                'success' => true,
                'data' => $formattedProducts,
                'count' => count($formattedProducts),
                'message' => 'Produits récupérés avec succès'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'STRIPE_ERROR',
                'message' => 'Erreur lors de la récupération des produits : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Création d'une session de checkout Stripe
     */
    #[Route('/create-checkout-session', name: 'create_checkout_session', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['priceId'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'MISSING_PRICE_ID',
                    'message' => 'L\'ID du prix est requis'
                ], 400);
            }

            $priceId = $data['priceId'];
            $successUrl = 'http://localhost/coffee?success=true';
            $cancelUrl = 'http://localhost/coffee?canceled=true';

            $session = $this->stripeService->createCheckoutSession($priceId, $successUrl, $cancelUrl);

            if (!$session) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'STRIPE_SESSION_ERROR',
                    'message' => 'Erreur lors de la création de la session de paiement'
                ], 500);
            }

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'sessionId' => $session->id,
                    'url' => $session->url
                ],
                'message' => 'Session de checkout créée avec succès'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'STRIPE_ERROR',
                'message' => 'Erreur lors de la création de la session : ' . $e->getMessage()
            ], 500);
        }
    }
}
