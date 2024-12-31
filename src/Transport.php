<?php


namespace PinaGoUnisender;


use Pina\Config;
use Pina\Html;
use Pina\Http\JsonAPI;
use Pina\Log;
use PinaNotifications\Messages\Message;
use PinaNotifications\Transports\TransportInterface;

class Transport implements TransportInterface
{

    public function send(string $address, Message $message): bool
    {
        if (empty($address)) {
            return false;
        }

        $m = [
            'recipients' => [
                [
                    'email' => $address,
                    'substitutions' => [
                        'title' => strval($message->getTitle()),
                        'text' => strval($message->getText()),
                        'link' => strval($message->getLink()),
                    ]
                ]
            ],
            'from_email' => Config::get('gounisender', 'from_email'),
            'from_name' => Config::get('gounisender', 'from_name'),
        ];

        $templateId = Config::get('gounisender', 'template_id');
        if ($templateId) {
            $m['template_id'] = $templateId;
            $m['template_engine'] = Config::get('gounisender', 'template_engine') ?? 'simple';
        } else {
            $m['subject'] = $message->getTitle();
            $m['body'] = [
                'plaintext' => $message->getText() . "\n\n" . $message->getLink(),
                'html' => nl2br($message->getText() . "\n\n" . Html::a($message->getLink(), $message->getLink())),
            ];
        }

        $packet = [
            'message' => $m,
        ];

        $api = new JsonAPI(Config::get('gounisender', 'endpoint'), ['X-API-KEY: ' . Config::get('gounisender', 'api_key')]);
        $result = $api->post('/email/send.json', $packet);
        if (!empty($result['status']) && $result['status'] == 'error') {
            Log::error('gounisender', $result['message']);
        }

        return $api->isSuccess();
    }
}