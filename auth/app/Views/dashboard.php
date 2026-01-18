<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($user->name); ?>!</h1>
    <p>Your email: <?php echo htmlspecialchars($user->email); ?></p>
    <p><a href="/auth/showUser/<?php echo (int) $user->id; ?>">View profile</a></p>
    <form method="post" action="/auth/logout">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
