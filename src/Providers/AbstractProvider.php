<?php

namespace BoxedCode\Authify\Providers;

use ReflectionClass;
use BoxedCode\Authify\Stores\AbstractStore;

abstract class AbstractProvider
{
    protected $transientStore;

    protected $configuration;

    protected $credentials;

    protected $handle;

    protected $server;

    public function __construct($handle, array $configuration, $credentials = null)
    {
        $this->configuration = $configuration;

        $this->credentials = $credentials;

        $this->handle = $handle;
    }

    public function getName()
    {
        $reflect = new ReflectionClass($this);

        $className = strtolower($reflect->getShortName());

        return substr($className, 0, -8);
    }

    public function getHandle()
    {
        return $this->handle;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getCredentials()
    {
        return $this->credentials;
    }

    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;

        return $this;
    }

    public function getServer()
    {
        if (!$this->server) {
            $this->server = $this->createServer();
        }

        return $this->server;
    }

    public function setTransientStore(AbstractStore $store)
    {
        $this->transientStore = $store;

        return $this;
    }

    abstract protected function createServer();
    abstract public function authorize(array $scopes = []);
    abstract public function validateResponseData(array $response);
    abstract public function exchange(array $response);
    abstract public function request($uri, array $parameters = [], $method = 'GET');

    protected function buildUrl($uri, array $parameters = [])
    {
        if (count($parameters) > 0) {
            if ('?' !== substr($uri, -1)) {
                $uri = $uri.'?';
            }

            $uri = $uri.http_build_query($parameters);
        }

        return $uri;
    }

    public function __call($name, $args)
    {
        $server = $this->getServer();

        if (method_exists($server, $name)) {
            return call_user_func_array([$server, $name], $args);
        }
    }
}