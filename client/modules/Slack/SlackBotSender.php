<?php
/**
 * This file is bot client
 *
 * This file contains bot client class that can send
 * curl HTTP POST requests to slack bot webhooks
 */
namespace Vicky\client\modules\Slack;

use Vicky\client\exceptions\SlackBotSenderException;

class SlackBotSender
{
    /**
     * @var
     */
    private static $botClient;

    /**
     * Slack bot webserver host URL
     *
     * @var
     */
    protected $slackBotUrl;

    /**
     * Slack bot secret key
     *
     * @var null
     */
    protected $auth;

    /**
     * SlackWebhookSender constructor.
     * 
     * @param      $slackBotUrl slack bot webserver host url
     * @param null $auth        slack bot webserver secret key
     */
    public function __construct($slackBotUrl, $auth = null)
    {
        $this->slackBotUrl = $slackBotUrl;
        $this->auth = $auth;
    }

    /**
     * Initialize slack bot client
     *
     * @return SlackBotSender
     */
    public static function getInstance($slackBotUrl, $auth)
    {
        if (!self::$botClient) {
            self::$botClient = new self($slackBotUrl, $auth);
        }
        
        return self::$botClient;
    }

    /**
     * Send HTTP POST request to slack bot to send in $channel
     *
     * @param        $channel  slack channel name (with '#' symbol)
     * @param        $message  message text
     * @param string $hookName slack bot hook which accepts requests
     *
     * @return bool
     */
    public function toChannel($channel, $message, $webhookName = 'tochannel')
    {
        $channel = (substr($channel, 0, 1) === '#') ? $channel : "#{$channel}";

        $slackRequest = [
            'auth'    => $this->auth,
            'name'    => $webhookName,
            'payload' => json_encode([
                "type"    => "message",
                "text"    => $message,
                "channel" => $channel
            ])
        ];

        $answer = $this->sendRequest($slackRequest);

        return $this->curlAnswerCheck($answer);
    }

    /**
     * Send HTTP POST request to slack bot to send in pivate chat to user personally
     *
     * @param        $userName slack username (without '@' symbol)
     * @param        $message  message text
     * @param string $hookName slack bot hook which accepts requests
     * 
     * @return bool
     */
    public function toUser($userName, $message, $webhookName = 'touser')
    {
        $slackRequest = [
            'auth'    => $this->auth,
            'name'    => $webhookName,
            'payload' => json_encode([
                "type"    => "message",
                "text"    => $message,
                "user"    => $userName
            ])
        ];

        $answer = $this->sendRequest($slackRequest);

        return $this->curlAnswerCheck($answer);
    }

    /**
     * Send HTTP request by curl method
     * 
     * @param $slackRequest array with request data
     * 
     * @return bool|mixed
     */
    protected function sendRequest($slackRequest)
    {
        if (!($curl = curl_init())) {
            return $answer = 'Cannot init curl session!';
        }
        
        curl_setopt_array($curl, [
            CURLOPT_URL            => $this->slackBotUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($slackRequest)
        ]);

        $answer = curl_exec($curl);

        $error = curl_error($curl);
        if ($error) {
            $answer = $error;
        }

        curl_close($curl);

        return $answer;
    }

    /**
     * Check result $answer of curl executing
     *
     * @param $answer response after curl execution
     *
     * @return bool
     *
     * @throws SlackBotSenderException
     */
    protected function curlAnswerCheck($answer)
    {
        if (strpos($answer, "Ok")) {
            throw new SlackBotSenderException($answer);
        }

        return true;
    }
}
