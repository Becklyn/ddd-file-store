<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-09
 *
 * @codeCoverageIgnore
 */
class BecklynDddFileStoreExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../resources/config')
        );
        $loader->load('services.yml');

        if (isset($config['filesystem'], $config['filesystem']['base_path'])) {
            $definition = $container->getDefinition('becklyn_ddd.file_store.storage.filesystem.filesystem');
            $definition->replaceArgument(0, $config['filesystem']['base_path']);
        }
    }

    public function prepend(ContainerBuilder $container) : void
    {
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Becklyn\\Ddd\\FileStore\\Infrastructure\\DoctrineMigrations' => __DIR__ . '/../Infrastructure/DoctrineMigrations',
            ],
        ]);
    }
}
