<?php

if (!defined('IS_INDIRECT')) {
    header('Location: /');
    die;
}

function get_config()
{
    return json_decode(file_get_contents(__DIR__ . '/secrets.json'), true);
}

function get_osm_provider() {
    $osm_credentials = get_config()['online-scout-manager'];

    return new \League\OAuth2\Client\Provider\GenericProvider([
        'clientId' => $osm_credentials['oauth-client-id'],
        'clientSecret' => $osm_credentials['oauth-client-secret'],
        'redirectUri' => $osm_credentials['oauth-redirect-uri'],
        'urlAuthorize'            => 'https://www.onlinescoutmanager.co.uk/oauth/authorize',
        'urlAccessToken'          => 'https://www.onlinescoutmanager.co.uk/oauth/token',
        'urlResourceOwnerDetails' => 'https://www.onlinescoutmanager.co.uk/oauth/resource'
    ]);
}



function make_osm_request($route, $method = 'GET', $body = null) {
    if (!isset($_SESSION['osm_access_token'])) {
        return null;
    }

    // Refresh the token if it's expired
    if (time() > $_SESSION['osm_expires']) {
        $provider = get_osm_provider();
        $newToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $_SESSION['osm_refresh_token']
        ]);
        $_SESSION['osm_access_token'] = $newToken->getToken();
        $_SESSION['osm_refresh_token'] = $newToken->getRefreshToken();
        $_SESSION['osm_expires'] = $newToken->getExpires();
    }

    $provider = get_osm_provider();
    $accessToken = $_SESSION['osm_access_token'];

    $request = $provider->getAuthenticatedRequest(
        $method,
        'https://www.onlinescoutmanager.co.uk' . $route,
        $accessToken
    );

    $client = new \GuzzleHttp\Client();
    $response = $client->send($request, ['form_params' => $body]);

    $contents = $response->getBody()->getContents();

    if (str_starts_with($contents, 'var data_holder = ')) {
        $contents = substr($contents, 18, -1);
    }

    // Find any "html" and know that this is the notepad of a section which notoriously breaks PHP JSON parsing.
    // We can just remove everything after and close the json object.
    $htmlPos = strpos($contents, '"html"');
    if ($htmlPos !== false) {
        $contents = substr($contents, 0, $htmlPos - 1) . '}}}}';
    }

    return json_decode($contents, true);
}