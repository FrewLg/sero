<?php

namespace App\Controller;

use App\Entity\Training;
use App\Form\TrainingType;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/training')]
class TrainingController extends AbstractController
{
    #[Route('/', name: 'app_training_index', methods: ['GET'])]
    public function index(TrainingRepository $trainingRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('training/index.html.twig', [
            'trainings' => $trainingRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_training_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $training = new Training();
        $form = $this->createForm(TrainingType::class, $training);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $training->setCreatedAt(new \Datetime());
            
            $entityManager->persist($training);
            $entityManager->flush();
            $this->addFlash("success", " created successflly !");

            return $this->redirectToRoute('app_training_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('training/new.html.twig', [
            'training' => $training,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_training_show', methods: ['GET'])]
    public function show(Training $training): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('training/show.html.twig', [
            'training' => $training,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_training_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Training $training, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(TrainingType::class, $training);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_training_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('training/edit.html.twig', [
            'training' => $training,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_training_delete', methods: ['POST'])]
    public function delete(Request $request, Training $training, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('delete'.$training->getId(), $request->request->get('_token'))) {
            $entityManager->remove($training);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_training_index', [], Response::HTTP_SEE_OTHER);
    }
}