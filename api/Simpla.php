<?php

/**
 * Основной класс Simpla для доступа к API CMS
 * @property Config()     config
 * @property Request()    request
 * @property Database()   db
 * @property Settings()   settings
 * @property Design()     design
 * @property Products()   products
 * @property Variants()   variants
 * @property Categories() categories
 * @property Brands()     brands
 * @property Features()   features
 * @property Money()      money
 * @property Pages()      pages
 * @property Blog()       blog
 * @property Cart()       cart
 * @property Image()      image
 * @property Delivery()   delivery
 * @property Payment()    payment
 * @property Orders()     orders
 * @property Users()      users
 * @property Coupons()    coupons
 * @property Comments()   comments
 * @property Feedbacks()  feedbacks
 * @property Notify()     notify
 * @property Managers()   managers
 */
class Simpla
{

    /**
     * алиасы API
     * @var array
     */
    private static $alias = [
        'db' => 'Database'
    ];
    /**
     * Созданные объекты
     * @var array
     */
    private static $objects = [];

    /**
     * Конструктор оставим пустым, но определим его на случай обращения parent::__construct() в классах API
     */
    public function __construct()
    {
        //error_reporting(E_ALL & !E_STRICT);
    }

    /**
     * Магический метод, создает нужный объект API
     *
     * @param $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        // прокси классы можно писать с маленькой или большой букв
        $name = lcfirst ($name);
        $class = ucfirst($name);
        // Проверка алиаса в API
        if (isset(self::$alias[$name])){
            $class = self::$alias[$name];
        }
        // Если такой объект уже существует, возвращаем его
        if (isset(self::$objects[$class])){
            return self::$objects[$class];
        }
        $file_class = __DIR__ . '/' . $class . '.php';

        if(is_readable($file_class)){
            include_once $file_class;
            self::$objects[$class] = new $class();

        } else {
            return null;
        }
        // Возвращаем созданный объект
        return self::$objects[$class];
    }
}