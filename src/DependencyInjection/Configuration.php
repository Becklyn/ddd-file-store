<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-10
 *
 * @codeCoverageIgnore
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('becklyn_ddd_file_store');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('filesystem')
            ->children()
            ->scalarNode('base_path')
            ->defaultValue('%kernel.project_dir%/var/becklyn-files')
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
