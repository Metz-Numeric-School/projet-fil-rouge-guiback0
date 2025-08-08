<?php

namespace App\Controller;

use App\Entity\Badge;
use App\Entity\UserBadge;
use App\Form\BadgeType;
use App\Repository\BadgeRepository;
use App\Repository\OrganisationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/badge')]
final class BadgeController extends AbstractController{
    #[Route(name: 'app_badge_index', methods: ['GET'])]
    public function index(BadgeRepository $badgeRepository): Response
    {
        return $this->render('badge/index.html.twig', [
            'badges' => $badgeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_badge_new', methods: ['GET', 'POST'])]
    public function new(Request $request, OrganisationRepository $organisationRepository, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Get all organisations
        $organisations = $organisationRepository->findAll();
        $selectedOrganisation = null;
        $users = [];
        
        // Handle organisation selection
        if ($request->query->has('organisation')) {
            $organisationId = $request->query->get('organisation');
            
            // Validate organisation ID
            if (!empty(trim($organisationId))) {
                $validatedId = filter_var($organisationId, FILTER_VALIDATE_INT);
                if ($validatedId !== false && $validatedId > 0) {
                    $selectedOrganisation = $organisationRepository->find($validatedId);
                    
                    if ($selectedOrganisation) {
                        // Get users from this organisation (users with principal service in this org)
                        $users = $userRepository->createQueryBuilder('u')
                            ->leftJoin('u.travail', 't')
                            ->leftJoin('t.service', 's')
                            ->where('s.organisation = :organisation')
                            ->andWhere('t.is_principal = true')
                            ->setParameter('organisation', $selectedOrganisation)
                            ->getQuery()
                            ->getResult();
                        
                        // Debug: If no users found with principal service, get all users who work in this org
                        if (empty($users)) {
                            $users = $userRepository->createQueryBuilder('u')
                                ->leftJoin('u.travail', 't')
                                ->leftJoin('t.service', 's')
                                ->where('s.organisation = :organisation')
                                ->setParameter('organisation', $selectedOrganisation)
                                ->getQuery()
                                ->getResult();
                        }
                    }
                }
            }
        }

        // Handle badge creation and assignment
        if ($request->isMethod('POST')) {
            $userId = $request->request->get('user');
            $badgeData = $request->request->all('badge');
            
            if ($userId && $badgeData && $this->isCsrfTokenValid('create_badge', $request->request->get('_token'))) {
                $user = $userRepository->find($userId);
                
                if ($user) {
                    // Check if user already has maximum badges (1)
                    if ($user->getUserBadges()->count() >= 1) {
                        $this->addFlash('error', "L'utilisateur {$user->getPrenom()} {$user->getNom()} a déjà le maximum de 1 badge autorisé.");
                    } else {
                        // Create new badge
                        $badge = new Badge();
                        
                        // Set badge properties from form data
                        if (isset($badgeData['typeBadge'])) {
                            $badge->setTypeBadge($badgeData['typeBadge']);
                        }
                        if (isset($badgeData['numeroBadge']) && !empty(trim($badgeData['numeroBadge']))) {
                            $numeroBadge = filter_var($badgeData['numeroBadge'], FILTER_VALIDATE_INT);
                            if ($numeroBadge !== false && $numeroBadge > 0) {
                                $badge->setNumeroBadge($numeroBadge);
                            } else {
                                $this->addFlash('error', 'Le numéro de badge doit être un nombre entier positif.');
                                return $this->redirectToRoute('app_badge_new', ['organisation' => $selectedOrganisation?->getId()]);
                            }
                        } else {
                            $this->addFlash('error', 'Le numéro de badge est obligatoire.');
                            return $this->redirectToRoute('app_badge_new', ['organisation' => $selectedOrganisation?->getId()]);
                        }
                        if (isset($badgeData['dateCreation'])) {
                            $badge->setDateCreation(new \DateTime($badgeData['dateCreation']));
                        } else {
                            $badge->setDateCreation(new \DateTime());
                        }
                        
                        $entityManager->persist($badge);
                        $entityManager->flush();
                        
                        // Assign badge to user
                        $userBadge = new UserBadge();
                        $userBadge->setUtilisateur($user);
                        $userBadge->setBadge($badge);
                        
                        $entityManager->persist($userBadge);
                        $entityManager->flush();
                        
                        $this->addFlash('success', "Badge #{$badge->getId()} créé et attribué avec succès à {$user->getPrenom()} {$user->getNom()}.");
                        
                        return $this->redirectToRoute('app_badge_index', [], Response::HTTP_SEE_OTHER);
                    }
                } else {
                    $this->addFlash('error', 'Utilisateur introuvable.');
                }
            } else {
                $this->addFlash('error', 'Données invalides ou token de sécurité manquant.');
            }
            
            // Redirect back to same page with organisation selected
            return $this->redirectToRoute('app_badge_new', ['organisation' => $selectedOrganisation?->getId()]);
        }

        return $this->render('badge/new.html.twig', [
            'organisations' => $organisations,
            'selectedOrganisation' => $selectedOrganisation,
            'users' => $users,
        ]);
    }


    #[Route('/{id}', name: 'app_badge_show', methods: ['GET'])]
    public function show(Badge $badge): Response
    {
        return $this->render('badge/show.html.twig', [
            'badge' => $badge,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_badge_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Badge $badge, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BadgeType::class, $badge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_badge_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('badge/edit.html.twig', [
            'badge' => $badge,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_badge_delete', methods: ['POST'])]
    public function delete(Request $request, Badge $badge, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$badge->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($badge);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_badge_index', [], Response::HTTP_SEE_OTHER);
    }
}
