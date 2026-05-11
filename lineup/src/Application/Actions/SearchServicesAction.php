<?php

namespace App\Application\Actions;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SearchServicesAction
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $query = trim($params['q'] ?? '');

        if ($query === '') {
            $stmt = $this->pdo->query('SELECT * FROM services');
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM services WHERE name LIKE ? OR description LIKE ?'
            );
            $like = '%' . $query . '%';
            $stmt->execute([$like, $like]);
        }

        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $json = json_encode($services);

        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
