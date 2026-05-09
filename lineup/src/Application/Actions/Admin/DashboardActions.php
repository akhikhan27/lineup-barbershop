<?php

declare(strict_types= 1);

namespace App\Application\Actions\Admin;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

Class DashboardActions{
    public function getDashboard(Request $request, Response $response): Response
    {
        $stmt = $this->pdo->prepare('SELECT * FROM barbers ORDER BY lastName ASC');
        $stmt->execute();
        $barbers = $stmt->fetchAll();
        
        $stmt2 = $this->pdo->prepare('SELECT * FROM appointments ORDER BY date ASC, time ASC');
        $stmt2->execute();
        $appointments = $stmt2->fetchAll();

        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/dashboard.twig', ['barbers' => $barbers, 'appointments' => $appointments]);
    }
}
  