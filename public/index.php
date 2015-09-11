<?php
date_default_timezone_set("PRC");
require '../vendor/autoload.php';

// Prepare app
$app = new \Slim\Slim(array(
    'templates.path' => '../templates',
));
// Create monolog logger and store logger in container as singleton
// (Singleton resources retrieve the same log resource definition each time)
$app->container->singleton('log', function () {
    $log = new \Monolog\Logger('slim-skeleton');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

$app->config('debug', true);
error_reporting(E_ALL);

// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => realpath('../templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

// Define routes
$app->get('/', function () use ($app) {
//    $language = getLanguage("index");
//    $data = array("text" => $language, "page"=>"style.html");
//    $app->render('amber/style.html', $data);
    $app->redirect("amber/style");
});
$app->get('/(:product/(:page))', function($product = "amber", $page = "style") use ($app) {
    if (strpos($page, '.html') > 0){
        $pageName = substr($page, 0, count($page) - 6);
        $app->redirect("$pageName");
        return;
    }

    $filePath = "../templates/$product/" . $page . ".html";
    if (file_exists($filePath)) {
        $pageName = $page;//substr($page, 0, count($page) - 6);
        $language = getLanguage("$pageName");
        $data = array("text" => $language, "page" => $page);
        $app->render("$product/$page.html", $data);
    } else {
        $app->notFound();
    }
});

$app->map('/subscribe', function() use ($app) {
    $ip=$_SERVER["REMOTE_ADDR"];
    $paramOk = true;
    $message = null;
    $paramOk = $paramOk && $name = @$_REQUEST['name'];
    if (!$paramOk && empty($message)) {
        $message = "Please input your name";
    }
    $paramOk = $paramOk && $email = @$_REQUEST['email'];
    if ($paramOk && empty($message)) {
        //\w{1,}([\-\+\.]\w{1,}){0,}@\w{1,}([\-\.]\w{1,}){0,}\.\w{1,}([\-\.]\w{1,}){0,}
        $regex = '/\\w{1,}([\\-\\+\\.]\\w{1,}){0,}@\\w{1,}([\\-\\.]\\w{1,}){0,}\\.\\w{1,}([\\-\\.]\\w{1,}){0,}/';
        $email = trim($email);
        $paramOk = preg_match_all($regex, $email) && true;

        if ($paramOk && strpos($email, " ") > 0) {
            $paramOk = false;
        }

        if (!$paramOk) {
            $message = "Please input correct email address";
        }
    }
    if (!$paramOk && empty($message)) {
        $message = "Please input your email";
    }
    $paramOk = $paramOk && $watchType = @$_REQUEST['watchType'];
    if (!$paramOk && empty($message)) {
        $message = "Please choose your watch' type";
    }
    $paramOk = $paramOk &&
        ($watchType == "Apple Watch Sport"
            || $watchType == "Apple Watch"
            || $watchType == "Apple Watch Edition");
    if (!$paramOk && empty($message)) {
        $message = "Please choose right watch type";
    }

    $paramOk = $paramOk && $watchSize = @$_REQUEST['watchSize'];
    if (!$paramOk && empty($message)) {
        $message = "Please choose your watch' size";
    }

    $paramOk = $paramOk && (strpos($watchSize, "38") === 0 || strpos($watchSize, "42") === 0);
    if (!$paramOk && empty($message)) {
        $message = "Please choose right watch size";
    }

    $comment = @$_REQUEST['comment'];

    if ($paramOk) {
        $catalog = "Amber";
        $name = urldecode($name);
        $email = urldecode($email);
        $catalog = urldecode($catalog);
        $watchType = urldecode($watchType);
        $watchSize = urldecode($watchSize);
        $comment = urldecode($comment);

        $db = connectDb();
        $res = $db->insert("subscriber", [
            "name"=>$name,
            "email"=>$email,
            "country"=>"",
            "city"=>"",
            "catalog"=>$catalog,
            "watch_type"=>$watchType,
            "watch_size"=>$watchSize,
            "comment"=>$comment,
            "ip"=>$ip]);
        if ($res) {
            outPutJson($app, 200, array("code"=>0, "msg"=>"Successfully Subscribed"));
        } else {
            outPutJson($app, 500, array("code"=>1001, "msg"=>"Server Error"));
        }
    } else {
        outPutJson($app, 400, array("code"=>1002, "msg"=>$message));
    }
})->via('GET', 'POST', 'PUT');

$app->get('/subscribeSummary', function()  use ($app){
    $_SESSION["administrator"] = "YES";
    if($_SESSION["administrator"] == "YES") {
        $year = date("Y");
        $todayTime = strtotime(@$_GET['fromDate']);
        if ($todayTime == 0) {
            $todayTime = strtotime("today");
        }
        $tomorrowTime = strtotime(@$_GET['toDate']);
        if ($tomorrowTime == 0 || $tomorrowTime < $todayTime) {
            $tomorrowTime = strtotime("+1 day", $todayTime);
        }
        $db = connectDb();

        $fromDate = date("Y-m-d", $todayTime);
        $toDate = date("Y-m-d", $tomorrowTime);


        $gapTime = $tomorrowTime - $todayTime;
        $gapCount = [];
        $gapFromDate = $fromDate;
        $gapToDate = $toDate;

        for ($i = 0 ; $i < 10; $i ++) {
            $count = $db->count("subscriber", ["timestamp[<>]"=>[$gapFromDate, $gapToDate]]);
            $gapCount[formatDate($gapFromDate, $gapToDate)] = $count;
            $gapToDate = $gapFromDate;
            $gapFromDate = date("Y-m-d", strtotime($gapToDate) - $gapTime);
        }

        $monthFrom = date("Y-m-01", $tomorrowTime);
        $monthTo = date("Y-m-01", strtotime("+1 month", strtotime($monthFrom)));
        $monthCount = [];

        for ($i = 0 ; $i < 5; $i ++) {
            $count = $db->count("subscriber", ["timestamp[<>]"=>[$monthFrom, $monthTo]]);
            $monthCount[formatDate($monthFrom, $monthTo)] = $count;
            $monthTo = $monthFrom;
            $monthFrom = date("Y-m-01", strtotime("-1 month", strtotime($monthFrom)));
        }
        print_r($gapCount);
        print_r($monthCount);

        $all = $db->select("subscriber", "*", ["timestamp[<>]"=>[$fromDate, $toDate], "LIMIT"=>"100"]);
        $data = array("data"=>$all, "gapCount"=>$gapCount, "monthCount" => $monthCount, "fromDate"=> $fromDate, "toDate"=>$toDate);

        // Render index view
        $app->render('summary.html', $data);
    } else {
    }
});
$app->get('/api/a', function() {
    echo "aaa";
},
    function() use($app){
        outPutJson($app, 200, array("k"=>"b", "param"=>$_GET));
    }
);
$app->get('/api/:function', function() {
        $_GET["p"] = 2;
    },
    'apiFunction', 'apiFunction', 'apiFunction');


// Run app
$app->run();


function apiFunction($f) {
    $app = \Slim\Slim::getInstance();
    outPutJson($app, 200, array("k"=>$f, "param"=>$_GET));
}

function formatDate($from, $to) {
    $year = date("Y");
    if (strpos($from, $year) === 0 && strpos($to, $year) === 0) {
        $nextDay = date("Y-m-d", strtotime("+1 day", strtotime($from)));
        $from = substr($from, strlen($year) + 1);
        if ($to == $nextDay) {
            return $from;
        } else {
            $to = substr($to, strlen($year) + 1);
        }
    }
    if (strrpos($from, "-01") === strlen($from)-3 && strrpos($to, "-01") === strlen($to)-3) {
        $from = substr($from, 0, strlen($from)-3)."月";
        return $from;
    }
    return $from."~".$to;
}

function getLanguage($view) {
    if (isset($_GET['language'])) {
        $language = $_GET['language'];
    } else if (isset($_COOKIE["language"])){
        $language=$_COOKIE["language"];
    } else {
        $language=$_SERVER["HTTP_ACCEPT_LANGUAGE"];
    }
    $lan = "en";
    if (strpos($language, "zh-CN") === 0) {
        $lan = "zh";
    } else if (strpos($language, "zh") === 0) {
        $lan = "zh";
    } else {
        $lan = "en";
    }
    setcookie("language", $lan);
    $filePath  = "../locale/ini/amber_text_".$lan.".ini";
    $language_text = @parse_ini_file($filePath, true);
    $language_text = @array_merge($language_text[$view], $language_text["common"]);
    return $language_text;
}

function cdnPath($path) {
    $config = getConfig();
    $prefix = $config['cdn']['prefix'];

    return $prefix.$path;
}

function connectDb() {
    require_once "../vendor/medoo/medoo.php";

    $config = getConfig();
    $connection = new medoo($config['database']);
    $connection->exec("SET sql_mode=`ANSI_QUOTES`;");
    return $connection;
}

function getConfig() {
    $iniFile = '../config/cfg.ini';
    if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
        $iniFile = '../config/cfg.local.ini';
    }

    return parse_ini_file($iniFile, true);
}

function outPutJson($app, $code, $data) {
    error_reporting(0);
    $app->response()->headers->set('Content-Type', 'application/json');
    $app->response->setStatus($code);
    echo json_encode($data);
}
