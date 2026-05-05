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

    public function addAppointment(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $userId = $data['user_id'] ?? '';
        $serviceId = $data['service_id'] ?? '';
        $date = $data['date'] ?? '';
        $time = $data['time'] ?? '';
    
        $stmt = $this->pdo->prepare('INSERT INTO appointments (user_id, service_id, date, time, status) VALUES (?,?,?,?,?)');
        $stmt->execute([$userId, $serviceId, $date, $time, 'pending']);
        return $response->withHeader('Location','/admin/dashboard')->withStatus(302);
    }

    public function editAppointment(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $userId = $data['user_id'] ?? '';
        $serviceId = $data['service_id'] ?? '';
        $date = $data['date'] ?? '';
        $time = $data['time'] ?? '';
        $status = $data['status'] ?? '';     
        
        $stmt = $this->pdo->prepare('UPDATE appointments SET user_id = ?, service_id = ?, date = ?, time = ?, status = ? WHERE id = ?');
        $stmt->execute([$userId, $serviceId, $date, $time, $status, $id]);
        return $response->withHeader('Location','/admin/dashboard')->withStatus(302);
    }

    public function deleteAppointment(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $stmt = $this->pdo->prepare('DELETE FROM appointments WHERE id = ?');
        $stmt->execute([$id]);   
        return $response->withHeader('Location','/admin/dashboard')->withStatus(302);
    }
    
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
