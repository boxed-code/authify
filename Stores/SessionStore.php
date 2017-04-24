<?php

namespace BoxedCode\Authify\Stores;

class SessionStore extends AbstractStore
{
    protected $sessionKey = 'AUTHIFY_SESSION_STORE';

    public function __construct($sessionKey = '')
    {
        if (!empty($sessionKey)) {
            $this->sessionKey = $sessionKey;
        }

        if (!session_id()) {
            session_start();
        }

        if (!$_SESSION[$this->sessionKey]) {
            $_SESSION[$this->sessionKey] = serialize([]);
        }
    }

    public function all()
    {
        return unserialize($_SESSION[$this->sessionKey]);
    }

    public function put($handle, $credentials, $merge = false)
    {
        $data = $this->all();

        if ($merge && isset($data[$handle])) {
            $credentials = array_merge($data[$handle], $credentials);
        }

        $data[$handle] = $credentials;

        $_SESSION[$this->sessionKey] = serialize($data);
    }

    public function get($handle, $default = [])
    {
        $data = $this->all();

        if (empty($data) || empty($data[$handle])) {
            return $default;
        }

        return $data[$handle];
    }

    public function destroy($handle)
    {
        $data = $this->all();

        unset($data[$handle]);

        $_SESSION[$this->sessionKey] = serialize($data);
    }
}