<?php

declare(strict_types= 1);

namespace App\Application\Actions\Admin;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

Class BarberActions
{
    private PDO $pdo;

    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function getBarbers(Request $request, Response $response): Response
    {
        $stmt = $this->pdo->prepare('SELECT * FROM barbers ORDER BY lastName ASC');
        $stmt->execute();
        $barbers = $stmt->fetchAll();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/dashboard.twig', ['barbers' => $barbers]);
    }
    public function addBarber(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $firstName = $data['firstName'] ?? '';
        $lastName = $data['lastName'] ?? '';
        $bio = $data['bio'] ?? '';
        $photo = $data['photo'] ?? '';

        $stmt = $this->pdo->prepare('INSERT INTO barbers (firstName,lastName,bio,photo) VALUES (?,?,?,?)');
        $stmt->execute([$firstName, $lastName, $bio, $photo]);

        return $response->withHeader('Location','/admin/dashboard')->withStatus(302);
    }
    public function editBarber(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $firstName = $data['firstName'] ?? '';
        $lastName = $data['lastName'] ?? '';
        $bio = $data['bio'] ?? '';
        $photo = $data['photo'] ?? '';     
        
        $stmt = $this->pdo->prepare('UPDATE barbers SET firstName = ?, lastName = ?, bio = ?, photo = ? WHERE id = ?');
        $stmt->execute([$firstName, $lastName, $bio, $photo, $id]);
        return $response->withHeader('Location','/admin/dashboard')->withStatus(302);
    }
    public function deleteBarber(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $stmt = $this->pdo->prepare('DELETE FROM barbers WHERE id = ?');
        $stmt->execute([$id]);   
        return $response->withHeader('Location','/admin/dashboard')->withStatus(302);
    }

    public function addBarberForm(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/add-barber.twig', ['barbers' => $barbers]);
    }
    
    public function editBarberForm(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $stmt = $this->pdo->prepare('SELECT * FROM barbers WHERE id = ?');
        $stmt->execute([$id]);
        $barber = $stmt->fetch();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/edit-barber.twig', ['barber' => $barber]);
    }

    
}