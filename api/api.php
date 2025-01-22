<?php /** @noinspection PhpUnused */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true ");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST");
header("Access-Control-Allow-Headers: *");
const IS_INDIRECT = true;
require_once(__DIR__ . '/loader.php');

// Utility function to send a JSON response
function response($data, $status = 200): void
{
    if (is_string($data)) {
            die($data);
    }
    $response = json_encode($data);
    if ($response == NULL) {
        echo $data;
        die;
    }

    header('Content-Type: application/json');
    http_response_code($status);
    echo $response;
    die;
}

if (!isset($_GET['action'])) {
    response(['error' => 'No action specified'], 400);
}

$routes = [
    'authenticate' => 'authenticate',
    'status' => 'status',
    'members' => 'members',
    'patrolchange' => 'change_patrol',
    'rankchange' => 'change_rank'
];

// Actions

function authenticate()
{
    $url = get_osm_provider()->getAuthorizationUrl(['scope' => ['section:member:write']]);
    header('Location: ' . $url);
}

function change_patrol()
{
    $body = json_decode(file_get_contents('php://input'), true);

    // check if $body has the params
    if (!array_key_exists('scoutid', $body) || !array_key_exists('patrolid', $body) || !array_key_exists('sectionid', $body)) {
        response(['error' => 'Missing parameters'], 400);
    }

    $res = make_osm_request('/ext/members/contact/?action=update', 'POST', [
        'scoutid' => $body['scoutid'],
        'column' => 'patrolid',
        'value' => $body['patrolid'],
        'sectionid' => $body['sectionid'],
        'context' => 'members'
    ]);

    response($res);
}

function change_rank()
{
    $body = json_decode(file_get_contents('php://input'), true);

    // check if $body has the params
    if (!array_key_exists('scoutid', $body) || !array_key_exists('rank', $body) || !array_key_exists('sectionid', $body)) {
        response(['error' => 'Missing parameters'], 400);
    }

    $res = make_osm_request('/ext/members/contact/?action=update', 'POST', [
        'scoutid' => (int)$body['scoutid'],
        'column' => 'patrolleader',
        'value' => $body['rank'],
        'sectionid' => $body['sectionid'],
        'context' => 'members'
    ]);

    response($res);
}

function members()
{
    if (!isset($_GET['section'])) {
        response(['error' => 'No section specified'], 400);
    }
    if (!isset($_GET['termid'])) {
        response(['error' => 'No term specified'], 400);
    }
    if (!isset($_GET['sectionid'])) {
        response(['error' => 'No sectionid specified'], 400);
    }

    $params = [
        'action' => 'getListOfMembers',
        'sort' => 'dob',
        'sectionid' => $_GET['sectionid'],
        'termid' => $_GET['termid'],
        'section' => $_GET['section'],
    ];
    $built = http_build_query($params);

    $response = make_osm_request('/ext/members/contact/?' . $built);
    response($response);
}

function status()
{
    if (!isset($_SESSION['osm_access_token'])) {
        response(['authenticated' => false, 'session' => session_id()]);
    }

    $response = make_osm_request('/ext/generic/startup/?action=getData');

    $member_access = $response['globals']['roles'];
    $roles = [];
    $valid_sections = ['earlylearning', 'beavers', 'cubs', 'scouts'];
    $type_colours = [
        'beavers' => 'blue',
        'cubs' => 'green',
        'scouts' => 'navy',
    ];

    foreach ($member_access as $role) {
        if ($role['permissions']['member'] >= 10 && in_array($role['section'], $valid_sections)) {
            $roles[$role['groupname']][] = [
                'name' => $role['sectionname'],
                'sectionid' => $role['sectionid'],
                'groupid' => $role['groupid'],
                'member_permission' => $role['permissions']['member'],
                'colour' => $type_colours[$role['section']],
                'section' => $role['section'],
            ];
        }
    }

    response([
        'authenticated' => true,
        'firstname' => $response['globals']['firstname'],
        'roles' => $roles,
        'terms' => $response['globals']['terms'],
    ]);
}


// Action Invoker

if (!isset($routes[strtolower($_GET['action'])])) {
    response(['error' => 'Invalid action'], 400);
}

$action = $routes[strtolower($_GET['action'])];
$action();
