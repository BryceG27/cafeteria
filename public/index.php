<?php 

$name       = $_GET['name'] ?? '';
$surname    = $_GET['surname'] ?? '';
$email      = $_GET['email'] ?? '';
$phone      = $_GET['phone'] ?? '';
$show_error = false;
$old_values = []; 

if((empty($_GET['email']) || empty($_GET['name']) || empty($_GET['surname'])) && !empty($_GET)) {
    $show_error = true;
    $old_values = [
        'name'    => $_GET['name'] ?? '',
        'surname' => $_GET['surname'] ?? '',
        'email'   => $_GET['email'] ?? '',
        'phone'   => $_GET['phone'] ?? '',
    ];

    $_GET = [];
} else {
    $old_values = [];
}


echo '<pre>';
var_dump($_GET);
echo '</pre>';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
  </head>
  <body>
    <div class="container pt-3">
        <div class="row align-items-center justify-content-center">
            <div class="col-md-12 text-center">
                <img width="200" height="64" src="https://www.moto.it/dist/img/microdata/logo-moto.jpg" alt="logo">
                <h3 class="mt-3">Inserisci i tuoi dati per essere ricontattato!</h3>
            </div>
            <?php 
            if(!$show_error && !empty($_GET)) {
                echo '<div class="col-md-12">
                        <div class="alert alert-success">Sottoscrizione effettuata con successo!</div>
                    </div>';
            }
            ?>
        </div>
        <form action="" id="form">
            <div class="row py-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text" class="form-control" name="name" id="name" value="<?php 
                        if($show_error && !empty($old_values['name']))
                            echo $old_values['name'];
                    ?>">
                    <?php 
                        if($show_error && empty($old_values['name']))
                            echo '<div class="text-danger">Il nome è obbligatorio.</div>';
                    ?>
                </div>
                <div class="col-md-6">
                    <label for="surname" class="form-label">Cognome</label>
                    <input type="text" class="form-control" name="surname" id="surname" value="<?php 
                        if($show_error && !empty($old_values['surname']))
                            echo $old_values['surname'];
                    ?>">
                    <?php 
                        if($show_error && empty($old_values['surname']))
                            echo '<div class="text-danger">Il cognome è obbligatorio.</div>';
                    ?>
                </div>
            </div>
            <div class="row py-3">
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" id="email" value="
                    <?php 
                        if($show_error && !empty($old_values['email']))
                            echo $old_values['email'];
                    ?>">
                    <?php 
                        if($show_error && empty($old_values['email']))
                            echo '<div class="text-danger">L\'email è obbligatoria.</div>';
                    ?>
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Telefono</label>
                    <input type="text" class="form-control" name="phone" id="phone">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary" id="send-form">Invia</button>
                    <button class="btn btn-success d-none" id="sending-form" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise spin" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
  </body>
</html>

<script>
    const send_form = document.getElementById('send-form');
    const sending_form = document.getElementById('sending-form');

    send_form.addEventListener('click', function(e) {
        e.preventDefault();

        if(document.getElementById('name').value == '') {
            alert("OU");
        } else {
            this.classList.add('d-none');
            sending_form.classList.remove('d-none');
            setTimeout(function() {
                document.getElementById('form').submit();
            }, 5000);
        }

    })
</script>

<style>
    .spin {
    animation: spin-animation 0.5s infinite;
    display: inline-block;
    }

    @keyframes spin-animation {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(359deg);
    }
</style>

<?php

exit();

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
