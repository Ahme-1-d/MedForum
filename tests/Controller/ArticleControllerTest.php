<?php

namespace Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\ArticleRepository;

class ArticleControllerTest extends WebTestCase
{
    /**
     * Test de réussite pour la création d'un nouvel article
     */
    public function testCreateArticleSuccess(): void
    {
        // Création d'un client de test
        $client = static::createClient();
        
        // Accès à la page de création d'article
        $crawler = $client->request('GET', '/article/new');
        
        // Vérification que la page est accessible (code 200)
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un nouvel article');
        
        // Récupération du formulaire
        $form = $crawler->selectButton('Enregistrer')->form();
        
        // Remplissage du formulaire avec des données valides
        $form['article[titre]'] = 'Article de test fonctionnel';
        $form['article[categorie]'] = 'Test';
        $form['article[content]'] = 'Contenu de test pour l\'article créé via un test fonctionnel. Ce contenu est suffisamment long pour être valide.';
        
        // Soumission du formulaire
        $client->submit($form);
        
        // Vérification de la redirection après soumission
        $this->assertResponseRedirects('/article/');
        
        // Suivi de la redirection
        $client->followRedirect();
        
        // Vérification que nous sommes bien sur la page d'index des articles
        $this->assertSelectorTextContains('h1', 'Liste des articles');
        
        // Vérification que le message de succès est affiché
        $this->assertSelectorExists('.alert-success');
        
        // Vérification que l'article a bien été créé en base de données
        $articleRepository = static::getContainer()->get(ArticleRepository::class);
        $article = $articleRepository->findOneBy(['titre' => 'Article de test fonctionnel']);
        
        $this->assertNotNull($article);
        $this->assertEquals('Test', $article->getCategorie());
        $this->assertEquals('bechir11', $article->getUsername());
    }
}
