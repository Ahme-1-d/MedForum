<?php

namespace App\Config;

use FOS\CKEditorBundle\FOSCKEditorBundle;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class CKEditorConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ckeditor');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('configs')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('main_config')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('toolbar')->defaultValue('full')->end()
                                ->scalarNode('height')->defaultValue(400)->end()
                                ->scalarNode('filebrowserUploadMethod')->defaultValue('form')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
