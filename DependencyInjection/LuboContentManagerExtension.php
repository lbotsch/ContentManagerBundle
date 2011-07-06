<?php

namespace Lubo\ContentManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;

class LuboContentManagerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.xml');
        
        $processor     = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
 
        if (!array_key_exists('editor_tools', $config)) $config['editor_tools'] = array('enabled' => false);
        
        if ($config['editor_tools']['enabled']) {
            $loader->load('edit_tools.xml');
        }
        
        // Set default page type
        $container->setParameter('lubo_content_manager.default_page_type', $config['default_page_type']);
        $container->setParameter('lubo_content_manager.editing_mode', $config['editor_tools']['enabled']);
        
        // Register page types with the page controller
        $definition = $container->findDefinition('lubo_content_manager.page_controller');
        $definition->setClass($config['page_controller']);
        foreach ($config['page_types'] as $pageType) {
            $definition->addMethodCall('addPageType', array($pageType['name'], $pageType['template']));
        }
        
        // Register SlotControllers with the SlotEngine
        $definition = $container->findDefinition('lubo_content_manager.slot_engine');
        foreach ($container->findTaggedServiceIds('lubo_content_manager.slot_controller') as $id => $tag) {
            $definition->addMethodCall('addSlotController', array(new Reference($id)));
        }
        
        // Enable/Disable edit tools
        if ($config['editor_tools']['enabled']) {
            $loader->load('edit_tools.xml');
            $definition = $container->findDefinition('lubo_content_manager.editor_controller');
            
            // Add Pagetree
            $definition->addMethodCall('addStyle', array('bundles/lubocontentmanager/css/pagetree.css'));
            $definition->addMethodCall('addScript', array('route:_etb_pagetree_script'));
            
            // Add MediaManager
            $definition->addMethodCall('addStyle', array('bundles/lubocontentmanager/css/media_manager.css'));
            $definition->addMethodCall('addScript', array('route:_etb_media_manager_script'));
            
            // Add Slot Editor scripts
            $definition->addMethodCall('addScript', array('route:_etb_area_slot_editors_script'));
            
            // Add custom styles/scripts
            foreach ($config['editor_tools']['styles'] as $style) {
                $definition->addMethodCall('addStyle', array($style));
            }
            foreach ($config['editor_tools']['scripts'] as $script) {
                $definition->addMethodCall('addScript', array($script));
            }
        }
    }

    public function getAlias()
    {
        return 'lubo_content_manager';
    }

    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/';
    }

    public function getNamespace()
    {
        return 'http://lbotsch.github.com/symfony/schema/';
    }
}