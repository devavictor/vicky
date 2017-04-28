<?php
/**
 * Vicky module, that check blockers issue files, send notification 
 * to vicky like JIRA and store next notification time
 *
 * @credits https://github.com/kommuna
 * @author  chewbacca@devadmin.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace kommuna\vicky\src\util;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use kommuna\vicky\modules\Jira\IssueFile;
use kommuna\vicky\modules\VickyClient;

require dirname(__DIR__).'/vendor/autoload.php';
$config = require '/etc/vicky/config.php';

ini_set('log_errors', 'On');
ini_set('error_log', $config['error_log']);
ini_set('max_execution_time', 0);
date_default_timezone_set($config['timeZone']);

$log = new Logger('vicky');
$log->pushHandler(
    new StreamHandler(
        $config['error_log'],
        $config['loggerDebugLevel'] ? Logger::DEBUG : Logger::ERROR
    )
);

$start = microtime(true);

$log->info("The script ".__FILE__." started.");

VickyClient::getInstance(
    $config['vickyClient']['url'],
    $config['vickyClient']['timeout']
);

IssueFile::setPathToFolder($config['blockersIssues']['folder']);
IssueFile::setNotificationInterval($config['notificationInterval']);

IssueFile::filesCheck(function(IssueFile $issueFile)
{
    VickyClient::getInstance()->send(
        $issueFile->getJiraWebhookData()->getRawData(), 
        'custom:blocker_notification'
    );
});

$log->info("Script finished in ".(microtime(true) - $start)." sec.");