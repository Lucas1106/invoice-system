<?php
session_start();
if(isset($_SESSION['logged_in'])){
    header("Location: LCR/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="stylesheet" href="auth/css/style.css">
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <h2>Login</h2>
        <form method="POST" action="login.php">
            <label for="email">E-mail</label>
            <input type="email" name="email" required>

            <label for="password">Senha</label>
            <input type="password" name="password" required>

            <button type="submit">Entrar</button>

            <?php 
            if(isset($_GET['error'])){
                echo "<p class='error'>Credenciais inválidas!</p>";
            }
            ?>
        </form>
    </div>
</div>

</body>
</html>
