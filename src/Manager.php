<?php

namespace BoxedCode\Authify;

use BoxedCode\Authify\Providers\AbstractProvider;
use BoxedCode\Authify\Providers\Manager as ProviderManager;
use BoxedCode\Authify\Stores\AbstractStore as Store;
use Exception;

class Manager
{
    protected $credentials;

    protected $configuration;

    protected $providers;

    public function __construct(Store $configuration, Store $credentials, ProviderManager $providers)
    {
        $this->configuration = $configuration;
        
        $this->credentials = $credentials;

        $this->providers = $providers;
    }

    public function make($name, $handle, $credentials = null)
    {
        if ($configuration = $this->configuration->get($name, false)) {
            return $this->providers->provider($name, $handle, $configuration, $credentials);
        }

        throw new Exception(
            sprintf('There was no provider configuration found by that name. [%s]', $name)
        );
    }

    public function get($handle)
    {
        if ($credentials = $this->credentials->get($handle, false)) {
            $tokens = unserialize($credentials['tokens']);
            return $this->make($credentials['provider'], $handle, $tokens);
        }

        throw new Exception(sprintf('There was no saved provider found with the handle. [%s]', $handle));
    }

    public function save(AbstractProvider $provider)
    {
        $credentials = [
            'tokens' => serialize($provider->getCredentials()), 
            'provider' => $provider->getName(),
        ];

        $this->credentials->put($provider->getHandle(), $credentials);
    }

    public function destroy(AbstractProvider $provider)
    {
        $this->credentials->destroy($provider->getHandle());
    }
}