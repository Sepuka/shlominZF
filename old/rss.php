<?php
/**
 * $Id: rss.php 129 2011-05-17 13:16:39Z Sepuka $
 */
header('Content-Type: text/xml; charset=utf-8');
header('Content-Language: ru');

require_once(dirname(__FILE__) . '/models/db.php');                  // Работа с БД

class RSS {
	const LIMIT_LAST_ATRICLES						=	10;								# Ограничение количества статей
	const LIMIT_CONTENT								=	250;							# Ограничение длины содержимого
    const title                                     =   'Новости блога шломин.рф';
    const link                                      =   'http://шломин.рф';
    const description                               =   'Бложек Максимушки';
    const webMaster									=	'admin@шломин.рф';
    const managingEditor							=	'admin@шломин.рф';

    /**
     * Сборка RSS
     *
     * @return string
     */
    public static function buildRSS() {
        $DOM = new DOMDocument('1.0', 'utf-8');
        $RSS = $DOM->createElement('RSS');
        $attr = $DOM->createAttribute('version');
        $attr->appendChild($DOM->createTextNode('2.0'));
        $RSS->appendChild($attr);
        $DOM->appendChild($RSS);

        $channel = $DOM->createElement('channel');
        $RSS->appendChild($channel);
        $title = $DOM->createElement('title');
        $title->appendChild($DOM->createTextNode(self::title));
        $channel->appendChild($title);
        $link = $DOM->createElement('link');
        $link->appendChild($DOM->createTextNode(self::link));
        $channel->appendChild($link);
        $description = $DOM->createElement('description');
        $description->appendChild($DOM->createCDATASection(self::description));
        $channel->appendChild($description);
        $language = $DOM->createElement('language');
        $language->appendChild($DOM->createTextNode('ru'));
        $channel->appendChild($language);
        $me = $DOM->createElement('managingEditor');
        $me->appendChild($DOM->createTextNode(self::managingEditor));
        $channel->appendChild($me);
        $webmaster = $DOM->createElement('webMaster');
        $webmaster->appendChild($DOM->createTextNode(self::webMaster));
        $channel->appendChild($webmaster);

        foreach (self::getListLastArticles() as $key => $value) {
        	if ((empty($value['headline'])) || (empty($value['content'])))
        		continue;
        	$item = $DOM->createElement('item');
        	$title = $DOM->createElement('title');
        	$title->appendChild($DOM->createCDATASection($value['headline']));
        	$item->appendChild($title);
        	$item->appendChild($DOM->createElement('link', sprintf('http://%s/index.php?act=page&amp;id=%d', $_SERVER['HTTP_HOST'], $key)));
        	$desc = $DOM->createElement('description');
        	$desc->appendChild($DOM->createCDATASection($value['content']));
        	$item->appendChild($desc);
        	$item->appendChild($DOM->createElement('pubDate', date('r', $value['date'])));
        	$channel->appendChild($item);
        }
        return $DOM->saveXML();
    }

    /**
     * Получение N последних статей
     *
     * @return array
     */
    protected static function getListLastArticles() {
        $query = sprintf("SELECT ROWID, headline, content, date FROM articles ORDER BY date DESC LIMIT 0,%d;", self::LIMIT_LAST_ATRICLES);
        $resource = sqlite_query($query, SQlite::getConn(), SQLITE_ASSOC);
        $result = array();
        while ($data = sqlite_fetch_array($resource, SQLITE_ASSOC)) {
        	$content = str_replace(array(chr(10), chr(13)), ' ', strip_tags(htmlspecialchars_decode($data['content'])));
        	$content = preg_replace("/&#?[a-z0-9]+;/i", "", $content); 
            $result[$data['ROWID']] = array(
            	'headline'	=> htmlspecialchars_decode($data['headline']),
            	'content'	=> (strlen($content) > self::LIMIT_CONTENT) ? substr($content, 0, strpos($content, ' ', self::LIMIT_CONTENT)) . ' ...' : $content,
            	'date'		=> $data['date']
            );
        }
        return $result;
    }
}

// Кеширование страницы
$cachefile = "cache/{$_SERVER['REQUEST_URI']}.cache";

if (file_exists($cachefile)) {
    if ($fp = fopen($cachefile, 'rb')) {
    	fpassthru($fp);
    	fclose($fp);
    }
    $f = stat($cachefile);
    if (time() - $f[9] > 600)
        unlink($cachefile);
    return;
} else {
    $cachefile_tmp = $cachefile . '.' . getmypid();
    $cachefp = fopen($cachefile_tmp, "w");
    ob_start();
}

// Этот код будет выполнен в случае отсутствия копии в кеше
$rss = new RSS();
echo $rss->buildRSS();

if ($cachefp) {
    $file = ob_get_contents();
    fwrite($cachefp, $file);
    fclose($cachefp);
    rename($cachefile_tmp, $cachefile);
    ob_end_flush();
}
?>