<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: area_usuario.php');
    exit;
}
?>
<?php include '_header.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar - Tamo Junto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h2 class="mb-4">Entrar</h2>
                    <a href="login.php" class="btn btn-primary btn-lg btn-block mb-3">Já tenho conta</a>
                    <a href="cadastro.php" class="btn btn-outline-primary btn-lg btn-block">Criar nova conta</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html> 