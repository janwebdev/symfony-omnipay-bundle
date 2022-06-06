<?php

namespace Janwebdev\OmnipayBundle\Manager;

use Omnipay\Common\Http\Client;
use Omnipay\Common\GatewayFactory;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Helper;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class OmnipayManager
{
    /**
     * @var GatewayFactory
     */
    protected GatewayFactory $gatewayFactory;

    /**
     * @var array
     */
    protected array $config;

    /**
     * @var GatewayInterface[]
     */
    protected array $storage;

    /**
     * @var GatewayInterface[]
     */
    protected array $registeredGateways = [];

    /**
     * @var string[]
     */
    protected array $disabledGateways = [];

    /**
     * @var string|null
     */
    protected ?string $defaultGatewayName = null;

    /**
     * @var bool
     */
    protected bool $initOnBoot = false;

    public function __construct(GatewayFactory $gatewayFactory, array $config = [])
    {
        $this->gatewayFactory = $gatewayFactory;
        $this->config = $config;
    }

    public function get(string $gatewayName): GatewayInterface
    {
        if (!isset($this->storage[$gatewayName])) {
            $gateway = $this->createGateway($gatewayName);
            $this->storage[$gatewayName] = $gateway;
        }

        return $this->storage[$gatewayName];
    }

    protected function createGateway(string $gatewayName): GatewayInterface
    {
        if (isset($this->registeredGateways[$gatewayName])) {
            $gateway = $this->registeredGateways[$gatewayName];
        } else {
            $gateway = $this->gatewayFactory->create($gatewayName, new Client());
        }

        return $gateway->initialize($this->getGatewayConfig($gatewayName));
    }

    protected function getGatewayConfig(string $gatewayName): array
    {
        return $this->config[$gatewayName] ?? [];
    }

    public function registerGateway(GatewayInterface $gatewayInstance, ?string $alias = null): void
    {
        $name = $alias ?? Helper::getGatewayClassName(get_class($gatewayInstance));

        if (in_array($name, $this->disabledGateways, true)) {
            return;
        }

        $this->registeredGateways[$name] = $gatewayInstance;

        if ($this->initOnBoot) {
            $gatewayInstance->initialize($this->getGatewayConfig($name));
            $this->storage[$name] = $gatewayInstance;
        }
    }

    public function setDisabledGateways(array $disabledGateways): void
    {
        $this->disabledGateways = $disabledGateways;
    }

    public function getDefaultGateway(): GatewayInterface
    {
        if (null === $this->defaultGatewayName) {
            throw new InvalidConfigurationException('Default gateway is not configured');
        }

        return $this->get($this->defaultGatewayName);
    }

    public function setDefaultGatewayName(string $defaultGatewayName): void
    {
        $this->defaultGatewayName = $defaultGatewayName;
    }

    public function initOnBoot(bool $initOnBoot): void
    {
        $this->initOnBoot = $initOnBoot;
    }
}
