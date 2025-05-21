<?php
session_start();

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($nome) || empty($cognome) || empty($username) ||
        empty($email) || empty($password) || empty($confirm_password)
    ) {
        $_SESSION['error'] = 'Tutti i campi sono obbligatori.';
        header('Location: sign-up.php');
        die;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "L'email non è valida.";
        header('Location: sign-up.php');
        die;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Le password non corrispondono.';
        header('Location: sign-up.php');
        die;
    }

    // controlla se l'email è già registrata
    $stmt = $conn->prepare("SELECT email FROM utenti WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "L'email è già registrata.";
        header('Location: sign-up.php');
        die;
    }

    $stmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO utenti (email, nome, cognome, password, username) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $email, $nome, $cognome, $hashedPassword, $username);

    if (!$stmt->execute()) {
        $_SESSION['error'] = "Errore durante la registrazione: " . $stmt->error;
        header("Location: sign-up.php");
        die;
    }

    $stmt->close();

    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="container">
    <p class="error-message">
        <?php
        if (isset($_SESSION['error'])) {
            echo $_SESSION['error'];
            unset($_SESSION['error']);
        }
        ?>
    </p>
    <h2 class="login-title">Create Your Account</h2>
    <form action="sign-up.php" method="POST">
        <div class="input-box">
            <input type="text" name="nome" placeholder="First Name" required>
            <i class="ri-user-line"></i>
        </div>
        <div class="input-box">
            <input type="text" name="cognome" placeholder="Last Name" required>
            <i class="ri-user-line"></i>
        </div>
        <div class="input-box">
            <input type="text" name="username" placeholder="Username" required>
            <i class="ri-user-line"></i>
        </div>
        <div class="input-box">
            <input type="email" name="email" placeholder="Email" required>
            <i class="ri-mail-line"></i>
        </div>
        <div class="input-box">
            <input type="password" name="password" placeholder="Password" required>
            <i class="ri-lock-line"></i>
        </div>
        <div class="input-box">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <i class="ri-lock-line"></i>
        </div>
        <br>
        <div class="remember-me">
            <input type="checkbox" name="terms" id="terms" required>
            <label for="terms">I accept the <a href="#">Terms and Conditions</a></label>
        </div>
        <div class="button-container">
            <button type="submit" class="login-button">Sign Up</button>
        </div>
        <div class="remember-forgot-box">
            <h5>Already have an account? <a href="login.php">Login</a></h5>
        </div>
    </form>
</div>
</body>
</html>