<?php

    /*
     * Twitter Example.
     * Authify is an oAuth 1 & 2 client with an inbuilt credential / access token manager.
     */
    
    /*
     * Setup the data stores, in this example we are using file stores for the configuration 
     * & credential stores, you will most probably want to write your own store implementation 
     * to persist these to file or a database.
     */
    
    // Store used to persist oAuth identifiers & secrets for each provider type (twitter, facebook, instagram, etc).
    $configurationStore = new FileStore(tempnam('/tmp', 'configuration'), [
        'identifier' => 'my-twitter-app-id',
        'secret' => 'my-twitter-app-secret'
    ]);

    // To persist provider access tokens for reuse, we can create multiple instances of each 
    // provider, this enables us to support multiple twitter accounts.
    $credentialsStore = new FileStore(tempnam('/tmp', 'credentials'));

    // Used to store temporary session data / credentials during the authorisation & exchange process.
    $temporaryStore = new SessionStore('session_transient_store');

    /*
     * Setup the provider & instance managers.
     */

    // Create a new provider manager;
    $providerManager = new ProviderManager($sessionStore);

    // Create a new instance manager.
    $instanceManager = new Manager($configurationStore, $credentialsStore, $providerManager);

    /*
     * Start the fun...
     */

    // Create a new twitter provider instance.
    $twitter = $manager->make('twitter', 'twitter-account-1');

    // Redirect the user for authorisation if we don't have credentials or 
    // valid response data from the oAuth endpoint yet.
     if (!$twitter->validateResponseData($_GET) && !$twitter->getCredentials()) {
        $twitter->authorize();
    } 

    // Exchange the temporary credentials returned for real ones, save them to 
    // the credential store, then post a tweet.
    elseif ($twitter->validateResponseData($_GET) && !$twitter->getCredentials()) {
        $twitter->exchange($_GET);

        $manager->save($twitter);

        $twitter->postStatus('Wow! A tweet from the league! http://apple.com');
    } 

    // Now we have persisted the credentials to our store, in a later request 
    // we could get the twitter account like so...
    $twitter = $manager->get('twitter-account-1');

    // Then start using it...
    var_dump($twitter->getUserTimeline('olsgreen'));
