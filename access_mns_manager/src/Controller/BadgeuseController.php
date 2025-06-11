<?php

namespace App\Controller;

use App\Entity\Badgeuse;
use App\Form\BadgeuseType;
use App\Repository\BadgeuseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/badgeuse')]
final class BadgeuseController extends AbstractController{
    #[Route(name: 'app_badgeuse_index', methods: ['GET'])]
    public function index(BadgeuseRepository $badgeuseRepository): Response
    {
        return $this->render('badgeuse/index.html.twig', [
            'badgeuses' => $badgeuseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_badgeuse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $badgeuse = new Badgeuse();
        $form = $this->createForm(BadgeuseType::class, $badgeuse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($badgeuse);
            $entityManager->flush();

            return $this->redirectToRoute('app_badgeuse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('badgeuse/new.html.twig', [
            'badgeuse' => $badgeuse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_badgeuse_show', methods: ['GET'])]
    public function show(Badgeuse $badgeuse): Response
    {
        return $this->render('badgeuse/show.html.twig', [
            'badgeuse' => $badgeuse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_badgeuse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Badgeuse $badgeuse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BadgeuseType::class, $badgeuse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_badgeuse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('badgeuse/edit.html.twig', [
            'badgeuse' => $badgeuse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_badgeuse_delete', methods: ['POST'])]
    public function delete(Request $request, Badgeuse $badgeuse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$badgeuse->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($badgeuse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_badgeuse_index', [], Response::HTTP_SEE_OTHER);
    }
}
