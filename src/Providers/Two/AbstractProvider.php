<?php

namespace BoxedCode\Authify\Providers\Two;

use BoxedCode\Authify\Providers\AbstractProvider as BaseProvider;
use League\OAuth2\Client\Provider\AbstractProvider as LeagueProvider;

abstract class AbstractProvider extends BaseProvider
{
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
        if (!isset($response['code'])) {
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

        $response = $this->getResponse($request);

        if ($response instanceof \GuzzleHttp\Psr7\Response) {
            $response = $response->getBody();
        }

        // Try parsing JSON.
        if (!is_array($response)) {
            $content = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new UnexpectedValueException(sprintf(
                    "Failed to parse JSON response: %s",
                    json_last_error_msg()
                ));
            }

            return $content;
        }

        return $response;
    }
}