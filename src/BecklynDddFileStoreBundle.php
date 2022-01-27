<?php declare(strict_types=1);

namespace Becklyn\Ddd\FileStore;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Marko Vujnovic <mv@becklyn.com>
 *
 * @since  2020-06-09
 *
 * @codeCoverageIgnore
 */
class BecklynDddFileStoreBundle extends Bundle
{
    public function build(ContainerBuilder $container) : void
    {
        parent::build($container);

        $mappings = [
            \realpath(__DIR__ . '/../resources/config/doctrine-mapping/file') => 'Becklyn\\Ddd\\FileStore\\Domain\\File',
            \realpath(__DIR__ . '/../resources/config/doctrine-mapping/storage/filesystem') => 'Becklyn\\Ddd\\FileStore\\Domain\\Storage\\Filesystem',
        ];

        $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings));
    }
}
