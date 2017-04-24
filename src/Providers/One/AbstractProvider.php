<?php

namespace BoxedCode\Authify\Providers\One;

use BoxedCode\Authify\Exceptions\NoAuthorizationException;
use BoxedCode\Authify\Exceptions\NoStateException;
use BoxedCode\Authify\Providers\AbstractProvider as BaseProvider;

abstract class AbstractProvider extends BaseProvider
{
    public function authorize(array $scopes = [])
    {
        // First part of OAuth 1.0 authentication is retrieving temporary credentials.
        // These identify you as a client to the server.
        $temporaryCredentials = $this->getServer()->getTemporaryCredentials();

        // Store the credentials in the session.
        $this->transientStore->put('temporaryCredentials', $temporaryCredentials);

        // Second part of OAuth 1.0 authentication is to redirect the
        // resource owner to the login screen on the server.
        $this->getServer()->authorize($temporaryCredentials);

        exit;
    }

    public function exchange(array $response)
    {
        // Retrieve the temporary credentials from the authorization or throw an exception.
        if (!($temporaryCredentials = $this->transientStore->get('temporaryCredentials', false))) {
            throw new NoStateException('Invalid session state.');
        }

        // Check the response token & verifier data exists.
        if (!isset($response['oauth_token']) || !isset($response['oauth_verifier'])) {
            throw new NoAuthorizationException(
                'There was no valid authorization token or verifier found in the response.'
            );
        }

        // Third and final part to OAuth 1.0 authentication is to retrieve token
        // credentials (formally known as access tokens in earlier OAuth 1.0 specs).
        $this->credentials = $this->getServer()->getTokenCredentials(
            $temporaryCredentials, $response['oauth_token'], $response['oauth_verifier']
        );

        // Now, we'll store the token credentials and discard the temporary
        // ones - they're irrelevant at this stage.
        $this->transientStore->destroy('temporaryCredentials');

        return true;
    }

    public function request($uri, array $parameters = [], $method = 'GET')
    {
        $url = $this->buildUrl($uri, $parameters);

        $client = $this->createHttpClient();

        $headers = $this->getHeaders($this->getCredentials(), $method, $url);

        try {
            $response = $client->$method($url, [
                'headers' => $headers,
            ]);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $body = $response->getBody();
            $statusCode = $response->getStatusCode();

            throw new \Exception(
                "Received error [$body] with status code [$statusCode] when retrieving token credentials."
            );
        }

        $contentType = explode(';', $response->getHeader('content-type')[0]);

        switch ($contentType[0]) {
            case 'application/json':
                return json_decode((string) $response->getBody(), true);

            case 'text/xml':
                return simplexml_load_string((string) $response->getBody());

            case 'text/plain':
                return (string) $response->getBody();

            default:
                throw new \InvalidArgumentException("Invalid response type [{$this->responseType}].");
        }
    }
}