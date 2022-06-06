<?php

namespace Janwebdev\OmnipayBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class GatewayTagCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('omnipay')) {
            return;
        }

        $definition = $container->findDefinition('omnipay');

        $taggedGateways = $container->findTaggedServiceIds('omnipay.gateway');
        foreach ($taggedGateways as $id => $tags) {
            foreach ($tags as $tag) {
                $args = [new Reference($id)];

                // Reference the gateway by the alias if provided
                if (isset($tag['alias'])) {
                    $args[] = $tag['alias'];
                }

                $definition->addMethodCall('registerGateway', $args);
            }
        }
    }
}
