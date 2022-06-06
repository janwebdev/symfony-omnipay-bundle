<?php

namespace Janwebdev\OmnipayBundle\Tests\Manager;

use Janwebdev\OmnipayBundle\Manager\OmnipayManager;
use Janwebdev\OmnipayBundle\Tests\FakeGateway;
use JetBrains\PhpStorm\Pure;
use Omnipay\Common\GatewayFactory;
use Omnipay\PayPal\ProGateway;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use PHPUnit\Framework\TestCase;

class OmnipayManagerTest extends TestCase
{
    public function testGetUnconfiguredGateway()
    {
        $omnipay = $this->createOmnipay();

        $gateway = $omnipay->get('PayPal_Pro');

        $this->assertInstanceOf(ProGateway::class, $gateway);

        $this->assertEquals($gateway->getDefaultParameters(), $gateway->getParameters());
    }

    public function testGetConfiguredGateway(): void
    {
        $config = [
            'username' => 'test-facilitator_api1.example.com',
            'password' => '3MPI3VB4NVQ3XSVF',
            'signature' => '6fB0XmM3ODhbVdfev2hUXL2x7QWxXlb1dERTKhtWaABmpiCK1wtfcWd.',
            'testMode' => false,
        ];

        $omnipay = $this->createOmnipay(['PayPal_Pro' => $config]);

        $gateway = $omnipay->get('PayPal_Pro');

        $this->assertInstanceOf(ProGateway::class, $gateway);
        $this->assertEquals($config, $gateway->getParameters());
    }

    public function testGetDefaultGateway(): void
    {
        $omnipay = $this->createOmnipay();
        $omnipay->setDefaultGatewayName('PayPal_Pro');

        $gateway = $omnipay->getDefaultGateway();

        $this->assertInstanceOf(ProGateway::class, $gateway);
        $this->assertEquals($gateway->getDefaultParameters(), $gateway->getParameters());
    }

	public function testGetNonConfiguredDefaultGateway(): void
    {
	    $this->expectException(InvalidConfigurationException::class);
	    $omnipay = $this->createOmnipay();
        $omnipay->getDefaultGateway();
    }

	public function testGetDisabledGateway(): void
    {
	    $this->expectException(\RuntimeException::class);
	    $omnipay = $this->createOmnipay();
        $omnipay->setDisabledGateways(['fake']);
        $omnipay->registerGateway(new FakeGateway(), 'fake');

        $omnipay->get('fake');
    }

	public function testGetNonExistantGateway(): void
    {
	    $this->expectException(\RuntimeException::class);
	    $omnipay = $this->createOmnipay();

        $gateway = $omnipay->get('sadfhjasfswef');
    }

    public function testGetCachedGateway(): void
    {
        $omnipay = $this->createOmnipay();

        $gateway1 = $omnipay->get('PayPal_Pro');
        $gateway2 = $omnipay->get('PayPal_Pro');

        $this->assertSame($gateway1, $gateway2);
    }

    public function testRegisterGateway(): void
    {
        $omnipay = $this->createOmnipay();

        $fakeGateway = new FakeGateway();

        $omnipay->registerGateway($fakeGateway);
        $actual = $omnipay->get(FakeGateway::class);

        $this->assertSame($fakeGateway, $actual);
    }

    public function testRegisterGatewayWithAlias(): void
    {
        $omnipay = $this->createOmnipay();
        $fakeGateway = new FakeGateway();
        $aliasName = 'CompletelyMadeUpAlias';

        $omnipay->registerGateway($fakeGateway, $aliasName);
        $actual = $omnipay->get($aliasName);

        $this->assertSame($fakeGateway, $actual);
    }

    protected function createOmnipay(array $config = []): OmnipayManager
    {
        $factory = new GatewayFactory();

        return new OmnipayManager($factory, $config);
    }
}
