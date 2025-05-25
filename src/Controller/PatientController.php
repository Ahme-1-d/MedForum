<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PatientController extends AbstractController
{
    #[Route('/patient/profile', name: 'app_profile_patient')]
    public function profil(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(RegistrationFormTypeForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Password update only if a new one was entered
            if ($form->get('plainPassword')->getData()) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $this->addFlash('success', 'Comment puis-je vous aider ensuite ?');
            } else {
                $this->addFlash('success', 'C’est fait ! Vos données ont bien été enregistrées. N’hésitez pas à me solliciter pour autre chose.');
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('patient/profile.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/patient/chatbot', name: 'app_chatbot_patient')]
    public function chatbot(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('patient/chatbot.html.twig');
    }
}