<?php
const IS_INDIRECT = true;
require_once __DIR__ . '/loader.php';

if (isset($_GET['logout'])) {
    session_destroy();
    echo "<script>localStorage.removeItem('authenticated');</script> You are now logged out. Redirecting you back in 3 seconds.";
    header('Refresh: 3; URL=' . get_config()['cors-domain']);
    die;
}

if (isset($_SESSION['osm_access_token'])) {
    $url = get_config()['cors-domain'] . '/?session=' . session_id();
    die("You are already logged in. <a href='$url'>Go to Patrol Planner</a> or <a href='?logout'>Log out</a>");
}

if (!isset($_GET['code'])) {
    die('No code provided. Try visiting the homepage, and logging in again.');
}

$code = $_GET['code'];
$accessToken = get_osm_provider()->getAccessToken('authorization_code', [
    'code' => $code
]);

$_SESSION['osm_access_token'] = $accessToken->getToken();
$_SESSION['osm_refresh_token'] = $accessToken->getRefreshToken();
$_SESSION['osm_expires'] = $accessToken->getExpires();

header('Location: /?session=' . session_id());