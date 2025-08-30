<?php
session_start();

if (isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);

    if (!empty($username)) {
        $_SESSION['username'] = $username;

        $planets = ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune'];
        $randomPlanet = $planets[array_rand($planets)];

        $basePath = __DIR__ . '/systems/solar';
        $planetPath = $basePath . '/' . $randomPlanet;

        if (!is_dir($planetPath)) {
            mkdir($planetPath, 0777, true);
        }

        $playerFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $username) . '.json';
        $playerFilePath = $planetPath . '/' . $playerFilename;

        $playerData = ['username' => $username];
        file_put_contents($playerFilePath, json_encode($playerData));

        header('Location: index.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #1a1a2e; color: #fff; }
        form { display: flex; flex-direction: column; gap: 10px; padding: 20px; background-color: #16213e; border-radius: 8px; }
        input { padding: 10px; border-radius: 4px; border: 1px solid #0f3460; }
        button { padding: 10px; background-color: #e94560; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <form method="POST">
        <label for="username">Choose a login:</label>
        <input type="text" id="username" name="username" required>
        <button type="submit">Start</button>
    </form>
</body>
</html>
