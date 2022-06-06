<?php

namespace Janwebdev\OmnipayBundle\Tests\DependencyInjection;

use Janwebdev\OmnipayBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testEmptyConfig(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), []);

        $this->assertArrayHasKey('gateways', $config);
        $this->assertEmpty($config['gateways']);
    }

    public function testMethodConfig(): void
    {
        $sampleConfig = self::getSampleMethodConfig();

        $this->assertEquals($sampleConfig, $this->processConfiguration($sampleConfig));
    }

    public function testMethodConfigWithDefaultGateway(): void
    {
        $sampleConfig = self::getSampleMethodConfig('Stripe');

        $this->assertEquals($sampleConfig, $this->processConfiguration($sampleConfig));
    }

    public function testMethodConfigWithDisabledGateways(): void
    {
        $sampleConfig = self::getSampleMethodConfig(null, ['Stripe']);

        $this->assertEquals($sampleConfig, $this->processConfiguration($sampleConfig));
    }

    public function testMethodConfigWithDefaultGatewayAndDisabledGateways(): void
    {
        $sampleConfig = self::getSampleMethodConfig('PayPal_Express', ['Stripe']);

        $this->assertEquals($sampleConfig, $this->processConfiguration($sampleConfig));
    }

    public function testMethodConfigWithInitializeOnRegistartion(): void
    {
        $sampleConfig = self::getSampleMethodConfig(null, [], true);

        $this->assertEquals($sampleConfig, $this->processConfiguration($sampleConfig));
    }

    protected static function getSampleMethodConfig(
        ?string $defaultGateway = null,
        array $disabledGateways = [],
        bool $initOnBoot = false
    ): array {
        return [
            'gateways' => [
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
            ],
            'default' => $defaultGateway,
            'disabled' => $disabledGateways,
            'init_on_boot' => $initOnBoot,
        ];
    }

    private function processConfiguration(array $sampleConfig): array
    {
	    return (new Processor())->processConfiguration(new Configuration(), [$sampleConfig]);
    }
}
