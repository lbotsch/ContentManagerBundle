<?php
 
namespace Lubo\ContentManagerBundle\DependencyInjection;
 
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
 
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
 
        $builder->root('lubo_content_manager')
            ->children()
                ->arrayNode('editor_tools')
                    ->defaultValue(array('enabled' => false))
                    ->treatNullLike(array('enabled' => false))
                    ->treatTrueLike(array('enabled' => true, 'scripts' => array(), 'styles' => array()))
                    ->treatFalseLike(array('enabled' => false))
                    ->children()
                        ->booleanNode('enabled')->defaultValue(false)->end()
                        ->arrayNode('scripts')->prototype('scalar')->end()->end()
                        ->arrayNode('styles')->prototype('scalar')->end()->end()
                    ->end()
                ->end()
                ->scalarNode('page_controller')
                    ->defaultValue('Lubo\ContentManagerBundle\Controller\PageController')
                ->end()
                ->scalarNode('default_page_type')
                    ->defaultValue('Default')
                ->end()
                ->arrayNode('page_types')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('template')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $builder;
    }
}