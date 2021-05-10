<?php

namespace App\Http\Controllers;

use App\Models\Order;

class BotController extends Controller
{
    public function index()
    {
        $updateId = null;
        $chatId = null;
        while (true) {
            $updates = json_decode(file_get_contents('https://api.telegram.org/bot'.getenv("TELEGRAM_TOKEN").'/getUpdates?offset=' . $updateId));
            foreach ($updates->result as $update) {
                $updateId = $update->update_id;
                if (isset($update->message)) {
                    $answer = $update->message->text;
                    $chatId = $update->message->chat->id;
                } elseif (isset($update->edited_message)) {
                    $answer = $update->edited_message->text;
                    $chatId = $update->edited_message->chat->id;
                } else {
                    $answer = '';
                }

                if ($chatId) {
                    $parameters = [
                        'chat_id' => $chatId,
                        'text' => $this->getStatus($answer)
                    ];
                    if (str_contains($answer, 'get order')) {
                        file_get_contents('https://api.telegram.org/bot'.getenv("TELEGRAM_TOKEN").'/sendMessage?' . http_build_query($parameters));
                    }
                }
            }
            $updateId += 1;
        }

    }

    public function getStatus($id)
    {
        $id = $this->extractOrderFromText($id);
        $order = Order::find($id);

        return $order ? $order[0]->status : 'Not found';
    }

    private function extractOrderFromText($text)
    {
        preg_match_all('!\d+!', $text, $matches);
        return $matches[0];
    }

}
