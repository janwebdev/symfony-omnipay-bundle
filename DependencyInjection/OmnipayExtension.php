<?php

namespace Janwebdev\OmnipayBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class OmnipayExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $gateways = $config['gateways'];
        $gatewayNames = array_keys($gateways);

        // Add configuration to the Omnipay service
        $omnipay = $container->getDefinition('omnipay');
        $omnipay->setPublic(true);
        $omnipay->addArgument($gateways);

        if ($disabledGateways = $config['disabled']) {
            $omnipay->addMethodCall('setDisabledGateways', [$disabledGateways]);
        }

        if ($defaultGateway = $config['default']) {
            $allowedValues = array_diff($gatewayNames, $disabledGateways);

            if (!in_array($defaultGateway, $gatewayNames, true)) {
                throw new InvalidConfigurationException(sprintf(
                    'You cannot specify non-existing gateway (%s) as default. Allowed values: %s',
                    $defaultGateway,
                    implode(', ', $allowedValues)
                ));
            }

            if (in_array($defaultGateway, $disabledGateways, true)) {
                throw new InvalidConfigurationException(sprintf(
                    'You cannot specify disabled gateway (%s) as default. Allowed values: %s',
                    $defaultGateway,
                    implode(', ', $allowedValues)
                ));
            }

            $omnipay->addMethodCall('setDefaultGatewayName', [$defaultGateway]);
        }

        if ($initOnBoot = $config['init_on_boot']) {
            $omnipay->addMethodCall('initOnBoot', [$initOnBoot]);
        }
    }
}
