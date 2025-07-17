<?php

namespace App\Controller\API;

use App\Entity\User;
use App\Entity\Zone;
use App\Entity\Acces;
use App\Service\OrganisationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/zones', name: 'api_zones_')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class APIZoneController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrganisationService $organisationService
    ) {}

    /**
     * Zones accessibles à l'utilisateur
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user instanceof User) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'INVALID_USER',
                    'message' => 'Utilisateur invalide'
                ], 401);
            }

            // Récupération des zones accessibles via les accès directs
            $accesses = $this->entityManager->getRepository(Acces::class)
                ->findBy(['badgeuse' => null]); // Adaptation selon la structure existante

            $zones = [];
            foreach ($accesses as $access) {
                $zone = $access->getZone();
                if ($zone) {
                    $zones[] = [
                        'id' => $zone->getId(),
                        'nom' => $zone->getNomZone(),
                        'description' => $zone->getDescription(),
                        'capacite' => $zone->getCapacite(),
                        'acces' => [
                            'date_installation' => $access->getDateInstallation()?->format('Y-m-d'),
                            'numero_badgeuse' => $access->getNumeroBadgeuse()
                        ]
                    ];
                }
            }

            // Récupération des zones via le service (si applicable)
            $serviceZones = $this->getServiceZones($user);
            $zones = array_merge($zones, $serviceZones);

            // Suppression des doublons
            $zones = $this->removeDuplicateZones($zones);

            return new JsonResponse([
                'success' => true,
                'data' => $zones
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération des zones'
            ], 500);
        }
    }

    /**
     * Détails d'une zone
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user instanceof User) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'INVALID_USER',
                    'message' => 'Utilisateur invalide'
                ], 401);
            }

            $zone = $this->entityManager->getRepository(Zone::class)->find($id);

            if (!$zone) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'ZONE_NOT_FOUND',
                    'message' => 'Zone non trouvée'
                ], 404);
            }

            // Vérification de l'accès à la zone
            if (!$this->userCanAccessZone($user, $zone)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'ACCESS_DENIED',
                    'message' => 'Accès refusé à cette zone'
                ], 403);
            }

            $zoneData = [
                'id' => $zone->getId(),
                'nom' => $zone->getNomZone(),
                'description' => $zone->getDescription(),
                'capacite' => $zone->getCapacite()
            ];

            // Récupération des badgeuses associées à la zone
            $badgeuses = $this->entityManager->getRepository(\App\Entity\Badgeuse::class)
                ->createQueryBuilder('b')
                ->join('b.acces', 'a')
                ->where('a.zone = :zone')
                ->setParameter('zone', $zone)
                ->getQuery()
                ->getResult();

            $zoneData['badgeuses'] = array_map(function ($badgeuse) {
                return [
                    'id' => $badgeuse->getId(),
                    'reference' => $badgeuse->getReference(),
                    'date_installation' => $badgeuse->getDateInstallation()->format('Y-m-d')
                ];
            }, $badgeuses);

            return new JsonResponse([
                'success' => true,
                'data' => $zoneData
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération de la zone'
            ], 500);
        }
    }

    /**
     * Historique d'accès à une zone
     */
    #[Route('/{id}/access-history', name: 'access_history', methods: ['GET'])]
    public function accessHistory(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user instanceof User) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'INVALID_USER',
                    'message' => 'Utilisateur invalide'
                ], 401);
            }

            $zone = $this->entityManager->getRepository(Zone::class)->find($id);

            if (!$zone) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'ZONE_NOT_FOUND',
                    'message' => 'Zone non trouvée'
                ], 404);
            }

            // Vérification de l'accès à la zone
            if (!$this->userCanAccessZone($user, $zone)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'ACCESS_DENIED',
                    'message' => 'Accès refusé à cette zone'
                ], 403);
            }

            // Paramètres de pagination et filtrage
            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(100, max(1, $request->query->getInt('limit', 20)));
            $startDate = $request->query->get('start_date');
            $endDate = $request->query->get('end_date');

            // Construction de la requête pour l'historique
            $queryBuilder = $this->entityManager->getRepository(\App\Entity\Pointage::class)
                ->createQueryBuilder('p')
                ->join('p.badgeuse', 'b')
                ->join('b.acces', 'a')
                ->join('p.badge', 'badge')
                ->join('badge.userBadges', 'ub')
                ->where('a.zone = :zone')
                ->andWhere('ub.date_fin IS NULL')
                ->orderBy('p.heure', 'DESC')
                ->setParameter('zone', $zone);

            // Filtrage par utilisateur (seul l'utilisateur connecté peut voir son historique, sauf managers)
            if (!$this->organisationService->isManager()) {
                $queryBuilder->andWhere('ub.Utilisateur = :user')
                    ->setParameter('user', $user);
            }

            // Filtrage par date
            if ($startDate) {
                $queryBuilder->andWhere('p.heure >= :startDate')
                    ->setParameter('startDate', new \DateTime($startDate));
            }

            if ($endDate) {
                $endDateTime = new \DateTime($endDate);
                $endDateTime->setTime(23, 59, 59);
                $queryBuilder->andWhere('p.heure <= :endDate')
                    ->setParameter('endDate', $endDateTime);
            }

            // Récupération du total pour la pagination
            $totalQuery = clone $queryBuilder;
            $totalRecords = $totalQuery->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();

            // Application de la pagination
            $offset = ($page - 1) * $limit;
            $pointages = $queryBuilder->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $history = [];
            foreach ($pointages as $pointage) {
                $userBadge = $this->entityManager->getRepository(\App\Entity\UserBadge::class)
                    ->findOneBy(['badge' => $pointage->getBadge(), 'date_fin' => null]);

                $history[] = [
                    'id' => $pointage->getId(),
                    'heure' => $pointage->getHeure()->format('Y-m-d H:i:s'),
                    'type' => $pointage->getType(),
                    'badgeuse' => [
                        'id' => $pointage->getBadgeuse()->getId(),
                        'reference' => $pointage->getBadgeuse()->getReference()
                    ],
                    'user' => $userBadge ? [
                        'id' => $userBadge->getUtilisateur()->getId(),
                        'nom' => $userBadge->getUtilisateur()->getNom(),
                        'prenom' => $userBadge->getUtilisateur()->getPrenom()
                    ] : null
                ];
            }

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'zone' => [
                        'id' => $zone->getId(),
                        'nom' => $zone->getNomZone()
                    ],
                    'history' => $history
                ],
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $totalRecords,
                    'total_pages' => ceil($totalRecords / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération de l\'historique'
            ], 500);
        }
    }

    /**
     * Statistiques d'une zone (managers uniquement)
     */
    #[Route('/{id}/stats', name: 'stats', methods: ['GET'])]
    #[IsGranted('ROLE_MANAGER')]
    public function stats(int $id, Request $request): JsonResponse
    {
        try {
            $zone = $this->entityManager->getRepository(Zone::class)->find($id);

            if (!$zone) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'ZONE_NOT_FOUND',
                    'message' => 'Zone non trouvée'
                ], 404);
            }

            // Vérification que la zone appartient à l'organisation
            if (!$this->zoneInUserOrganisation($zone)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'ACCESS_DENIED',
                    'message' => 'Accès refusé à cette zone'
                ], 403);
            }

            $date = $request->query->get('date', date('Y-m-d'));
            $startDate = new \DateTime($date);
            $startDate->setTime(0, 0, 0);
            $endDate = clone $startDate;
            $endDate->setTime(23, 59, 59);

            // Statistiques d'accès pour la journée
            $accessCount = $this->entityManager->getRepository(\App\Entity\Pointage::class)
                ->createQueryBuilder('p')
                ->join('p.badgeuse', 'b')
                ->join('b.acces', 'a')
                ->where('a.zone = :zone')
                ->andWhere('p.heure >= :startDate')
                ->andWhere('p.heure <= :endDate')
                ->setParameter('zone', $zone)
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->select('COUNT(p.id)')
                ->getQuery()
                ->getSingleScalarResult();

            // Utilisateurs uniques qui ont accédé à la zone
            $uniqueUsers = $this->entityManager->getRepository(\App\Entity\Pointage::class)
                ->createQueryBuilder('p')
                ->join('p.badgeuse', 'b')
                ->join('b.acces', 'a')
                ->join('p.badge', 'badge')
                ->join('badge.userBadges', 'ub')
                ->where('a.zone = :zone')
                ->andWhere('p.heure >= :startDate')
                ->andWhere('p.heure <= :endDate')
                ->andWhere('ub.date_fin IS NULL')
                ->setParameter('zone', $zone)
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->select('COUNT(DISTINCT ub.Utilisateur)')
                ->getQuery()
                ->getSingleScalarResult();

            $stats = [
                'zone' => [
                    'id' => $zone->getId(),
                    'nom' => $zone->getNomZone(),
                    'capacite' => $zone->getCapacite()
                ],
                'date' => $date,
                'total_accesses' => $accessCount,
                'unique_users' => $uniqueUsers,
                'occupancy_rate' => $zone->getCapacite() > 0 ?
                    round(($uniqueUsers / $zone->getCapacite()) * 100, 2) : 0
            ];

            return new JsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'FETCH_ERROR',
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Récupère les zones accessibles via le service de l'utilisateur
     */
    private function getServiceZones(User $user): array
    {
        $travailler = $this->entityManager->getRepository(\App\Entity\Travailler::class)
            ->findOneBy(['Utilisateur' => $user, 'date_fin' => null]);

        if (!$travailler || !$travailler->getService()) {
            return [];
        }

        $serviceZones = $this->entityManager->getRepository(\App\Entity\ServiceZone::class)
            ->findBy(['service' => $travailler->getService()]);

        $zones = [];
        foreach ($serviceZones as $serviceZone) {
            $zone = $serviceZone->getZone();
            if ($zone) {
                $zones[] = [
                    'id' => $zone->getId(),
                    'nom' => $zone->getNomZone(),
                    'description' => $zone->getDescription(),
                    'capacite' => $zone->getCapacite(),
                    'acces' => [
                        'type' => 'service',
                        'service' => $travailler->getService()->getNomService()
                    ]
                ];
            }
        }

        return $zones;
    }

    /**
     * Supprime les zones en double
     */
    private function removeDuplicateZones(array $zones): array
    {
        $uniqueZones = [];
        $seenIds = [];

        foreach ($zones as $zone) {
            if (!in_array($zone['id'], $seenIds)) {
                $uniqueZones[] = $zone;
                $seenIds[] = $zone['id'];
            }
        }

        return $uniqueZones;
    }

    /**
     * Vérifie si l'utilisateur peut accéder à une zone
     */
    private function userCanAccessZone(User $user, Zone $zone): bool
    {
        // Vérification des accès via le service
        $travailler = $this->entityManager->getRepository(\App\Entity\Travailler::class)
            ->findOneBy(['Utilisateur' => $user, 'date_fin' => null]);

        if ($travailler && $travailler->getService()) {
            $serviceZone = $this->entityManager->getRepository(\App\Entity\ServiceZone::class)
                ->findOneBy([
                    'service' => $travailler->getService(),
                    'zone' => $zone
                ]);

            return $serviceZone !== null;
        }

        return false;
    }

    /**
     * Vérifie si la zone appartient à l'organisation de l'utilisateur
     */
    private function zoneInUserOrganisation(Zone $zone): bool
    {
        $organisation = $this->organisationService->getCurrentUserOrganisation();

        if (!$organisation) {
            return false;
        }

        // Vérification via les services qui ont accès à la zone
        $serviceZones = $this->entityManager->getRepository(\App\Entity\ServiceZone::class)
            ->findBy(['zone' => $zone]);

        foreach ($serviceZones as $serviceZone) {
            if ($serviceZone->getService()->getOrganisation()->getId() === $organisation->getId()) {
                return true;
            }
        }

        return false;
    }
}
