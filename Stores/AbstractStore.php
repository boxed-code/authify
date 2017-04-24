<?php

namespace BoxedCode\Authify\Stores;

abstract class AbstractStore
{
    abstract public function all();
    abstract public function get($key);
    abstract public function put($key, $data, $merge = false);
    abstract public function destroy($key);
}