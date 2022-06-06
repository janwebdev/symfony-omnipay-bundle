<?php

namespace Janwebdev\OmnipayBundle;

use Janwebdev\OmnipayBundle\DependencyInjection\Compiler\GatewayTagCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OmnipayBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new GatewayTagCompilerPass());
    }
}
