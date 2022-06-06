<?php

namespace Janwebdev\OmnipayBundle\Tests;

use Omnipay\Common\AbstractGateway;

class FakeGateway extends AbstractGateway
{
	public function getName(): string
	{
		return 'zasada';
	}
}