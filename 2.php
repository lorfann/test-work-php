<?php

function convertString ( string $a, string $b) :string {
    
   
        
        $positions = []; // массив для хранения позиций вхождений подстроки $b в строке $a
        $lastPosition = 0; // начальная позиция поиска 
        
        while (($lastPosition = strpos($a, $b, $lastPosition)) !== false) {
            $positions[] = $lastPosition;
            $lastPosition += mb_strlen($b);
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