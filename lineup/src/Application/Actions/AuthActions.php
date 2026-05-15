<?php

namespace App\Application\Actions;

use App\Service\TwoFactorService;
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
    private TwoFactorService $tfa;

    public function __construct(PDO $pdo, Translator $translator, TwoFactorService $tfa)
    {
        $this->pdo = $pdo;
        $this->translator = $translator;
        $this->tfa = $tfa;
    }

    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            if (!empty($user['tfa_secret'])) {
                $_SESSION['2fa_user_id'] = $user['id'];
                $_SESSION['2fa_user_data'] = $user;
                return $response->withHeader('Location', '/auth/2fa/verify')->withStatus(302);
            }

            $_SESSION['user'] = $user;

            if ($user['role'] === 'admin') {
                return $response->withHeader('Location', '/admin/dashboard')->withStatus(302);
            } else {
                return $response->withHeader('Location', '/')->withStatus(302);
            }
        }
        $_SESSION['flash'] = ['type' => 'error', 'message' => $this->translator->trans('auth.error.invalid_credentials')];
        return $response->withHeader('Location', '/login')->withStatus(302);
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $firstName = trim($data['firstName'] ?? '');
        $lastName = trim($data['lastName'] ?? '');
        $phoneNumber = trim($data['phoneNumber'] ?? '');

        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $this->translator->trans('auth.error.invalid_email')];
            return $response->withHeader('Location', '/register')->withStatus(302);
        } elseif (!preg_match($pattern, $password)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $this->translator->trans('auth.error.password_strength')];
            return $response->withHeader('Location', '/register')->withStatus(302);
        }
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
            $_SESSION['flash'] = ['type' => 'error', 'message' => $this->translator->trans('auth.error.email_taken')];
            return $response->withHeader('Location', '/register')->withStatus(302);
        }
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

    public function showVerifyForm(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['2fa_user_id'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }
        $view = Twig::fromRequest($request);
        return $view->render($response, '2fa-verify.twig');
    }

    public function verify(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['2fa_user_id'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $data = $request->getParsedBody();
        $code = trim($data['code'] ?? '');
        $user = $_SESSION['2fa_user_data'];

        if ($this->tfa->verifyCode($user['tfa_secret'], $code)) {
            $_SESSION['user'] = $user;
            unset($_SESSION['2fa_user_id'], $_SESSION['2fa_user_data']);

            if ($user['role'] === 'admin') {
                return $response->withHeader('Location', '/admin/dashboard')->withStatus(302);
            } else {
                return $response->withHeader('Location', '/')->withStatus(302);
            }
        }

        $_SESSION['flash'] = ['type' => 'error', 'message' => $this->translator->trans('auth.2fa.invalid_code')];
        return $response->withHeader('Location', '/auth/2fa/verify')->withStatus(302);
    }

    public function showSetupForm(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $user = $_SESSION['user'];
        $hasTfa = !empty($user['tfa_secret']);
        $secret = $_SESSION['2fa_temp_secret'] ?? $this->tfa->generateSecret();
        $_SESSION['2fa_temp_secret'] = $secret;

        $qrCode = $this->tfa->getQrCodeUri($secret, $user['email']);

        $view = Twig::fromRequest($request);
        return $view->render($response, '2fa-setup.twig', [
            'qr_code' => $qrCode,
            'secret' => $secret,
            'has_tfa' => $hasTfa,
        ]);
    }

    public function setup(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $data = $request->getParsedBody();
        $code = trim($data['code'] ?? '');
        $secret = $_SESSION['2fa_temp_secret'] ?? '';

        if (!$secret) {
            return $response->withHeader('Location', '/auth/2fa/setup')->withStatus(302);
        }

        if ($this->tfa->verifyCode($secret, $code)) {
            $userId = $_SESSION['user']['id'];
            $stmt = $this->pdo->prepare('UPDATE users SET tfa_secret = ? WHERE id = ?');
            $stmt->execute([$secret, $userId]);

            $_SESSION['user']['tfa_secret'] = $secret;
            unset($_SESSION['2fa_temp_secret']);

            $_SESSION['flash'] = ['type' => 'success', 'message' => $this->translator->trans('auth.2fa.enabled')];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $this->translator->trans('auth.2fa.invalid_code')];
        }

        return $response->withHeader('Location', '/auth/2fa/setup')->withStatus(302);
    }

    public function disable(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $userId = $_SESSION['user']['id'];
        $stmt = $this->pdo->prepare('UPDATE users SET tfa_secret = NULL WHERE id = ?');
        $stmt->execute([$userId]);

        $_SESSION['user']['tfa_secret'] = null;
        unset($_SESSION['2fa_temp_secret']);

        $_SESSION['flash'] = ['type' => 'success', 'message' => $this->translator->trans('auth.2fa.disabled')];

        return $response->withHeader('Location', '/auth/2fa/setup')->withStatus(302);
    }
}
