<?php
      require_once("function.php");
      require_once("init.php");
      require_once("helpers.php");
   is_writeable('uploads');
  $is_auth = rand(0, 1);
  $errors = [];
  $cats_ids = [];
  $cats_ids = array_column($categories, 'id');
  $lot = $_POST;
  $files = $_FILES;
  $rules = [
    'category_id' => function($value) use ($cats_ids) {
        return validateCategory($value, $cats_ids);
    },
    'title' => function() {
        if (validateFilled('title') === false){
        return "Это поле должно быть заполнено";
      }
    },
    'first_price' => function() {
         if(validatePrice('first_price') === false){
           return "Это поле должно быть заполнено целым положительным числом";
      }
    },
    'description' => function() {
        if (validateFilled('description') === false){
        return "Это поле должно быть заполнено";
      }
    },
    'bet_step' => function() {
        if(validateBet('bet_step') === false){
        return "Это поле должно быть заполнено целым положительным числом";
       } //
    },
    'date_delection' => function() {
      $date = $_POST['date_delection'];
      if (!is_date_valid($date)){
          return "Введите дату завершения торгов в формате ГГГГ-ММ-ДД";
      }
      elseif(strtotime($date) < time()){
        return "Введите дату хотя бы следующего дня или позже";
      }
    },
    'lot-img' => function() {
        $lotimg = $_FILES['lot-img']['name'];
        if(validateImage($lotimg) === false){
        return "Загрузите картинку в формате jpg, jpeg или png";// функция наличия файла('lot-img');
      }
    }
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  foreach ($lot as $key => $value) {
       if (isset($rules[$key])) {
           $rule = $rules[$key];
           $result = $rules[$key];
           $not_yet_errors = [];
           $not_yet_errors[$key] = $rule($value);
           if ($not_yet_errors[$key] !== null) {
             $errors[$key] = $rule($value);
           }
       }
   }
}
foreach ($files as $key => $value) {
     if (isset($rules[$key]) && $rules[$key] !== null) {
         $rule = $rules[$key];
         $result = $rule($value);
         if($result !== null){
         $errors[$key] = $rule($value);
       }
     }
}

   var_dump($errors);
if (empty($errors)){
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $lot = $_POST;

      ////
          $file_name = $_FILES['lot-img']['name'];
          $path_url = __DIR__ . '/uploads/';
          if(!is_writeable($path_url)){
            chmod($path_url, 777);
          }
          $file_path = __DIR__ . '/uploads/' . uniqid() . $file_name;
          move_uploaded_file($_FILES['lot-img']['tmp_name'], $file_path);
      $lot['url'] = $file_path;
      $sql = 'INSERT INTO lots (date_creation, name, author, first_price, category_id, description, bet_step, date_delection, url) VALUES (NOW(), ?, 1, ?, ?, ?, ?, ?, ?)';

      $stmt = db_get_prepare_stmt($link, $sql, $lot);
      $res = mysqli_stmt_execute($stmt);

      if ($res) {
        $lot_id = mysqli_insert_id($link);

        header("Location: lot.php?id=" . $lot_id);
      }
      else{
        print("Ошибка добавления лота!");
      }
    }
  }
$errors = array_filter($errors);
$content = include_template('add.php', ['categories' => $categories, 'connection' => $connection, 'rules' => $rules, 'errors' => $errors]);
$layout_content = include_template('layout.php', ['content' => $content, 'title' => 'Добавление лота', 'categories' => $categories, 'is_auth' => $is_auth, 'user_name' => 'Илья', 'rules' => $rules]);
print($layout_content);
?>
