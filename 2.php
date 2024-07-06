<?php

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
