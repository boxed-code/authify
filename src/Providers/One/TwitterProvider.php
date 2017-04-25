<?php

namespace BoxedCode\Authify\Providers\One;

use League\OAuth1\Client\Server\Twitter;

class TwitterProvider extends AbstractProvider
{
    protected function createServer()
    {
        return new Twitter(array(
            'identifier' => $this->configuration['identifier'],
            'secret' => $this->configuration['secret'],
            'callback_uri' => $this->configuration['callback_uri'],
        ));
    }

    public function getUserDetails()
    {
        return $this->getServer()->getUserDetails($this->credentials);
    }

    public function getUserTimeline($screenName, $parameters = [])
    {
        $parameters = array_merge($parameters, ['screen_name' => $screenName]);

        return $this->request(
            'https://api.twitter.com/1.1/statuses/user_timeline.json', 
            $parameters
        );
    }

    public function postStatus($message, $parameters = [])
    {
        $parameters = array_merge($parameters, ['status' => $message]);

        return $this->request(
            'https://api.twitter.com/1.1/statuses/update.json', 
            $parameters,
            'POST'
        );
    }
}