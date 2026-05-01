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

    public function getAppointments(Request $request, Response $response): Response
    {
        $stmt = $this->pdo->prepare('SELECT * FROM appointments ORDER BY date ASC, time ASC');
        $stmt->execute();
        $appointments = $stmt->fetchAll();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/dashboard.twig', ['appointments' => $appointments]);
    }
    public function deleteService(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $stmt = $this->pdo->prepare('DELETE FROM appointments WHERE id = ?');
        $stmt->execute([$id]);   
        return $response->withHeader('Location','/admin/dashboard')->withStatus(302);
    }



}
