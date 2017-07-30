<?php

namespace BoxedCode\Authify\Providers;

use Exception;
use BoxedCode\Authify\Providers\One\TwitterProvider;
use BoxedCode\Authify\Providers\Two\FacebookProvider;
use BoxedCode\Authify\Stores\AbstractStore as Store;

class Manager
{
    protected $transientStore;

    public function __construct(Store $transientStore)
    {
        $this->transientStore = $transientStore;
    }

    public function provider($name, $handle = '', array $configuration = [], $credentials = null)
    {
        if (empty($handle)) {
            $handle = $name.'-'.time();
        }

        $methodName = 'create'.ucwords($name).'Provider';

        if (method_exists($this, $methodName)) {
            return call_user_func_array(
                [$this, $methodName], [$handle, $configuration, $credentials]
            );
        }

        throw new Exception(
            sprintf('There is no provider registered by that name. [%s]', $name)
        );
    }

    protected function createTwitterProvider($handle, array $configuration, $credentials)
    {
        return (new TwitterProvider($handle, $configuration, $credentials))
            ->setTransientStore($this->transientStore);
    }

    protected function createFacebookProvider($handle, array $configuration, $credentials)
    {
        return (new FacebookProvider($handle, $configuration, $credentials))
            ->setTransientStore($this->transientStore);
    }
}