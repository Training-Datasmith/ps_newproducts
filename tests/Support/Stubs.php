<?php

class Configuration
{
    private static $values = array();

    private static $updateResult = true;

    private static $deleteResult = true;

    public static function reset()
    {
        HelperForm::$lastInstance = null;
        self::$values = array();
        self::$updateResult = true;
        self::$deleteResult = true;
    }

    public static function setUpdateResult($result)
    {
        self::$updateResult = (bool) $result;
    }

    public static function setDeleteResult($result)
    {
        self::$deleteResult = (bool) $result;
    }

    public static function get($key)
    {
        if (array_key_exists($key, self::$values)) {
            return self::$values[$key];
        }

        return false;
    }

    public static function set($key, $value)
    {
        self::$values[$key] = $value;
    }

    public static function updateValue($key, $value)
    {
        if (!self::$updateResult) {
            return false;
        }
        self::$values[$key] = $value;

        return true;
    }

    public static function deleteByName($key)
    {
        if (!self::$deleteResult) {
            return false;
        }
        unset(self::$values[$key]);

        return true;
    }
}

class Tools
{
    private static $request = array();

    public static function reset()
    {
        self::$request = array();
    }

    public static function setRequest(array $request)
    {
        self::$request = $request;
    }

    public static function getValue($key, $default = false)
    {
        if (array_key_exists($key, self::$request)) {
            return self::$request[$key];
        }

        return $default;
    }

    public static function isSubmit($key)
    {
        return array_key_exists($key, self::$request);
    }

    public static function getAdminTokenLite($controller)
    {
        return 'token-' . $controller;
    }

    public static function enableCache()
    {
    }

    public static function restoreCacheSettings()
    {
    }
}

class Hook
{
    public static $execCalls = array();

    public static function reset()
    {
        self::$execCalls = array();
    }

    public static function exec($hookName)
    {
        self::$execCalls[] = $hookName;
    }
}

class Language
{
    public $id;

    public function __construct($id)
    {
        $this->id = (int) $id;
    }
}

class HelperForm
{
    /** @var HelperForm */
    public static $lastInstance;

    public $show_toolbar;

    public $table;

    public $default_form_language;

    public $allow_employee_form_lang;

    public $identifier;

    public $submit_action;

    public $currentIndex;

    public $token;

    public $tpl_vars;

    public $lastForms = array();

    public function __construct()
    {
        self::$lastInstance = $this;
    }

    public function generateForm($forms)
    {
        $this->lastForms = $forms;

        return 'FORM:' . md5(serialize($forms));
    }
}

class Product
{
    public static $returnValue = false;

    public static $calls = array();

    public static function reset()
    {
        self::$returnValue = false;
        self::$calls = array();
    }

    public static function getNewProducts($idLang, $page, $limit)
    {
        self::$calls[] = array((int) $idLang, (int) $page, (int) $limit);

        return self::$returnValue;
    }
}

class ImageRetriever
{
    public $link;

    public function __construct($link)
    {
        $this->link = $link;
    }
}

class PriceFormatter
{
}

class ProductColorsRetriever
{
}

class ProductPresenterFactory
{
    public $context;

    public function __construct($context)
    {
        $this->context = $context;
    }

    public function getPresentationSettings()
    {
        return array('settings' => true);
    }
}

if (!defined('PS_TEST_ASSEMBLER_SINGLE')) {
    class ProductAssembler
    {
        public $context;

        public $assembleProductsCalls = 0;

        public $assembleProductCalls = 0;

        public function __construct($context)
        {
            $this->context = $context;
        }

        public function assembleProducts($products)
        {
            ++$this->assembleProductsCalls;
            $out = array();
            foreach ($products as $p) {
                $out[] = array('assembled' => true, 'id' => $p['id']);
            }

            return $out;
        }

        public function assembleProduct($product)
        {
            ++$this->assembleProductCalls;

            return array('assembled' => true, 'id' => $product['id']);
        }
    }
} else {
    class ProductAssembler
    {
        public $context;

        public $assembleProductCalls = 0;

        public function __construct($context)
        {
            $this->context = $context;
        }

        public function assembleProduct($product)
        {
            ++$this->assembleProductCalls;

            return array('assembled' => true, 'id' => $product['id']);
        }
    }
}

class Link
{
    public function getPageLink($page)
    {
        return 'https://shop.test/' . $page;
    }

    public function getAdminLink($controller, $ajax = false)
    {
        return 'index.php?controller=' . $controller;
    }
}

class Shop
{
    public static function isFeatureActive()
    {
        return false;
    }
}

class Group
{
    public static function isFeatureActive()
    {
        return false;
    }
}

class Currency
{
    public static function isMultiCurrencyActivated()
    {
        return false;
    }
}

class Country
{
}

class Customer
{
}

class SmartyStub
{
    public $cache = array();

    public $assigned = array();

    public $fetchResult = 'FETCHED';

    public function reset()
    {
        $this->cache = array();
        $this->assigned = array();
        $this->fetchResult = 'FETCHED';
    }

    public function assign($vars)
    {
        $this->assigned = $vars;
    }

    public function createTemplate($templatePath, $cacheId, $compileId, $smarty)
    {
        return new SmartyTemplateStub($this, $templatePath, $cacheId, $compileId);
    }

    public function clearCache($template, $cacheId = null, $compileId = null)
    {
        $removed = 0;
        foreach ($this->cache as $key => $_) {
            list($tpl, $cid, $comp) = explode("\0", $key, 3);
            if ($template !== null && $tpl !== $template) {
                continue;
            }
            if ($compileId !== null && $comp !== (string) $compileId) {
                continue;
            }
            if ($cacheId !== null) {
                $parts = explode('|', $cid);
                $prefix = explode('|', $cacheId);
                $match = true;
                for ($i = 0; $i < count($prefix); ++$i) {
                    if (!isset($parts[$i]) || $parts[$i] !== $prefix[$i]) {
                        $match = false;
                        break;
                    }
                }
                if (!$match) {
                    continue;
                }
            }
            unset($this->cache[$key]);
            ++$removed;
        }

        return $removed;
    }

    public function clearAllCache()
    {
        $count = count($this->cache);
        $this->cache = array();

        return $count;
    }

    public function storeCache($templatePath, $cacheId, $compileId, $content)
    {
        $key = $templatePath . "\0" . $cacheId . "\0" . $compileId;
        $this->cache[$key] = $content;
    }

    public function isCachedEntry($templatePath, $cacheId, $compileId)
    {
        $key = $templatePath . "\0" . $cacheId . "\0" . $compileId;

        return isset($this->cache[$key]);
    }
}

class SmartyTemplateStub
{
    private $smarty;

    private $templatePath;

    private $cacheId;

    private $compileId;

    public function __construct(SmartyStub $smarty, $templatePath, $cacheId, $compileId)
    {
        $this->smarty = $smarty;
        $this->templatePath = $templatePath;
        $this->cacheId = $cacheId;
        $this->compileId = $compileId;
    }

    public function isCached($template, $cacheId, $compileId)
    {
        return $this->smarty->isCachedEntry($this->templatePath, $this->cacheId, $this->compileId);
    }

    public function fetch()
    {
        if ($this->smarty->isCachedEntry($this->templatePath, $this->cacheId, $this->compileId)) {
            $key = $this->templatePath . "\0" . $this->cacheId . "\0" . $this->compileId;

            return $this->smarty->cache[$key];
        }

        $content = $this->smarty->fetchResult;
        $this->smarty->storeCache($this->templatePath, $this->cacheId, $this->compileId, $content);

        return $content;
    }
}

class ControllerStub
{
    public function getLanguages()
    {
        return array(array('id_lang' => 1));
    }
}

class Context
{
    public static $instance;

    public $language;

    public $link;

    public $controller;

    public $shop;

    public $country;

    public $smarty;

    public function __construct()
    {
        $this->language = new stdClass();
        $this->language->id = 1;
        $this->link = new Link();
        $this->controller = new ControllerStub();
        $this->shop = new stdClass();
        $this->shop->theme = new stdClass();
        $this->shop->theme->name = 'classic';
        $this->country = new stdClass();
        $this->country->id = 8;
        $this->smarty = new SmartyStub();
    }

    public static function reset()
    {
        self::$instance = new self();
    }

    public static function getContext()
    {
        if (!self::$instance) {
            self::reset();
        }

        return self::$instance;
    }

    public function getTranslator()
    {
        return new TranslatorStub();
    }
}

class TranslatorStub
{
}

class Module
{
    public $name = 'ps_newproducts';

    public $tab = 'front_office_features';

    public $author = 'PrestaShop';

    public $version = '1.0.5';

    public $need_instance = 0;

    public $bootstrap = true;

    public $ps_versions_compliancy = array();

    public $displayName;

    public $description;

    public $context;

    public $smarty;

    public $table = 'module';

    public $identifier = 'id_module';

    public static $installResult = true;

    public static $uninstallResult = true;

    public static $registerHookResults = array();

    public static $registeredHooks = array();

    public static $clearCacheCalls = array();

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
    }

    public static function resetStatics()
    {
        self::$installResult = true;
        self::$uninstallResult = true;
        self::$registerHookResults = array();
        self::$registeredHooks = array();
        self::$clearCacheCalls = array();
    }

    public function install()
    {
        return self::$installResult;
    }

    public function uninstall()
    {
        return self::$uninstallResult;
    }

    public function registerHook($hook)
    {
        if (isset(self::$registerHookResults[$hook]) && self::$registerHookResults[$hook] === false) {
            return false;
        }
        self::$registeredHooks[] = $hook;

        return true;
    }

    protected function getCacheId($name = null)
    {
        $cache_array = array();
        $cache_array[] = $name !== null ? $name : $this->name;
        $cache_array[] = (int) $this->context->country->id;

        return implode('|', $cache_array);
    }

    public function isCached($template, $cache_id = null, $compile_id = null)
    {
        if ($compile_id === null) {
            $compile_id = $this->getDefaultCompileId();
        }

        return $this->context->smarty->isCachedEntry($template, $cache_id, $compile_id);
    }

    public function fetch($templatePath, $cache_id = null, $compile_id = null)
    {
        if ($compile_id === null) {
            $compile_id = $this->getDefaultCompileId();
        }

        $tpl = $this->context->smarty->createTemplate($templatePath, $cache_id, $compile_id, $this->smarty);

        return $tpl->fetch();
    }

    protected function getDefaultCompileId()
    {
        return $this->context->shop->theme->name;
    }

    public function getTemplatePath($template)
    {
        if (strpos($template, 'module:') !== false) {
            return null;
        }

        return '/fake/' . $template;
    }

    protected function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        self::$clearCacheCalls[] = array($template, $cache_id, $compile_id);
        if ($compile_id === null) {
            $compile_id = $this->getDefaultCompileId();
        }
        if ($cache_id === null) {
            $cache_id = $this->name;
        }
        $resolved = $this->getTemplatePath($template);

        return ToolsStubModule::clearCache($this->context->smarty, $resolved, $cache_id, $compile_id);
    }

    public function trans($id, array $parameters = array(), $domain = null)
    {
        return $id;
    }

    public function displayError($message)
    {
        return '[error]' . $message;
    }

    public function displayConfirmation($message)
    {
        return '[ok]' . $message;
    }
}

class ToolsStubModule extends Tools
{
    public static function clearCache($smarty, $tpl, $cache_id, $compile_id)
    {
        if ($smarty === null) {
            return null;
        }
        if ($tpl === false && $cache_id === null && $compile_id === null) {
            return $smarty->clearAllCache();
        }
        if ($tpl === null && $cache_id !== null) {
            return $smarty->clearCache(null, $cache_id, $compile_id);
        }

        return $smarty->clearCache($tpl, $cache_id, $compile_id);
    }
}
