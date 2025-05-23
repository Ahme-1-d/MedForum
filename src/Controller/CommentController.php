<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Service\ContentModerationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comment')]
class CommentController extends AbstractController
{
    private $contentModerationService;

    public function __construct(ContentModerationService $contentModerationService)
    {
        $this->contentModerationService = $contentModerationService;
    }

    #[Route('/', name: 'app_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $commentRepository->createQueryBuilder('c')
            ->orderBy('c.date_creation', 'DESC')
            ->getQuery();

        $comments = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('comment/index.html.twig', [
            'comments' => $comments,
        ]);
    }

    #[Route('/new', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $comment->setDateCreation(new \DateTime());
        
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Content moderation check
            if (!$this->contentModerationService->isContentAppropriate($comment->getContenu())) {
                $this->addFlash('danger', 'Le commentaire contient des termes inappropriés. ' . 
                    $this->contentModerationService->getContentFeedback($comment->getContenu()));
                
                return $this->render('comment/new.html.twig', [
                    'comment' => $comment,
                    'form' => $form,
                ]);
            }

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Le commentaire a été créé avec succès.');
            return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_comment_show', methods: ['GET'])]
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Content moderation check
            if (!$this->contentModerationService->isContentAppropriate($comment->getContenu())) {
                $this->addFlash('danger', 'Le commentaire contient des termes inappropriés. ' . 
                    $this->contentModerationService->getContentFeedback($comment->getContenu()));
                
                return $this->render('comment/edit.html.twig', [
                    'comment' => $comment,
                    'form' => $form,
                ]);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le commentaire a été modifié avec succès.');
            return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le commentaire a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
    }
}
