<?php

namespace BoxedCode\Authify\Providers\Two;

class FacebookProvider extends AbstractProvider
{
    public function createServer()
    {
        return new \League\OAuth2\Client\Provider\Facebook([
            'clientId'          => $this->configuration['identifier'],
            'clientSecret'      => $this->configuration['secret'],
            'redirectUri'       => $this->configuration['callback_uri'],
            'graphApiVersion'   => 'v2.9',
        ]);
    }

    public function refresh(array $scopes = [])
    {
        return $this->authorize($scopes);
    }

    public function getResourceOwner()
    {
        return $this->getServer()->getResourceOwner($this->getCredentials());
    }

    public function getLongLivedAccessToken()
    {
        $this->credentials = $this->getServer()->getLongLivedAccessToken($this->getCredentials());

        return $this->credentials;
    }

    public function getUserFeed($user_id, $parameters = [])
    {
        $feed_url = 'https://graph.facebook.com/'.$user_id.'/feed';

        return $this->request($feed_url, $parameters);
    }

    public function postStatus($object_id, $message, $parameters = [])
    {
        $parameters = array_merge($parameters, ['message' => $message]);

        return $this->request(
            'https://graph.facebook.com/'.$object_id.'/feed', $parameters, 'POST'
        );
    }

    public function postStatusAsPage($page_id, $message, $parameters = [])
    {
        // Get page token
        $page = $this->getPage($page_id, ['fields' => 'access_token']);
        
        // Add the page token to the parameters.
        $parameters = array_merge(
            $parameters, ['access_token' => $page['access_token']]
        );

        return $this->postStatus($page_id, $message, $parameters);
    }

    public function getUserPages($parameters = [])
    {
        return $this->request(
            'https://graph.facebook.com/me/accounts', $parameters
        );
    }

    public function getPage($page_id, $parameters = [])
    {
        return $this->request(
            'https://graph.facebook.com/'.$page_id, $parameters
        );
    }

    public function request($uri, array $parameters = [], $method = 'GET')
    {
        $token = $parameters['access_token'] ?: $this->getCredentials()->getToken();

        $appSecretProof = \League\OAuth2\Client\Provider\AppSecretProof::create(
            $this->configuration['secret'], $token
        );

        $parameters = array_merge($parameters, [
            'appsecret_proof' => $appSecretProof
        ]);

        return parent::request($uri, $parameters, $method);
    }
}