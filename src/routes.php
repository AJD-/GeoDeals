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
    $app->post('/api/profile/[{username}]', function ($request, $response, $args) {
            $obj = array(
            'first_name' => 'Kellen',
            'last_name' => 'Schmidt',
            'username' => 'kelleniscool',
            'email' => 'kellenschmidt@dealsinthe.us',
            'phone' => '1234567890'
        );

        return $this->response->withJson($obj);
    });


// Get Profile
    $app->get('/api/profile/[{username}]', function ($request, $response, $args) {
            $obj = array(
            'first_name' => 'Kellen',
            'last_name' => 'Schmidt',
            'username' => 'kelleniscool',
            'email' => 'kellenschmidt@dealsinthe.us',
            'phone' => '1234567890'
        );

        return $this->response->withJson($obj);
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



// Upvote
    $app->post('/api/upvote', function ($request, $response, $args) {

            $obj = array(
                "score"=>17
                );

        return $this->response->withJson($obj);
    });

// Upvote
    $app->post('/api/downvote', function ($request, $response, $args) {

            $obj = array(
                "score"=>17
                );

        return $this->response->withJson($obj);
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



