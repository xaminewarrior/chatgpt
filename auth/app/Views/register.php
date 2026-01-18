<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <h1>Create Account</h1>
    <?php if (!empty($error)) : ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" action="/auth/register">
        <label>
            Name
            <input type="text" name="name" required>
        </label>
        <br>
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
        <button type="submit">Sign Up</button>
    </form>
    <p>Already have an account? <a href="/auth/showLogin">Login</a></p>
</body>
</html>
