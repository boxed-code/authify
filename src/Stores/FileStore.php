<?php

namespace BoxedCode\Authify\Stores;

use BoxedCode\Authify\Stores\AbstractStore;

class FileStore extends AbstractStore
{
    protected $file_path;

    public function __construct($file_path, $defaults = [])
    {
        $this->file_path = $file_path;

        if (!file_exists($file_path)) {
            file_put_contents($this->file_path, serialize($defaults));
        }
    }

    public function all()
    {
        return unserialize(file_get_contents($this->file_path));
    }

    public function put($handle, $credentials, $merge = false)
    {
        $data = $this->all();

        if ($merge && !empty($data[$handle])) {
            $credentials = array_merge($data[$handle], $credentials);
        }

        $data[$handle] = $credentials;

        file_put_contents($this->file_path, serialize($data));
    }

    public function get($handle)
    {
        $data = $this->all();

        return $data[$handle];
    }

    public function destroy($handle)
    {
        $data = $this->all();

        unset($data[$handle]);

        file_put_contents($this->file_path, serialize($data));
    }
}