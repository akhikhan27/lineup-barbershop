<?php

declare(strict_types= 1);

namespace App\Application\Actions\Admin;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

Class AdminAppointmentActions
{
    private PDO $pdo;

    public function __construct(PDO $pdo){ $this->pdo = $pdo; }
    
    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $status = $data['status'] ?? '';

        $stmt = $this->pdo->prepare('UPDATE appointments SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        return $response->withHeader('Location','/admin/dashboard')->withStatus(302);
    }




}
