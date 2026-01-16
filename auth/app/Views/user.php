<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User</title>
</head>
<body>
    <h1>User profile</h1>
    <p>Name: <?php echo htmlspecialchars($user->name); ?></p>
    <p>Email: <?php echo htmlspecialchars($user->email); ?></p>
    <p>Member since: <?php echo htmlspecialchars($user->createdAt); ?></p>
    <p><a href="/dashboard">Back to dashboard</a></p>
</body>
</html>
