<?php

namespace C201\FileStore\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-10
 *
 * @codeCoverageIgnore
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('c201_file_store');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('filesystem')
            ->children()
            ->scalarNode('base_path')
            ->defaultValue('%kernel.project_dir%/var/c201-files')
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
