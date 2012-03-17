<?php
/**
 * Контроллер выполняющий роль Sitemap
 * @link http://www.sitemaps.org/ru/protocol.html
 */
class SitemapController extends Zend_Controller_Action
{
    const CHANGE_FREQ   = 'monthly';

    protected $_config          = null;

    /**
    * Обработка вызовов несуществующих действий
    *
    * @param string $method
    * @param array $args
    */
    public function __call($method, $args)
    {
        $this->getResponse()->setHttpResponseCode(404);
        $this->_helper->layout->setLayout('404');
    }

    public function init()
    {
        $this->_config = Application_Model_MemcachedConfig::getInstance();
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
    }

    public function indexAction()
    {
        $urlPattern = sprintf('http://%s/article/', $_SERVER['HTTP_HOST']);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $urlset = $dom->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');
        $dom->appendChild($urlset);
        $q = Application_Model_Articles::sitemap();
        foreach (Application_Model_Articles::sitemap() as $resource) {
            $url = $dom->createElement('url');
            $url->appendChild($dom->createElement('loc', $urlPattern . $resource->id));
            $url->appendChild($dom->createElement('lastmod', $resource->changeDate));
            $url->appendChild($dom->createElement('changefreq', self::CHANGE_FREQ));
            $urlset->appendChild($url);
        }
        $this->getResponse()
            ->setHeader('Content-Type', 'text/xml; charset=UTF-8')
            ->appendBody($dom->saveXML());
    }
}