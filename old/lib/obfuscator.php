<?php
	/**
     * Функция удаляет переносы строк, пробелы, комментарии вида "//" и HTML-комментарии
     *
     * @param string $s
     * @return string
     */
    function obfuscator($s)
    {
        $s = strtr($s, array(chr(13) => '', chr(9) => ''));
        $pos = 0;
        $quote = false;
        $length = strlen($s);
        while ($pos < $length) {
            if (strpos($s, chr(10), $pos) === false)
                break;
            if (($s[$pos] == '"') || ($s[$pos] == "'"))
                if ($s[$pos] == $quote) $quote = false; else $quote = $s[$pos];
            if (!$quote) {
                if ($s[$pos] == '/') {
                    if ($s[$pos + 1] == '/')
                        $s = substr($s, 0, $pos) . substr($s, strpos($s, chr(10), $pos));
                }
                if ($s[$pos] == '!') {
                    if (($s[$pos - 1] == '<') && ($s[$pos + 1] == '-') && ($s[$pos + 2] == '-'))
                        $s = substr($s, 0, $pos - 1) . substr($s, strpos($s, '-->', $pos) + 3);
                }
                if ($s[$pos] == ' ') {
                    if ($s[$pos + 1] == ' ') {
                        $s = substr($s, 0, $pos) . substr($s, $pos + 1);
                        $pos--;
                    }
                }
            }
            $pos++;
        }
        $pos = 0;
        $length = strlen($s);
        while ($pos <= $length) {
            if (($length = strpos($s, chr(10), $pos)) === false)
                break;
            $s = substr($s, 0, $length) . substr($s, $length + 1);
            $pos = $length;
        }
        return $s;
    }
?>