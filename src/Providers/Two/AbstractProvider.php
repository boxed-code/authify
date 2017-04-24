<?php

namespace BoxedCode\Authify\Providers\Two;

use BoxedCode\Authify\Providers\AbstractProvider as BaseProvider;
use League\OAuth2\Client\Provider\AbstractProvider as LeagueProvider;

abstract class AbstractProvider extends BaseProvider
{
    public function validateResponseData(array $response)
    {
        return isset($response['code']);
    }

    public function authorize(array $scopes = [])
    {
        $url = $this->getServer()->getAuthorizationUrl([
            'scope' => $scopes,
        ]);

        $this->transientStore->put('oauth_state', $this->getServer()->getState());
        
        header('Location: '.$url);

        exit;
    }

    public function exchange(array $response)
    {
        // Retrieve the temporary credentials from the authorization or throw an exception.
        if (!isset($response['state']) || !($response['state'] = $this->transientStore->get('oauth_state', false))) {
            throw new \BoxedCode\Authify\Exceptions\NoStateException('Invalid session state.');
        }

        // Check the response token & verifier data exists.
        if (!$this->validateResponseData($response)) {
            throw new NoAuthorizationException(
                'There was no valid authorization code found in the response.'
            );
        }

        // Try to get an access token (using the authorization code grant)
        $this->credentials = $this->getServer()->getAccessToken('authorization_code', [
            'code' => $response['code']
        ]);

        return true;
    }

    abstract public function refresh();

    public function request($uri, array $parameters = [], $method = 'GET')
    {
        $parameters = array_merge([
            'access_token' => $this->getCredentials()->getToken(),
        ], $parameters);

        $url = $this->buildUrl($uri, $parameters);

        $request = $this->getAuthenticatedRequest(
            $method, $url, $this->getCredentials()
        );

        return $this->getParsedResponse($request);
    }
}