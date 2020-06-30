<?php

namespace C201\FileStore;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-06-09
 *
 * @codeCoverageIgnore
 */
class C201FileStoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $mappings = [
            realpath(__DIR__ . '/../resources/config/doctrine-mapping/file')               => 'C201\FileStore\Domain\File',
            realpath(__DIR__ . '/../resources/config/doctrine-mapping/storage/filesystem') => 'C201\FileStore\Domain\Storage\Filesystem',
        ];

        $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings));
    }
}
