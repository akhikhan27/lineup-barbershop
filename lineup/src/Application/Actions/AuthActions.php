<?php
namespace App\Application\Actions;

use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
Class AuthActions{
  private PDO $pdo;
  public function __construct(PDO $pdo) { $this->pdo = $pdo; }
  public function login(Request $request, Response $response) : Response {
    $data = $request->getParsedBody();
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $error = null;

    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
      session_regenerate_id(true);
      $_SESSION['user'] = $user;

    
      return $response->withHeader('Location', '/')->withStatus(302);
    }
    $error = 'Invalid email or password.';
    $view = Twig::fromRequest($request);
    return $view->render($response, 'login.twig');

}

public function register(Request $request, Response $response) : Response {
  $data = $request->getParsedBody();
  $email = trim($data['email'] ?? '');
  $password = $data['password'] ?? '';
  $firstName = trim($data['firstName'] ??'');
  $lastName = trim($data['lastName'] ??'');
  $phoneNumber = trim($data['phoneNumber'] ??'');
  $error = null;

  $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/';
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $error = 'Invalid email address';
  } 
  elseif (!preg_match($pattern, $password)) {
    $error = 'Password must be at least 8 characters and include upper, lower,
      number, and symbol.';
    } 
    else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      try {
        $stmt = $this->pdo->prepare('INSERT INTO users (email, password, firstName, lastName, phoneNumber) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$email, $hash, $firstName, $lastName, $phoneNumber]);
        return $response->withHeader('Location', '/')->withStatus(302);
      } catch (PDOException $e) {
          $error = 'That email is already registered.';
      }
    }
    $view = Twig::fromRequest($request);
    return $view->render($response, 'register.twig');
}
public function registerAdmin(Request $request, Response $response) : Response {
  $data = $request->getParsedBody();
  $email = trim($data['email'] ?? '');
  $password = $data['password'] ?? '';
  $firstName = trim($data['firstName'] ??'');
  $lastName = trim($data['lastName'] ??'');
  $phoneNumber = trim($data['phoneNumber'] ??'');
  $role = 'admin';
  $error = null;

  $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/';
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $error = 'Invalid email address';
  } 
  elseif (!preg_match($pattern, $password)) {
    $error = 'Password must be at least 8 characters and include upper, lower,
      number, and symbol.';
    } 
    else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      try {
        $stmt = $this->pdo->prepare('INSERT INTO users (email, password, firstName, lastName, phoneNumber,role) VALUES (?, ?, ?, ?, ?,?)');
        $stmt->execute([$email, $hash, $firstName, $lastName, $phoneNumber, $role]);
        return $response->withHeader('Location', '/')->withStatus(302);
      } catch (PDOException $e) {
          $error = 'That email is already registered.';
      }
    }
    $view = Twig::fromRequest($request);
    return $view->render($response, 'register-admin.twig');
}

public function logout(Request $request, Response $response) : Response {
  $_SESSION = [];
  if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
  }
  session_destroy();
  return $response->withHeader('Location', '/login')->withStatus(302);
  }
}

