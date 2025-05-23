<?php

namespace App\Service;

class ContentModerationService
{
private array $inappropriateWords = [
    // General terms
    'inappropriate', 'offensive', 'vulgar', 'insult', 'hate',
    'racist', 'sexist', 'violent', 'threat', 'abuse',

    // English bad words
    'fuck', 'shit', 'bitch', 'asshole', 'damn', 'cunt', 'nigger', 'nigga', 'whore', 'slut',
    'dick', 'prick', 'motherfucker', 'faggot', 'twat', 'bastard', 'jackass', 'hoe',
    'suck my dick', 'go to hell',

    // French general terms
    'insulte', 'grossier', 'vulgaire', 'obscène', 'raciste',
    'sexiste', 'violent', 'menace', 'haine', 'offensant',

    // French bad words
    'putain', 'merde', 'enculé', 'foutu', 'salope', 'ta gueule', 'nique ta mère',
    'fils de pute', 'connard', 'connasse', 'enculé de ta mère', 'va te faire foutre',
    'chatte', 'baise-moi', 'espèce de salope', 'putain de merde', 'trou du cul',
    'suffis', 'va chier', 'pute'
];

    
    /**
     * Check if content contains inappropriate words
     */
    public function isContentAppropriate(string $text): bool
    {
        $textLower = mb_strtolower($text);
        
        foreach ($this->inappropriateWords as $word) {
            if (mb_strpos($textLower, $word) !== false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get feedback about inappropriate content
     */
    public function getContentFeedback(string $text): string
    {
        $textLower = mb_strtolower($text);
        $foundWords = [];
        
        foreach ($this->inappropriateWords as $word) {
            if (mb_strpos($textLower, $word) !== false) {
                $foundWords[] = $word;
            }
        }
        
        if (empty($foundWords)) {
            return "Le contenu est approprié.";
        }
        
        return "Le contenu contient des termes inappropriés: " . implode(', ', $foundWords);
    }
}
