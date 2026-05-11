<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Service\UploadService;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Symfony\Component\Translation\Translator;

class BarberActions
{
    private PDO $pdo;
    private UploadService $uploadService;
    private Translator $translator;

    public function __construct(PDO $pdo, UploadService $uploadService, Translator $translator)
    {
        $this->pdo = $pdo;
        $this->uploadService = $uploadService;
        $this->translator = $translator;
    }

    public function addBarber(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $firstName = $data['firstName'] ?? '';
        $lastName = $data['lastName'] ?? '';
        $bio = $data['bio'] ?? '';

        $files = $request->getUploadedFiles();

        try {
            $photo = isset($files['photo']) ? $this->uploadService->upload($files['photo']) : null;
            $stmt = $this->pdo->prepare('INSERT INTO barbers (firstName,lastName,bio,photo) VALUES (?,?,?,?)');
            $stmt->execute([$firstName, $lastName, $bio, $photo]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => $this->translator->trans('flash.success.barber_added')];
        } catch (\Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $this->translator->trans('flash.error.barber_add_failed')];
        }

        return $response->withHeader('Location', '/admin/dashboard')->withStatus(302);
    }
    public function editBarber(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $firstName = $data['firstName'] ?? '';
        $lastName = $data['lastName'] ?? '';
        $bio = $data['bio'] ?? '';

        $files = $request->getUploadedFiles();

        try {
            $stmt = $this->pdo->prepare('SELECT photo FROM barbers WHERE id = ?');
            $stmt->execute([$id]);
            $oldPhoto = $stmt->fetchColumn();

            $photo = isset($files['photo']) ? $this->uploadService->upload($files['photo'], $oldPhoto ?: null) : ($oldPhoto ?: null);

            $stmt = $this->pdo->prepare('UPDATE barbers SET firstName = ?, lastName = ?, bio = ?, photo = ? WHERE id = ?');
            $stmt->execute([$firstName, $lastName, $bio, $photo, $id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => $this->translator->trans('flash.success.barber_updated')];
        } catch (\Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $this->translator->trans('flash.error.barber_update_failed')];
        }
        return $response->withHeader('Location', '/admin/dashboard')->withStatus(302);
    }
    public function deleteBarber(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        try {
            $stmt = $this->pdo->prepare('DELETE FROM barbers WHERE id = ?');
            $stmt->execute([$id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => $this->translator->trans('flash.success.barber_deleted')];
        } catch (\Exception $e) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => $this->translator->trans('flash.error.barber_delete_failed')];
        }
        return $response->withHeader('Location', '/admin/dashboard')->withStatus(302);
    }

    public function addBarberForm(Request $request, Response $response): Response
    {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/add-barber.twig');
    }

    public function editBarberForm(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $stmt = $this->pdo->prepare('SELECT * FROM barbers WHERE id = ?');
        $stmt->execute([$id]);
        $barber = $stmt->fetch();
        $view = Twig::fromRequest($request);
        return $view->render($response, 'admin/edit-barbers.twig', ['barber' => $barber]);
    }
}
