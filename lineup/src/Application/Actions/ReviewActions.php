<?php

namespace App\Application\Actions;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use PDOException;
use Symfony\Component\Translation\Translator;

class ReviewActions
{
    private PDO $pdo;
    private Translator $translator;
    public function __construct(PDO $pdo, Translator $translator)
    {
        $this->pdo = $pdo;
        $this->translator = $translator;
    }

    public function showReviewForm(Request $request, Response $response, array $args): Response
    {
        $appointmentId = $args['id'];
        $view = Twig::fromRequest($request);
        return $view->render($response, 'reviews.twig', ['appointment_id' => $appointmentId]);
    }

    public function submitReview(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $appointmentId = $args['id'];
        $userId = $_SESSION['user']['id'];
        $comment = $data['comment'] ?? '';
        $rating = $data['rating'] ?? '';

        $stmt = $this->pdo->prepare('INSERT INTO reviews (user_id, comment, rating, appointment_id) VALUES (?,?,?,?)');
        try {
            $stmt->execute([$userId, $comment, $rating, $appointmentId]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => $this->translator->trans('flash.success.review_created')];
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $this->translator->trans('flash.error.review_create_failed')];
        }
        return $response->withHeader('Location', '/appointments')->withStatus(302);
    }
}
