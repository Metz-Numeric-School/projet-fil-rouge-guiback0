<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Travailler;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
final class UserController extends AbstractController{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ServiceRepository $serviceRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            
            // Auto-assign user to the first principal service found
            $principalService = $serviceRepository->findOneBy(['nom_service' => 'Service principal']);
            if ($principalService) {
                $travailler = new Travailler();
                $travailler->setUtilisateur($user);
                $travailler->setService($principalService);
                $travailler->setDateDebut(new \DateTime());
                $travailler->setIsPrincipal(true);
                $entityManager->persist($travailler);
            }
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Utilisateur créé avec succès et assigné au service principal.');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
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
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Utilisateur modifié avec succès.');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            // Check if user has principal service assignment
            $principalService = $user->getPrincipalService();
            if ($principalService) {
                $this->addFlash('error', 'Impossible de supprimer un utilisateur assigné à un service principal. Veuillez d\'abord réassigner l\'utilisateur.');
                return $this->redirectToRoute('app_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
            }
            
            $entityManager->remove($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
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

}
