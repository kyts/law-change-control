<?php
function __autoload($className) {
  $className = str_replace("..", "", $className);
  require_once("classes/$className.class.php");
}
////////////////////////////////////////////////////////////////////////////
// Login process controll 
////////////////////////////////////////////////////////////////////////////

$error[0] = 'Я вас не знаю';
$error[1] = 'Включи куки';
$error[2] = 'Тебе сюда нельзя';

  # Функция для генерации случайной строки 
  function generateCode($length=6) { 
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789"; 
    $code = ""; 
    $clen = strlen($chars) - 1;   
    while (strlen($code) < $length) { 
        $code .= $chars[mt_rand(0,$clen)];   
    } 
    return $code; 
  } 
  
  # Если есть куки с ошибкой то выводим их в переменную и удаляем куки
  if (isset($_COOKIE['errors'])){
      $errors = $_COOKIE['errors'];
      setcookie('errors', '', time() - 60*24*30*12, '/');
  }

  # Подключаем конфиг
  //require_once("classes/classMyDB.php");

  $db = new MyDB('zak.sqlite');
  if(!$db){
    echo $db->lastErrorMsg();
  } else {
   // echo "Opened database successfully\n";
  }

//print_r($_POST['password']);
  if(isset($_POST['login']) & isset($_POST['password'])) 
  { 
//print_r($_POST);
    # Вытаскиваем из БД запись, у которой логин равняеться введенному 
    
    $data = $db->query("SELECT users_id, users_password FROM `users` WHERE `users_login`='".$_POST['login']."' LIMIT 1");
    if($data) {
	$data = $data->fetchArray(SQLITE3_ASSOC);
    }	

    # Соавниваем пароли 
    if($data['users_password'] === md5(md5($_POST['password']))) 
    { 
      # Генерируем случайное число и шифруем его 
      $hash = md5(generateCode(10)); 
           
      # Записываем в БД новый хеш авторизации и IP 
      $db->query("UPDATE users SET users_hash='".$hash."' WHERE users_id='".$data['users_id']."'") or die("SQL Error!"); 
       
      # Ставим куки 
      setcookie("id", $data['users_id'], time()+60*60*24*30); 
      setcookie("hash", $hash, time()+60*60*24*30); 
      
      echo "OK";
      # Переадресовываем браузер на страницу проверки нашего скрипта 
      //header("Location: index.php"); exit(); 
    } 
    else 
    { 
      echo "Введено невірний логін або пароль"; 
    } 
  } 

  # Проверяем наличие в куках номера ошибки
  if (isset($errors)) {print '<h4>'.$error[$errors].'</h4>';}

  ?>