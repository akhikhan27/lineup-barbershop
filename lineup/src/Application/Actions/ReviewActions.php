<?php
namespace App\Application\Actions;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

Class ReviewActions{
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function showReviewForm (Request $request, Response $response, array $args) : Response {
        $appointmentId = $args['id'];
        $view = Twig::fromRequest($request);
        return $view->render($response, 'reviews.twig', ['appointment_id' => $appointmentId]);
    }

    public function submitReview (Request $request, Response $response, array $args) : Response {
        $data = $request->getParsedBody();
        $appointmentId = $args['id'];
        $userId = $_SESSION['user']['id'];
        $comment = $data['comment'] ?? '';
        $rating = $data['rating'] ?? '';

        $stmt = $this->pdo->prepare('INSERT INTO reviews (user_id, comment, rating, appointment_id) VALUES (?,?,?,?)');
        $stmt->execute([$userId,$comment,$rating,$appointmentId]);

        return $response-withHeader('Location', '/appointments')->withStatus(302);

    }
}