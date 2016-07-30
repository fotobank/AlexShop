<?PHP

/**
 * Simpla CMS
 * @copyright     2011 Denis Pikusov
 * @link          http://simp.la
 * @author        Denis Pikusov
 * Базовый класс для всех View

 */

require_once __DIR__ . '/../api/Simpla.php';

/**
 * Class View
 */
class View extends Simpla
{
    /* Смысл класса в доступности следующих переменных в любом View */
    /**
     * @var mixed
     */
    public $currency;
    /**
     * @var
     */
    public $currencies;
    /**
     * @var
     */
    public $user;
    /**
     * @var
     */
    public $group;
    /**
     * @var
     */
    public $page;

    /* Класс View похож на синглтон, храним статически его инстанс */
    /**
     * @var \View
     */
    private static $view_instance;

    /**
     * View constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // Если инстанс класса уже существует - просто используем уже существующие переменные
        if (self::$view_instance){
            $this->currency = &self::$view_instance->currency;
            $this->currencies = &self::$view_instance->currencies;
            $this->user = &self::$view_instance->user;
            $this->group = &self::$view_instance->group;
            $this->page = &self::$view_instance->page;
        } else {
            // Сохраняем свой инстанс в статической переменной,
            // чтобы в следующий раз использовать его
            self::$view_instance = $this;

            // Все валюты
            $this->currencies = $this->money->get_currencies(['enabled' => 1]);

            // Выбор текущей валюты
            if ($currency_id = $this->request->get('currency_id', 'integer')){
                $_SESSION['currency_id'] = $currency_id;
                header('Location: ' . $this->request->url(['currency_id' => null]));
            }

            // Берем валюту из сессии
            if (isset($_SESSION['currency_id']))
                $this->currency = $this->money->get_currency($_SESSION['currency_id']);
            // Или первую из списка
            else
                $this->currency = reset($this->currencies);

            // Пользователь, если залогинен
            if (isset($_SESSION['user_id'])){
                $u = $this->users->get_user((int)$_SESSION['user_id']);
                if ($u && $u->enabled){
                    $this->user = $u;
                    $this->group = $this->users->get_group($this->user->group_id);

                }
            }

            // Текущая страница (если есть)
            $subdir = substr(dirname(__DIR__), strlen($_SERVER['DOCUMENT_ROOT']));
            $page_url = trim(substr($_SERVER['REQUEST_URI'], strlen($subdir)), '/');
            if (strpos($page_url, '?') !== false)
                $page_url = substr($page_url, 0, strpos($page_url, '?'));
            $this->page = $this->pages->get_page((string)$page_url);
            $this->design->assign('page', $this->page);

            // Передаем в дизайн то, что может понадобиться в нем
            $this->design->assign('currencies', $this->currencies);
            $this->design->assign('currency', $this->currency);
            $this->design->assign('user', $this->user);
            $this->design->assign('group', $this->group);

            $this->design->assign('config', $this->config);
            $this->design->assign('settings', $this->settings);

            // Настраиваем плагины для смарти
            $this->design->smarty->registerPlugin('function', 'get_posts', [$this, 'get_posts_plugin']);
            $this->design->smarty->registerPlugin('function', 'get_brands', [$this, 'get_brands_plugin']);
            $this->design->smarty->registerPlugin('function', 'get_browsed_products', [$this, 'get_browsed_products']);
            $this->design->smarty->registerPlugin('function', 'get_featured_products', [$this, 'get_featured_products_plugin']);
            $this->design->smarty->registerPlugin('function', 'get_new_products', [$this, 'get_new_products_plugin']);
            $this->design->smarty->registerPlugin('function', 'get_discounted_products', [$this, 'get_discounted_products_plugin']);
            $this->design->smarty->registerPlugin("function", "get_uni", array($this, 'uni_plugin'));
        }
    }

    /**
     * Отображение

     */
    public function fetch()
    {
        return false;
    }

    /**
     * Плагины для смарти

     */
    public function get_posts_plugin($params, &$smarty)
    {
        if (!isset($params['visible']))
            $params['visible'] = 1;
        if (!empty($params['var']))
            $smarty->assign($params['var'], $this->blog->get_posts($params));
    }

    /**
     * @param $params
     * @param $smarty
     */
    public function get_brands_plugin($params, &$smarty)
    {
        if (!isset($params['visible']))
            $params['visible'] = 1;
        if (!empty($params['var']))
            $smarty->assign($params['var'], $this->brands->get_brands($params));
    }

    /**
     * @param $params
     * @param $smarty
     */
    public function get_browsed_products($params, &$smarty)
    {
        if (!empty($_COOKIE['browsed_products'])){
            $browsed_products_ids = explode(',', $_COOKIE['browsed_products']);
            $browsed_products_ids = array_reverse($browsed_products_ids);
            if (isset($params['limit']))
                $browsed_products_ids = array_slice($browsed_products_ids, 0, $params['limit']);

            $products = [];
            foreach ($this->products->get_products(['id' => $browsed_products_ids, 'visible' => 1]) as $p)
                $products[$p->id] = $p;

            $browsed_products_images = $this->products->get_images(['product_id' => $browsed_products_ids]);
            foreach ($browsed_products_images as $browsed_product_image)
                if (isset($products[$browsed_product_image->product_id]))
                    $products[$browsed_product_image->product_id]->images[] = $browsed_product_image;

            foreach ($browsed_products_ids as $id){
                if (isset($products[$id])){
                    if (isset($products[$id]->images[0]))
                        $products[$id]->image = $products[$id]->images[0];
                    $result[] = $products[$id];
                }
            }
            $smarty->assign($params['var'], $result);
        }
    }


    /**
     * @param $params
     * @param $smarty
     */
    public function get_featured_products_plugin($params, &$smarty)
    {
        if (!isset($params['visible']))
            $params['visible'] = 1;
        $params['featured'] = 1;
        if (!empty($params['var'])){
            foreach ($this->products->get_products($params) as $p)
                $products[$p->id] = $p;

            if (!empty($products)){
                // id выбраных товаров
                $products_ids = array_keys($products);

                // Выбираем варианты товаров
                $variants = $this->variants->get_variants(['product_id' => $products_ids, 'in_stock' => true]);

                // Для каждого варианта
                foreach ($variants as &$variant){
                    // добавляем вариант в соответствующий товар
                    $products[$variant->product_id]->variants[] = $variant;
                }

                // Выбираем изображения товаров
                $images = $this->products->get_images(['product_id' => $products_ids]);
                foreach ($images as $image)
                    $products[$image->product_id]->images[] = $image;

                foreach ($products as &$product){
                    if (isset($product->variants[0]))
                        $product->variant = $product->variants[0];
                    if (isset($product->images[0]))
                        $product->image = $product->images[0];
                }
            }

            $smarty->assign($params['var'], $products);

        }
    }


    /**
     * @param $params
     * @param $smarty
     */
    public function get_new_products_plugin($params, &$smarty)
    {
        if (!isset($params['visible']))
            $params['visible'] = 1;
        if (!isset($params['sort']))
            $params['sort'] = 'created';
        if (!empty($params['var'])){
            foreach ($this->products->get_products($params) as $p)
                $products[$p->id] = $p;

            if (!empty($products)){
                // id выбраных товаров
                $products_ids = array_keys($products);

                // Выбираем варианты товаров
                $variants = $this->variants->get_variants(['product_id' => $products_ids, 'in_stock' => true]);

                // Для каждого варианта
                foreach ($variants as &$variant){
                    // добавляем вариант в соответствующий товар
                    $products[$variant->product_id]->variants[] = $variant;
                }

                // Выбираем изображения товаров
                $images = $this->products->get_images(['product_id' => $products_ids]);
                foreach ($images as $image)
                    $products[$image->product_id]->images[] = $image;

                foreach ($products as &$product){
                    if (isset($product->variants[0]))
                        $product->variant = $product->variants[0];
                    if (isset($product->images[0]))
                        $product->image = $product->images[0];
                }
            }

            $smarty->assign($params['var'], $products);

        }
    }


    /**
     * @param $params
     * @param $smarty
     */
    public function get_discounted_products_plugin($params, &$smarty)
    {
        if (!isset($params['visible']))
            $params['visible'] = 1;
        $params['discounted'] = 1;
        if (!empty($params['var'])){
            foreach ($this->products->get_products($params) as $p)
                $products[$p->id] = $p;

            if (!empty($products)){
                // id выбраных товаров
                $products_ids = array_keys($products);

                // Выбираем варианты товаров
                $variants = $this->variants->get_variants(['product_id' => $products_ids, 'in_stock' => true]);

                // Для каждого варианта
                foreach ($variants as &$variant){
                    // добавляем вариант в соответствующий товар
                    $products[$variant->product_id]->variants[] = $variant;
                }

                // Выбираем изображения товаров
                $images = $this->products->get_images(['product_id' => $products_ids]);
                foreach ($images as $image)
                    $products[$image->product_id]->images[] = $image;

                foreach ($products as &$product){
                    if (isset($product->variants[0]))
                        $product->variant = $product->variants[0];
                    if (isset($product->images[0]))
                        $product->image = $product->images[0];
                }
            }

            $smarty->assign($params['var'], $products);

        }
    }

    public function uni_plugin($params, &$smarty){
        if(!empty($params['var']))
            $smarty->assign($params['var'], $this->$params['class']->$params['method']($params));
    }
}
