<?php
////////////////////////////////////////////////////////////////////////////
// Register new user 
////////////////////////////////////////////////////////////////////////////

function __autoload($className) {
  $className = str_replace("..", "", $className);
  require_once("classes/$className.class.php");
}

$db = new MyDB('zak.sqlite');
if(!$db){
    echo $db->lastErrorMsg();
} else {
   // echo "Opened database successfully\n";
}


if(isset($_POST['submit'])) 
{ 

    $err = array(); 

    # проверям логин 
   if(!preg_match("/^[a-zA-Z0-9]+$/",$_POST['login'])) 
    { 
        $err[] = "Логін може складатися тільки з букв англійського алфавіту і цифр"; 
    } 
     
    if(strlen($_POST['login']) < 3 or strlen($_POST['login']) > 30) 
    { 
        $err[] = "Логін повинен бути не менше 3-х символів і не більше 30"; 
    } 
     
    # проверяем, не сущестует ли пользователя с таким именем 

    $in_table = $db->querySingle("SELECT count(users_id) FROM users WHERE users_login='".$_POST['login']."'");

    if($in_table > 0) 
    { 
        $err[] = "Користувач з таким і'мям вже існує"; 
    } 
  
     
    # Если нет ошибок, то добавляем в БД нового пользователя 
   if(count($err) == 0) 
    { 
         
        $login = $_POST['login']; 
         
        # Убераем лишние пробелы и делаем двойное шифрование 
        $password = md5(md5(trim($_POST['password']))); 
         

        $sm = $db->exec("INSERT INTO users (users_login, users_password, users_hash) VALUES ('".$login."', '".$password."', '')"); 

        if($sm){
          echo '<h2>Користувача '.$login.' зареєстровано.</h2>';
        }
        //print_r($sm);
        //header("Location: login.php"); exit(); 
    }
} 
?>

  <form method="POST" action="">
  Логін <input type="text" name="login" id="reg_inp" /><br />
  Пароль <input type="password" name="password" id="reg_inp" /><br />
  <input name="submit" type="submit" value="Зарегистрироваться"> 
  </form>
  <?php
    if (!empty($err)) {
      print "<b>Помилка:</b><br>"; 
      foreach($err AS $error) 
      { 
        print $error."<br>"; 
      }   
    }
  ?>