<?php

namespace C201\FileStore\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-09
 *
 * @codeCoverageIgnore
 */
class C201FileStoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../resources/config')
        );
        $loader->load('services.yml');

        if (isset($config['filesystem'], $config['filesystem']['base_path'])) {
            $definition = $container->getDefinition('c201_file_store.storage.filesystem.filesystem');
            $definition->replaceArgument(0, $config['filesystem']['base_path']);
        }
    }
}
