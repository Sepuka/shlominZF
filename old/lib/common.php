<?php
    /**
     * Сборка страниц
     * 
     * Загружает основную страницу и добавляет в нее подшаблоны и переменные
     *
     * @param array $tmpl
     * @param array $vars
     * @param string $path уточнение пути к файлам шаблонов
     * @return string
     */
    function buildPage($tmpl, $vars, $path = '.')
    {
        $page = key($tmpl);
        $mainTemplate = loadTemplate($page, $path);
        $template = str_replace(array_keys($tmpl[$page]), loadTemplate(array_values($tmpl[$page]), $path), $mainTemplate);
        return str_replace(array_keys($vars), array_values($vars), $template);
    }

    /**
     * Возвращает шаблон
     * 
     * Возвращает шаблон страницы или массива страниц в виде массива
     *
     * @param mixed $object
     * @param string $path уточнение пути к файлам шаблонов
     * @return array
     */
    function loadTemplate($object, $path = 'templates/')
    {
        static $pages = array();
        if (is_array($object)) {
            foreach ($object as $templates)
                $pages[] = loadTemplate($templates, $path);
        } elseif (is_string($object)) {
            return file_get_contents($path . $object);
        }
        return $pages;
    }
?>