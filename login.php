<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (empty($user) || empty($pass)) {
        $_SESSION['error'] = 'Credenziali non valide';
        header('Location: login.php');
        die;
    }

    $stmt = $conn->prepare("SELECT * FROM utenti WHERE email = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] =  'Utente non trovato';
        header('Location: login.php');
        die;
    }

    $userData = $result->fetch_assoc();

    if (!password_verify($pass, $userData['password'])) {
        $_SESSION['error'] = 'Password errata';
        header('Location: login.php');
        exit();
    }

    $_SESSION['user_id'] = $userData['ID'];
    $_SESSION['username'] = $userData['username'];

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="css/login.css"/>
    <title>Login</title>
</head>

<body>
<form action="login.php" method="POST" class="container">
    <h1 class="login-title">Login</h1>
    <section class="input-box">
        <input id="email" type="text" name="email" placeholder="Email" required/>
        <i class="bx bxs-user"></i>
    </section>
    <section class="input-box">
        <input id="password" type="password" name="password" placeholder="Password" required/>
        <i class="bx bxs-lock-alt"></i>
    </section>
    <br/>
    <p class="error-message">
        <?php
        if (isset($_SESSION['error'])) {
            echo $_SESSION['error'];
            unset($_SESSION['error']);
        }
        ?>
    </p>
    <div class="button-container">
        <button class="login-button" type="submit">Login</button>
    </div>

    <h5 class="don't-have-an-account">
        Don't have an account?
        <a href="sign-up.php"><b>Register</b></a>
    </h5>
</form>
</body>

</html>
