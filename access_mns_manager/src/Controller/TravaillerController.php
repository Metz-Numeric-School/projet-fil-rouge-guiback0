<?php

namespace App\Controller;

use App\Entity\Travailler;
use App\Form\TravaillerType;
use App\Repository\TravaillerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/travailler')]
final class TravaillerController extends AbstractController{
    #[Route(name: 'app_travailler_index', methods: ['GET'])]
    public function index(TravaillerRepository $travaillerRepository): Response
    {
        return $this->render('travailler/index.html.twig', [
            'travaillers' => $travaillerRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_travailler_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $travailler = new Travailler();
        $form = $this->createForm(TravaillerType::class, $travailler);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($travailler);
            $entityManager->flush();

            return $this->redirectToRoute('app_travailler_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('travailler/new.html.twig', [
            'travailler' => $travailler,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_travailler_show', methods: ['GET'])]
    public function show(Travailler $travailler): Response
    {
        return $this->render('travailler/show.html.twig', [
            'travailler' => $travailler,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_travailler_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Travailler $travailler, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TravaillerType::class, $travailler);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_travailler_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('travailler/edit.html.twig', [
            'travailler' => $travailler,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_travailler_delete', methods: ['POST'])]
    public function delete(Request $request, Travailler $travailler, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$travailler->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($travailler);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_travailler_index', [], Response::HTTP_SEE_OTHER);
    }
}
