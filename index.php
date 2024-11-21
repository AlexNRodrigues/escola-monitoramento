<?php


ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "vendor/autoload.php";

date_default_timezone_set('America/Sao_Paulo');

use app\config\Backup;
use Symfony\Component\Yaml\Yaml; 
use app\controllers\monitoramento\MainController;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

Backup::runBackup();

session_start();

if (!isset($_SESSION["ALUNO"])) {
    $_SESSION["Gabarito_aluno"] = false;
    $_SESSION["GESTOR"] = false;
    $_SESSION["ALUNO"] = false;
    $_SESSION["ADM"] = false;
    $_SESSION["PROFESSOR"] = false;
    $_SESSION["PFA"] = false;
    $_SESSION["PAG_VOLTAR"] = "adm";
}

// Separa a parte da query string
$queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

// Converte a query string em um array
$queryArray = [];
if ($queryString) {
    parse_str($queryString, $queryArray);
}
$_GET['query_array'] = $queryArray;

$routesYaml = Yaml::parseFile(__DIR__ . '/routes.yaml');
$rotas = $routesYaml['rotas'];

$action = isset($_GET["action"]) ? $_GET["action"] : "";

if ($action == "home") {
    $action = "index";
}

if (strpos($action, "/") !== false) {
    $action = "index";
    header("location: ../home");
}

if (empty($action) || strpos($action, '/') !== false) {
    $action = "index";
}

if (array_key_exists($action, $rotas)) {
    $controller = $rotas[$action];
    $method = $action;
    $controller::$method();
} else {
    MainController::index();
}

ob_end_flush();
