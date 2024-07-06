<?php

function findSimple(int $a, int $b): array {
  if ($a > 0 && $b > 0) {
    //проверяем что числа положительные

    $simpleNumbers = [];  // создаем пустой массив для хранения простых чисел

    $range = range($a, $b); // создаем массив с числами от $a до $b включительно

    foreach ($range as $number) {
      $isSimple = true; //переменная-флаг, который будет указывать, является ли число простым

      for ($i = 2; $i <= sqrt($number); $i++) {
        // Проверяем делимость числа на все числа от 2 до квадратного корня из этого числа
        if ($number % $i == 0) {
          $isSimple = false; // переключаем флаг в положение false 
          break;
        }
      }

      if ($number >= 2 && $isSimple) { // Если число больше или равно 2 и является простым, добавляем его в массив простых чисел
        $simpleNumbers[] = $number;
      }
    }
    return $simpleNumbers;
  } else {
    throw new Exception("переданны отрицательные числа");
  }
}


/*===========================================================================================*/


function createTrapeze(array $a): array {
    // Проверяем, что количество элементов в массиве кратно 3
    if (count($a) % 3 !== 0) {
        throw new Exception("количество элементов массива не кратно 3");
    } else {
        $result = [];
        for ($i = 0; $i < count($a); $i += 3) {
            $trapeze = [
                'a' => $a[$i],
                'b' => $a[$i + 1],
                'c' => $a[$i + 2],
            ];

            $result[] = $trapeze;
        }

        return $result;
    }
}


/*===========================================================================================*/



function squareTrapeze($a) {
    foreach ($a as $key => $trapeze) { 
        if (isset($trapeze['a'], $trapeze['b'], $trapeze['c'])) { // Проверяем, существуют ли ключи 'a', 'b' и 'c' в текущем элементе массива
            $a[$key]['s'] = (($trapeze['a'] + $trapeze['b']) / 2) * $trapeze['c']; // добавляем ключ с результатом расчета
        }
    }
    return $a;  // Возвращаем измененный массив
}



/*===========================================================================================*/



function getSizeForLimit( array $a,float $b): array {
    $result = null; // переменная для трапеции с максимальной площадью
    $maxArea = 0; // переменная для  максимальной площади

    foreach ($a as $trapeze) {
        if ($trapeze['s'] <= $b && $trapeze['s'] > $maxArea) {
            $maxArea = $trapeze['s'];
            $result = $trapeze;
        }
    }

    return $result;
}

/*===========================================================================================*/


function getMin(array $a): float {
    if (empty($a)) {
        throw new Exception("массив пустой");
    }

    $minValue = reset($a); //  минимального значением  на старте будет первый элемент массива
    foreach ($a as $value) {
        if ($value < $minValue) {
            $minValue = $value;
        }
    }

    return $minValue;
}


/*===========================================================================================*/




function printTrapeze($a) {
    // Начало таблицы
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>a</th><th>b</th><th>c</th><th>s</th></tr>";

    // Перебор каждого элемента массива
    foreach ($a as $trapeze) {
        // Проверка, является ли площадь трапеции нечетной
        $isOdd = $trapeze['s'] % 2 != 0;

        // Если площадь нечетная, выделить строку
        if ($isOdd) {
            echo "<tr style='background: #edb9fa;'>";
        } else {
            echo "<tr>";
        }

        // Вывод значений a, b, c и s в таблицу
        echo "<td>{$trapeze['a']}</td>";
        echo "<td>{$trapeze['b']}</td>";
        echo "<td>{$trapeze['c']}</td>";
        echo "<td>{$trapeze['s']}</td>";
        echo "</tr>";
    }

    
    echo "</table>";
}




/*========================================================================================*/

abstract class BaseMath {
    public function exp1($a, $b, $c) {
      return $a * pow($b, $c);
    }
  
    public function exp2($a, $b, $c) {
      return pow($a / $b, $c);
    }
    abstract public function getValue();
  }
  
  class F1 extends BaseMath {
    private $a;
    private $b;
    private $c;
    private $value;
  
    public function __construct($a, $b, $c) {
      $this->a = $a;
      $this->b = $b;
      $this->c = $c;
      $this->calculateValue();
    }
  
    private function calculateValue() {
      $minValue = min($this->a, $this->b, $this->c);
      $this->value =
        $this->exp1($this->a, $this->b, $this->c) +
        pow(($this->a / $this->c) % 3, $minValue);
    }
  
    public function getValue() {
      return $this->value;
    }
  }
  