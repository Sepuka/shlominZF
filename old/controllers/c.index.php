<?php
/**
 * Контроллер по-умолчанию
 * 
 * $Id: c.index.php 129 2011-05-17 13:16:39Z Sepuka $
 */

require_once(dirname(__FILE__) . '/../views/v.index.php');                  // Представление

class c_index {
    const DEFAULT_TEMPLATE                          =   'index.tpl';        // шаблон по-умолчанию

    public function __construct()
    {
		if (empty($_GET['articleID']))
            $_GET['articleID'] = 0;
        if (empty($_GET['id']))
        	$_GET['id'] = 0;
        echo $this->router();
    }

    public function getIndexPage()
    {
        return v_index::getPage('index');
    }

    protected function router()
    {
    	// Параметр act может быть source для исходных текстов которые показываются поисковикам
    	// и page, в этом случае ни чего не делаем, страницу загрузит javascript, это для пользователя.
    	if (!empty($_GET['act']))
    		if ($_GET['act'] == 'source')
    			return v_index::getPage('source');
        if (!empty($_GET['id'])) {
            switch ($_GET['id']) {
                case 'parentCategories' :                               // Получение списка категорий
                    return v_index::getPage('parentCategories');
                    break;
                case 'loadContent' :
                    return v_index::getPage('loadContent');             // Получение тела запрашиваемой страницы
                    break;
                default: return $this->getIndexPage();
            }
        }
        return $this->getIndexPage();
    }
}
?>