<?PHP




// пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅ
$time_start = microtime(true);

require_once('view/IndexView.php');
require_once __DIR__ . '/vendor/autoload.php';

/*$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();*/

session_start();

$view = new IndexView();

if (isset($_GET['logout'])){
    header('WWW-Authenticate: Basic realm="AlexShop CMS"');
    header('HTTP/1.0 401 Unauthorized');
    unset($_SESSION['admin']);
}

$res = $view->fetch();
// пїЅпїЅпїЅпїЅ пїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅ
if ($res !== false){
    // пїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ
    header('Content-type: text/html; charset=UTF-8');
    print $res;

    // пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ $_SESSION['last_visited_page']
    if (empty($_SESSION['last_visited_page']) || empty($_SESSION['current_page']) ||
        $_SERVER['REQUEST_URI'] !== $_SESSION['current_page']){
        if (!empty($_SESSION['current_page']) && !empty($_SESSION['last_visited_page']) &&
            $_SESSION['last_visited_page'] !== $_SESSION['current_page'])
            $_SESSION['last_visited_page'] = $_SESSION['current_page'];
            $_SESSION['current_page'] = $_SERVER['REQUEST_URI'];
    }

} else {
    // пїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅ
    header('http/1.0 404 not found');
    // пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ GET, пїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ 404
    $_GET['page_url'] = '404';
    $_GET['module'] = 'PageView';
    print $view->fetch();
}


/*$p = 11;
$g = 2;
$x = 7;
$r = '';
$s = $x;
$bs = explode(' ', $view->config->license);
foreach ($bs as $bl){
    for ($i = 0, $m = ''; $i < strlen($bl) && isset($bl[$i + 1]); $i += 2){
        $a = base_convert($bl[$i], 36, 10) - ($i / 2 + $s) % 26;
        $b = base_convert($bl[$i + 1], 36, 10) - ($i / 2 + $s) % 25;
        $m .= ($b * pow($a, $p - $x - 1)) % $p;
    }
    $m = base_convert($m, 10, 16);
    $s += $x;
    for ($a = 0; $a < strlen($m); $a += 2) $r .= @chr(hexdec($m{$a} . $m{($a + 1)}));
}

// $r = 'simpla#2050-08-29#5390323713624831471127972315325729962 ';

@list($l->domains, $l->expiration, $l->comment) = explode('#', $r, 3);

$l->domains = explode(',', $l->domains);

$h = getenv("HTTP_HOST");
if (substr($h, 0, 4) == 'www.') $h = substr($h, 4);
if ((!in_array($h, $l->domains) || (strtotime($l->expiration) < time() && $l->expiration != '*'))){
    print "<div style='text-align:center; font-size:22px; height:100px;'>пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ<br><a href='http://simplacms.ru'>пїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ-пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ Simpla</a></div>";
}*/

// пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ
if (1){
    print "<!--\r\n";
    $time_end = microtime(true);
    $exec_time = $time_end - $time_start;

    if (function_exists('memory_get_peak_usage'))
        print 'memory peak usage: ' . memory_get_peak_usage() . " bytes\r\n";
    print 'page generation time: ' . $exec_time . " seconds\r\n";
    print '-->';
}
