<?php

namespace Janwebdev\OmnipayBundle\Tests\DependencyInjection;

use Janwebdev\OmnipayBundle\DependencyInjection\OmnipayExtension;
use Janwebdev\OmnipayBundle\Manager\OmnipayManager;
use Janwebdev\OmnipayBundle\OmnipayBundle;
use Omnipay\Common\GatewayFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

abstract class OmnipayExtensionTest extends TestCase
{
    abstract protected function loadFromFile(ContainerBuilder $container, $file);

    public function testDefaultOmnipayService(): void
    {
        $container = $this->createContainerFromFile('default');

        $this->assertTrue($container->hasDefinition('omnipay'));

        $definition = $container->getDefinition('omnipay');

        $this->assertEquals(OmnipayManager::class, $definition->getClass());
    }

    public function testConfiguredOmnipayService(): void
    {
        $container = $this->createContainerFromFile('gateways');

        $this->assertValidContainer($container);
    }

    public function testConfiguredOmnipayWithDefaultService(): void
    {
        $container = $this->createContainerFromFile('gateways-with-default-gateway');

        $this->assertValidContainer($container, 'Stripe');
    }

    public function testConfiguredOmnipayWithDisabledGateways(): void
    {
        $container = $this->createContainerFromFile('gateways-with-disabled-gateways');

        $this->assertValidContainer($container, null, ['Stripe']);
    }

    public function testConfiguredOmnipayWithDefaultGatewayAdnDisabledGateways(): void
    {
        $container = $this->createContainerFromFile('gateways-with-default-gateway-and-disabled-gateways');

        $this->assertValidContainer($container, 'Stripe', ['PayPal_Express']);
    }

    public function testConfiguredOmnipayServiceWithInitializeOnRegistration(): void
    {
        $container = $this->createContainerFromFile('gateways-with-initialize-on-registration');

        $this->assertValidContainer($container);
    }

	public function testOmnipayServiceWithNonExistingDefaultGateway(): void
    {
	    $this->expectException(InvalidConfigurationException::class);
	    $this->createContainerFromFile('non-existing-default-gateway');
    }

	public function testOmnipayServiceWithDisabledDefaultGateway(): void
    {
	    $this->expectException(InvalidConfigurationException::class);
	    $this->createContainerFromFile('disabled-default-gateway');
    }

    protected static function getSampleMethodConfig(): array
    {
        return [
            'Stripe' => [
                'apiKey' => 'sk_test_BQokikJOvBiI2HlWgH4olfQ2',
            ],
            'PayPal_Express' => [
                'username' => 'test-facilitator_api1.example.com',
                'password' => '3MPI3VB4NVQ3XSVF',
                'signature' => '6fB0XmM3ODhbVdfev2hUXL2x7QWxXlb1dERTKhtWaABmpiCK1wtfcWd.',
                'testMode' => false,
                'solutionType' => 'Sole',
                'landingPage' => 'Login',
            ],
        ];
    }

    protected function createContainer(): ContainerBuilder
    {
        $bundles = [
            'OmnipayBundle' => OmnipayBundle::class,
        ];

        $container = new ContainerBuilder(new ParameterBag([
            'kernel.bundles'     => $bundles,
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.debug'       => false,
            'kernel.environment' => 'test',
            'kernel.name'        => 'kernel',
            'kernel.root_dir'    => __DIR__,
        ]));

        return $container;
    }

    protected function createContainerFromFile(string $file): ContainerBuilder
    {
        if (getenv('TRAVIS') !== false && PHP_MAJOR_VERSION == 5 && in_array(PHP_MINOR_VERSION, [5, 6])) {
            $this->markTestSkipped('This test fails on Travis CI for some unknown, but passes in other environments using these same versions');
        }

        $container = $this->createContainer();

        $container->registerExtension(new OmnipayExtension());
        $this->loadFromFile($container, $file);

        $container->compile();

        return $container;
    }

    private function assertValidContainer(
        ContainerBuilder $container,
        string $defaultGateway = null,
        array $disabledGateways = [],
        ?bool $initOnBoot = null
    ): void
    {
        $this->assertTrue($container->hasDefinition('omnipay'));

        $definition = $container->getDefinition('omnipay');

        $this->assertEquals(OmnipayManager::class, $definition->getClass());
        $this->assertEquals(GatewayFactory::class, $definition->getArgument(0)->getClass());
        $this->assertEquals(self::getSampleMethodConfig(), $definition->getArgument(1));

        if ($defaultGateway) {
            $this->assertEquals([$defaultGateway], $this->getMethodCallArguments($definition, 'setDefaultGatewayName'));
        }

        if ($disabledGateways) {
            $this->assertEquals([$disabledGateways], $this->getMethodCallArguments($definition, 'setDisabledGateways'));
        }

        if ($initOnBoot) {
            $this->assertEquals(
	            $initOnBoot,
                $this->getMethodCallArguments($definition, 'initOnBoot')
            );
        }
    }

    private function getMethodCallArguments(Definition $definition, string $method)
    {
        foreach ($definition->getMethodCalls() as [$methodName, $arguments]) {
            if ($methodName === $method) {
                return $arguments;
            }
        }

        $this->assertTrue(false, sprintf('Method call %s has not been added to the definition', $method));
    }
}
