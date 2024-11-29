<?php

// Обработка JSON-запросов
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
    $_POST = $input ?? [];
}

// Проверка наличия параметра action в POST или GET запросе
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    echo json_encode(['error' => 'Action parameter is missing.']);
    exit;
}

$usersFile = __DIR__ . '/users.json'; // Путь к файлу users.json

if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([])); // Создаем пустой файл, если он не существует
}

$users = json_decode(file_get_contents($usersFile), true);

switch ($action) {
    case 'register':
        $fullName = $_POST['fullName'] ?? '';
        $email = $_POST['email'] ?? '';
        $dateOfBirth = $_POST['dateOfBirth'] ?? '';

        if (!$fullName || !$email || !$dateOfBirth) {
            echo json_encode(['error' => 'All fields are required.']);
            exit;
        }

        // Проверка на существующего пользователя с таким же email
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                echo json_encode(['error' => 'User with this email already exists.']);
                exit;
            }
        }

        $users[] = ['fullName' => $fullName, 'email' => $email, 'dateOfBirth' => $dateOfBirth];
        file_put_contents($usersFile, json_encode($users));

        echo json_encode(['message' => 'User registered successfully.']);
        break;

    case 'list':
        echo json_encode($users);
        break;

    case 'modify':
        $oldEmail = $_POST['oldEmail'] ?? '';
        $fullName = $_POST['fullName'] ?? '';
        $email = $_POST['email'] ?? '';
        $dateOfBirth = $_POST['dateOfBirth'] ?? '';

        if (!$oldEmail || !$email || !$fullName || !$dateOfBirth) {
            echo json_encode(['error' => 'All fields are required.']);
            exit;
        }

        foreach ($users as &$user) {
            if ($user['email'] === $oldEmail) {
                $user['fullName'] = $fullName;
                $user['email'] = $email;
                $user['dateOfBirth'] = $dateOfBirth;
                file_put_contents($usersFile, json_encode($users));
                echo json_encode(['message' => 'User modified successfully.']);
                exit;
            }
        }

        echo json_encode(['error' => 'User not found.']);
        break;

    case 'check':
        $email = $_GET['email'] ?? '';
        if (!$email) {
            echo json_encode(['error' => 'Email is required.']);
            exit;
        }

        foreach ($users as $user) {
            if ($user['email'] === $email) {
                echo json_encode(['message' => 'User is registered.', 'user' => $user]);
                exit;
            }
        }

        echo json_encode(['error' => 'User not found.']);
        break;

    default:
        echo json_encode(['error' => 'Invalid action.']);
        break;
}
