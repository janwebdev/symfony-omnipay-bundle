<?php

namespace Janwebdev\OmnipayBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('omnipay');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('gateways')
                    ->useAttributeAsKey('name')
                        ->prototype('variable')
                    ->end()
                ->end()
                ->scalarNode('default')
                    ->defaultNull()
                ->end()
                ->booleanNode('init_on_boot')
                    ->defaultFalse()
                ->end()
                ->arrayNode('disabled')
                    ->prototype('scalar')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
