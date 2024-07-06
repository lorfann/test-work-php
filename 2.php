<?php

require_once('connect.php'); // импорт файла с логином и паролем от базы








function convertString ( string $a, string $b) :string {
    
        $positions = []; // массив для хранения позиций вхождений подстроки $b в строке $a
        $lastPosition = 0; // начальная позиция поиска 
        
        while (($lastPosition = strpos($a, $b, $lastPosition)) !== false) {
            $positions[] = $lastPosition;
            $lastPosition += strlen($b);
        }
    
        // Проверка что  в строке $a содержится 2 и более подстроки $b
        if (count($positions) < 2) {
            return $a; // если меньше то просто возвращаем строку $a
        }
    
        // Находим позицию второго вхождения
        $secondPos = $positions[1];
    
        // Инвертируем подстроку $b
        $reversedB = strrev($b);
    
        // Заменяем второе вхождение подстроки $b на инвертированную подстроку
        $a = substr_replace($a, $reversedB, $secondPos, strlen($b));
    
        return $a;
    
}


/*====================================================================================== */




function mySortForKey($a, $b) {
    // Проверка наличия ключа $b в каждом дочернем массиве
    foreach ($a as $index => $childArray) {
        if (!array_key_exists($b, $childArray)) {
            throw new Exception("Ключ '$b' отсутствует в массиве с индексом $index.");
        }
    }

    // Функция сравнения для usort
    usort($a, function($elem1, $elem2) use ($b) {
        return $elem1[$b] <=> $elem2[$b]; // Оператор <=> (космический корабль) сравнивает значения ключа $b в двух элементах массива $elem1 и $elem2. Он возвращает -1, 0 или 1, что используется для сортировки.
    });

    return $a;
}

/*====================================================================================== */

function importXml($a)
{
    $driver = 'mysql:host=localhost;dbname=test_samson;charset=utf8';
    $username = $login;
    $password = $pass;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //  Устанавливаем режим обработки ошибок как исключения
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Устанавливаем режим выборки данных как ассоциативные массивы
    ];
    try {
        $pdo = new PDO($driver, $username, $password, $options);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage()); // Если подключение не удалось, выводим сообщение об ошибке и завершаем выполнение скрипт
    }
    $xml = simplexml_load_file($a, 'SimpleXMLElement', LIBXML_NOCDATA); // Чтение и парсинг XML файла
    if ($xml === false) {
        die("Failed to load XML file.");
    }
    foreach ($xml->Товар as $product) {
        $code = $product['Код'];
        $name = $product['Название'];
        // Вставка товара в таблицу a_product
        $stmt = $pdo->prepare(
            "INSERT INTO a_product (code, name) VALUES (?, ?)"
        );
        $stmt->execute([$code, $name]);
        $product_id = $pdo->lastInsertId();
        // Вставка цен в таблицу a_price
        foreach ($product->Цена as $price) {
            $price_type = $price['Тип'];
            $price_value = (float) $price;
            $stmt = $pdo->prepare(
                "INSERT INTO a_price (product_id, price_type, price) VALUES (?, ?, ?)"
            );
            $stmt->execute([$product_id, $price_type, $price_value]);
        }
        // Вставка свойств в таблицу a_property
        foreach (
            $product->Свойства->children()
            as $property_name => $property_value
        ) {
            $stmt = $pdo->prepare(
                "INSERT INTO a_property (product_id, property_value) VALUES (?, ?)"
            );
            $stmt->execute([$product_id, $property_value]);
        }
        foreach ($product->Разделы->Раздел as $category_name) {
            $stmt = $pdo->prepare("SELECT id FROM a_category WHERE name = ?");
            $stmt->execute([$category_name]);
            $category_id = $stmt->fetchColumn();
            if (!$category_id) {
                $stmt = $pdo->prepare(
                    "INSERT INTO a_category (name) VALUES (?)"
                );
                $stmt->execute([$category_name]);
                $category_id = $pdo->lastInsertId();
            }
            $stmt = $pdo->prepare(
                "UPDATE a_product SET category_id = ? WHERE id = ?"
            );
            $stmt->execute([$category_id, $product_id]);
        }
    }
}

