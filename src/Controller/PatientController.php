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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

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

    #[Route('/patient/contact', name: 'app_contact_patient', methods: ['GET', 'POST'])]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $role = $request->request->get('role');
            $email = $request->request->get('email');
            $issueType = $request->request->get('issue-type');
            $message = $request->request->get('message');

            $emailMessage = (new Email())
                ->from($email)
                ->to('support@emedical.tn') //adresse admin
                ->subject("Demande technique de $name ($role) - $issueType")
                ->text(
                    "Nom : $name\n" .
                    "Rôle : $role\n" .
                    "Email : $email\n" .
                    "Type de problème : $issueType\n\n" .
                    "Message :\n$message"
                );

            try {
                $mailer->send($emailMessage);
                $this->addFlash('success', 'Votre message a bien été envoyé.');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi du message.');
            }

            return $this->redirectToRoute('app_contact_patient');
        }

        return $this->render('patient/ContactUs.html.twig');
    }

}