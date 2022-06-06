<?php

namespace Janwebdev\OmnipayBundle\Tests\DependencyInjection\Compiler;

use Janwebdev\OmnipayBundle\DependencyInjection\Compiler\GatewayTagCompilerPass;
use Janwebdev\OmnipayBundle\Manager\OmnipayManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class GatewayTagCompilerPassTest extends TestCase
{
    public function testProcessDoesntFailWhenOmnipayUndefined(): void
    {
        $container = $this->createContainer(false);

        $this->assertFalse($container->hasDefinition('omnipay'));

        $pass = new GatewayTagCompilerPass();
        $pass->process($container);

        $this->assertTrue(true, 'Just making sure nothing blew up');
    }

    public function testProcessWithAlias(): void
    {
        $container = $this->createContainer(true, 'TestGateway');

        $pass = new GatewayTagCompilerPass();
        $pass->process($container);

        $omnipayDefinition = $container->findDefinition('omnipay');

        $methodCalls = $this->getMethodCallsByName($omnipayDefinition, 'registerGateway');
        $this->assertCount(1, $methodCalls);

        list($reference, $alias) = reset($methodCalls);
        $this->assertReferenceEquals('test.gateway', $reference);
        $this->assertEquals('TestGateway', $alias);
    }

    public function testProcessWithoutAlias(): void
    {
        $container = $this->createContainer(true);

        $pass = new GatewayTagCompilerPass();
        $pass->process($container);

        $omnipayDefinition = $container->findDefinition('omnipay');

        $methodCalls = $this->getMethodCallsByName($omnipayDefinition, 'registerGateway');
        $this->assertCount(1, $methodCalls);

        list($reference) = reset($methodCalls);
        $this->assertReferenceEquals('test.gateway', $reference);
    }

    protected function createContainer(bool $withOmnipay, ?string $fakeGatewayAlias = null): ContainerBuilder
    {
        $container = new ContainerBuilder();

        if ($withOmnipay) {
            $container->setDefinition('omnipay', new Definition(OmnipayManager::class));
        }

        $gatewayDefinition = new Definition('My\Fake\Gateway');
        if ($fakeGatewayAlias === null) {
            $gatewayDefinition->addTag('omnipay.gateway');
        } else {
            $gatewayDefinition->addTag('omnipay.gateway', ['alias' => $fakeGatewayAlias]);
        }

        $container->setDefinition('test.gateway', $gatewayDefinition);

        return $container;
    }

    protected function getMethodCallsByName(Definition $serviceDefinition, string $methodName): array
    {
        $ret = [];
        foreach ($serviceDefinition->getMethodCalls() as [$name, $args]) {
            if ($name === $methodName) {
                $ret[] = $args;
            }
        }

        return $ret;
    }

    private function assertReferenceEquals(string $expectedId, Reference $actualReference): void
    {
        $this->assertEquals($expectedId, $actualReference->__toString());
    }
}
