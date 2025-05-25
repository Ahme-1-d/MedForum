<?php

namespace App\Controller;


use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PatientController extends AbstractController
{
    #[Route('/patient', name: 'app_home_patient')]
    public function index(): Response
    {
        return $this->render('patient/index.html.twig', [
        ]);
    }
    
}

#[Route('/patient/profile', name: 'app_profile_patient')]
public function profil(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    $form = $this->createForm(RegistrationFormTypeForm::class, $user);
    $form->handleRequest($request);
}
