<?php

namespace Janwebdev\OmnipayBundle\Tests;

use Janwebdev\OmnipayBundle\DependencyInjection\Compiler\GatewayTagCompilerPass;
use Janwebdev\OmnipayBundle\OmnipayBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class OmnipayBundleTest extends TestCase
{
	public function testBuild(): void
	{
		$container = new ContainerBuilder();

		$bundle = new OmnipayBundle();
		$bundle->build($container);

		$matchingPasses = [];
		foreach ($container->getCompilerPassConfig()->getPasses() as $compilerPass) {
			if ($compilerPass instanceof GatewayTagCompilerPass) {
				$matchingPasses[] = $compilerPass;
			}
		}

		$this->assertNotEmpty($matchingPasses);
	}
}