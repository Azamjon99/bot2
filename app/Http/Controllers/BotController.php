<?php
namespace App\Http\Controllers;

use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\TripRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Services\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;


class BotController extends BaseController
{
    protected $telegram, $requestData, $chatId, $callback_data, $call_Message_id,$message_id, $user, $phone_number, $command, $country, $trip, $order;
    public function __construct()
    {
        $this->telegram = new Telegram();
    }
 
    public function index(Request $request, UserRepositoryInterface $user, CountryRepositoryInterface $country, TripRepositoryInterface $trip, OrderRepositoryInterface $order)
    {
        // var_dump('ssd'); exit; 
        $this->order=$order;
        $this->trip = $trip;
        $this->country =$country;
        $this->requestData = $this->telegram->requestData($request->all());
        $this->chatId = $this->requestData['message']['chat']['id'] ?? null;
        $command = $this->requestData['message']['text'] ?? null;
        $this->phone_number = $this->requestData['message']['contact']['phone_number'] ?? null;
        $userId = $this->requestData['message']['from']['id'] ?? null;
        // $forwardFromChat = $this->requestData['message']['forward_from_chat'] ?? null;
        $this->callback_data = $this->requestData['callback_query']['data'] ?? null;
        $this->call_Message_id = $this->requestData['callback_query']['message']['chat']['id'] ?? null;
        $this->message_id = $this->requestData['callback_query']['message']['message_id'] ?? null;

        if ($userId) 
        {
            $json = $this->getFromFile($userId);
            if ($json) 
            {
                $this->user = $json;
                App::setLocale($this->user->lang);
            } 
            else 
            {
                $this->user = ['id' => $userId, 'lang'=>'uz'];
                $this->putFile();
            }
        }
        if ($command == '/start') 
        {
            $this->telegram->sendMessage($this->chatId,"Выберите язык", $this->chooseLanguage());
            $this->telegram->sendMessage($this->chatId,"Tilni tanlang", $this->chooseLanguage());
            $this->setLastQuery('/start');
        }
        elseif($this->user->last_query=="/start") {
            if($command == 'Russian') 
            {
                $this->setLanguage('ru');
                $this->telegram->sendMessage($this->chatId, __('bot.language'));
                $this->telegram->sendMessage($this->chatId, __('bot.name'));
                $this->setLastQuery(__('bot.name'));
            }
            elseif($command == 'Uzbek(Latin)') 
            {
                $this->setLanguage('uz');
                $this->telegram->sendMessage($this->chatId, __('bot.language'));
                $this->telegram->sendMessage($this->chatId,__('bot.name'));
                $this->setLastQuery(__('bot.name'));
            }
            else
            {
                $this->telegram->sendMessage($this->chatId, 'Please choose the language');
            }
        }
       
        elseif($this->user->last_query == __('bot.name'))
        {
            $this->user->name = $command;
            $this->telegram->sendMessage($this->chatId, __('bot.phone'), $this->sendPhoneNumber());
            $this->setLastQuery(__('bot.phone'));
        }
        elseif($this->user->last_query == __('bot.phone'))
        {
            $this->setPhone($command);
            $this->telegram->sendMessage($this->chatId, __('bot.form'), $this->chooseTrip());
            $this->setLastQuery(__('bot.form'));
        }
        elseif($this->user->last_query == __('bot.form'))
        {
            $this->setTrip($command);
            $this->telegram->sendMessage($this->chatId, __('bot.country'), $this->chooseCountry());
            $this->setLastQuery(__('bot.country'));
        }
        elseif($this->user->last_query == __('bot.country'))
        {
            $this->setCountry($command);
            $this->telegram->sendMessage($this->chatId, __('bot.hotel'),$this->nextButton());
            $this->setLastQuery(__('bot.hotel'));
        }
        elseif($this->user->last_query == __('bot.hotel'))
        {
            if($command == __('bot.next'))
            {
                $this->setHotel("not entered");
                $this->telegram->sendMessage($this->chatId, __('bot.start_date'));
                $this->setLastQuery(__('bot.start_date'));
            }
            else
            {
                $this->setHotel($command);
                $this->telegram->sendMessage($this->chatId, __('bot.start_date'));
                $this->setLastQuery(__('bot.start_date'));
            }
        }
        elseif($this->user->last_query == __('bot.start_date'))
        {
            // $this->validateData($command);
            $this->setDate($command);
            $this->telegram->sendMessage($this->chatId, __('bot.night'));
            $this->setLastQuery(__('bot.night'));
        }
        elseif($this->user->last_query == __('bot.night')){
            $this->setNights($command);
            $this->telegram->sendMessage($this->chatId, __('bot.night'));
            $this->setLastQuery(__('bot.night'));

        }
}


    // public function productsKeyboard($category)
    // {
    //     //  $categories = Category::all();
    //     // foreach($categories as $category){
    //     foreach($category->products as $product) {
    //         $data[] = [
    //             $this->telegram->makeButton($product->name),
    //         ];
    //     }
    //     return $data;

    // }


//     public function inlineOptionsKeyboard($product){

//         $options=$product->productOptionValues;
//         $optionPrice=$product->productValues;
//         $keyboard = [
            
//         ];
// if(count($options)%2){
//     $last_option=count($options)%2;
//     for($i=0; $i<count($options)-1; $i+=2 ){
//         $keyboard[]=[
//             $this->telegram->makeInlineButton($options[$i]['name'] . " - " . $optionPrice[$i]['value'] ,
//             "https://core.telegram.org/bots/api#inlinekeyboardbutton" ),
//             $this->telegram->makeInlineButton($options[$i+1]['name'] . " - " . $optionPrice[$i]['value'] ,
//             "https://core.telegram.org/bots/api#inlinekeyboardbutton" ),
    
//         ];
//     }
//     $keyboard[]=[
//         $this->telegram->makeInlineButton($options[$last_option]['name'],"https://core.telegram.org/bots/api#inlinekeyboardbutton" ),
    
//     ];
// }else{
// for($i=0; $i<count($options)-1; $i+=2 ){
//     $keyboard[]=[
//         $this->telegram->makeInlineButton1($options[$i]['name']. " - " . $optionPrice[$i]['value'],
//         $optionPrice[$i]['product_id']  ),
//        $this->telegram->makeInlineButton1($options[$i+1]['name']. " - " . $optionPrice[$i]['value'],
//        $optionPrice[$i+1]['product_id']),

//     ];
// }
// }
// $keyboard[]=[
//     $this->telegram->makeInlineButton("orqaga","https://core.telegram.org/bots/api#inlinekeyboardbutton" ),

// ];

//         return $keyboard;
     
//     }








    
}