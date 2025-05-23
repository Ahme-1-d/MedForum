<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Service\ContentModerationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/article')]
class ArticleController extends AbstractController
{
    private $contentModerationService;

    public function __construct(ContentModerationService $contentModerationService)
    {
        $this->contentModerationService = $contentModerationService;
    }

    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $articleRepository->createQueryBuilder('a')
            ->orderBy('a.id', 'DESC')
            ->getQuery();

        $articles = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $article->setUsername('bechir11'); // Default username for demo
        
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Content moderation check
            if (!$this->contentModerationService->isContentAppropriate($article->getContent())) {
                $this->addFlash('danger', 'Le contenu de l\'article contient des termes inappropriés. ' . 
                    $this->contentModerationService->getContentFeedback($article->getContent()));
                
                return $this->render('article/new.html.twig', [
                    'article' => $article,
                    'form' => $form,
                ]);
            }
            
            // Handle image upload
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('articles_directory'),
                        $newFilename
                    );
                    
                    $article->setImageName($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Une erreur est survenue lors du téléchargement de l\'image.');
                }
            }

            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', 'L\'article a été créé avec succès.');
            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET', 'POST'])]
    public function show(Article $article, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepository): Response
    {
        // Get comments for this article
        $comments = $commentRepository->findByArticle($article);
        
        // Create a new comment form
        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setDateCreation(new \DateTime());
        
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Content moderation check
            if (!$this->contentModerationService->isContentAppropriate($comment->getContenu())) {
                $this->addFlash('danger', 'Le commentaire contient des termes inappropriés. ' . 
                    $this->contentModerationService->getContentFeedback($comment->getContenu()));
                
                return $this->render('article/show.html.twig', [
                    'article' => $article,
                    'comments' => $comments,
                    'comment_form' => $form,
                ]);
            }
            
            $entityManager->persist($comment);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('article/show.html.twig', [
            'article' => $article,
            'comments' => $comments,
            'comment_form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Content moderation check
            if (!$this->contentModerationService->isContentAppropriate($article->getContent())) {
                $this->addFlash('danger', 'Le contenu de l\'article contient des termes inappropriés. ' . 
                    $this->contentModerationService->getContentFeedback($article->getContent()));
                
                return $this->render('article/edit.html.twig', [
                    'article' => $article,
                    'form' => $form,
                ]);
            }
            
            // Handle image upload
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('articles_directory'),
                        $newFilename
                    );
                    
                    // Remove old image if exists
                    if ($article->getImageName()) {
                        $oldImagePath = $this->getParameter('articles_directory') . '/' . $article->getImageName();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    $article->setImageName($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Une erreur est survenue lors du téléchargement de l\'image.');
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'L\'article a été modifié avec succès.');
            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            // Remove image if exists
            if ($article->getImageName()) {
                $imagePath = $this->getParameter('articles_directory') . '/' . $article->getImageName();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $entityManager->remove($article);
            $entityManager->flush();
            
            $this->addFlash('success', 'L\'article a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/{id}/pdf', name: 'app_article_pdf', methods: ['GET'])]
    public function generatePdf(Article $article): Response
    {
        // Configure Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        // Generate HTML content for PDF
        $html = $this->renderView('article/pdf.html.twig', [
            'article' => $article,
        ]);
        
        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        
        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');
        
        // Render the PDF
        $dompdf->render();
        
        // Generate filename
        $filename = 'article-' . $article->getId() . '.pdf';
        
        // Return PDF as response
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );
    }
}
