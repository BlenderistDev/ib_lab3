<?php
class Bigram 
{
    /** @var string количество колонок в таблице */
    private const TABLE_COLS_NUM = 6;

    /** @var array таблица для шифра */
    private $aBigramTable = [];

    /** @var array список букв языка */
    private $aLanguageLetters = [
      'А', 'Б', 'В', 'Г', 'Д', 'Е', 
      'Ё', 'Ж', 'З', 'И', 'Й', 'К', 
      'Л', 'М', 'Н', 'О', 'П', 'Р', 
      'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 
      'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 
      'Э', 'Ю', 'Я', '.', ',', '?',
      '!', '-', ':', '(', ')', ' '
    ];

    /** @var array закэшированные строки/колонки для каждой буквы */
    private $aLetterCache = [];

    /**
     * В конструкторе создаем таблицу для шифрования
     * и заполняем кэш
     * @param string $sCodePhrase кодовая фраза для создания таблицы
     */
    public function __construct($sCodePhrase)
    {
        // создаем последовательность символов языка с использованием кодовой фразы
        $aCodePhraseLetters = $this->mbStringToArray($sCodePhrase);
        $aCodePhraseLetters = array_unique($aCodePhraseLetters);
        $aLanguageLetterInCorrectOrder = array_unique(array_merge($aCodePhraseLetters, $this->aLanguageLetters));

        // заполняем таблицу для шифровки и кэш для быстрого доступа к данным о колонке/строке буквы
        $iRow = 0;
        foreach($aLanguageLetterInCorrectOrder as $sLetter) {
            $this->aBigramTable[$iRow] []= $sLetter;
            $this->aLetterCache[$sLetter]['col'] = count($this->aBigramTable[$iRow]) - 1;
            $this->aLetterCache[$sLetter]['row'] = $iRow;
            if (count($this->aBigramTable[$iRow]) === self::TABLE_COLS_NUM) {
                $iRow++;
            }
        }
    }



    /**
     * шифрование
     * @param string $sText текст для шифрования
     * @return string
     */
    public function crypt($sText) 
    {
        $aLettersArray = $this->mbStringToArray($sText);
        $sResult = '';
        for ($i=0; $i < count($aLettersArray); $i=$i+2) {
            $sFirstLetter = $aLettersArray[$i];
            // если количество букв нечетно, то в конце добавим пробел
            $sSecondLetter = ' ';
            if (isset($aLettersArray[$i+1])) {
                $sSecondLetter = $aLettersArray[$i+1];
            }
            $iFirstLetterCol = $this->aLetterCache[$sFirstLetter]['col'];
            $iFirstLetterRow = $this->aLetterCache[$sFirstLetter]['row'];
            $iSecondLetterCol = $this->aLetterCache[$sSecondLetter]['col'];
            $iSecondLetterRow = $this->aLetterCache[$sSecondLetter]['row'];
            if ($iFirstLetterCol === $iSecondLetterCol) {
                // если буквы в одном столбце
                $sResult .= $this->aBigramTable[$this->incrementRow($iFirstLetterRow)][$iFirstLetterCol];
                $sResult .= $this->aBigramTable[$this->incrementRow($iSecondLetterRow)][$iSecondLetterCol];
            } elseif ($iFirstLetterRow === $iSecondLetterRow) {
                // если буквы в одной строке
                $sResult .= $this->aBigramTable[$iFirstLetterRow][$this->incrementCol($iFirstLetterCol)];
                $sResult .= $this->aBigramTable[$iSecondLetterRow][$this->incrementCol($iSecondLetterCol)];
            } else {
                $sResult .= $this->aBigramTable[$iFirstLetterRow][$iSecondLetterCol];
                $sResult .= $this->aBigramTable[$iSecondLetterRow][$iFirstLetterCol];
            }
        }
        return $sResult;
    }

    /**
     * Расшифровывает сообщение
     * @param string $sText текст для расшифровки
     * @return string
     */
    public function decrypt($sText)
    {
      $aLettersArray = $this->mbStringToArray($sText);
      $sResult = '';
      for ($i=0; $i < count($aLettersArray); $i=$i+2) {
          $sFirstLetter = $aLettersArray[$i];
          // если количество букв нечетно, то в конце добавим пробел
          $sSecondLetter = ' ';
          if (isset($aLettersArray[$i+1])) {
              $sSecondLetter = $aLettersArray[$i+1];
          }
          $iFirstLetterCol = $this->aLetterCache[$sFirstLetter]['col'];
          $iFirstLetterRow = $this->aLetterCache[$sFirstLetter]['row'];
          $iSecondLetterCol = $this->aLetterCache[$sSecondLetter]['col'];
          $iSecondLetterRow = $this->aLetterCache[$sSecondLetter]['row'];
          if ($iFirstLetterCol === $iSecondLetterCol) {
              // если буквы в одном столбце
              $sResult .= $this->aBigramTable[$this->decrementRow($iFirstLetterRow)][$iFirstLetterCol];
              $sResult .= $this->aBigramTable[$this->decrementRow($iSecondLetterRow)][$iSecondLetterCol];
          } elseif ($iFirstLetterRow === $iSecondLetterRow) {
              // если буквы в одной строке
              $sResult .= $this->aBigramTable[$iFirstLetterRow][$this->decrementCol($iFirstLetterCol)];
              $sResult .= $this->aBigramTable[$iSecondLetterRow][$this->decrementCol($iSecondLetterCol)];
          } else {
              $sResult .= $this->aBigramTable[$iFirstLetterRow][$iSecondLetterCol];
              $sResult .= $this->aBigramTable[$iSecondLetterRow][$iFirstLetterCol];
          }
      }
      return $sResult;
    }

    /**
     * Выводит таблицу, используемую для шифрования
     */
    public function printBigramTable()
    {
        foreach ($this->aBigramTable as $sLetterRow) {
            print(implode('', $sLetterRow) . PHP_EOL);
        }
    }

    /**
     * Увеличивает индекс строки
     * @param int $iRowIndex
     * @return int
     */
    private function incrementRow($iRowIndex)
    {
        $iIncrementedRowIndex = $iRowIndex + 1;
        if ($iIncrementedRowIndex >= count($this->aBigramTable)) {
            $iIncrementedRowIndex = 0;
        }
        return $iIncrementedRowIndex;
    }

    /**
    * Увеличивает индекс колонки
    * @param int $iColIndex
    * @return int
    */
    private function incrementCol($iColIndex)
    {
        $iIncrementedColIndex = $iColIndex + 1;
        if ($iIncrementedColIndex >= self::TABLE_COLS_NUM) {
            $iIncrementedColIndex = 0;
        }
        return $iIncrementedColIndex;
    }

    /**
     * Уменьшает индекс строки
     * @param int $iRowIndex
     * @return int
     */
    private function decrementRow($iRowIndex)
    {
        $iDecrementedRowIndex = $iRowIndex - 1;
        if ($iDecrementedRowIndex < 0) {
            $iDecrementedRowIndex = count($this->aBigramTable) - 1;
        }
        return $iDecrementedRowIndex;
    }

    /**
     * Уменьшает индекс колонки
     * @param int $iColIndex
     * @return int
     */
    private function decrementCol($iColIndex)
    {
        $iDecrementedColIndex = $iColIndex - 1;
        if ($iDecrementedColIndex < 0) {
            $iDecrementedColIndex = self::TABLE_COLS_NUM - 1;
        }
        return $iDecrementedColIndex;
    }

    /**
     * Разбивает многобайтовую строку на массив символов
     * @param string $sString - строка для создания массива
     * @param $sEncoding - кодировка строки
     * @return array
     */
    private function mbStringToArray($sString, $sEncoding = 'UTF-8')
    {
        $strlen = mb_strlen($sString);
        while ($strlen) {
            $aResult[] = mb_substr($sString, 0, 1, $sEncoding);
            $sString = mb_substr($sString, 1, $strlen, $sEncoding);
            $strlen = mb_strlen($sString, $sEncoding);
        }
        return $aResult;
    }
}

$oBigram = new Bigram("БЕЗОПАСНОСТЬ");
$oBigram->printBigramTable();
$sCryptedText = $oBigram->crypt("УРА, Я ДОДЕЛАЛ ЛАБУ");
$sDecriptedText = $oBigram->decrypt($sCryptedText);
var_dump($sCryptedText);
var_dump($sDecriptedText);
