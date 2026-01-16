<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <?php if (!empty($error)) : ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" action="/login">
        <label>
            Email
            <input type="email" name="email" required>
        </label>
        <br>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <br>
        <button type="submit">Login</button>
    </form>
    <p>Need an account? <a href="/register">Register</a></p>
</body>
</html>
