<?php

namespace BoxedCode\Authify\Stores;

use BoxedCode\Authify\Stores\AbstractStore;
use Concrete\Core\Config\Repository\Repository;

class ConcreteConfigStore extends AbstractStore
{
    protected $configuration;

    protected $configuration_key;

    public function __construct($configuration_key, Repository $configuration)
    {
        $this->configuration_key = $configuration_key;

        $this->configuration = $configuration;
    }

    protected function getConfigurationKeyName($name = '')
    {
        $base_key = $this->configuration_key;

        if (!empty($name)) {
            return $base_key.'.'.$name;
        }

        return $base_key;
    }

    public function all()
    {
        return $this->configuration->get($this->getConfigurationKeyName(), []);
    }

    public function put($handle, $credentials, $merge = false)
    {
        if ($merge && !empty($this->get($handle))) {
            $credentials = array_merge($this->get($handle), $credentials);
        }

        $this->configuration->save($this->getConfigurationKeyName($handle), $credentials);
    }

    public function get($handle)
    {
        return $this->configuration->get($this->getConfigurationKeyName($handle), []);
    }

    public function destroy($handle)
    {
        $data = $this->all();
        unset($data[$handle]);
        $this->configuration->save($this->getConfigurationKeyName(), $data);
    }
}