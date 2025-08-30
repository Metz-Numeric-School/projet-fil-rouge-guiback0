<?php

namespace App\Controller\API\User;

use App\Entity\User;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/manager/api/user', name: 'api_user_')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserService $userService
    ) {}

    /**
     * Récupère toutes les informations complètes d'un utilisateur
     */
    #[Route('/profile/complete', name: 'profile_complete', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getCompleteProfile(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_USER',
                'message' => 'Utilisateur invalide'
            ], 401);
        }

        try {
            // Récupération des informations de base de l'utilisateur (sans mot de passe)
            $userData = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'telephone' => $user->getTelephone(),
                'date_naissance' => $user->getDateNaissance()?->format('Y-m-d'),
                'date_inscription' => $user->getDateInscription()->format('Y-m-d'),
                'poste' => $user->getPoste(),
                'horraire' => $user->getHorraire()?->format('H:i'),
                'heure_debut' => $user->getHeureDebut()?->format('H:i'),
                'jours_semaine_travaille' => $user->getJoursSemaineTravaille(),
                'roles' => $user->getRoles(),
                'compte_actif' => $user->isCompteActif(),
                'date_derniere_connexion' => $user->getDateDerniereConnexion()?->format('Y-m-d H:i:s'),
                'date_derniere_modification' => $user->getDateDerniereModification()?->format('Y-m-d H:i:s'),
                'date_suppression_prevue' => $user->getDateSuppressionPrevue()?->format('Y-m-d')
            ];

            // Récupération de l'organisation
            $organisation = $this->userService->getUserOrganisation($user);
            $organisationData = null;
            if ($organisation) {
                $organisationData = [
                    'id' => $organisation->getId(),
                    'nom_organisation' => $organisation->getNomOrganisation(),
                    'email' => $organisation->getEmail(),
                    'telephone' => $organisation->getTelephone(),
                    'site_web' => $organisation->getSiteWeb(),
                    'siret' => $organisation->getSiret(),
                    'adresse' => [
                        'numero_rue' => $organisation->getNumeroRue(),
                        'suffix_rue' => $organisation->getSuffixRue(),
                        'nom_rue' => $organisation->getNomRue(),
                        'code_postal' => $organisation->getCodePostal(),
                        'ville' => $organisation->getVille(),
                        'pays' => $organisation->getPays()
                    ]
                ];
            }

            // Récupération du service actuel et historique
            $servicesData = [];
            $currentService = null;
            foreach ($user->getTravail() as $travailler) {
                $service = $travailler->getService();
                if ($service) {
                    $serviceInfo = [
                        'id' => $service->getId(),
                        'nom_service' => $service->getNomService(),
                        'niveau_service' => $service->getNiveauService(),
                        'date_debut' => $travailler->getDateDebut()->format('Y-m-d'),
                        'date_fin' => $travailler->getDateFin()?->format('Y-m-d'),
                        'is_current' => $travailler->getDateFin() === null
                    ];
                    
                    if ($travailler->getDateFin() === null) {
                        $currentService = $serviceInfo;
                    }
                    
                    $servicesData[] = $serviceInfo;
                }
            }

            // Récupération des zones accessibles via le service actuel
            $zonesData = [];
            if ($currentService && isset($currentService['id'])) {
                $currentServiceEntity = $this->entityManager->getRepository(\App\Entity\Service::class)
                    ->find($currentService['id']);
                
                if ($currentServiceEntity) {
                    foreach ($currentServiceEntity->getServiceZones() as $serviceZone) {
                        $zone = $serviceZone->getZone();
                        if ($zone) {
                            $zonesData[] = [
                                'id' => $zone->getId(),
                                'nom_zone' => $zone->getNomZone(),
                                'description' => $zone->getDescription(),
                                'capacite' => $zone->getCapacite()
                            ];
                        }
                    }
                }
            }

            // Récupération des badges de l'utilisateur
            $badgesData = [];
            foreach ($user->getUserBadges() as $userBadge) {
                $badge = $userBadge->getBadge();
                if ($badge) {
                    $badgesData[] = [
                        'id' => $badge->getId(),
                        'numero_badge' => $badge->getNumeroBadge(),
                        'type_badge' => $badge->getTypeBadge(),
                        'date_creation' => $badge->getDateCreation()->format('Y-m-d'),
                        'date_expiration' => $badge->getDateExpiration()?->format('Y-m-d'),
                        'is_active' => $badge->getDateExpiration() === null || $badge->getDateExpiration() > new \DateTime()
                    ];
                }
            }

            // Récupération des accès et badgeuses autorisées
            $accesData = [];
            $badgeusesData = [];
            $uniqueBadgeuses = [];

            // Pour chaque zone accessible, récupérer les accès
            if ($currentService && isset($currentService['id'])) {
                $currentServiceEntity = $this->entityManager->getRepository(\App\Entity\Service::class)
                    ->find($currentService['id']);
                
                if ($currentServiceEntity) {
                    foreach ($currentServiceEntity->getServiceZones() as $serviceZone) {
                        $zone = $serviceZone->getZone();
                        if ($zone) {
                            foreach ($zone->getAcces() as $acces) {
                                $badgeuse = $acces->getBadgeuse();
                                
                                $accesInfo = [
                                    'id' => $acces->getId(),
                                    'nom_acces' => $acces->getNomAcces(),
                                    'date_installation' => $acces->getDateInstallation()->format('Y-m-d H:i:s'),
                                    'zone' => [
                                        'id' => $zone->getId(),
                                        'nom_zone' => $zone->getNomZone()
                                    ]
                                ];

                                if ($badgeuse) {
                                    $accesInfo['badgeuse'] = [
                                        'id' => $badgeuse->getId(),
                                        'reference' => $badgeuse->getReference(),
                                        'date_installation' => $badgeuse->getDateInstallation()->format('Y-m-d')
                                    ];

                                    // Ajouter la badgeuse à la liste unique
                                    if (!isset($uniqueBadgeuses[$badgeuse->getId()])) {
                                        $uniqueBadgeuses[$badgeuse->getId()] = [
                                            'id' => $badgeuse->getId(),
                                            'reference' => $badgeuse->getReference(),
                                            'date_installation' => $badgeuse->getDateInstallation()->format('Y-m-d'),
                                            'zones_accessibles' => []
                                        ];
                                    }

                                    // Ajouter la zone à cette badgeuse
                                    $uniqueBadgeuses[$badgeuse->getId()]['zones_accessibles'][] = [
                                        'id' => $zone->getId(),
                                        'nom_zone' => $zone->getNomZone()
                                    ];
                                }

                                $accesData[] = $accesInfo;
                            }
                        }
                    }
                }
            }

            // Convertir le tableau associatif des badgeuses en tableau indexé
            $badgeusesData = array_values($uniqueBadgeuses);

            // Dédoublonner les zones dans chaque badgeuse
            foreach ($badgeusesData as &$badgeuse) {
                $badgeuse['zones_accessibles'] = array_unique($badgeuse['zones_accessibles'], SORT_REGULAR);
                $badgeuse['zones_accessibles'] = array_values($badgeuse['zones_accessibles']);
            }

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'user' => $userData,
                    'organisation' => $organisationData,
                    'services' => [
                        'current' => $currentService,
                        'history' => $servicesData
                    ],
                    'zones_accessibles' => $zonesData,
                    'badges' => $badgesData,
                    'acces_autorises' => $accesData,
                    'badgeuses_autorisees' => $badgeusesData
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'SERVER_ERROR',
                'message' => 'Erreur lors de la récupération des informations utilisateur',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update user's last login timestamp
     */
    #[Route('/update-last-login', name: 'update_last_login', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function updateLastLogin(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_USER',
                'message' => 'Utilisateur invalide'
            ], 401);
        }

        $this->entityManager->beginTransaction();
        try {
            $user->updateLastLogin();
            $this->entityManager->flush();
            $this->entityManager->commit();

            return new JsonResponse([
                'success' => true,
                'message' => 'Dernière connexion mise à jour'
            ]);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return new JsonResponse([
                'success' => false,
                'error' => 'SERVER_ERROR',
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    /**
     * Deactivate user account (GDPR compliance)
     */
    #[Route('/deactivate', name: 'deactivate_account', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function deactivateAccount(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_USER',
                'message' => 'Utilisateur invalide'
            ], 401);
        }

        $this->entityManager->beginTransaction();
        try {
            $user->deactivate();
            $this->entityManager->flush();
            $this->entityManager->commit();

            return new JsonResponse([
                'success' => true,
                'message' => 'Compte désactivé avec succès. Vos données seront automatiquement supprimées après 5 ans de conservation.',
                'data' => [
                    'date_suppression_prevue' => $user->getDateSuppressionPrevue()?->format('Y-m-d')
                ]
            ]);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return new JsonResponse([
                'success' => false,
                'error' => 'SERVER_ERROR',
                'message' => 'Erreur lors de la désactivation du compte'
            ], 500);
        }
    }


    /**
     * Export user's personal data (GDPR data portability)
     */
    #[Route('/export-data', name: 'export_user_data', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function exportUserData(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'success' => false,
                'error' => 'INVALID_USER',
                'message' => 'Utilisateur invalide'
            ], 401);
        }

        try {
            // Get complete profile data for export
            $completeProfile = $this->getCompleteProfileData($user);

            return new JsonResponse([
                'success' => true,
                'message' => 'Données personnelles exportées avec succès',
                'data' => $completeProfile,
                'export_timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
                'gdpr_notice' => 'Ces données ont été exportées conformément à l\'article 20 du RGPD (droit à la portabilité des données).'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'SERVER_ERROR',
                'message' => 'Erreur lors de l\'exportation des données'
            ], 500);
        }
    }

    /**
     * Helper method to get complete profile data
     */
    private function getCompleteProfileData(User $user): array
    {
        // Get organisation data
        $organisation = $this->organisationService->getUserOrganisation($user);
        $organisationData = null;
        if ($organisation) {
            $organisationData = [
                'id' => $organisation->getId(),
                'nom_organisation' => $organisation->getNomOrganisation(),
                'email' => $organisation->getEmail(),
                'telephone' => $organisation->getTelephone(),
                'site_web' => $organisation->getSiteWeb(),
                'siret' => $organisation->getSiret()
            ];
        }

        // Get services data
        $servicesData = [];
        foreach ($user->getTravail() as $travailler) {
            $service = $travailler->getService();
            if ($service) {
                $servicesData[] = [
                    'nom_service' => $service->getNomService(),
                    'niveau_service' => $service->getNiveauService(),
                    'date_debut' => $travailler->getDateDebut()->format('Y-m-d'),
                    'date_fin' => $travailler->getDateFin()?->format('Y-m-d'),
                    'is_principal' => $travailler->getService() ? $travailler->getService()->isIsPrincipal() : false
                ];
            }
        }

        // Get badges data
        $badgesData = [];
        foreach ($user->getUserBadges() as $userBadge) {
            $badge = $userBadge->getBadge();
            if ($badge) {
                $badgesData[] = [
                    'numero_badge' => $badge->getNumeroBadge(),
                    'type_badge' => $badge->getTypeBadge(),
                    'date_creation' => $badge->getDateCreation()->format('Y-m-d'),
                    'date_expiration' => $badge->getDateExpiration()?->format('Y-m-d')
                ];
            }
        }

        return [
            'personal_information' => [
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'telephone' => $user->getTelephone(),
                'date_naissance' => $user->getDateNaissance()?->format('Y-m-d'),
                'date_inscription' => $user->getDateInscription()->format('Y-m-d'),
                'poste' => $user->getPoste(),
                'horraire' => $user->getHorraire()?->format('H:i'),
                'heure_debut' => $user->getHeureDebut()?->format('H:i'),
                'jours_semaine_travaille' => $user->getJoursSemaineTravaille()
            ],
            'account_information' => [
                'compte_actif' => $user->isCompteActif(),
                'date_derniere_connexion' => $user->getDateDerniereConnexion()?->format('Y-m-d H:i:s'),
                'date_derniere_modification' => $user->getDateDerniereModification()?->format('Y-m-d H:i:s'),
                'date_suppression_prevue' => $user->getDateSuppressionPrevue()?->format('Y-m-d'),
                'roles' => $user->getRoles()
            ],
            'organisation' => $organisationData,
            'services' => $servicesData,
            'badges' => $badgesData
        ];
    }
} 