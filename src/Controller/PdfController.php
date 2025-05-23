<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Article;

class PdfController extends AbstractController
{
    #[Route('/article/{id}/pdf', name: 'app_article_pdf_alt', methods: ['GET'])]
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
