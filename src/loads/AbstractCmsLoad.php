<?php
/**
 * General parent load for all admin load classes
 *
 * @author Mikael Mkrtchyan
 * @site   http://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @year   2012-2017
 * @package ngs.AdminTools.loads
 * @version 6.5.0
 *
 **/

namespace ngs\AdminTools\loads;

use ngs\AdminTools\exceptions\NoAccessException;
use ngs\event\EventManager;
use ngs\AdminTools\util\LoggerFactory;
use ngs\Dispatcher;
use ngs\exceptions\NgsErrorException;
use ngs\request\AbstractLoad;
use ngs\AdminTools\util\NavigationUtil;
use Monolog\Logger;

abstract class AbstractCmsLoad extends AbstractLoad
{

    protected $im_limit = 30;
    protected $im_pagesShowed = 9;
    protected $im_mobilePagesShowed = 3;
    private ?Logger $logger = null;
    private EventManager $eventManager;

    public function __construct()
    {
        $this->logger = LoggerFactory::getLogger(get_class($this), get_class($this));
        $this->eventManager = EventManager::getInstance();
    }

    /**
     * returns event manager
     * 
     * @return EventManager|\ngs\AdminTools\managers\event\FilterManager|null
     */
    public function getEventManager() {
        return $this->eventManager;
    }


    /**
     * @return int
     */
    public function getLimit(): int
    {
        if (is_numeric($this->args()->limit) && $this->args()->limit > 0) {
            return $this->args()->limit;
        }
        return $this->im_limit;
    }


    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->getLimit() * ($this->getCurrentPage() - 1);
    }


    /**
     * @return int
     */
    public function getPagesShowed(): int
    {
        if ($this->isRequestDeviceMobile()) {
            return $this->im_mobilePagesShowed;
        }

        return $this->im_pagesShowed;
    }


    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        $page = 1;
        if (is_numeric($this->args()->page) && $this->args()->page > 1) {
            $page = $this->args()->page;
        }
        return $page;
    }


    /**
     * redirects to route
     *
     * @param string $uri
     */
    public function redirectTo(string $uri) {
        if (NGS()->getHttpUtils()->isAjaxRequest()) {
            NGS()->getTemplateEngine()->setHttpStatusCode(301);
            NGS()->getTemplateEngine()->assignJson('redirect_to', NavigationUtil::getFullLink($uri));
            NGS()->getTemplateEngine()->display(true);
            exit;
        }
        header("Location: " . NavigationUtil::getFullLink($uri), true, 301);exit;
    }


    /**
     * @param int $itemsCount
     */
    public function initPaging(int $itemsCount): void
    {
        $limit = $this->getLimit();
        $pagesShowed = $this->getPagesShowed();
        $page = $this->getCurrentPage();
        if ($limit < 1) {
            return;
        }
        $pageCount = ceil($itemsCount / $limit);
        $centredPage = ceil($pagesShowed / 2);
        $pStart = 0;
        if (($page - $centredPage) > 0) {
            $pStart = $page - $centredPage;
        }
        if (($page + $centredPage) >= $pageCount) {
            $pEnd = $pageCount;
            if (($pStart - ($page + $centredPage - $pageCount)) > 0) {
                $pStart = $pStart - ($page + $centredPage - $pageCount) + 1;
            }
        } else {
            $pEnd = $pStart + $pagesShowed;
            if ($pEnd > $pageCount) {
                $pEnd = $pageCount;
            }
        }
        $this->addParam('pageCount', $pageCount);
        $this->addParam('page', $page);
        $this->addParam('pStart', $pStart);
        $this->addParam('pEnd', $pEnd);
        $this->addParam('limit', $limit);
        $this->addParam('itemsCount', $itemsCount);
        $this->addParam('itemsPerPageOptions', [15, 30, 50, 100]);

        $jsParams = ['page' => $page, 'limit' => $limit, 'itemsCount' => $itemsCount, 'offset' => $this->getOffset(),
            'pagesShowed' => $this->getPagesShowed(), 'searchKey' => $this->args()->search_key,
            'sorting' => $this->args()->sorting, 'ordering' => strtolower($this->args()->ordering)];
        $this->addJsonParam('pageParams', $jsParams);
    }


    /**
     * returns logger instance
     *
     * @return Logger|null
     */
    protected function getLogger() {
        return $this->logger;
    }


    /**
     * @return string
     */
    public function getPermalink(): string
    {
        return $this->getCmsPermalink();
    }


    /**
     * @return string
     */
    public function getCmsPermalink(): string
    {
        return '';
    }


    /**
     * @return mixed|null
     * @throws NgsErrorException
     */
    public function getRequestGroup()
    {
        if (!NGS()->get('REQUEST_GROUP') === null) {
            throw new NgsErrorException('please set in constats REQUEST_GROUP');
        }
        return NGS()->get('REQUEST_GROUP');
    }

    /**
     * @throws NoAccessException
     * @throws \ngs\exceptions\DebugException
     */
    public function onNoAccess():void {
        $exception = new NoAccessException("home", -1);
        NGS()->getSessionManager()->logout();
        $exception->setRedirectTo("login");
        throw $exception;
    }


    /**
     * @return array
     */
    public function getRequestAllowedGroups() {
        if(method_exists($this, 'getManager') && $this->getManager() && $this->getManager()->getMapper()) {
            $manager = $this->getManager();
            $mapper = method_exists($manager, 'getChildMapper') && $manager->getChildMapper() ?  $manager->getChildMapper() : $manager->getMapper();
            $dto = $mapper->createDto();
            $accessInfo = $dto->getAccess('id');
            if(!$accessInfo) {
                return [];
            }
            $read = $accessInfo['read'];
            $className = get_class($this);
            if(strpos($className, "AddLoad") !== false || strpos($className, "EditLoad") !== false) {
                $write = $accessInfo['write'];
                $notAllowed = array_merge($read['not_allowed'], $write['not_allowed']);
                $allowed = array_merge($read['allowed'], $write['allowed']);
                $allowed = array_diff($allowed, $notAllowed);
                $allowed = array_values($allowed);
                return ['not_allowed' => $notAllowed, 'allowed' => $allowed];
            }
            return $read;
        }
        $adminGroup = NGS()->getSessionManager()->getUserGroupByName('admin');
        if($adminGroup){
            return ["allowed" => [$adminGroup->getId()]];
        }
        return [];
    }

    /**
     * @return bool
     */
    private function isRequestDeviceMobile(): bool
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }
}
