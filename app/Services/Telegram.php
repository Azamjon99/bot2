<?php


namespace App\Services;

use phpDocumentor\Reflection\Types\Null_;

class Telegram
{

    protected $token = '1682534615:AAFuct679YAc46yGIQrw7LBvWtDueNeK2J4';
    protected $url = 'https://api.telegram.org/bot';
    public $method = '';
    public $data = [];

    public function requestData($request)
    {
        $data = [];
        $data['update_id'] = $request['update_id'] ?? '';
        $data['message'] = $request['message'] ?? '';
        $data['pre_checkout_query'] = $request['pre_checkout_query'] ?? null;
        $data['callback_query'] = $request['callback_query'] ?? null;
        $data['inline_query'] = $request['inline_query'] ?? '';
        $data['InlineQueryResultArticle'] = $request['InlineQueryResultArticle'] ?? '';
        return $data;
    }

    public function sendRequest()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url.$this->token."/".$this->method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($this->data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return ['ok' => false, 'description' => "cURL Error #:".$err];
        } else {
            return ['ok' => true, 'result' => $response];
        }
    }

    public function sendMessage($chatId, $message, $keyboard = null,  $web = true, $inline=false)
    {

        $this->data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => $web,
        ];
        if ($keyboard) { 
            if(!$inline){
                $this->data['reply_markup'] = $this->makeKeyboard($keyboard);

            }else{
                $this->data['reply_markup'] = $this->makeInlineKeyboard($keyboard);

            }
        }
        $this->method = 'sendMessage';
        return $this->sendRequest();
    }
    

    public function editMessageText($call_Message_id,$message_id, $message, $keyboard = null,$web = true, $inline=false)
    {

        $this->data = [
            'chat_id' => $call_Message_id,
            'message_id' => $message_id,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => $web,
          
        ];
        if ($keyboard) { 
            if(!$inline){
                $this->data['reply_markup'] = $this->makeKeyboard($keyboard);

            }else{
                $this->data['reply_markup'] = $this->makeInlineKeyboard($keyboard);

            }
        }
        $this->method = 'editMessageText';
        return $this->sendRequest();
    }






    public function getChat($chatId)
    {

        $this->data = [
            'chat_id' => $chatId,
        ];
        $this->method = 'getChat';
        return $this->sendRequest();
    }

    public function getChatMembersCount($chatId)
    {

        $this->data = [
            'chat_id' => $chatId,
        ];
        $this->method = 'getChatMembersCount';
        return $this->sendRequest();
    }

    public function getChatAdministrators($chatId)
    {

        $this->data = [
            'chat_id' => $chatId,
        ];
        $this->method = 'getChatAdministrators';
        return $this->sendRequest();
    }

    public function getFile($fileId)
    {

        $this->data = [
            'file_id' => $fileId,
        ];
        $this->method = 'getFile';
        return $this->sendRequest();
    }

    public function sendInvoice($chatId, $id, $price)
    {

        $this->data = [
            'chat_id' => $chatId,
            'title' => 'Оплата заказа #'.$id,
            'description' => 'Оплатить заказ онлайн.',
            'payload' => $id,
            'provider_token' => 'PAYMENT_TOKEN',
            'start_parameter' => 'pay_'.$id,
            'currency' => 'UZS',
            'total_price' => $price * 100,
            'prices' => json_encode([['label' => 'Сумма с доставкой', 'amount' => $price * 100]]),
            'reply_markup' => $this->makeInlineKeyboard([[[
                'text' => 'Оплатить',
                'pay' => true
            ]]]),
        ];
        $this->method = 'sendInvoice';
        return $this->sendRequest();
    }

    public function answerPreCheckoutQuery($id, $ok, $error_message = '')
    {

        $this->data = [
            'pre_checkout_query_id' => $id,
            'ok' => $ok,
            'error_message' => $error_message ?? '',
        ];
        $this->method = 'answerPreCheckoutQuery';
        return $this->sendRequest();
    }

    public function sendPhoto($chatId, $photo, $caption = '', $keyboard = null, $inline = false)
    {

        $this->data = [
            'chat_id' => $chatId,
            'photo' => $photo,
            'caption' => $caption,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];
        if ($keyboard) {
            if($inline) {
                $this->data['reply_markup'] = $this->makeInlineKeyboard($keyboard);
            } else {
                $this->data['reply_markup'] = $this->makeKeyboard($keyboard);
            }
        }
        $this->method = 'sendPhoto';
        return $this->sendRequest();
    }

    public function sendLocation($chatId, $latitude, $longitude, $keyboard = null)
    {
        $this->data = [
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude
        ];
        if ($keyboard) {
            $this->data['reply_markup'] = $this->makeKeyboard($keyboard);
        }
        $this->method = 'sendLocation';
        $this->sendRequest();
    }

    public function makeButton($text, $contact = false, $location = false)
    {
        return [
            'text' => $text,
            'request_contact' => $contact,
            'request_location' => $location
        ];
    }

    public function makeKeyboard($buttons)
    {
        return json_encode(['keyboard' => $buttons, 'resize_keyboard' => true]);
    }

    public function makeInlineKeyboard($buttons)
    {
        return json_encode(['inline_keyboard' => $buttons]);
    }

    public function makeInlineButton($text, $url)
    {
        return [
            'text' => $text,
            'url' => $url
        ];
    }


    public function makeInlineButton1($text, $callback_data)
    {
        return [
            'text' => $text,
            'callback_data' => $callback_data
        ];
    }



    public function downloadFile($filePath, $channelId)
    {
        $fileName = "tg_".$channelId.'.jpg';
        $file = @file_get_contents("https://api.telegram.org/file/bot".$this->token."/".$filePath);
        @file_put_contents(public_path('uploads/tg-data/'.$fileName), $file);
        return $fileName;
    }

    public function InlineQueryResultArticle($id, $title, $message_text){
      
       return [ 
            'type' => "article",
            'id'=> "1", 
            'title'=> "chek inline keybord ",
            'description'=> "test ",
            'input_message_content'=> ['message_text' => "you can share inline keyboard to other chat"],
        
        
            ];
   
    // dd($data);
    } 
    public function answerInlineQuery($inline_query_id, $results)
    {
        $this->data = [
            'inline_query_id' => $inline_query_id,
            'results' => $results,
        ];
        $this->method = 'answerInlineQuery';
        return $this->sendRequest();
    }
}