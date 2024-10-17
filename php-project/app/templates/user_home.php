<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Home</title>
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($username); ?></h2>
    
    <h3>Your scripts:</h3>
    <ul>
        <?php foreach ($scripts as $script): ?>
            <li>
                <form method="POST">
                    <input type="hidden" name="script_name" value="<?php echo htmlspecialchars($script); ?>">
                    <button type="submit" name="run_script">Run <?php echo htmlspecialchars($script); ?></button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <a href="?logout">Logout</a>
</body>
</html>
