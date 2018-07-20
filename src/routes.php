<?php

use Slim\Http\Request;
use Slim\Http\Response;

function convert_from_latin1_to_utf8_recursively($dat) {
    if (is_string($dat)) {
        return utf8_encode($dat);
    } elseif (is_array($dat)) {
        $ret = [];
        foreach ($dat as $i => $d) $ret[ $i ] = convert_from_latin1_to_utf8_recursively($d);

        return $ret;
    } elseif (is_object($dat)) {
        foreach ($dat as $i => $d) $dat->$i = convert_from_latin1_to_utf8_recursively($d);

        return $dat;
    } else {
        return $dat;
    }
}

function errorResponse(Response $response, string $message, int $status) {
    return $response->withStatus($status)->withJson([
        'message' => $message
    ]);
}

// Routes
$app->post('/parts', function (Request $request, Response $response, array $args) {
    $params = $request->getParsedBody();
    $query = $request->getQueryParams();

    if (!isSet($params['sequence'])) {
        return errorResponse($response, '`sequence` post parameter is missing', 400);
    } else {
        $params['sequence'] = '%' . $params['sequence'] . '%';
    }

    $sql = "SELECT part_id, part_name, short_desc, `description`, part_type, `status`, part_status, sample_status, uses, doc_size, works, sequence_length, notes, source FROM `parts` WHERE `sequence` LIKE :sequence";

    if (isSet($query['available']) and $query['available']) {
        $sql .= " AND `status` = 'Available'";
    }

    $page = 0;
    if (isSet($query['page'])) {
        $page = $query['page'];
    }
    $skip = $page * 50;

    $stmt = $this->database->prepare($sql);
    $stmt->execute($params);
    $parts = $stmt->fetchAll();
    return $response->withJson([
        'data' => convert_from_latin1_to_utf8_recursively(array_slice($parts, $skip, 50)),
        'rows' => count($parts),
    ]);
});

// $app->get('/[{name}]', function (Request $request, Response $response, array $args) {
//     // Sample log message
//     $this->logger->info("Slim-Skeleton '/' route");
//     var_dump($this->database);

//     // Render index view
//     return $this->renderer->render($response, 'index.phtml', $args);
// });
