<?php
    require_once("init.php");
    require_once("function.php");
    require_once("helpers.php");
    $id = intval($_GET['id']) ?? NULL;
    if ($id === NULL) {
      header("Location: pages/404.html");
      exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        die("kjnrfoqewi");
    }
    require_once("helpers.php");
    $sql_lot = "SELECT categories.name AS category_name, lots.name, lots.id AS id, description, first_price, url, date_delection, bet_step FROM lots JOIN categories ON categories.id = lots.category_id WHERE lots.id = $id;";
    $result_lot = mysqli_query($connection, $sql_lot);
    if (!$result_lot) {
      exit;
    }
    $lot = mysqli_fetch_assoc($result_lot);
    var_dump($lot);
    if ($id != $lot["id"]) {
      header("Location: pages/404.html");
      exit;
    }

    $errors = [];
    $rules = [
        'cost' => function() {
            return validatePrice('cost');
        }
    ];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($_POST as $key => $value) {
            if (isset($rules[$key])) {
                $rule = $rules[$key];
                $result = $rule($value);
                if ($result !== null) {
                    $errors[$key] = $result;
                }
            }
        }
    }
    if (empty($errors)){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $new_lot = [$_POST['cost'], $_SESSION['user_id'], $id];
            $sql = 'INSERT INTO `bets`(`time_bet`, `cost`, `user_id`, `lot_id`) VALUES (NOW, ?, ?, ?)';
            $stmt = db_get_prepare_stmt($connection, $sql, $new_lot);
            $res = mysqli_stmt_execute($stmt);
            if ($res) {
                $lot_id = mysqli_insert_id($connection);
                header("Location: lot.php?id=" . $id);
            }
            else{
                print("Ошибка добавления ставки!" . mysqli_error($connection));
                exit;
            }
        }
    }

      $result_time = deletion_of_lot($lot['date_delection']);
      // Получаю максимальную ставку для того, чтобы сложить ее с первоначальной ценой и получить текущую цену
      $sql_current = "SELECT MAX(cost) AS cost, lot_id FROM bets JOIN lots ON lots.id = bets.lot_id WHERE lots.id = $id;";
      $result_current = mysqli_query($connection, $sql_current);
      $lot_max_bet = mysqli_fetch_assoc($result_current);
      $max_bet = $lot_max_bet['cost'];
      if(!$max_bet){
        $max_bet = $lot['bet_step'];
      }
      $content = include_template('lot.php', ['lot' => $lot, 'categories' => $categories, 'result_time' => $result_time, 'is_auth' => $is_auth, 'max_bet' => $max_bet]);
      $layout_content = include_template('layout.php', ['content' => $content, 'title' => $lot['name'], 'categories' => $categories, 'is_auth' => $is_auth, 'username' => $username]);
      print($layout_content);
?>
