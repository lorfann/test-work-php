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

function importXml($a) {

    $driver = 'mysql:host=localhost;dbname=test_samson;charset=utf8';
    $username = $login;
    $password = $pass;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($driver, $username, $password, $options);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
    $xml = simplexml_load_file($a, 'SimpleXMLElement', LIBXML_NOCDATA);
    if ($xml === false) {
        die("Failed to load XML file.");
    }

    // Подготовленный запрос для вставки товара в таблицу a_product
    $insertProductStmt = $pdo->prepare("INSERT INTO a_product (code, name) VALUES (?, ?)");
    // Подготовленный запрос для вставки цены в таблицу a_price
    $insertPriceStmt = $pdo->prepare("INSERT INTO a_price (product_id, price_type, price) VALUES (?, ?, ?)");
    // Подготовленный запрос для вставки свойства в таблицу a_property
    $insertPropertyStmt = $pdo->prepare("INSERT INTO a_property (product_id, property_value) VALUES (?, ?)");

    foreach ($xml->Товар as $product) {
        $code = $product['Код'];
        $name = $product['Название'];

        // Выполнение запроса на вставку товара
        $insertProductStmt->execute([$code, $name]);
        $product_id = $pdo->lastInsertId();

        foreach ($product->Цена as $price) {
            $price_type = $price['Тип'];
            $price_value = (float) $price;
            // Выполнение запроса на вставку цены
            $insertPriceStmt->execute([$product_id, $price_type, $price_value]);
        }

        foreach ($product->Свойства->children() as $property_name => $property_value) {
            // Выполнение запроса на вставку свойства
            $insertPropertyStmt->execute([$product_id, $property_value]);
        }

        foreach ($product->Разделы->Раздел as $category_name) {
            $stmt = $pdo->prepare("SELECT id FROM a_category WHERE name = ?");
            $stmt->execute([$category_name]);
            $category_id = $stmt->fetchColumn();
            if (!$category_id) {
                $stmt = $pdo->prepare("INSERT INTO a_category (name) VALUES (?)");
                $stmt->execute([$category_name]);
                $category_id = $pdo->lastInsertId();
            }
            $stmt = $pdo->prepare("UPDATE a_product SET category_id = ? WHERE id = ?");
            $stmt->execute([$category_id, $product_id]);
        }
    }
}

/*====================================================================================== */


function exportXml($a, $b) {
    // Подключение к базе данных
    $driver = 'mysql:host=localhost;dbname=test_samson;charset=utf8';
    $username = $login;
    $password = $pass;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //  Устанавливаем режим обработки ошибок как исключения
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Устанавливаем режим выборки данных как ассоциативные массивы
    ];
    
    $pdo = new PDO($driver, $username, $password, $options);

    // Получение всех вложенных категорий
    $categoryIds = getCategoryIds($pdo, $b);

    if (empty($categoryIds)) {
        // Если нет категорий, то нет и товаров
        file_put_contents($a, '<?xml version="1.0" encoding="windows-1251"?><Товары></Товары>');
        return;
    }

    // Получение товаров и их характеристик
    $products = getProducts($pdo, $categoryIds);

    // Формирование XML
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="windows-1251"?><Товары></Товары>');

    foreach ($products as $product) {
        $productElement = $xml->addChild('Товар');
        $productElement->addAttribute('Код', $product['code']);
        $productElement->addAttribute('Название', $product['name']);

        // Добавление цен
        foreach ($product['prices'] as $price) {
            $priceElement = $productElement->addChild('Цена', $price['price']);
            $priceElement->addAttribute('Тип', $price['price_type']);
        }

        // Добавление свойств
        $propertiesElement = $productElement->addChild('Свойства');
        foreach ($product['properties'] as $property) {
            $propertiesElement->addChild($property['name'], $property['value']);
        }

        // Добавление разделов
        $categoriesElement = $productElement->addChild('Разделы');
        foreach ($product['categories'] as $category) {
            $categoriesElement->addChild('Раздел', $category['name']);
        }
    }

    // Сохранение XML в файл
    $xml->asXML($a);
}

function getCategoryIds($pdo, $categoryCode) {
    $stmt = $pdo->prepare('
        WITH RECURSIVE category_tree AS (
            SELECT id, code, name, parent_id
            FROM a_category
            WHERE code = ?
            UNION ALL
            SELECT c.id, c.code, c.name, c.parent_id
            FROM a_category c
            INNER JOIN category_tree ct ON ct.id = c.parent_id
        )
        SELECT id FROM category_tree
    ');
    $stmt->execute([$categoryCode]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getProducts($pdo, $categoryIds) {
    if (empty($categoryIds)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

    // Получение товаров
    $stmt = $pdo->prepare("
        SELECT p.id, p.code, p.name
        FROM a_product p
        JOIN product_category pc ON p.id = pc.product_id
        WHERE pc.category_id IN ($placeholders)
    ");
    $stmt->execute($categoryIds);
    $products = $stmt->fetchAll();

    // Получение характеристик товаров
    foreach ($products as &$product) {
        $productId = $product['id'];

        // Получение цен
        $stmt = $pdo->prepare("SELECT price_type, price FROM a_price WHERE product_id = ?");
        $stmt->execute([$productId]);
        $product['prices'] = $stmt->fetchAll();

        // Получение свойств
        $stmt = $pdo->prepare("SELECT property_value AS value FROM a_property WHERE product_id = ?");
        $stmt->execute([$productId]);
        $product['properties'] = $stmt->fetchAll();

        // Получение категорий
        $stmt = $pdo->prepare("
            SELECT c.name
            FROM a_category c
            JOIN product_category pc ON c.id = pc.category_id
            WHERE pc.product_id = ?
        ");
        $stmt->execute([$productId]);
        $product['categories'] = $stmt->fetchAll();
    }

    return $products;
}