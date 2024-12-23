<?php
session_start();
include('storage.php');

$users = new Storage(new JsonIO('users.json'));

function validateLogin($input, &$data, &$errors, $users, &$validpassword) {
  if (!isset($input["name"])) {
    $errors['name'] = "Name is required!";
  }
  else if (trim($input["name"]) === "") {
    $errors['name'] = "Name is required!";
  }
  else{
    $namefound = false;
    foreach($users->findAll() as $id => $user){
        if ($user['name'] === $input['name']){
            $namefound = true;
            $data['name'] = $user['name'];
            $validpassword = $user['password'];
            $data['id'] = $id;
        }
    }

    if (!$namefound){
      $errors['name'] = "Name is invalid!";
    }
    else{
      if (!isset($input['password'])) {
        $errors['password'] = "Password is required!";
      }
      else{
        if($input['password'] !== $validpassword){
          $errors['password'] = "Password is invalid!";
        }
      }
    }
  }
  return count($errors) === 0;
}

function validateRegister($input, &$data, &$errors, &$emails, &$passwords, $users){
    if (!isset($input["Rname"])) {
      $errors['Rname'] = "Name is required!";
    }
    else if (trim($input["Rname"]) === "") {
      $errors['Rname'] = "Name is required!";
    }
    else{
      foreach($users->findAll() as $user){
          if ($user['name'] === $input['Rname']){
              $errors['Rname'] = "This name is already taken!";
          }
      }
      if (!isset($errors['Rname'])){
        $data["name"] = $input["Rname"];
      }
    }
  
    if (!isset($input["emails"])) {
      $errors['email'] = "Email address is required!";
    }
    else {
      $notemptyemails = array_filter($input["emails"], function($e) {
        return trim($e) !== "";
      });
      if (count($notemptyemails) === 0) {
        $errors['email'] = "Email address is required!";
      }
      $validemails = array_filter($notemptyemails, function($e) {
        return filter_var($e, FILTER_VALIDATE_EMAIL) !== false;
      });
      if (count($notemptyemails) !== count($validemails)) {
        $errors['email'] = "Email address has invalid format!";
      } else {
        $emails = $validemails;
      }
      }
  
      if (!isset($input['passwords'])) {
          $errors['Rpassword'] = "Password is required!";
      }
      else{
          $passwords = $input['passwords'];
      }
  
      if(trim($passwords[0]) === ""){
          $errors['Rpassword'] = "Password is required!";
      }
      else if(str_contains($passwords[0], ' ')){
          $errors['Rpassword'] = "Password can not contain spaces!";
      }
      else{
          if ($passwords[0] !== $passwords[1]){
              $errors['passwordConf'] = "Passwords do not match!";
          }
          else{
              $data['password'] = $passwords[0];
              $passwords[1] = $passwords[0];
          }
      }
    return count($errors) === 0;
}

$data = [];
$errors = [];

$validpassword;
if (count($_POST) > 0 && isset($_POST['form_type']) && $_POST['form_type'] === 'login') {
  if (validateLogin($_POST, $data, $errors, $users, $validpassword)) {
    if (sizeof($errors) === 0){
        $_SESSION['login_id'] = $data['id'];
        header("Location: index.php");
        die;
    }
  }
}

$emails = [];
$passwords = [];

if (count($_POST) > 0 && isset($_POST['form_type']) && $_POST['form_type'] === 'register') {
  if (validateRegister($_POST, $data, $errors, $emails, $passwords, $users)) {
    if (sizeof($errors) === 0){
        $data['email'] = $emails[0];      
        $users->add($data);
        foreach($users->findAll() as $id => $user){
          if ($user['name'] === $data['name']){
            $_SESSION['login_id'] = $id;
          }
        }
      $data = [];
      header("Location: index.php");
      die;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LOGIN PAGE</title>
  <link rel="stylesheet" href="index.css">
</head>
<body>
    <h1> GedvaChat </h1>
        <div class="centerdiv">
            <form class="dataform" method="post" novalidate>
                <h3>Log in</h3>
                <input 
                    placeholder="Your username" 
                    class="inputfield <?php if (isset($errors['name'])) echo 'error'; ?>" 
                    type="text" 
                    name="name" 
                    required
                    value="<?php if (isset($data['name'])) echo htmlspecialchars($data['name']); ?>">
                <br>
                <input 
                    placeholder="Your password" 
                    class="inputfield <?php if (isset($errors['password'])) echo 'error'; ?>" 
                    type="password" 
                    name="password" 
                    required
                    value="">
                <br>
                <input type="hidden" name="form_type" value="login">
                <button class="button-27" role="button" type="submit">Login</button>
            </form>


            <form class="dataform" method="post" novalidate>
              <h3>Create an Account</h3>
              <input 
                  placeholder="Your username" 
                  class="inputfield <?php if (isset($errors['Rname'])) echo 'errorRegister'; ?>" 
                  type="text" 
                  name="Rname" 
                  required
                  value="">
              <br>
              
              <input 
                  placeholder="Your email" 
                  class="inputfield <?php if (isset($errors['email'])) echo 'errorRegister'; ?>" 
                  type="email" 
                  name="emails[]" 
                  required
                  value="<?php if (isset($emails[0])) echo htmlspecialchars($emails[0]); ?>">
              <br>
              
              <input 
                  placeholder="Your password" 
                  class="inputfield <?php if (isset($errors['Rpassword'])) echo 'errorRegister'; ?>" 
                  type="password" 
                  name="passwords[]" 
                  required>
              <br>
              
              <input 
                  placeholder="Confirm your password" 
                  class="inputfield <?php if (isset($errors['passwordConf'])) echo 'errorRegister'; ?>" 
                  type="password" 
                  name="passwords[]" 
                  required>
              <br>
              
              <input type="hidden" name="form_type" value="register">
              <button class="button-27" role="button" type="submit">Create Account</button>
            </form>
        </div>
</body>
</html>