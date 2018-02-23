<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/', function (Request $request, Response $response, array $args) {
    return $response->withJson([
        'status'  => 'success',
        'message' => null,
        'data'    => [
            'routes' => [
                '/doggos',
                '/doggos/{id}',
                '/addresses',
                '/addresses/{id}',
                // '/addresses/{id}/doggos',
            ],
        ],
    ]);
});

// Routes
$app->get('/doggos', function (Request $request, Response $response, array $args) {
    $db     = $this->db->query('SELECT * FROM doggos');
    $doggos = [];
    foreach ($db->fetchAll() as $doggo) {
        $doggos[] = [
            'id'          => (int)$doggo['id'],
            'name'        => (string)$doggo['name'],
            'temperament' => (string)$doggo['temperament'],
        ];
    }

    return $response->withJson([
        'status'  => 'success',
        'message' => null,
        'data'    => $doggos,
    ]);
});

$app->get('/doggos/{id}', function (Request $request, Response $response, array $args) {
    $db    = $this->db->query('SELECT * FROM doggos WHERE id = ' . $args['id']);
    $doggo = $db->fetch();

    if (empty($doggo)) {
        return $response->withStatus(404)
            ->withJson([
                'status'  => 'fail',
                'message' => 'No doggo found. Much sad.',
                'data'    => null,
            ]);
    }

    return $response->withJson([
        'status'  => 'success',
        'message' => null,
        'data'    => [
            'id'          => (int)$doggo['id'],
            'name'        => (string)$doggo['name'],
            'temperament' => (string)$doggo['temperament'],
        ],
    ]);
});

$app->get('/doggos/{id}/address', function (Request $request, Response $response, array $args) {
    $db    = $this->db->query('SELECT * FROM doggos WHERE id = ' . $args['id']);
    $doggo = $db->fetch();

    if (empty($doggo)) {
        return $response->withStatus(404)
            ->withJson([
                'status'  => 'fail',
                'message' => 'No doggo found. Much sad.',
                'data'    => null,
            ]);
    }

    $db             = $this->db->query('SELECT * FROM address_doggo 
      LEFT JOIN addresses 
      ON addresses.id = address_doggo.address_id 
      WHERE address_doggo.doggo_id = ' . $args['id']);
    $addressResults = $db->fetch();
    $address        = [
        'id'      => (int)$addressResults['id'],
        'address' => $addressResults['address'],
    ];

    return $response->withJson([
        'status'  => 'success',
        'message' => null,
        'data'    => [
            'id'          => (int)$doggo['id'],
            'name'        => (string)$doggo['name'],
            'temperament' => (string)$doggo['temperament'],
            'address'     => $address,
        ],
    ]);
});

$app->get('/addresses', function (Request $request, Response $response, array $args) {
    $db      = $this->db->query('SELECT * FROM addresses');
    $address = [];
    foreach ($db->fetchAll() as $row) {
        $address[] = [
            'id'      => (int)$row['id'],
            'address' => (string)$row['address'],
        ];
    }

    return $response->withJson([
        'status'  => 'success',
        'message' => null,
        'data'    => $address,
    ]);
});

$app->post('/addresses', function (Request $request, Response $response, array $args) {
    $parsedBody = $request->getParsedBody();
    if (empty($parsedBody['address'])) {
        return $response->withStatus(400)
            ->withJson([
                'status'  => 'fail',
                'message' => 'Address is required.',
                'data'    => null,
            ]);
    }

    $db = $this->db->prepare('INSERT INTO addresses (address) VALUES (:address)');
    $db->bindParam(':address', $parsedBody['address']);
    $db->execute();

    return $response->withStatus(201)
        ->withJson([
            'status'  => 'success',
            'message' => null,
            'data'    => null,
        ]);
});

$app->get('/addresses/{id}', function (Request $request, Response $response, array $args) {
    $db      = $this->db->query('SELECT * FROM addresses WHERE id = ' . $args['id']);
    $address = $db->fetch();

    if (empty($address)) {
        return $response->withStatus(404)
            ->withJson([
                'status'  => 'fail',
                'message' => 'No address found.',
                'data'    => null,
            ]);
    }

    return $response->withJson([
        'status'  => 'success',
        'message' => null,
        'data'    => [
            'id'      => (int)$address['id'],
            'address' => (string)$address['address'],
        ],
    ]);
});

$app->delete('/addresses/{id}', function (Request $request, Response $response, array $args) {
    $db      = $this->db->query('SELECT * FROM addresses WHERE id = ' . $args['id']);
    $address = $db->fetch();

    if (empty($address)) {
        return $response->withStatus(404)
            ->withJson([
                'status'  => 'fail',
                'message' => 'No address found.',
                'data'    => null,
            ]);
    }

    $db = $this->db->prepare('DELETE FROM addresses WHERE id = :id');
    $db->bindParam(':id', $address['id']);
    $db->execute();

    $db2 = $this->db->prepare('DELETE FROM address_doggo WHERE address_id = :aid');
    $db2->bindParam(':aid', $address['id']);
    $db2->execute();

    return $response->withStatus(200)->withJson([
        'status'  => 'success',
        'message' => null,
        'data'    => null,
    ]);
});

$app->get('/addresses/{id}/doggos', function (Request $request, Response $response, array $args) {
    $db      = $this->db->query('SELECT * FROM addresses WHERE id = ' . $args['id']);
    $address = $db->fetch();

    if (empty($address)) {
        return $response->withStatus(404)
            ->withJson([
                'status'  => 'fail',
                'message' => 'No address found.',
                'data'    => null,
            ]);
    }

    $db           = $this->db->query('SELECT * FROM address_doggo 
      LEFT JOIN doggos 
      ON doggos.id = address_doggo.doggo_id 
      WHERE address_doggo.address_id = ' . $args['id']);
    $doggoResults = $db->fetchAll();
    $doggos       = [];
    foreach ($doggoResults as $row) {
        $doggos[] = [
            'id'          => (int)$row['id'],
            'name'        => $row['name'],
            'temperament' => $row['temperament'],
        ];
    }

    return $response->withJson([
        'status'  => 'success',
        'message' => null,
        'data'    => [
            'id'      => (int)$address['id'],
            'address' => (string)$address['address'],
            'doggos'  => $doggos,
        ],
    ]);
});

$app->post('/addresses/{id}/doggos', function (Request $request, Response $response, array $args) {
    $db      = $this->db->query('SELECT * FROM addresses WHERE id = ' . $args['id']);
    $address = $db->fetch();

    if (empty($address)) {
        return $response->withStatus(404)
            ->withJson([
                'status'  => 'fail',
                'message' => 'No address found.',
                'data'    => null,
            ]);
    }

    $parsedBody = $request->getParsedBody();
    if (empty($parsedBody['name']) || empty($parsedBody['temperament'])) {
        return $response->withStatus(400)
            ->withJson([
                'status'  => 'fail',
                'message' => 'Name and temperament are required',
                'data'    => null,
            ]);
    }

    $db = $this->db->prepare('INSERT INTO doggos (name, temperament) VALUES (:name, :temperament)');
    $db->bindParam(':name', $parsedBody['name']);
    $db->bindParam(':temperament', $parsedBody['temperament']);
    $db->execute();
    $doggoId = $this->db->lastInsertId();

    $db2 = $this->db->prepare('INSERT INTO address_doggo (address_id, doggo_id) VALUES (:aid, :did)');
    $db2->bindParam(':aid', $args['id']);
    $db2->bindParam(':did', $doggoId);
    $db2->execute();

    return $response->withStatus(201)
        ->withJson([
            'status'  => 'success',
            'message' => null,
            'data'    => null,
        ]);


    $db           = $this->db->query('SELECT * FROM address_doggo 
      LEFT JOIN doggos 
      ON doggos.id = address_doggo.doggo_id 
      WHERE address_doggo.address_id = ' . $args['id']);
    $doggoResults = $db->fetchAll();
    $doggos       = [];
    foreach ($doggoResults as $row) {
        $doggos[] = [
            'id'          => (int)$row['id'],
            'name'        => $row['name'],
            'temperament' => $row['temperament'],
        ];
    }

    return $response->withJson([
        'status'  => 'success',
        'message' => null,
        'data'    => [
            'id'      => (int)$address['id'],
            'address' => (string)$address['address'],
            'doggos'  => $doggos,
        ],
    ]);
});
