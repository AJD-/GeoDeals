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
	$sth = $this->db->prepare("SELECT deals.deal_id, username, title, store, description, category, expiration_date, posted_date, deals.updated_date, path_to_file
				   FROM deals, users, categories, stores, pictures
				   WHERE deals.user_id = users.user_id
				   AND deals.category_id = categories.category_id
				   AND deals.store_id = stores.store_id
				   AND deals.deal_id = pictures.deal_id
				   ORDER BY deals.updated_date DESC"); //?
	$sth->execute();
	$deals = $sth->fetchAll();
        return $this->response->withJson($deals);
    });


// Get Specific Deal
    $app->get('/api/deal/[{deal_id}]', function ($request, $response, $args) {
	$sth = $this->db->prepare("SELECT deals.deal_id, username, title, store, description, category, expiration_date, posted_date, deals.updated_date, path_to_file
				   FROM deals, users, categories, stores, pictures
				   WHERE deals.user_id = users.user_id
				   AND deals.category_id = categories.category_id
				   AND deals.store_id = stores.store_id
				   AND deals.deal_id = pictures.deal_id
				   AND deals.deal_id = :deal_id");
	$sth->bindParam("deal_id", $args['deal_id']);
	$sth->execute();
	$deals = $sth->fetchObject();
        return $this->response->withJson($deals);
    });


// Edit a Deal
    $app->put('/api/deal/[{deal_id}]', function ($request, $response, $args) {
	$input = $request->getParsedBody();
	$sql = "UPDATE deals
		SET title = :title,
		    store = :store,
		    description = :description,
		    category = :category,
		    expiration_date = :expiration_date,
		    updated_date = :updated_date
		WHERE deal_id = :deal_id";
	$sth = $this->db->prepare($sql);
	$sth->bindParam("title", $input['title']);
	$sth->bindParam("store_id", $input['store_id']);
	$sth->bindParam("description", $input['description']);
	$sth->bindParam("category_id", $input['category_id']);
	$sth->bindParam("expiration_date", $input['expiration_date']);
	$sth->bindParam("updated_date", date('Y-m-d H:i:s'));
	$sth->execute();
	//pictures
	$outputSql = "SELECT deals.deal_id, username, title, store, description, category, expiration_date, deals.updated_date, path_to_file
		      FROM deals, users, categories, stores, pictures
		      WHERE deals.user_id = users.user_id
		      AND deals.category_id = category_id
		      AND deals.store_id = stores,store_id
		      AND deals.deal_id = pictures.deal_id
		      ORDER BY deals.updated_date DESC
		      LIMIT 1";
	$output = $this->db->prepare($outputSql);
	$output->execute();
	$return = $output->fetchObject();
	return $this->response->withJson($input);
    });


// Add New Deal
    $app->post('/api/newdeal', function ($request, $response, $args) {
	$input = $request->getParsedBody();
	$sql = "INSERT INTO deals
		SET title = :title,
		    store_id = :store_id,
		    description = :description,
		    category_id = :category_id,
		    expiration_date = :expiration_date,
		    posted_date = :posted_date,
		    updated_date = :updated_date"; //pictures?
	$sth = $this->db->prepare($sql);
	$sth->bindParam("title", $input['title']);
	$sth->bindParam("store_id", $input['store_id']);
	$sth->bindParam("description", $input['description']);
	$sth->bindParam("category_id", $input['category_id']);
	$sth->bindParam("expiration_date", $input['expiration_date']);
	$currentDateTime = date('Y-m-d H:i:s');
	$sth->bindParam("posted_date", $currentDateTime);
	$sth->bindParam("updated_date", $currentDateTime); //pictures?
	$sth->execute();
	//$input['deal_id'] = $this->db->lastInsertId();
	$outputSql = "SELECT deal_id, username, title, store, description, category, expiration_date, posted_date, deals.updated_date, path_to_file
		      FROM deals, users, categories, stores, pictures
		      WHERE deals.user_id = users.user_id
		      AND deals.category_id = categories.category_id
		      AND deals.store_id = stores.store_id
		      AND deals.deal_id = pictures.deal_id
		      ORDER BY posted_date DESC
		      LIMIT 1";
	$output = $this->db->prepare($outputSql);
	$output->execute();
	$return = $output->fetchObject();
        return $this->response->withJson($return);
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



