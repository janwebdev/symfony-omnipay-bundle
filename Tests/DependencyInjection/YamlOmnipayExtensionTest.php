<?php

namespace Janwebdev\OmnipayBundle\Tests\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class YamlOmnipayExtensionTest extends OmnipayExtensionTest
{
    protected function loadFromFile(ContainerBuilder $container, $file): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/Fixtures/yaml'));
        $loader->load($file.'.yaml');
    }
}
