<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Service;
use App\Entity\Organisation;
use App\Entity\Travailler;
use App\Entity\UserBadge;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\FormError;

#[Route('/user')]
#[IsGranted('ROLE_SUPER_ADMIN')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        // Show all users for admin, only active users for regular users
        if ($this->isGranted('ROLE_ADMIN')) {
            $users = $userRepository->findAll();
        } else {
            $users = $userRepository->findBy(['compte_actif' => true]);
        }

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/organisation/{id}', name: 'app_user_by_organisation', methods: ['GET'])]
    public function byOrganisation(Organisation $organisation): Response
    {
        // Get users from this organisation
        $users = [];
        foreach ($organisation->getServices() as $service) {
            foreach ($service->getTravail() as $travail) {
                $user = $travail->getUtilisateur();
                if ($user && !in_array($user, $users)) {
                    // Show all users for admin, only active users for regular users
                    if ($this->isGranted('ROLE_ADMIN') || $user->isCompteActif()) {
                        $users[] = $user;
                    }
                }
            }
        }

        return $this->render('user/by_organisation.html.twig', [
            'users' => $users,
            'organisation' => $organisation,
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ServiceRepository $serviceRepository, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Handle password since it's unmapped
            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            } elseif (!$user->getId()) {
                // Only require password for new users
                $form->get('password')->addError(new FormError('Le mot de passe est obligatoire'));
            }
            
            if ($form->isValid()) {
                $entityManager->persist($user);

                // Auto-assign user to the first principal service found
                $principalService = $serviceRepository->findOneBy(['is_principal' => true]);
                if ($principalService) {
                    $travailler = new Travailler();
                    $travailler->setUtilisateur($user);
                    $travailler->setService($principalService);
                    $travailler->setDateDebut(new \DateTime());
                    $entityManager->persist($travailler);
                }

                $entityManager->flush();

                $this->addFlash('success', 'Utilisateur créé avec succès et assigné au service principal.');
                return $this->redirectToRoute('app_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user, [
            'show_admin_fields' => $this->isGranted('ROLE_ADMIN'),
            'is_edit' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Handle password update only if a new password was provided
            $newPassword = $form->get('password')->getData();
            if (!empty($newPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            }
            
            if ($form->isValid()) {
                $user->updateLastModification();
                $entityManager->flush();

                $this->addFlash('success', 'Utilisateur modifié avec succès.');
                return $this->redirectToRoute('app_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/deactivate', name: 'app_user_deactivate', methods: ['POST'])]
    public function deactivate(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Get user's organisation before deactivation
        $organisation = null;
        $principalService = $user->getPrincipalService();
        if ($principalService) {
            $organisation = $principalService->getOrganisation();
        }
        
        if ($this->isCsrfTokenValid('deactivate' . $user->getId(), $request->getPayload()->getString('_token'))) {
            // Deactivate user (GDPR compliant soft deletion)
            $user->deactivate();
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur désactivé avec succès. Le compte sera automatiquement supprimé après 5 ans de conservation des données.');
        }

        // Redirect to user's organisation if found, otherwise to user index
        if ($organisation) {
            return $this->redirectToRoute('app_organisation_show', ['id' => $organisation->getId()], Response::HTTP_SEE_OTHER);
        }
        
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/activate', name: 'app_user_activate', methods: ['POST'])]
    public function activate(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Get user's organisation before activation
        $organisation = null;
        $principalService = $user->getPrincipalService();
        if ($principalService) {
            $organisation = $principalService->getOrganisation();
        }
        
        if ($this->isCsrfTokenValid('activate' . $user->getId(), $request->getPayload()->getString('_token'))) {
            // Reactivate user
            $user->setCompteActif(true);
            $user->setDateSuppressionPrevue(null); // Cancel scheduled deletion
            $user->updateLastModification();
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur réactivé avec succès.');
        }

        // Redirect to user's organisation if found, otherwise to user index
        if ($organisation) {
            return $this->redirectToRoute('app_organisation_show', ['id' => $organisation->getId()], Response::HTTP_SEE_OTHER);
        }
        
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/permanent-delete', name: 'app_user_permanent_delete', methods: ['POST'])]
    public function permanentDelete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Only allow permanent deletion for super admin and only if user should be deleted
        if (!$this->isGranted('ROLE_SUPER_ADMIN') || !$user->shouldBeDeleted()) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('app_user_index');
        }

        // Get user's organisation before deletion
        $organisation = null;
        $principalService = $user->getPrincipalService();
        if ($principalService) {
            $organisation = $principalService->getOrganisation();
        }
        
        if ($this->isCsrfTokenValid('permanent_delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            // Remove all Travailler relations
            $travaillerRepository = $entityManager->getRepository(Travailler::class);
            $travaillers = $travaillerRepository->findBy(['Utilisateur' => $user]);
            foreach ($travaillers as $travail) {
                $entityManager->remove($travail);
            }
            
            // Remove all UserBadge relations
            $userBadgeRepository = $entityManager->getRepository(UserBadge::class);
            $userBadges = $userBadgeRepository->findBy(['Utilisateur' => $user]);
            foreach ($userBadges as $userBadge) {
                $entityManager->remove($userBadge);
            }
            
            // Flush to ensure all relations are removed before deleting the user
            $entityManager->flush();
            
            // Then permanently remove the user
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur définitivement supprimé après expiration du délai de conservation RGPD.');
        }

        // Redirect to user's organisation if found, otherwise to user index
        if ($organisation) {
            return $this->redirectToRoute('app_organisation_show', ['id' => $organisation->getId()], Response::HTTP_SEE_OTHER);
        }
        
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/service/{id}', name: 'app_user_by_service', methods: ['GET'])]
    public function byService(Service $service, UserRepository $userRepository): Response
    {
        // Get users working in this service
        $users = [];
        foreach ($service->getTravail() as $travail) {
            if ($travail->getUtilisateur()) {
                $users[] = $travail->getUtilisateur();
            }
        }

        // Get available users from the same organisation not currently working in this service
        $organisation = $service->getOrganisation();
        $availableUsers = [];

        if ($organisation) {
            // Get all users who have their principal service in this organisation
            $allOrgUsers = $userRepository->createQueryBuilder('u')
                ->join('u.travail', 't')
                ->join('t.service', 's')
                ->where('s.organisation = :organisation')
                ->andWhere('s.is_principal = true')
                ->setParameter('organisation', $organisation)
                ->getQuery()
                ->getResult();

            // Filter out users already working in this service
            $serviceUserIds = [];
            foreach ($users as $user) {
                $serviceUserIds[] = $user->getId();
            }

            foreach ($allOrgUsers as $user) {
                if (!in_array($user->getId(), $serviceUserIds)) {
                    $availableUsers[] = $user;
                }
            }
        }

        return $this->render('user/by_service.html.twig', [
            'users' => $users,
            'service' => $service,
            'available_users' => $availableUsers,
        ]);
    }

    #[Route('/{id}/services', name: 'app_user_services', methods: ['GET'])]
    public function services(User $user): Response
    {
        $principalService = $user->getPrincipalService();
        $secondaryServices = $user->getSecondaryServices();

        return $this->render('user/services.html.twig', [
            'user' => $user,
            'principal_service' => $principalService,
            'secondary_services' => $secondaryServices,
        ]);
    }

    #[Route('/deactivated/organisation/{id}', name: 'app_user_deactivated_by_organisation', methods: ['GET'])]
    public function deactivatedByOrganisation(Organisation $organisation): Response
    {
        // Only allow admins to view deactivated users
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès refusé : seuls les administrateurs peuvent voir les utilisateurs désactivés.');
        }

        // Get deactivated users from this organisation
        $deactivatedUsers = [];
        foreach ($organisation->getServices() as $service) {
            foreach ($service->getTravail() as $travail) {
                $user = $travail->getUtilisateur();
                if ($user && !$user->isCompteActif() && !in_array($user, $deactivatedUsers)) {
                    $deactivatedUsers[] = $user;
                }
            }
        }

        return $this->render('user/deactivated_by_organisation.html.twig', [
            'deactivated_users' => $deactivatedUsers,
            'organisation' => $organisation,
        ]);
    }
}
