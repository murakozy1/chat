<?php
session_start();
include('storage.php');

$users = new Storage(new JsonIO('users.json'));
$messages = new Storage(new JsonIO(('messages.json')));
$loggedin = false;
$userid;

if(isset($_SESSION['login_id'])){
    if ($_SESSION['login_id'] !== ""){
        $loggedin = true;
        $name = $users->findById($_SESSION['login_id'])['name'];
    }
}

if (isset($_POST['logout'])){
    unset($_SESSION['login_id']);
    session_destroy();
    header("Location: login.php");
    exit;
}

$data = [];

if (isset($_POST['newmessage'])){
    $data['message'] = $_POST['newmessage'];
    $data['name'] = $name;
    $data['user_id'] = $_SESSION['login_id'];
    $messages->add($data);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <?php if ($loggedin): ?>
        <div class="container">
            <div class="box1">
                <h3>Welcome back <?= $name?> !</h3>
                <form action="" method="post">
                    <button type="submit" name="logout" class="button-27">Log out</button>
                </form>
            </div>

            <div class="box2">
                <h1>Chat room</h1>
                <div class="messagebox">
                    <?php foreach ($messages->findAll() as $id => $message): ?>
                        <?php if ($message['user_id'] !== $_SESSION['login_id']): ?>    
                            <div class="message">
                                <h5><?= $message['name'] ?>: </h5>
                                <?= $message['message'] ?>
                                <br>
                            </div>
                        <?php else: ?>
                            <div class="mymessage">
                                <h5><?= $message['name'] ?>:</h5>
                                <?= $message['message'] ?>
                                <br>
                            </div>
                        <?php endif ?>
                    <?php endforeach ?>
                        <br>
                        <form action="" method="post">
                            <input type="text" name="newmessage" value="">
                            <button type="submit" class="button-28">Send</button>
                        </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <h1>Fuck off</h1>
    <?php endif ?>
    <script>
        window.onload = function() {
            window.scrollTo(0, document.body.scrollHeight);
        };
    </script>   
</body>
</html>