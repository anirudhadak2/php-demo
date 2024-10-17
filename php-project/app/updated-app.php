<?php
session_start();

// Database configuration from environment variables
$host = getenv('DB_HOST') ?: 'mysql-db';
$dbname = getenv('DB_NAME') ?: 'users_db';
$username_db = getenv('DB_USER') ?: 'root';
$password_db = getenv('DB_PASSWORD') ?: 'rootpassword';

// Connect to MySQL database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the users table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Ensure master folder exists
if (!is_dir('master')) {
    mkdir('master', 0777, true);
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        echo "<p>Username already taken. Try another.</p>";
    } else {
        // Insert new user into the database
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);

        // Create user folder inside /master
        $user_folder = "master/$username";
        mkdir($user_folder);

        // Copy sample script into user's folder
        copy("scripts/sample_script.php", "$user_folder/sample_script.php");

        echo "<p>User $username registered successfully. Please log in.</p>";
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user from database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $username;
        header("Location: /user_home");
        exit();
    } else {
        echo "<p>Invalid credentials. Please try again.</p>";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /login");
    exit();
}

// Display user home
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $user_folder = "master/$username";

    // Initialize scripts array to prevent undefined variable issues
    $scripts = [];

    // List PHP scripts in the user's folder
    if (is_dir($user_folder)) {
        $scripts = array_filter(scandir($user_folder), function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'php';
        });
    }

    // Check if any scripts exist in the user's folder
    if (!empty($scripts)) {
        echo "<h1>Welcome, $username!</h1>";
        echo "<h2>Your scripts:</h2>";
        foreach ($scripts as $script) {
            echo "<form method='POST'>
                    <input type='hidden' name='script_name' value='$script'>
                    <button type='submit' name='run_script'>Run $script</button>
                  </form><br>";
        }
    } else {
        echo "<p>No scripts found in your folder.</p>";
    }

    // Handle script execution
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['run_script'])) {
        $script_name = $_POST['script_name'];
        $script_path = "$user_folder/$script_name";

        if (file_exists($script_path)) {
            ob_start();
            include($script_path);
            $output = ob_get_clean();
            echo "<pre>$output</pre>";
        } else {
            echo "<p>Script not found or access denied.</p>";
        }
    }

    echo "<a href='?logout'>Logout</a>";
    exit();
}

// Display registration form
if (!isset($_SESSION['username']) && $_SERVER['REQUEST_URI'] === '/register') {
    include('templates/register.php');
}

// Display login form
if (!isset($_SESSION['username']) && $_SERVER['REQUEST_URI'] === '/login') {
    include('templates/login.php');
}
