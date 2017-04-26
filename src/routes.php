<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Mailgun\Mailgun;
use \Firebase\JWT\JWT;

// Enable or disable logging of http requests
$enableLogging = false;

// Return the client info from the http request header
function getHeaderInfo() {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $route = $_SERVER['REQUEST_URI'];
    $request_method = $_SERVER['REQUEST_METHOD'];
    preg_match('#\((.*?)\)#', $user_agent, $match);
    $start = strrpos($user_agent, ')') + 2;
    $end = strrpos($user_agent, ' ');
    $browser = substr($user_agent, $start, $end-$start);
    return array('ip_address' => $ip_address, 'operating_system' => $match[1], 'browser' => $browser, 'route' => $route, 'request_method' => $request_method);
}

// Take unformatted url suffix from http header and convert to formatted endpoint
function getEndpointFromRoute($unformatted) {
    // Remove backslashes ('\')
    $endpoint = str_replace('\\', '', $unformatted);
    // Remove everything up to and including '/api'
    $start = strpos($endpoint, '/api');
    $endpoint = substr($endpoint, $start + 4);
    // Get endpoint without args
    // If route ends with a forward slash ('/')
    if(substr($endpoint, strlen($endpoint) - 1, 1) == '/') {
        // Remove trailing forward slash ('/')
        $endpoint = substr($endpoint, 0, strpos($endpoint, '/', 1));
    // If route contains a url argument
    } else if(strrpos($endpoint, '/', 1) != 0) {
        // Remove url argument
        $endpoint = substr($endpoint, 0, strrpos($endpoint, '/'));
    }
    return $endpoint;
}

// Log http requests in the 'requests' table
function logRequest($_request, $_this) {

    // Only log if logging is turn on
    if(!$enableLogging) return;

    // Get user_id from jwt in authorization header
    $key = "your_secret_key";
    $jwt = $_request->getHeaders();
    $decoded = JWT::decode($jwt['HTTP_AUTHORIZATION'][0], $key, array('HS256'));
    $user_id = $decoded->context->user->user_id;

    $headerInfo = getHeaderInfo();
    // Get the endpoint_id for the endpoint that is in use
    $endpoint_id_sql = "SELECT endpoint_id
                        FROM endpoints
                        WHERE endpoint = :endpoint
                        AND request_type = :request_type";
    $sth = $_this->db->prepare($endpoint_id_sql);
    $endpoint = getEndpointFromRoute($headerInfo['route']);
    $sth->bindParam("endpoint", $endpoint);
    $sth->bindParam("request_type", $headerInfo['request_method']);
    $sth->execute();
    $endpoint_id = $sth->fetchObject()->endpoint_id;

    // Insert the log data into the table
    $sql = "INSERT INTO requests
            SET endpoint_id = :endpoint_id,
                user_id = :user_id,
                request_date = :request_date,
                ip_address = :ip_address,
                operating_system = :operating_system,
                browser = :browser";
    $sth = $_this->db->prepare($sql);
    $sth->bindParam("endpoint_id", $endpoint_id);
    $sth->bindParam("user_id", $user_id);
    $sth->bindParam("request_date", date('Y-m-d H:i:s'));
    $sth->bindParam("ip_address", $headerInfo['ip_address']);
    $sth->bindParam("operating_system", $headerInfo['operating_system']);
    $sth->bindParam("browser", $headerInfo['browser']);
    $sth->execute();
}


function isAuthenticated($jwt, $_this){
    // Potentially need to check expiration on successive requests
    // Should I also confirm the JWT they're sending is correct?
    // Not merely confirm that they're authenticated.
    $key = "your_secret_key";

    try {
        $decoded = JWT::decode($jwt['HTTP_AUTHORIZATION'][0], $key, array('HS256'));
    } catch (UnexpectedValueException $e) {
        echo $e->getMessage();
    }

    if (isset($decoded)) {
        $sql = "SELECT * FROM tokens WHERE user_id = :user_id";

        try {
            $db = $_this->db;
            $stmt = $db->prepare($sql);
            $stmt->bindParam("user_id", $decoded->context->user->user_id);
            $stmt->execute();
            $user_from_db = $stmt->fetchObject();
            $db = null;

            if (isset($user_from_db->user_id)) {
                return true;
            }
        } catch (PDOException $e) {
            return false;
        }
    }
}

function authError(){
    echo json_encode([
        "response" => "Authorization Token Error"
    ]);
}

function generateToken($user, $user_id, $_this){

    // Here need to use env var for secret key -- this string for testing
    $key = "your_secret_key";

    $payload = array(
        "iss"     => "http://www.dealsinthe.us",
        "iat"     => time(),
        "exp"     => time() + (3600 * 24 * 15),
        "context" => [
            "user" => [
                "user_login" => $user,
                "user_id"    => $user_id
            ]
        ]
    );

    try {
        $jwt = JWT::encode($payload, $key);
    } catch (Exception $e) {
        echo json_encode($e);
    }

    $sql = "INSERT INTO tokens (user_id, token, created_date, expiration_date)
        VALUES (:user_id, :token, :created_date, :expiration_date)";
    try{
        $db = $_this->db;
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $user_id);
        $stmt->bindParam("token", $jwt);
        $stmt->bindParam("created_date", $payload['iat']);
        $stmt->bindParam("expiration_date", $payload['exp']);
        $stmt->execute();
        $db = null;
        return $jwt;
    } catch (PDOException $e) {
        return null;    
    }
}
  
// Get HTML of confirmation email
function getVerifyEmail($firstName, $token) {
    $link = 'http://dealsinthe.us/api/verify-email/' . $token;
    $message = '
    <html>
        <head>
            <style>
                * {
                    text-align: center;
                    font-family: Arial, Helvetica, sans-serif;
                }

                body {
                    background-color: #e8e8e8;
                }

                #main {
                    display: block;
                    margin: auto;
                    padding-bottom: 30px;
                    width: 600px;
                    background-color: white;
                    border-radius: 3px;
                    box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.2), 0 1px 5px 0 rgba(0,0,0,0.12);
                }

                #hero-img {
                    margin-top: 25px;
                }

                #bottom-img {
                    margin-top: 14px;
                }

                h1 {
                    color: black;
                    margin: 36px 0 24px 0;
                }

                #message {
                    color: #5e5e5e;
                    font-size: 17px;
                    padding: 0 28px;
                }

                #button {
                    display: block;
                    background: #039be5;
                    color: white;
                    height: 58px;
                    line-height: 58px;
                    width: 90%;
                    font-size: 16px;
                    text-decoration: none;
                    margin: 34px auto;
                    padding: auto 0;
                    border: 0;
                    border-radius: 3px;
                    box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.2), 0 1px 5px 0 rgba(0,0,0,0.12);
                }

                #bottom-comment {
                    color: #5e5e5e;
                }

                #copyright {
                    color: #5e5e5e;
                    margin: 28px 0 4px 0;
                }

                #address {
                    color: #5e5e5e;
                    margin-top: 4px;
                }
            </style>
        </head>
        <body>
            <div id="main">
                <img id="hero-img" src="cid:GeoDealsLogo7.png" width="210">
                <h1>Verify your email address </h1>
                <p id="message">' . $firstName . ', please confirm that you want to use this as your GeoDeals account email address. Once it\'s done you\'ll be able to start saving! </p>
                <a id="button" href="' . $link . '"><b>Verify my email </b></a>
                <p id="bottom-comment">If you did not sign up for GeoDeals please ignore this email. </p>
            </div>
            <div>
                <p id="copyright">&copy; 2017 GeoDeals. All rights reserved. </p>
                <p id="address">GeoDeals, 3140 Dyer St #2409 Dallas, TX 75205 </p>
                <img id="bottom-img" src="cid:GeoDealDude.png" width="160">
            </div>
        </body>
    </html>';

    return $message;
}

function getVerifiedResponse() {
    $html = '
    <html>
        <head>
            <style>
                * {
                    font-family: Arial, Helvetica, sans-serif;
                }

                body {
                    background-color: #e8e8e8;
                }

                #main {
                    display: block;
                    margin: auto;
                    padding-bottom: 15px;
                    width: 620px;
                    background-color: white;
                    border-radius: 3px;
                    box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.2), 0 1px 5px 0 rgba(0,0,0,0.12);
                    /* Makes hero image margin-top and center work */
                    overflow: hidden;
                }

                #hero-img {
                    display: block;
                    margin-left: auto;
                    margin-right: auto;
                    margin-top: 25px;
                }

                #bottom-img {
                    margin-top: 14px;
                }

                h1 {
                    color: black;
                    margin: 36px 0 24px 0;
                    padding: 0 28px;
                }

                .message {
                    color: #5e5e5e;
                    font-size: 17px;
                    padding: 0 28px;
                }

                #greeting {
                    margin-bottom: 2px;
                }

                #signature {
                    margin: 0;
                }

                #button {
                    display: block;
                    background: #039be5;
                    color: white;
                    height: 58px;
                    line-height: 58px;
                    width: 90%;
                    font-size: 16px;
                    text-align: center;
                    text-decoration: none;
                    margin: 34px auto;
                    padding: auto 0;
                    border: 0;
                    border-radius: 3px;
                    box-shadow: 0 2px 2px 0 rgba(0,0,0,0.14), 0 3px 1px -2px rgba(0,0,0,0.2), 0 1px 5px 0 rgba(0,0,0,0.12);
                }

                #bottom {
                    text-align: center;
                }

                #copyright {
                    color: #5e5e5e;
                    margin: 28px 0 4px 0;
                    font-size: 14px;
                }

                #address {
                    color: #5e5e5e;
                    margin-top: 4px;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div id="main">
                <img id="hero-img" src="/slim-app/public/GeoDealsLogo7.png" width="210">
                <h1>Your email address has been verified</h1>
                <p class="message">Thank you, your account has been activated and you\'re now ready to begin using GeoDeals. Happy saving! </p>
                <p class="message" id="greeting">Sincerely, </p>
                <p class="message" id="signature">The GeoDeals Team </p>
                <a id="button" href="http://dealsinthe.us"><b>Go To GeoDeals</b></a>
            </div>
            <div id="bottom">
                <p id="copyright">&copy; 2017 GeoDeals. All rights reserved. </p>
                <p id="address">GeoDeals, 3140 Dyer St #2409 Dallas, TX 75205 </p>
                <img id="bottom-img" src="/slim-app/public/GeoDealDude.png" width="160">
            </div>
        </body>
    </html>';

    return $html;
}

// Get email content as text without html or css
function getVerifyEmailAsText($firstName, $token) {
    $withHtml = getVerifyEmail($firstName, $token);
    $withoutHead = substr($withHtml, strpos($withHtml, '</head>'));
    $withoutTags = strip_tags($withoutHead);
    return $withoutTags;
}

// Send verification email with link to verify email
function sendVerifyEmail($toAddress, $firstName, $token) {
    # First, instantiate the SDK with your API credentials
    $mgClient = new Mailgun('key-547d6d3ea18bc2442ae114c6d3506c7a');

    $domain = 'mg.dealsinthe.us';

    # Now, compose and send your message.
    $result = $mgClient->sendMessage($domain, array(
        'from'    => 'donotreply@dealsinthe.us', 
        'to'      => $toAddress,
        'subject' => 'Verify your email for GeoDeals',
        'text'    => getVerifyEmailAsText($firstName, $token),
        'html'    => getVerifyEmail($firstName, $token)
    ), array(
        'inline' => array('./GeoDealDude.png', './GeoDealsLogo7.png')
    ));

    return $result;
}

// Routes
// Verify email
$app->get('/api/verify-email/[{token}]', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);

    $sql = "UPDATE users
            SET verified = 1
            WHERE user_id = (
                SELECT user_id
                FROM tokens
                WHERE token = :token
            )";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("token", $args['token']);
    $sth->execute();

    // Return HTML confirmation page with link to GeoDeals
    $body = $response->getBody();
    $body->write(getVerifiedResponse());

    return $this->response->getBody();
});

// Get header information
$app->get('/api/myip', function ($request, $response, $args) {

    $jwt = $request->getHeaders();

    if(isAuthenticated($jwt, $this)){
        return $this->response->withJson(getHeaderInfo());
    }
    else{
        authError();
    }

});

// Change Password
$app->post('/api/password', function ($request, $response) {

    // Log http request
    logRequest($request, $this);

    $input = $request->getParsedBody();

    $sql = "UPDATE users
            SET password = :new_password
            WHERE email = :email
            AND password = :old_password";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("new_password", $input['new_password']);
    $sth->bindParam("old_password", $input['old_password']);
    $sth->bindParam("email", $input['email']);
    $sth->execute();

    return $this->response->withJson(array("rows affected" => $sth->rowCount()));
});

// Sign In
$app->post('/api/signin', function (Request $request, Response $response) {

    $data = $request->getParsedBody();

    //$result = file_get_contents('./users.json');
    $users = json_decode($result, true);

    $login = $data['user_login'];
    $password = $data['user_password'];

    // $options = [
    //     'cost' => 12,
    // ];
    // $hashed_pw = password_hash($password, PASSWORD_BCRYPT, $options)."\n";


    $find = "SELECT * FROM users WHERE username = :username";
    try {
        $db = $this->db;
        $stmt = $db->prepare($find);
        $stmt->bindParam("username", $login);
        $stmt->execute();
        $returned_user = $stmt->fetchObject();
        $db = null;
        $current_user = null;

        if ($returned_user) {

            $u_id = $returned_user->user_id;
            $u_uname = $returned_user->username;
            $hashed_pw = $returned_user->password;

            // $my_file = 'authfile.txt';
            // $handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
            // fwrite($handle, $data);

                if(password_verify($password, $hashed_pw)){
                    $current_user = array(
                        "user_login" => $u_uname,
                        "user_id" => $u_id
                    );
                }

            // This performs the validation (unhashed/salted right now)
            // if($u_uname == $login && $u_pw == $hashed_pw){
            //     $current_user = array(
            //         "user_login" => $u_uname,
            //         "user_id" => $u_id
            //     );
            // }
        }
    } catch (PDOException $e) {
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }

    if (!isset($current_user)) {
        echo json_encode("No user with that user/password combination");
    } else {

        // Find a corresponding token.
        $sql = "SELECT * FROM tokens
            WHERE user_id = :user_id AND expiration_date >" . time();

        $token_from_db = false;
        try {
            $db = $this->db;
            $stmt = $db->prepare($sql);
            $stmt->bindParam("user_id", $current_user['user_id']);
            $stmt->execute();
            $token_from_db = $stmt->fetchObject();
            $db = null;

            if ($token_from_db) {
                echo json_encode([
                    "token"      => $token_from_db->token,
                    "user_login" => $token_from_db->user_id
                ]);
            }
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }

        // Create a new token if a user is found but no token is found for them
        if (count($current_user) != 0 && !$token_from_db) {

            $jwt = generateToken($current_user['user_login'], $current_user['user_id'], $this);

            if($jwt != null)
            {
                echo json_encode([
                    "token"      => $jwt,
                    "user_login" => $current_user['user_id']
                ]);
            }
            else{
                echo '{"error":{"text": "Error during token generation"}}';
            }
        }
    }
});

// Register
$app->post('/api/profile', function ($request, $response, $args) {

    $input = $request->getParsedBody();

    $options = [
        'cost' => 12,
    ];
    $hashed_pw = password_hash($input['password'], PASSWORD_BCRYPT, $options);
    //$hashed_pw = password_hash($input['password'], PASSWORD_DEFAULT);

    $addVote = "INSERT INTO users 
                SET first_name = :first_name, 
                last_name = :last_name,
                email = :email,
                username = :username,
                password = :password,
                phone = :phone, 
                birth_date = :birth_date, 
                email_marketing = :email_marketing,
                creation_date = :now_date,
                updated_date = :now_date";
    $sth = $this->db->prepare($addVote);
    $sth->bindParam("first_name", $input['first_name']);
    $sth->bindParam("last_name", $input['last_name']);
    $sth->bindParam("email", $input['email']);
    $sth->bindParam("username", $input['username']);
    $sth->bindParam("password", $hashed_pw);
    $sth->bindParam("phone", $input['phone']);
    $sth->bindParam("birth_date", $input['birth_date']);
    $sth->bindParam("email_marketing", $input['email_marketing']);
    $currentDateTime = date('Y-m-d H:i:s');
    $sth->bindParam("now_date", $currentDateTime);
    $sth->execute();

    $outputSql = "SELECT user_id
                  FROM users 
                  ORDER BY creation_date DESC
                  LIMIT 1";
    $output = $this->db->prepare($outputSql);
    $output->execute();
    $result = $output->fetchObject();
    $user_id = $result->user_id;

    $jwt = generateToken($input['username'], $user_id, $this);

    //$email = sendVerifyEmail($input['email'], $input['first_name'], $jwt);

    if($jwt != null)
    {
        $return = array(
            'token' => $jwt,
            'creation_date' => $currentDateTime,
            'user_id' => $user_id,
            'email_response' => "testing"
        );
    }
    else{
        $return = '{"error":{"text": "Error during token generation"}}';
    }

    return $this->response->withJson($return);
});
// Update Profile
$app->put('/api/profile/[{old_username}]', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);

    $input = $request->getParsedBody();

    $getProfile = "SELECT first_name, last_name, email, username, phone, birth_date, email_marketing
                   FROM users
                   WHERE username = :old_username";
    $sth = $this->db->prepare($getProfile);
    $sth->bindParam("old_username", $args['old_username']);
    $sth->execute();
    $profile = $sth->fetchObject();

    $sql = "UPDATE users 
            SET first_name = :first_name, 
                last_name = :last_name,
                email = :email,
                username = :username,
                phone = :phone, 
                birth_date = :birth_date, 
                email_marketing = :email_marketing,
                updated_date = :updated_date
            WHERE username = :old_username";
    $sth = $this->db->prepare($sql);
    $sth->bindValue("first_name", ($input['first_name'] == null ? $profile->first_name : $input['first_name']));
    $sth->bindValue("last_name", ($input['last_name'] == null ? $profile->last_name : $input['last_name']));
    $sth->bindValue("email", ($input['email'] == null ? $profile->email : $input['email']));
    $sth->bindValue("username", ($input['username'] == null ? $profile->username : $input['username']));
    $sth->bindValue("phone", ($input['phone'] == null ? $profile->phone : $input['phone']));
    $sth->bindValue("birth_date", ($input['birth_date'] == null ? $profile->birth_date : $input['birth_date']));
    $sth->bindValue("email_marketing", ($input['email_marketing'] == null ? $profile->email_marketing : $input['email_marketing']));
    $sth->bindParam("old_username", $args['old_username']);
    $currentDateTime = date('Y-m-d H:i:s');
    $sth->bindParam("updated_date", $currentDateTime);
    $sth->execute();

    // Add updated_date to http response
    $input += ["updated_date" => $currentDateTime];
    return $this->response->withJson($input);
});
// Get Profile
$app->get('/api/profile/[{username}]', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);

    // Changed the status to expired (status_id = 3) for all deals that are past their expiration_date
    $expired = "UPDATE deals d1 
                SET d1.status_id = 3 
                WHERE d1.deal_id IN (
                    SELECT expired_deal_ids
                    FROM (
                        SELECT deal_id 
                        AS expired_deal_ids 
                        FROM deals d3 
                        WHERE status_id = 0 
                        AND expiration_date < :now_date
                    ) 
                    AS d2
                )";
    $sth = $this->db->prepare($expired);
    $sth->bindParam("now_date", date('Y-m-d H:i:s'));
    $sth->execute();

    $sth = $this->db->prepare("SELECT username, email, first_name, last_name, phone, birth_date, email_marketing, creation_date, updated_date
                               FROM users 
                               WHERE username = :username");
    $sth->bindParam("username", $args['username']);
    $sth->execute();
    $user = $sth->fetchObject();
    return $this->response->withJson($user);
});
//Delete profile
$app->delete('/api/profile', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);

    $input = $request->getParsedBody();

    $sth = $this->db->prepare("DELETE FROM users
                               WHERE user_id = :user_id");
    $sth->bindParam("user_id", $input['user_id']);
    $sth->execute();

    return $this->response->withJson(array("rows affected" => $sth->rowCount()));
});
// Deals
$app->get('/api/deals/[{location}]', function ($request, $response, $args) {
    $obj = array( 'deals' => [
    array(
    "deal_id"=> 1,
    "username"=> "rhallmark",
    "title"=> "Half off T-Shirts",
    "store"=> "Target",
    "description"=> "Half off kids shirt at target",
    "category"=> "Clothing"
    ),
    array(
    "deal_id"=> 2,
    "username"=> "russellrocks",
    "title"=> "20% off pots",
    "store"=> "Walmart",
    "description"=> "Get all the pots!",
    "category"=> "Kitchen"
    ),
    array(
    "deal_id"=> 3,
    "username"=> "kellenrocks",
    "title"=> "Buy one get one free pants",
    "store"=> "Khols",
    "description"=> "I got four pants for free!",
        "category"=> "Clothing"
    )]
    );
    return $this->response->withJson($obj);
});
// Get specific deal
$app->get('/api/deal/[{deal_id}]', function ($request, $response, $args) {
    $obj = array(
    "deal_id"=> 1,
    "username"=> "rhallmark",
    "title"=> "Half off T-Shirts",
    "store"=> "Target",
    "description"=> "Half off kids shirt at target",
    "category"=> "Clothing"
    );
    return $this->response->withJson($obj);
});
// Add New Deal
$app->post('/api/newdeal', function ($request, $response, $args) {
    $obj = array(
    "deal_id"=> 1,
    "username"=> "rhallmark",
    "title"=> "Half off T-Shirts",
    "store"=> "Target",
    "description"=> "Half off kids shirt at target",
    "category"=> "Clothing",
    "pictures"=> array(
    "/Pictures/DCIM/picturepath1.jpg",
    "/Pictures/Gallery/picturepath2.jpg"
    )
    );
    return $this->response->withJson($obj);
});
//Delete deal
$app->delete('/api/deal', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);

    $input = $request->getParsedBody();

    $sth = $this->db->prepare("UPDATE deals
                               SET status_id = 4
                               WHERE deal_id = :deal_id");
    $sth->bindParam("deal_id", $input['deal_id']);
    $sth->execute();

    return $this->response->withJson(array("rows affected" => $sth->rowCount()));
});
// Vote
$app->post('/api/vote', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);

    // Get current vote for user on specific deal
    $input = $request->getParsedBody();
    $search = "SELECT vote_type
               FROM votes 
               WHERE user_id = :user_id 
               AND deal_id = :deal_id";
    $sth = $this->db->prepare($search);
    $sth->bindParam("user_id", $input['user_id']);
    $sth->bindParam("deal_id", $input['deal_id']);
    $success = $sth->execute();
    $vote = $sth->fetchObject();
    
    // If query executes successfully (all input args are valid)
    if($success) {
        // If user has not voted on current deal
        // Add new entry in votes table
        if($vote == false) {
            $addVote = "INSERT INTO votes 
                        SET vote_type = :vote_type,
                            user_id = :user_id,
                            deal_id = :deal_id,
                            vote_date = :vote_date";
            $sth = $this->db->prepare($addVote);
            $sth->bindParam("vote_type", $input['vote_type']);
            $sth->bindParam("user_id", $input['user_id']);
            $sth->bindParam("deal_id", $input['deal_id']);
            $sth->bindParam("vote_date", date('Y-m-d H:i:s'));
            $sth->execute();
        }
        // If user has already voted on current deal
        // Update existing entry in votes table with vote_type
        else {
            $editVote = "UPDATE votes 
                         SET vote_type = :vote_type,
                             vote_date = :vote_date
                         WHERE user_id = :user_id
                         AND deal_id = :deal_id";
            $sth = $this->db->prepare($editVote);
            $sth->bindParam("vote_type", $input['vote_type']);
            $sth->bindParam("user_id", $input['user_id']);
            $sth->bindParam("deal_id", $input['deal_id']);
            $sth->bindParam("vote_date", date('Y-m-d H:i:s'));
            $sth->execute();
        }
    }

    return $this->response->withJson(array("rows affected" => $sth->rowCount()));
});
// Get vote count
$app->get('/api/votes/[{deal_id}]', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);

    // Get number of upvotes
    $sth = $this->db->prepare("SELECT COUNT(vote_id) AS upvotes
                               FROM votes 
                               WHERE vote_type = 1 
                               AND deal_id = :deal_id");
    $sth->bindParam("deal_id", $args['deal_id']);
    $sth->execute();
    $upvotes = $sth->fetchObject()->upvotes;

    // Get number of downvotes
    $sth = $this->db->prepare("SELECT COUNT(vote_id) AS downvotes
                               FROM votes 
                               WHERE vote_type = 0 
                               AND deal_id = :deal_id");
    $sth->bindParam("deal_id", $args['deal_id']);
    $sth->execute();
    $downvotes = $sth->fetchObject()->downvotes;

    // Calculate vote count
    $difference = $upvotes-$downvotes;
    return $this->response->withJson(array("votes" => $difference));
});
// Flag
$app->post('/api/flag', function ($request, $response, $args) {
    
    // Log http request
    logRequest($request, $this);

    $input = $request->getParsedBody();

    $sql = "INSERT INTO reports
            SET deal_id = :deal_id,
                user_id = :user_id,
                reason_id = :reason_id,
                report_date = :report_date,
                updated_date = :updated_date";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("deal_id", $input['deal_id']);
    $sth->bindParam("user_id", $input['user_id']);
    $sth->bindParam("reason_id", $input['reason_id']);
    $currentDateTime = date('Y-m-d H:i:s');
    $sth->bindParam("report_date", $currentDateTime);
    $sth->bindParam("updated_date", $currentDateTime);
    $sth->execute();

    $outputSql = "SELECT report_id 
                  FROM reports 
                  WHERE report_date = :report_date
                  LIMIT 1";
    $output = $this->db->prepare($outputSql);
    $output->bindParam("report_date", $currentDateTime);
    $output->execute();
    $report_id = $output->fetchObject()->report_id;

    $return = array(
    'report_id' => $report_id,
    'report_date' => $currentDateTime,
    'updated_date' => $currentDateTime
    );

    return $this->response->withJson($return);
});
// Get flag reasons
$app->get('/api/flags', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);
    
    $sql = "SELECT reason
            FROM reasons";
    $sth = $this->db->prepare($sql);
    $sth->execute();
    $reasons = $sth->fetchAll();
    
    return $this->response->withJson($reasons);
});
// Post comment
$app->post('/api/comment', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);

    $input = $request->getParsedBody();

    $sql = "INSERT INTO comments 
            SET deal_id = :deal_id, 
                user_id = :user_id,
                comment = :comment,
                posted_date = :posted_date,
                updated_date = :updated_date";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("deal_id", $input['deal_id']);
    $sth->bindParam("user_id", $input['user_id']);
    $sth->bindParam("comment", $input['comment']);
    $currentDateTime = date('Y-m-d H:i:s');
    $sth->bindParam("posted_date", $currentDateTime);
    $sth->bindParam("updated_date", $currentDateTime);
    $sth->execute();

    $outputSql = "SELECT * 
                  FROM comments 
                  ORDER BY posted_date DESC
                  LIMIT 1";
    $output = $this->db->prepare($outputSql);
    $output->execute();
    $return = $output->fetchObject();

    return $this->response->withJson($return);
});
// PUT for updating comment
$app->put('/api/comment', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);

    $input = $request->getParsedBody();

    $sql = "UPDATE comments 
            SET comment = :comment,
                updated_date = :updated_date
            WHERE comment_id = :comment_id";
    $sth = $this->db->prepare($sql);
    $sth->bindParam("comment", $input['comment']);
    $sth->bindParam("updated_date", date('Y-m-d H:i:s'));
    $sth->bindParam("comment_id", $input['comment_id']);
    $sth->execute();

    $outputSql = "SELECT * 
                  FROM comments 
                  ORDER BY posted_date DESC
                  LIMIT 1";
    $output = $this->db->prepare($outputSql);
    $output->execute();
    $return = $output->fetchObject();

    return $this->response->withJson($return);
});
//Get comments
$app->get('/api/comments/[{deal_id}]', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);

    $sth = $this->db->prepare("SELECT username, comment, posted_date, comments.updated_date
                               FROM comments 
                               JOIN users 
                               ON comments.user_id = users.user_id
                               WHERE deal_id = :deal_id
                               AND status_id = 0
                               ORDER BY posted_date");
    $sth->bindParam("deal_id", $args['deal_id']);
    $sth->execute();
    $comments = $sth->fetchAll();

    return $this->response->withJson($comments);
});
//Delete comment
$app->delete('/api/comment', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);

    $input = $request->getParsedBody();

    $sth = $this->db->prepare("UPDATE comments
                               SET status_id = 4
                               WHERE comment_id = :comment_id");
    $sth->bindParam("comment_id", $input['comment_id']);
    $sth->execute();

    return $this->response->withJson(array("rows affected" => $sth->rowCount()));
});
//Get statuses
$app->get('/api/statuses', function ($request, $response, $args) {

    // Log http request
    logRequest($request, $this);

    $sth = $this->db->prepare("SELECT *
                               FROM statuses");
    $sth->execute();
    $statuses = $sth->fetchAll();
    return $this->response->withJson($statuses);
});
?>