<?php

namespace App\Application\Actions;

use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;

class AuthActions
{
    private PDO $pdo;
    private Translator $translator;

    public function __construct(PDO $pdo, Translator $translator)
    {
        $this->pdo = $pdo;
        $this->translator = $translator;
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $error = null;
        $role = $_SESSION['user']['role'] ?? 'customer';

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = $user;

            if ($user['role'] === 'admin') {
                return $response->withHeader('Location', '/admin/dashboard')->withStatus(302);
            } else {
                return $response->withHeader('Location', '/')->withStatus(302);
            }
        }
        $error = $this->translator->trans('auth.error.invalid_credentials');
        $view = Twig::fromRequest($request);
        return $view->render($response, 'login.twig', ['error' => $error]);
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $firstName = trim($data['firstName'] ?? '');
        $lastName = trim($data['lastName'] ?? '');
        $phoneNumber = trim($data['phoneNumber'] ?? '');
        $error = null;

        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = $this->translator->trans('auth.error.invalid_email');
        } elseif (!preg_match($pattern, $password)) {
            $error = $this->translator->trans('auth.error.password_strength');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $this->pdo->prepare('INSERT INTO users (email, password, firstName, lastName, phoneNumber) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$email, $hash, $firstName, $lastName, $phoneNumber]);
                $userId = $this->pdo->lastInsertId();
                $_SESSION['user'] = [
                'id' => $userId,
                'email' => $email,
                'role' => 'customer'
                ];
                return $response->withHeader('Location', '/')->withStatus(302);
            } catch (PDOException $e) {
                $error = $this->translator->trans('auth.error.email_taken');
            }
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, 'register.twig', ['error' => $error]);
    }

    public function logout(Request $request, Response $response): Response
    {
        $_SESSION = [];
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        session_destroy();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
