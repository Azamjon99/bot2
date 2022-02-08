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
        $this->order=$order;
        $this->trip = $trip;
        $this->country =$country;
        $this->requestData = $this->telegram->requestData($request->all());
        $this->chatId = $this->requestData['message']['chat']['id'] ?? null;
        $command = $this->requestData['message']['text'] ?? null;
        $this->phone_number = $this->requestData['message']['contact']['phone_number'] ?? null;
        $userId = $this->requestData['message']['from']['id'] ?? null;
        $this->callback_data = $this->requestData['callback_query']['data'] ?? null;
        $this->call_Message_id = $this->requestData['callback_query']['message']['chat']['id'] ?? null;
        $this->message_id = $this->requestData['callback_query']['message']['message_id'] ?? null;
        if ($userId) 
        {
            $json = $this->getFromFile($userId);
            ($json) ? ($this->user = $json)&&(App::setLocale($this->user->lang)) : ($this->user = ['id' => $userId, 'lang'=>'uz'])&&($this->putFile());

        }
        if ($command == '/start') 
        {
            $this->telegram->sendMessage($this->chatId,"Выберите язык", $this->chooseLanguage());
            $this->telegram->sendMessage($this->chatId,"Tilni tanlang", $this->chooseLanguage());
            $this->setLastQuery('/start');
            exit;
        }
        switch ($this->user->last_query) {
            case "/start":
                switch ($command){
                    case "Russian":
                        $this->setLanguage('ru');
                        $this->telegram->sendMessage($this->chatId, __('bot.language'));
                        $this->telegram->sendMessage($this->chatId, __('bot.name'));
                        $this->setLastQuery(__('bot.name'));
                        break;
                    case 'Uzbek(Latin)':
                        $this->setLanguage('uz');
                        $this->telegram->sendMessage($this->chatId, __('bot.language'));
                        $this->telegram->sendMessage($this->chatId,__('bot.name'));
                        $this->setLastQuery(__('bot.name'));
                        break;
                    
                    default :
                        $this->telegram->sendMessage($this->chatId, 'Please choose the language');
                        break;
                }
                break;
                       
            case __('bot.name'):
                $this->user->name = $command;
                $this->telegram->sendMessage($this->chatId, __('bot.phone'), $this->sendPhoneNumber());
                $this->setLastQuery(__('bot.phone'));
                break;
            case __('bot.phone'):
                $this->setPhone($command);
                $this->telegram->sendMessage($this->chatId, __('bot.form'), $this->chooseTrip());
                $this->setLastQuery(__('bot.form'));
                break;

            case __('bot.form'):
                $this->setTrip($command);
                $this->telegram->sendMessage($this->chatId, __('bot.country'), $this->chooseCountry());
                $this->setLastQuery(__('bot.country'));
                break;
            case __('bot.country'):
                $this->setCountry($command);
                $this->telegram->sendMessage($this->chatId, __('bot.hotel'),$this->nextButton());
                $this->setLastQuery(__('bot.hotel'));
                break;

            case __('bot.hotel'):
                switch ($command){
                    case __('bot.next'):
                        $this->setHotel("not entered");
                        $this->telegram->sendMessage($this->chatId, __('bot.start_date'));
                        $this->setLastQuery(__('bot.start_date'));
                        break;

                    default :
                        $this->setHotel($command);
                        $this->telegram->sendMessage($this->chatId, __('bot.start_date'));
                        $this->setLastQuery(__('bot.start_date'));
                        break;
                }
                break;
            case __('bot.start_date'):
                $this->setDate($command);
                $this->telegram->sendMessage($this->chatId, __('bot.night'));
                $this->setLastQuery(__('bot.night'));  
                break;
            case __('bot.night'):
                $this->setNights($command);
                $this->telegram->sendMessage($this->chatId, __('bot.number'));
                $this->setLastQuery(__('bot.number')); 
                break;
            case __('bot.number'):
                $this->setNumber($command);
                $this->telegram->sendMessage($this->chatId, __('bot.children'), $this->checkChildren());
                $this->setLastQuery(__('bot.children'));
                break;
            case __('bot.children'):
                switch ($command){
                    case __('bot.yes'):
                        $this->telegram->sendMessage($this->chatId, __('bot.numberChildren'));
                        $this->telegram->sendMessage($this->chatId, __('bot.numberBaby'));
                        $this->setLastQuery(__('bot.numberBaby'));
                        break;
                    case __('bot.no'):
                        $this->telegram->sendMessage($this->chatId, __('bot.bilet'));
                        $this->setNumberChildren(0);

                        $this->setLastQuery(__('bot.bilet')); 

                        break;
                    default :
                        $this->telegram->sendMessage($this->chatId, __('Xato javob yuborildi'));exit;
                        break;
                }
                break;
            case __('bot.numberBaby'):
                $this->setNumberChildren($command);
                $this->telegram->sendMessage($this->chatId, __('bot.bilet'));
                $this->setLastQuery(__('bot.bilet'));
                break;
            case __('bot.bilet'):
                    switch ($command)
                    {
                        case __('bot.yes'):
                            $this->telegram->sendMessage($this->chatId, __('bot.numberChildren'));
                            $this->telegram->sendMessage($this->chatId, __('bot.numberBaby'));
                            $this->setLastQuery(__('bot.numberBaby'));
                            break;
                        case __('bot.no'):
                            $this->telegram->sendMessage($this->chatId, __('bot.bilet'));
                            $this->setLastQuery(__('bot.bilet')); 
                            break;
                        default :
                            $this->telegram->sendMessage($this->chatId, __('Xato javob yuborildi'));exit;
                            break;
                    }
                    break;
            default:
                $this->telegram->sendMessage($this->chatId,"buyruq topilmadi");
               break;
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