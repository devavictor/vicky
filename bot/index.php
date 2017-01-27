<?php
namespace Vicky;

use PhpSlackBot\Bot;
use Vicky\bot\models\MyCommand;
use Vicky\bot\models\ToUserWebhook;
use Vicky\bot\models\ToChannelWebhook;

require dirname(__DIR__).'/vendor/autoload.php';
$config = require (isset($argv[1])) ? $argv[1] : 'config.php';

ini_set('log_errors', 'On');
ini_set('error_log', $config['error_log']);
ini_set('max_execution_time', 0);
date_default_timezone_set('Europe/Moscow');

$bot = new Bot();
$bot->setToken($config['botToken']);
$bot->loadCommand(new MyCommand());
$bot->loadInternalCommands();

$bot->loadInternalWebhooks();
$bot->loadWebhook(new ToUserWebhook());
$bot->loadWebhook(new ToChannelWebhook());
$bot->enableWebserver(8080, $config['botAuth']);
$bot->run();