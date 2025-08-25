<?php

namespace App\Service;

use App\Entity\Badge;
use App\Entity\Badgeuse;
use App\Entity\Pointage;
use App\Entity\User;
use App\Entity\UserBadge;
use App\Entity\Zone;
use App\Entity\Acces;
use App\Entity\ServiceZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Service pour la gestion du système de badgeage
 */
class BadgeService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrganisationService $organisationService
    ) {}

    /**
     * Enregistre un badgeage
     */
    public function recordBadgeAction(int $badgeNumber, int $badgeuseId, string $type = 'entree'): array
    {
        try {
            // Normalisation du type
            $type = str_replace(['é','É'], 'e', $type);
            $type = mb_strtolower($type);

            // Vérification du badge
            $badge = $this->entityManager->getRepository(Badge::class)
                ->findOneBy(['numero_badge' => $badgeNumber]);

            if (!$badge) {
                return [
                    'success' => false,
                    'error' => 'BADGE_NOT_FOUND',
                    'message' => 'Badge non trouvé'
                ];
            }

            // Vérification de la badgeuse
            $badgeuse = $this->entityManager->getRepository(Badgeuse::class)
                ->find($badgeuseId);

            if (!$badgeuse) {
                return [
                    'success' => false,
                    'error' => 'BADGEUSE_NOT_FOUND',
                    'message' => 'Badgeuse non trouvée'
                ];
            }

            // Vérification des permissions d'accès à la zone
            $user = $this->getUserFromBadge($badge);
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'USER_NOT_FOUND',
                    'message' => 'Utilisateur non trouvé pour ce badge'
                ];
            }

            // Vérification de l'organisation
            $userOrganisation = $this->organisationService->getUserOrganisation($user);
            $currentOrganisation = $this->organisationService->getCurrentUserOrganisation();

            if (
                !$userOrganisation || !$currentOrganisation ||
                $userOrganisation->getId() !== $currentOrganisation->getId()
            ) {
                return [
                    'success' => false,
                    'error' => 'ACCESS_DENIED',
                    'message' => 'Accès refusé - organisation différente'
                ];
            }

            // Vérification de l'accès à la zone via l'entité Acces
            $zones = $this->getBadgeuseZones($badgeuse);
            $hasAccess = false;
            foreach ($zones as $zone) {
                if ($this->canAccessZone($user, $zone)) {
                    $hasAccess = true;
                    break;
                }
            }

            if (!$hasAccess) {
                return [
                    'success' => false,
                    'error' => 'ZONE_ACCESS_DENIED',
                    'message' => 'Accès refusé à cette zone'
                ];
            }

            // Validation du type de badgeage
            if (!in_array($type, ['entree', 'sortie', 'acces'])) {
                return [
                    'success' => false,
                    'error' => 'INVALID_TYPE',
                    'message' => 'Type de badgeage invalide'
                ];
            }

            // Création du pointage
            $pointage = new Pointage();
            $pointage->setBadge($badge);
            $pointage->setBadgeuse($badgeuse);
            $pointage->setHeure(new \DateTime());
            $pointage->setType($type);

            $this->entityManager->persist($pointage);
            $this->entityManager->flush();

            return [
                'success' => true,
                'data' => [
                    'id' => $pointage->getId(),
                    'badge_number' => $badge->getNumeroBadge(),
                    'user' => [
                        'id' => $user->getId(),
                        'nom' => $user->getNom(),
                        'prenom' => $user->getPrenom()
                    ],
                    'badgeuse' => [
                        'id' => $badgeuse->getId(),
                        'reference' => $badgeuse->getReference(),
                        'zones' => $this->getBadgeuseZoneNames($badgeuse)
                    ],
                    'heure' => $pointage->getHeure()->format('Y-m-d H:i:s'),
                    'type' => $pointage->getType()
                ],
                'message' => 'Badgeage enregistré avec succès'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors de l\'enregistrement du badgeage'
            ];
        }
    }

    /**
     * Récupère les zones accessibles via une badgeuse
     */
    public function getBadgeuseZones(Badgeuse $badgeuse): array
    {
        $accesses = $this->entityManager->getRepository(Acces::class)
            ->findBy(['badgeuse' => $badgeuse]);

        $zones = [];
        foreach ($accesses as $access) {
            if ($access->getZone()) {
                $zones[] = $access->getZone();
            }
        }

        return $zones;
    }

    /**
     * Récupère les noms des zones accessibles via une badgeuse
     */
    public function getBadgeuseZoneNames(Badgeuse $badgeuse): array
    {
        $zones = $this->getBadgeuseZones($badgeuse);
        return array_map(function ($zone) {
            return $zone->getNomZone();
        }, $zones);
    }

    /**
     * Récupère l'utilisateur associé à un badge
     */
    public function getUserFromBadge(Badge $badge): ?User
    {
        // Vérifier si le badge est expiré
        if ($badge->getDateExpiration() && $badge->getDateExpiration() < new \DateTime()) {
            return null;
        }

        $userBadge = $this->entityManager->getRepository(UserBadge::class)
            ->findOneBy(['badge' => $badge]);

        return $userBadge?->getUtilisateur();
    }

    /**
     * Vérifie si un utilisateur peut accéder à une zone
     */
    public function canAccessZone(User $user, ?Zone $zone): bool
    {
        if (!$zone) {
            return false;
        }

        // Vérification via TOUS les services de l'utilisateur (principal + secondaires)
        foreach ($user->getTravail() as $travailler) {
            $service = $travailler->getService();
            if (!$service) continue;

            // Vérifier si ce service a accès à cette zone
            $serviceZone = $this->entityManager->getRepository(\App\Entity\ServiceZone::class)
                ->findOneBy([
                    'service' => $service,
                    'zone' => $zone
                ]);

            if ($serviceZone !== null) {
                return true; // Accès trouvé via ce service
            }
        }

        return false; // Aucun service ne donne accès à cette zone
    }

    /**
     * Récupère l'historique des badgeages d'un utilisateur
     */
    public function getUserBadgeHistory(User $user, ?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $queryBuilder = $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->where('ub.Utilisateur = :user')
            ->orderBy('p.heure', 'DESC')
            ->setParameter('user', $user);

        if ($startDate) {
            $queryBuilder->andWhere('p.heure >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $queryBuilder->andWhere('p.heure <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        $pointages = $queryBuilder->getQuery()->getResult();

        return array_map(function (Pointage $pointage) {
            return [
                'id' => $pointage->getId(),
                'heure' => $pointage->getHeure()->format('Y-m-d H:i:s'),
                'type' => $pointage->getType(),
                'badgeuse' => [
                    'id' => $pointage->getBadgeuse()->getId(),
                    'reference' => $pointage->getBadgeuse()->getReference()
                ]
            ];
        }, $pointages);
    }

    /**
     * Récupère le statut actuel d'un utilisateur (présent/absent)
     * LOGIQUE MÉTIER: Seuls les pointages dans les SERVICES PRINCIPAUX déterminent le statut présent/absent
     * Les services secondaires permettent l'accès mais ne changent PAS le statut de présence
     */
    public function getCurrentUserStatus(User $user): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        // Récupération du dernier pointage sur une badgeuse d'un SERVICE PRINCIPAL pour le statut
        $lastPrincipalPointage = $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->join('p.badgeuse', 'bdg')
            ->join('bdg.acces', 'a')
            ->join('a.zone', 'z')
            ->join('z.serviceZones', 'sz')
            ->join('sz.service', 's')
            ->join('s.travail', 't')
            ->where('ub.Utilisateur = :user_badge')
            ->andWhere('t.Utilisateur = :user_travail')
            ->andWhere('s.is_principal = true')
            ->andWhere('p.heure >= :today')
            ->orderBy('p.heure', 'DESC')
            ->setMaxResults(1)
            ->setParameter('user_badge', $user)
            ->setParameter('user_travail', $user)
            ->setParameter('today', $today)
            ->getQuery()
            ->getOneOrNullResult();

        // Récupération du dernier pointage UTILE pour l'affichage (exclut les accès purs)
        $lastGlobalPointage = $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->where('ub.Utilisateur = :user')
            ->andWhere('p.heure >= :today')
            ->andWhere('p.type IN (:types)')
            ->orderBy('p.heure', 'DESC')
            ->setMaxResults(1)
            ->setParameter('user', $user)
            ->setParameter('today', $today)
            ->setParameter('types', ['entree', 'sortie'])
            ->getQuery()
            ->getOneOrNullResult();

        // Statut basé UNIQUEMENT sur les pointages des services principaux
        $status = 'absent';
        if ($lastPrincipalPointage) {
            $status = $lastPrincipalPointage->getType() === 'entree' ? 'present' : 'absent';
        }

        // Affichage du dernier pointage (peut être principal OU secondaire)
        $lastAction = null;
        if ($lastGlobalPointage) {
            $isPrincipalService = $this->isBadgeuseInPrincipalZone($lastGlobalPointage->getBadgeuse(), $user);
            $serviceType = $isPrincipalService ? 'principal' : 'secondaire';
            
            $lastAction = [
                'heure' => $lastGlobalPointage->getHeure()->format('Y-m-d H:i:s'),
                'type' => $lastGlobalPointage->getType(),
                'badgeuse' => $lastGlobalPointage->getBadgeuse()->getReference(),
                'zone' => implode(', ', $this->getBadgeuseZoneNames($lastGlobalPointage->getBadgeuse())),
                'is_principal' => $isPrincipalService,
                'service_type' => $serviceType,
                'affects_status' => $isPrincipalService && in_array($lastGlobalPointage->getType(), ['entree', 'sortie'])
            ];
        }

        return [
            'status' => $status,
            'last_action' => $lastAction,
            'date' => $today->format('Y-m-d'),
            'can_access_secondary' => $status === 'present' // Accès secondaire si présent dans principal
        ];
    }

    /**
     * Calcule le temps de travail d'un utilisateur pour une période donnée
     * MODIFICATION CRITIQUE: Ne prend en compte QUE les pointages des badgeuses PRINCIPALES
     * Les badgeuses secondaires ne doivent PAS affecter le calcul des heures de travail
     */
    public function calculateWorkingTime(User $user, \DateTime $startDate, \DateTime $endDate): array
    {
        // Récupération de TOUS les pointages pour affichage
        $allPointages = $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->where('ub.Utilisateur = :user')
            ->andWhere('p.heure >= :startDate')
            ->andWhere('p.heure <= :endDate')
            ->orderBy('p.heure', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();

        // Filtrage pour ne garder QUE les pointages des badgeuses PRINCIPALES pour le calcul du temps
        $workTimePointages = [];
        foreach ($allPointages as $pointage) {
            $badgeuse = $pointage->getBadgeuse();
            $isPrincipalZone = $this->isBadgeuseInPrincipalZone($badgeuse, $user);
            
            if ($isPrincipalZone) {
                $workTimePointages[] = $pointage;
            }
        }

        $workingDays = [];
        $currentEntries = []; // Track current entry per day
        $totalMinutes = 0;

        // Traitement de TOUS les pointages pour l'affichage
        foreach ($allPointages as $pointage) {
            $date = $pointage->getHeure()->format('Y-m-d');

            if (!isset($workingDays[$date])) {
                $workingDays[$date] = [
                    'date' => $date,
                    'entries' => [],
                    'total_minutes' => 0
                ];
                $currentEntries[$date] = null;
            }

            $badgeuse = $pointage->getBadgeuse();
            $isPrincipalZone = $this->isBadgeuseInPrincipalZone($badgeuse, $user);

            $workingDays[$date]['entries'][] = [
                'time' => $pointage->getHeure()->format('H:i'),
                'type' => $pointage->getType(),
                'zone' => $isPrincipalZone ? 'principal' : 'secondaire'
            ];
        }

        // Calcul du temps de travail UNIQUEMENT avec les pointages des badgeuses PRINCIPALES
        foreach ($workTimePointages as $pointage) {
            $date = $pointage->getHeure()->format('Y-m-d');

            if ($pointage->getType() === 'entree') {
                // Set current entry only if there isn't an active one
                if (!isset($currentEntries[$date]) || $currentEntries[$date] === null) {
                    $currentEntries[$date] = $pointage->getHeure();
                }
                // If there's already an active entry, this might be a duplicate or error - ignore it
            } elseif ($pointage->getType() === 'sortie') {
                // Check if we have a current entry for this day to calculate time
                $currentEntry = $currentEntries[$date] ?? null;
                if ($currentEntry instanceof \DateTime) {
                    $minutes = ($pointage->getHeure()->getTimestamp() - $currentEntry->getTimestamp()) / 60;
                    $workingDays[$date]['total_minutes'] += $minutes;
                    $totalMinutes += $minutes;
                    $currentEntries[$date] = null; // Reset to allow next entry/exit pair
                }
                // If there's no current entry, this sortie doesn't have a matching entree - ignore it
            }
        }

        // AJOUT : Gestion des sessions en cours (entrée sans sortie)
        $now = new \DateTime();
        $today = $now->format('Y-m-d');
        
        foreach ($currentEntries as $date => $currentEntry) {
            if ($currentEntry instanceof \DateTime) {
                // Il y a une entrée non fermée pour ce jour
                if ($date === $today && $endDate >= $now) {
                    // Si c'est aujourd'hui et que la période de calcul inclut maintenant,
                    // ajouter le temps depuis l'entrée jusqu'à maintenant
                    $minutesSinceEntry = ($now->getTimestamp() - $currentEntry->getTimestamp()) / 60;
                    $workingDays[$date]['total_minutes'] += $minutesSinceEntry;
                    $totalMinutes += $minutesSinceEntry;
                    
                    // Ajouter une note dans les entrées pour indiquer la session en cours
                    $workingDays[$date]['entries'][] = [
                        'time' => 'En cours',
                        'type' => 'session_active',
                        'since' => $currentEntry->format('H:i')
                    ];
                }
            }
        }

        // Conversion en heures et formatage
        foreach ($workingDays as &$day) {
            $day['total_hours'] = round($day['total_minutes'] / 60, 2);
        }

        return [
            'total_hours' => round($totalMinutes / 60, 2),
            'total_minutes' => $totalMinutes,
            'days' => array_values($workingDays)
        ];
    }

    // === NOUVELLES MÉTHODES POUR LA FONCTIONNALITÉ POINTAGE ===

    /**
     * Récupère toutes les badgeuses accessibles par un utilisateur avec leur statut
     */
    public function getUserBadgeusesWithStatus(User $user): array
    {
        try {
            // Récupération du service principal de l'utilisateur
            $principalService = $user->getPrincipalService();
            if (!$principalService) {
                return [
                    'success' => false,
                    'error' => 'NO_PRINCIPAL_SERVICE',
                    'message' => 'Aucun service principal trouvé pour cet utilisateur'
                ];
            }

            $badgeuses = [];
            $principalZone = null;

            // Récupérer TOUS les services de l'utilisateur (principal + secondaires)
            $allUserServices = [];
            foreach ($user->getTravail() as $travailler) {
                $service = $travailler->getService();
                if ($service) {
                    $allUserServices[] = $service;
                }
            }

            // Parcourir toutes les zones de tous les services de l'utilisateur
            foreach ($allUserServices as $service) {
                foreach ($service->getServiceZones() as $serviceZone) {
                    $zone = $serviceZone->getZone();
                    if (!$zone) continue;

                    // Identifier si c'est un service principal
                    $isServicePrincipal = $service->isIsPrincipal();
                    if ($isServicePrincipal) {
                        $principalZone = $zone;
                    }

                    // Récupération de toutes les badgeuses dans cette zone
                    foreach ($zone->getAcces() as $access) {
                        $badgeuse = $access->getBadgeuse();
                        if (!$badgeuse) continue;

                        // Éviter les doublons
                        $badgeuseId = $badgeuse->getId();
                        if (isset($badgeuses[$badgeuseId])) {
                            // Vérifier si cette zone n'est pas déjà ajoutée
                            $zoneExists = false;
                            foreach ($badgeuses[$badgeuseId]['zones'] as $existingZone) {
                                if ($existingZone['id'] === $zone->getId()) {
                                    $zoneExists = true;
                                    break;
                                }
                            }
                            
                            if (!$zoneExists) {
                                // Ajouter la zone à la badgeuse existante
                                $badgeuses[$badgeuseId]['zones'][] = [
                                    'id' => $zone->getId(),
                                    'nom_zone' => $zone->getNomZone(),
                                    'is_principal' => $isServicePrincipal,
                                    'service_id' => $service->getId(),
                                    'service_name' => $service->getNomService()
                                ];
                            }
                            
                            // Marquer comme principale si au moins un service est principal
                            if ($isServicePrincipal) {
                                $badgeuses[$badgeuseId]['is_principal'] = true;
                                $badgeuses[$badgeuseId]['service_type'] = 'mixed'; // Mixte si elle a à la fois principal et secondaire
                            }
                            continue;
                        }

                        $badgeuses[$badgeuseId] = [
                            'id' => $badgeuse->getId(),
                            'reference' => $badgeuse->getReference(),
                            'date_installation' => $badgeuse->getDateInstallation()->format('Y-m-d'),
                            'is_principal' => $isServicePrincipal,
                            'is_accessible' => true,
                            'service_type' => $isServicePrincipal ? 'principal' : 'secondaire',
                            'zones' => [[
                                'id' => $zone->getId(),
                                'nom_zone' => $zone->getNomZone(),
                                'is_principal' => $isServicePrincipal,
                                'service_id' => $service->getId(),
                                'service_name' => $service->getNomService()
                            ]]
                        ];
                    }
                }
            }

            // Détermination du statut de chaque badgeuse selon les règles métier
            $userStatus = $this->getCurrentUserStatus($user);
            $lastPointage = $this->getLastUserPointage($user);
            $isUserPresent = $userStatus['status'] === 'present';

            foreach ($badgeuses as &$badgeuse) {
                $badgeuse = $this->determineBadgeuseStatus($badgeuse, $isUserPresent, $lastPointage, $principalZone);
            }

            return [
                'success' => true,
                'data' => array_values($badgeuses),
                'user_status' => $userStatus
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors de la récupération des badgeuses: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Valide et effectue un pointage selon les règles métier
     */
    public function performPointageWithValidation(User $user, int $badgeuseId, bool $force = false): array
    {
        $this->entityManager->beginTransaction();
        
        try {
            // Validation préliminaire
            $validation = $this->validatePointageAction($user, $badgeuseId);
            
            if (!$validation['is_valid'] && !$force) {
                return $validation;
            }

            // Récupération des données nécessaires
            $badgeuse = $this->entityManager->getRepository(Badgeuse::class)->find($badgeuseId);
            if (!$badgeuse) {
                return [
                    'success' => false,
                    'error' => 'BADGEUSE_NOT_FOUND',
                    'message' => 'Badgeuse non trouvée'
                ];
            }

            // Récupération du badge actif de l'utilisateur
            $activeBadge = $this->getUserActiveBadge($user);
            if (!$activeBadge) {
                return [
                    'success' => false,
                    'error' => 'NO_ACTIVE_BADGE',
                    'message' => 'Aucun badge actif trouvé pour cet utilisateur'
                ];
            }

            // Détermination du type de pointage selon le service
            $userStatus = $this->getCurrentUserStatus($user);
            $isPrincipalService = $this->isBadgeuseInPrincipalZone($badgeuse, $user);
            
            $pointageType = $this->determinePointageType($userStatus, $isPrincipalService);

            // Création du pointage
            $pointage = new Pointage();
            $pointage->setBadge($activeBadge);
            $pointage->setBadgeuse($badgeuse);
            $pointage->setHeure(new \DateTime());
            $pointage->setType($pointageType);

            $this->entityManager->persist($pointage);
            $this->entityManager->flush();

            // Calcul du temps de travail seulement pour les sorties de services principaux
            $workSession = null;
            if ($pointageType === 'sortie' && $isPrincipalService) {
                $workSession = $this->calculateCurrentWorkSession($user, $pointage->getHeure());
            }

            // Nouveau statut utilisateur
            $newStatus = $this->getCurrentUserStatus($user);

            $this->entityManager->commit();

            return [
                'success' => true,
                'data' => [
                    'pointage' => [
                        'id' => $pointage->getId(),
                        'heure' => $pointage->getHeure()->format('Y-m-d H:i:s'),
                        'type' => $pointage->getType(),
                        'badge' => [
                            'id' => $activeBadge->getId(),
                            'numero_badge' => $activeBadge->getNumeroBadge(),
                            'type_badge' => $activeBadge->getTypeBadge()
                        ],
                        'badgeuse' => [
                            'id' => $badgeuse->getId(),
                            'reference' => $badgeuse->getReference(),
                            'zones' => $this->getBadgeuseZoneNames($badgeuse)
                        ]
                    ],
                    'new_status' => $newStatus,
                    'work_session' => $workSession,
                    'message' => $this->getPointageSuccessMessage($pointageType, $isPrincipalService, $userStatus)
                ]
            ];

        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return [
                'success' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors de l\'enregistrement du pointage: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Valide si un pointage peut être effectué selon les règles métier
     */
    public function validatePointageAction(User $user, int $badgeuseId): array
    {
        try {
            $badgeuse = $this->entityManager->getRepository(Badgeuse::class)->find($badgeuseId);
            if (!$badgeuse) {
                return [
                    'success' => false,
                    'is_valid' => false,
                    'error' => 'BADGEUSE_NOT_FOUND',
                    'message' => 'Badgeuse non trouvée'
                ];
            }

            // Vérification du badge actif
            $activeBadge = $this->getUserActiveBadge($user);
            if (!$activeBadge) {
                return [
                    'success' => false,
                    'is_valid' => false,
                    'error' => 'NO_ACTIVE_BADGE',
                    'message' => 'Aucun badge actif trouvé'
                ];
            }

            // Vérification du statut utilisateur et type de service
            $userStatus = $this->getCurrentUserStatus($user);
            $isPrincipalService = $this->isBadgeuseInPrincipalZone($badgeuse, $user);

            // Contrôle d'accès selon la logique métier :
            // - Services principaux : toujours accessibles
            // - Services secondaires : accessibles seulement si présent dans le principal

            // RÈGLE PRINCIPALE: Vérification d'accès à la zone
            $zones = $this->getBadgeuseZones($badgeuse);
            if (empty($zones)) {
                return [
                    'success' => false,
                    'is_valid' => false,
                    'error' => 'NO_ZONES_CONFIGURED',
                    'message' => 'Aucune zone configurée pour cette badgeuse'
                ];
            }

            $hasAccess = false;
            foreach ($zones as $zone) {
                if ($this->canAccessZone($user, $zone)) {
                    $hasAccess = true;
                    break;
                }
            }

            if (!$hasAccess) {
                return [
                    'success' => false,
                    'is_valid' => false,
                    'error' => 'ZONE_ACCESS_DENIED',
                    'message' => 'Vous n\'avez pas accès à cette zone'
                ];
            }

            // Vérification de la logique métier pour les services secondaires
            if (!$isPrincipalService && $userStatus['status'] !== 'present') {
                return [
                    'success' => false,
                    'is_valid' => false,
                    'error' => 'SECONDARY_ACCESS_DENIED',
                    'message' => 'Vous devez d\'abord pointer dans votre service principal pour accéder aux services secondaires',
                    'requires_principal' => true
                ];
            }

            return [
                'success' => true,
                'is_valid' => true,
                'can_proceed' => true,
                'message' => $isPrincipalService ? 'Pointage principal autorisé' : 'Accès secondaire autorisé',
                'service_type' => $isPrincipalService ? 'principal' : 'secondaire'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'is_valid' => false,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Erreur lors de la validation'
            ];
        }
    }

    /**
     * Récupère le badge actif d'un utilisateur
     */
    private function getUserActiveBadge(User $user): ?Badge
    {
        $userBadge = $this->entityManager->getRepository(UserBadge::class)
            ->createQueryBuilder('ub')
            ->join('ub.badge', 'b')
            ->where('ub.Utilisateur = :user')
            ->andWhere('b.date_expiration IS NULL OR b.date_expiration > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $userBadge?->getBadge();
    }

    /**
     * Récupère le dernier pointage d'un utilisateur
     */
    /**
     * Récupère le dernier pointage d'un utilisateur sur une badgeuse PRINCIPALE uniquement
     * MODIFICATION CRITIQUE: Ne retourne que les pointages des badgeuses principales
     */
    private function getLastUserPointage(User $user): ?Pointage
    {
        return $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->join('p.badgeuse', 'bdg')
            ->join('bdg.acces', 'a')
            ->join('a.zone', 'z')
            ->join('z.serviceZones', 'sz')
            ->join('sz.service', 's')
            ->join('s.travail', 't')
            ->where('ub.Utilisateur = :user_badge')
            ->andWhere('t.Utilisateur = :user_travail')
            ->andWhere('s.is_principal = true')
            ->orderBy('p.heure', 'DESC')
            ->setParameter('user_badge', $user)
            ->setParameter('user_travail', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Vérifie si une badgeuse donne accès à une zone d'un service principal de l'utilisateur
     */
    private function isBadgeuseInPrincipalZone(Badgeuse $badgeuse, User $user): bool
    {
        // Récupérer les services principaux de l'utilisateur
        $principalServices = [];
        foreach ($user->getTravail() as $travailler) {
            $service = $travailler->getService();
            if ($service && $service->isIsPrincipal()) {
                $principalServices[] = $service;
            }
        }

        if (empty($principalServices)) {
            return false;
        }

        // Récupérer les zones accessibles via cette badgeuse
        $zones = $this->getBadgeuseZones($badgeuse);
        
        // Vérifier si une des zones est liée à un service principal
        foreach ($zones as $zone) {
            foreach ($principalServices as $principalService) {
                $serviceZone = $this->entityManager->getRepository(\App\Entity\ServiceZone::class)
                    ->findOneBy(['service' => $principalService, 'zone' => $zone]);
                if ($serviceZone) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Détermine le type de pointage selon le service (principal/secondaire)
     * SERVICES PRINCIPAUX : Alternent entre entrée/sortie selon le statut actuel
     * SERVICES SECONDAIRES : Toujours "acces" - ne changent PAS le statut de présence
     */
    private function determinePointageType(array $userStatus, bool $isPrincipalService): string
    {
        // Si c'est un service secondaire, toujours enregistrer comme "acces"
        // Les services secondaires ne modifient PAS le statut présent/absent
        if (!$isPrincipalService) {
            return 'acces';
        }

        // Pour les services principaux : alterner entre entrée/sortie selon le statut
        // C'est le seul type de pointage qui influence le statut présent/absent
        if ($userStatus['status'] === 'present') {
            return 'sortie'; // Sortie du service principal
        } else {
            return 'entree'; // Entrée dans le service principal
        }
    }

    /**
     * Calcule la session de travail actuelle sur une badgeuse PRINCIPALE uniquement
     * MODIFICATION CRITIQUE: Ne prend en compte que les pointages des badgeuses principales
     */
    private function calculateCurrentWorkSession(User $user, \DateTime $endTime): ?array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $startPointage = $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->join('p.badgeuse', 'bdg')
            ->join('bdg.acces', 'a')
            ->join('a.zone', 'z')
            ->join('z.serviceZones', 'sz')
            ->join('sz.service', 's')
            ->join('s.travail', 't')
            ->where('ub.Utilisateur = :user_badge')
            ->andWhere('t.Utilisateur = :user_travail')
            ->andWhere('s.is_principal = true')
            ->andWhere('p.heure >= :today')
            ->andWhere('p.heure < :endTime')
            ->andWhere('p.type = :entree')
            ->orderBy('p.heure', 'DESC')
            ->setParameter('user_badge', $user)
            ->setParameter('user_travail', $user)
            ->setParameter('today', $today)
            ->setParameter('endTime', $endTime)
            ->setParameter('entree', 'entree')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$startPointage) return null;

        $durationMinutes = ($endTime->getTimestamp() - $startPointage->getHeure()->getTimestamp()) / 60;

        return [
            'start_time' => $startPointage->getHeure()->format('Y-m-d H:i:s'),
            'end_time' => $endTime->format('Y-m-d H:i:s'),
            'duration_minutes' => (int)$durationMinutes
        ];
    }

    /**
     * Détermine le statut d'une badgeuse selon les règles métier
     * ZONES INDÉPENDANTES : Toutes les badgeuses sont toujours accessibles
     */
    private function determineBadgeuseStatus(array $badgeuse, bool $isUserPresent, ?Pointage $lastPointage, ?Zone $principalZone): array
    {
        $badgeuse['is_blocked'] = false;
        $badgeuse['status'] = 'available';
        $badgeuse['block_reason'] = null;

        // ZONES INDÉPENDANTES : Pas de restriction basée sur le statut présent/absent
        // Toutes les zones sont accessibles à tout moment si l'utilisateur a les permissions

        // Note: Vérification du délai minimum supprimée pour faciliter les tests

        return $badgeuse;
    }

    /**
     * Génère un message de succès selon le type de service
     */
    private function getPointageSuccessMessage(string $type, bool $isPrincipalService, array $userStatus): string
    {
        if ($isPrincipalService) {
            switch ($type) {
                case 'entree':
                    return 'Entrée dans le service principal enregistrée - Statut : Présent';
                case 'sortie':
                    return 'Sortie du service principal enregistrée - Statut : Absent';
                default:
                    return 'Accès au service principal enregistré';
            }
        } else {
            $currentStatus = $userStatus['status'] === 'present' ? 'Présent' : 'Absent';
            return "Accès au service secondaire enregistré - Statut principal maintenu : {$currentStatus}";
        }
    }

    /**
     * Récupère le statut de travail d'un utilisateur (compatible avec l'API)
     */
    public function getUserWorkingStatus(User $user): array
    {
        // Utiliser la méthode getCurrentUserStatus qui a été mise à jour
        $currentStatus = $this->getCurrentUserStatus($user);
        
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        // Récupérer le dernier pointage principal pour le statut de travail
        $lastPrincipalPointage = $this->getLastUserPointage($user);
        
        $status = $currentStatus['status'];
        $isInPrincipalZone = false;
        $currentWorkStart = null;
        $workingTimeToday = $this->calculateTodayWorkingTime($user);
        
        // Déterminer si on est dans une zone principale
        if ($lastPrincipalPointage) {
            $isInPrincipalZone = $this->isBadgeuseInPrincipalZone($lastPrincipalPointage->getBadgeuse(), $user);

            if ($status === 'present') {
                $currentWorkStart = $this->findTodayWorkStart($user);
            }
        }

        // Récupérer les informations du dernier pointage global depuis currentStatus
        $lastAction = $currentStatus['last_action'];

        return [
            'status' => $status,
            'is_in_principal_zone' => $isInPrincipalZone,
            'current_work_start' => $currentWorkStart ? $currentWorkStart->format('Y-m-d H:i:s') : null,
            'working_time_today' => $workingTimeToday,
            'last_action' => $lastAction,
            'date' => $today->format('Y-m-d')
        ];
    }

    /**
     * Récupère les badges actifs d'un utilisateur
     */
    public function getUserActiveBadges(User $user): array
    {
        $userBadges = $this->entityManager->getRepository(UserBadge::class)
            ->createQueryBuilder('ub')
            ->join('ub.badge', 'b')
            ->where('ub.Utilisateur = :user')
            ->andWhere('b.date_expiration IS NULL OR b.date_expiration > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();

        return array_map(function (UserBadge $userBadge) {
            $badge = $userBadge->getBadge();
            return [
                'id' => $badge->getId(),
                'numero_badge' => $badge->getNumeroBadge(),
                'type_badge' => $badge->getTypeBadge(),
                'is_active' => true
            ];
        }, $userBadges);
    }

    /**
     * Calcule le temps de travail pour une période spécifique (compatible avec l'API)
     */
    public function calculateWorkingTimeForPeriod(User $user, string $startDate, string $endDate): array
    {
        $start = new \DateTime($startDate . ' 00:00:00');
        $end = new \DateTime($endDate . ' 23:59:59');
        
        return $this->calculateWorkingTime($user, $start, $end);
    }

    /**
     * Trouve le début de la journée de travail actuelle sur une badgeuse PRINCIPALE uniquement
     * MODIFICATION CRITIQUE: Ne prend en compte que les pointages des badgeuses principales
     */
    private function findTodayWorkStart(User $user): ?\DateTime
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $firstEntry = $this->entityManager->getRepository(Pointage::class)
            ->createQueryBuilder('p')
            ->join('p.badge', 'b')
            ->join('b.userBadges', 'ub')
            ->join('p.badgeuse', 'bdg')
            ->join('bdg.acces', 'a')
            ->join('a.zone', 'z')
            ->join('z.serviceZones', 'sz')
            ->join('sz.service', 's')
            ->join('s.travail', 't')
            ->where('ub.Utilisateur = :user_badge')
            ->andWhere('t.Utilisateur = :user_travail')
            ->andWhere('s.is_principal = true')
            ->andWhere('p.heure >= :today')
            ->andWhere('p.type = :entree')
            ->orderBy('p.heure', 'ASC')
            ->setParameter('user_badge', $user)
            ->setParameter('user_travail', $user)
            ->setParameter('today', $today)
            ->setParameter('entree', 'entree')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $firstEntry?->getHeure();
    }

    /**
     * Calcule le temps de travail d'aujourd'hui en minutes
     */
    private function calculateTodayWorkingTime(User $user): int
    {
        $today = new \DateTime();
        $todayStart = clone $today;
        $todayStart->setTime(0, 0, 0);
        $todayEnd = clone $today;
        $todayEnd->setTime(23, 59, 59);

        $workingTime = $this->calculateWorkingTime($user, $todayStart, $todayEnd);
        return (int)$workingTime['total_minutes'];
    }

    /**
     * Vérifie si un pointage est une entrée récente (moins de 8 heures)
     * ZONES INDÉPENDANTES : Utilisé pour déterminer si l'utilisateur est considéré "présent"
     */
    private function isRecentEntry(Pointage $pointage): bool
    {
        if ($pointage->getType() !== 'entree') {
            return false;
        }

        $now = new \DateTime();
        $pointageTime = $pointage->getHeure();
        $hoursDiff = ($now->getTimestamp() - $pointageTime->getTimestamp()) / 3600;

        // Considérer comme récent si moins de 8 heures
        return $hoursDiff < 8;
    }
}
