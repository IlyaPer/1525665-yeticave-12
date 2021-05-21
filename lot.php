<?php
    require_once("init.php");
    $id = intval($_GET['id']) ?? NULL;
    if ($id === NULL) {
      header("Location: pages/404.html");
      exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        die("action работает!");
    }

    require_once("./function.php");
    require_once("helpers.php");
    $sql_lot = "SELECT categories.name AS category_name, lots.name, lots.id AS id, description, first_price, url, date_delection, bet_step FROM lots JOIN categories ON categories.id = lots.category_id WHERE lots.id = $id;";
    $result_lot = mysqli_query($connection, $sql_lot);
    if (!$result_lot) {
      exit;
    }
    $lot = mysqli_fetch_assoc($result_lot);
    if ($id != $lot['id']) {
      header("Location: pages/404.html");
      exit;
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
