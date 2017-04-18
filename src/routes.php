<?php
// Routes
// Register
$app->post('/api/register', function ($request, $response) {
    $input = $request->getParsedBody();
    // I don't think we actually create this object,
    // I think it's passed in as json data via the request
    // and then we instantly input it into the table after doing
    // some sort of hashing/salting on the pw
    $obj = array(
    'first_name' => 'Kellen',
    'last_name' => 'Schmidt',
    'email' => 'kellenschmidt@dealsinthe.us',
    'username' => 'kelleniscool',
    'password' => 'password',
    'phone' => '1234567890'
    );
    $token = array(
    'token' => 'fsdakf098f2p098mfakl320fal'
    );
    return $this->response->withJson($token);
});
// Change Password
$app->post('/api/password', function ($request, $response) {
    $input = $request->getParsedBody();
    return $this->response->withJson($input);
});
// Sign In
$app->post('/api/signin', function ($request, $response) {
    $input = $request->getParsedBody();
    $obj = array(
    'email' => 'kellenschmidt@dealsinthe.us',
    'password' => 'password'
    );
    $token = array(
    'token' => 'fsdakf098f2p098mfakl320fal'
    );
    return $this->response->withJson($token);
});
// Update Profile
$app->put('/api/profile/[{old_username}]', function ($request, $response, $args) {
    $input = $request->getParsedBody();
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
    $sth->bindParam("first_name", $input['first_name']);
    $sth->bindParam("last_name", $input['last_name']);
    $sth->bindParam("email", $input['email']);
    $sth->bindParam("username", $input['username']);
    $sth->bindParam("phone", $input['phone']);
    $sth->bindParam("birth_date", $input['birth_date']);
    $sth->bindParam("email_marketing", $input['email_marketing']);
    $sth->bindParam("old_username", $args['old_username']);
    $sth->bindParam("updated_date", date('Y-m-d H:i:s'));
    $sth->execute();

    // Add updated_date to http response
    $input += ["updated_date" => $currentDateTime];
    return $this->response->withJson($input);
});
// Get Profile
$app->get('/api/profile/[{username}]', function ($request, $response, $args) {
    $sth = $this->db->prepare("SELECT username, email, first_name, last_name, phone, birth_date, email_marketing, creation_date, updated_date FROM users WHERE username=:username");
    $sth->bindParam("username", $args['username']);
    $sth->execute();
    $user = $sth->fetchObject();
    return $this->response->withJson($user);
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
// Vote
$app->post('/api/vote', function ($request, $response, $args) {
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

    return $this->response->withJson();
});
// Get vote count
$app->get('/api/votes/[{deal_id}]', function ($request, $response, $args) {
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
    $obj = array(
    "report_id"=>1,
    "reason_id"=>1,
    "date"=> '2017-04-10 15:45:21'
    );
    return $this->response->withJson($obj);
});
// Comment
$app->post('/api/comment', function ($request, $response, $args) {
    $obj = array(
    "comment_id"=>1,
    "date"=> '2017-04-10 15:45:21'
    );
    return $this->response->withJson($obj);
});
?>