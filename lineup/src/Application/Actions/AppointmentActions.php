<?php
namespace App\Application\Actions;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

Class AppointmentActions{
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function bookAppointment(Request $request, Response $response) : Response {
        $data = $request->getParsedBody();
        $userId = $_SESSION['user']['id'];
        $serviceId = $data['service_id'] ?? '';
        $date = $data['date'] ?? '';    
        $time = $data['time'] ?? '';
        $stmt = $this->pdo->prepare('INSERT INTO appointments (user_id, service_id, date, time, status) VALUES (?,?,?,?,?)');
        $stmt->execute([$userId, $serviceId, $date, $time, 'pending']);

        return $response->withHeader('Location','/appointments')->withStatus(302);
    }

    public function showBookingForm(Request $request, Response $response) : Response {
    $serviceId = $request->getQueryParams()['service_id'] ?? '';
    $selectedDate = $request->getQueryParams()['date'] ?? date('Y-m-d');

    $allSlots = ['13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00', '18:00:00', '19:00:00', '20:00:00'];
    $stmt = $this->pdo->prepare("SELECT time FROM appointments WHERE date = ?");
    $stmt->execute([$selectedDate]);
    $bookedTimes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $availableSlots = array_diff($allSlots, $bookedTimes);


    $view = Twig::fromRequest($request);
    return $view->render($response, 'book.twig', ['service_id'=> $serviceId, 'slots'=> $availableSlots, 'date'=> $selectedDate]);
    }
}