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
        $sth = $this->db->prepare("SELECT deal_id, username, title, store, description, category, expiration_date, posted_date, updated_date, picture_name
                                   FROM deals, users, categories, stores, pictures
                                   WHERE deals.user_id = users.user_id
                                   AND deals.category_id = categories.category_id
                                   AND deals.store_id = stores.store_id
                                   AND deals.picture_id = pictures.picture_id
                                   ORDER BY deals.updated_date DESC"); //?
        $sth->execute();
        $deals = $sth->fetchAll();
        return $this->response->withJson($deals);
    });


// Get Specific Deal
    $app->get('/api/deal/[{deal_id}]', function ($request, $response, $args) {
        $sth = $this->db->prepare("SELECT deal_id, username, title, store, description, category, expiration_date, posted_date, updated_date, picture_name
                                   FROM deals, users, categories, stores, pictures
                                   WHERE deals.user_id = users.user_id
                                   AND deals.category_id = categories.category_id
                                   AND deals.store_id = stores.store_id
                                   AND deals.picture_id = pictures.picture_id
                                   AND deal_id = :deal_id");
        $sth->bindParam("deal_id", $args['deal_id']);
        $sth->execute();
        $deals = $sth->fetchObject();
        return $this->response->withJson($deals);
    });


// Edit a Deal
    $app->put('/api/deal/[{deal_id}]', function ($request, $response, $args) {
        $getDeal = "SELECT deal_id, username, title, store, description, category, expiration_date, posted_date, updated_date, picture_name
                    FROM deals, users, categories, stores, pictures
                    WHERE deals.user_id = users.user_id
                    AND deals.category_id = categories.category_id
                    AND deals.store_id = stores.store_id
                    AND deals.picture_id = pictures.picture_id
                    AND deal_id = :deal_id";

        $sth = $this->db->prepare($getDeal);
        $sth->bindParam("deal_id", $args['deal_id']);
        $sth->execute();
        $deal = $sth->fetchObject();

        $picture_id = null;
        $currentDateTime = date('Y-m-d H:i:s');

        if ('image' != null) {
                $storage = new \Upload\Storage\FileSystem('./pictures');
                $file = new \Upload\File('image', $storage);

                // Optionally you can rename the file on upload
                $new_filename = uniqid();
                $file->setName($new_filename);

                // Validate file upload
                $file->addValidations(array(
                        // Ensure file is of type "image/png" or "image/jpeg"
                        new \Upload\Validation\Mimetype('image/png', 'image/jpeg')),

                        // Ensure file is no larger than 5M (use "B", "K", M", or "G")
                        new \Upload\Validation\Size('5M')
                ));

                // Access data about the file that has been uploaded
                $data = array(
                        'name'       => $file->getNameWithExtension(),
                        'extension'  => $file->getExtension(),
                        'mime'       => $file->getMimetype(),
                        'size'       => $file->getSize(),
                        'md5'        => $file->getMd5(),
                        'dimensions' => $file->getDimensions()
                );

                // Try to upload file
                try {
                        // Success!
                        $file->upload();
                } catch (\Exception $e) {
                        // Fail!
                        $errors = $file->getErrors();
                }

                $sql_pic = "INSERT INTO pictures
                            SET picture_name = :picture_name,
                                uploaded_date = :uploaded_date";
                $sth = $this->db->prepare($sql_pic);
                $sth->bindParam("picture_name", $data['name']);
                $sth->bindParam("uploaded_date", $currentDateTime);
                $sth->execute();

                $sth = $this->db->prepare("SELECT picture_id FROM pictures WHERE picture_name = :picture_name");
                $sth->bindParam("picture_name", $data['name']);
                $sth->execute();
                $picture_id = $sth->fetchObject()->picture_id;
        }

        $input = $request->getParsedBody();
        $sql_deal = "UPDATE deals
                     SET title = :title,
                         store_id = :store_id,
                         description = :description,
                         category_id = :category_id,
                         expiration_date = :expiration_date,
                         posted_date = :posted_date,
                         updated_date = :updated_date,
                         picture_id = :picture_id
                     WHERE deal_id = :deal_id";
        $sth = $this->db->prepare($sql_deal);
        $sth->bindValue("title", ($input['title'] == null ? $deal->title : $input['title']));
        $sth->bindValue("store_id", ($input['store_id'] == null ? $deal->store_id : $input['store_id']));
        $sth->bindValue("description", ($input['description'] == null ? $deal->description : $input['description']));
        $sth->bindValue("category_id", ($input['category_id'] == null ? $deal->category_id : $input['category_id']));
        $sth->bindValue("expiration_date", ($input['expiration_date'] == null ? $deal->expiration_date : $input['expiration_date']));
        $sth->bindParam("posted_date", $currentDateTime);
        $sth->bindParam("updated_date", $currentDateTime);
        $sth->bindValue("picture_id", ($input['picture_id'] == null ? $deal->picture_id : $picture_id);
        $sth->execute();

        return $this->response->withJson($input); //?
    });


// Add New Deal
    $app->post('/api/newdeal', function ($request, $response, $args) {
        $storage = new \Upload\Storage\FileSystem('./pictures');
        $file = new \Upload\File('image', $storage);

        // Optionally you can rename the file on upload
        $new_filename = uniqid();
        $file->setName($new_filename);

        // Validate file upload
        $file->addValidations(array(
                // Ensure file is of type "image/png" or "image/jpeg"
                new \Upload\Validation\Mimetype('image/png', 'image/jpeg')),

                // Ensure file is no larger than 5M (use "B", "K", M", or "G")
                new \Upload\Validation\Size('5M')
        ));

        // Access data about the file that has been uploaded
        $data = array(
                'name'       => $file->getNameWithExtension(),
                'extension'  => $file->getExtension(),
                'mime'       => $file->getMimetype(),
                'size'       => $file->getSize(),
                'md5'        => $file->getMd5(),
                'dimensions' => $file->getDimensions()
        );

        // Try to upload file
        try {
                // Success!
                $file->upload();
        } catch (\Exception $e) {
                // Fail!
                $errors = $file->getErrors();
        }

        $currentDateTime = date('Y-m-d H:i:s');

        $sql_pic = "INSERT INTO pictures
                    SET picture_name = :picture_name,
                        uploaded_date = :uploaded_date";
        $sth = $this->db->prepare($sql_pic);
        $sth->bindParam("picture_name", $data['name']);
        $sth->bindParam("uploaded_date", $currentDateTime);
        $sth->execute();

        $sth = $this->db->prepare("SELECT picture_id FROM pictures WHERE picture_name = :picture_name");
        $sth->bindParam("picture_name", $data['name']);
        $sth->execute();
        $picture_id = $sth->fetchObject()->picture_id;

        $input = $request->getParsedBody();
        $sql_deal = "INSERT INTO deals
                SET title = :title,
                    store_id = :store_id,
                    description = :description,
                    category_id = :category_id,
                    expiration_date = :expiration_date,
                    posted_date = :posted_date,
                    updated_date = :updated_date,
                    picture_id = :picture_id";
        $sth = $this->db->prepare($sql_deal);
        $sth->bindParam("title", $input['title']);
        $sth->bindParam("store_id", $input['store_id']);
        $sth->bindParam("description", $input['description']);
        $sth->bindParam("category_id", $input['category_id']);
        $sth->bindParam("expiration_date", $input['expiration_date']);
        $sth->bindParam("posted_date", $currentDateTime);
        $sth->bindParam("updated_date", $currentDateTime);
        $sth->bindParam("picture_id", $picture_id);
        $sth->execute();

        return $this->response->withJson($input); //?
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



