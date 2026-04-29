<?php

declare(strict_types= 1);

namespace App\Application\Actions\Admin;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

Class ServiceActions
{
    private PDO $pdo;

    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function getServices(Request $request, Response $response): Response
    {
        $stmt = $this->pdo->prepare('SELECT * FROM services ORDER BY name ASC');
        $stmt->execute();
        $services = $stmt->fetchAll();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/services.twig', ['services' => $services]);
    }

    public function addService(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? 0;
        $categoryId = $data['category_id'] ?? '';

        $stmt = $this->pdo->prepare('INSERT INTO services (name,description,price,category_id) VALUES (?,?,?,?)');
        $stmt->execute([$name, $description, $price, $categoryId]);

        return $response->withHeader('Location','/admin/services')->withStatus(302);
    }

    public function editService(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? 0;
        $categoryId = $data['category_id'] ?? '';     
        
        $stmt = $this->pdo->prepare('UPDATE services SET name = ?, description = ?, price = ?, category_id = ? WHERE id = ?');
        $stmt->execute([$name, $description, $price, $categoryId, $id]);
        return $response->withHeader('Location','/admin/services')->withStatus(302);
    }
    public function deleteService(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $stmt = $this->pdo->prepare('DELETE FROM services WHERE id = ?');
        $stmt->execute([$id]);   
        return $response->withHeader('Location','/admin/services')->withStatus(302);
    }

    public function addServiceForm(Request $request, Response $response): Response
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories');
        $stmt->execute();
        $categories = $stmt->fetchAll();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/add-service.twig', ['categories' => $categories]);
    }
    public function editServiceForm(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $stmt = $this->pdo->prepare('SELECT * from services WHERE id = ?');
        $stmt->execute([$id]);   
        $service = $stmt->fetch();
        
        $stmt2 = $this->pdo->prepare('SELECT * FROM categories');
        $stmt2->execute();
        $categories = $stmt2->fetchAll();
    
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/edit-service.twig', ['service' => $service, 'categories' => $categories]);
    }

}