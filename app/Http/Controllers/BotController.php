<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\TripRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Trip;
use App\Repositories\TripRepository;
use App\Services\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Null_;
use App\Repositories\UserRepository;

class BotController extends Controller
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
            if (User::where('id' , $userId )->exists()) 
            {
                $this->user = $user->getUserById($userId);
                App::setLocale($this->user->language);
            } 
            else 
            {
                $userData = ['id' => $userId];
                $this->user = $user->createUser($userData);
            }
        }

        if ($command == '/start') 
        {
            $this->telegram->sendMessage($this->chatId,"Выберите язык", $this->chooseLanguage());
            $this->telegram->sendMessage($this->chatId,"Tilni tanlang", $this->chooseLanguage());
        } 
        elseif($command == 'Russian') 
        {
            $this->setLanguage('ru');
            $this->setLastQuery(__('bot.name'));
            $this->telegram->sendMessage($this->chatId, __('bot.language'));
            $this->telegram->sendMessage($this->chatId, __('bot.name'));
        }
        elseif($command == 'Uzbek(Latin)') 
        {
            $this->setLanguage('uz');
            $this->setLastQuery(__('bot.name'));
            $this->telegram->sendMessage($this->chatId, __('bot.language'));
            $this->telegram->sendMessage($this->chatId,__('bot.name'));
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
            $this->telegram->sendMessage($this->chatId, __('bot.country'), $this->chooseCountry());
            $this->setCountry($command);
            $this->setLastQuery(__('bot.country'));
        }
        elseif($this->user->last_query == __('bot.country'))
        {
            $this->telegram->sendMessage($this->chatId, "", $this->chooseCountry());
            $this->setLastQuery(__('bot.country'));

        }

   

}


protected function chooseLanguage()
{
    $data[] = [$this->telegram->makeButton("Russian"),$this->telegram->makeButton("Uzbek(Latin)")];
    return $data;
}

public function sendPhoneNumber()
{
    $data[] =  [$this->telegram->makeButton("send my number", true)];
    return $data;
}

public function setLastQuery($lastQuery)
{
    $this->user->last_query = $lastQuery;
    $this->user->save();
}
public function setPhone($command)
{
    if($command)
    {
        $this->validatePhone($command);

        $this->user->phone_number = $command;
    }
    elseif($this->phone_number)
    {
        $this->user->phone_number = $this->phone_number;
    }
    $this->user->save();
}
public function setLanguage($lang)
{
    App::setLocale($lang);
    $this->user->language= $lang;
    $this->user->save();
}

public function setCountry($name)
{
  
    $countryId= $this->country->getCountryByName($name)?->id;
 
    $id = $this->user->id;
    $this->order->addCountry($id,$countryId );
}
public function validatePhone($command)
{
    $nameArr = ['name'=>$command];
    $validator = Validator::make($nameArr, 
            [
              'name' => 'regex:/^[0-9]+$/',
            ], 
            [
                'name.regex' => 'invalid name'
            ]
        );
        if ($validator->fails()) 
        {
            $this->telegram->sendMessage($this->chatId, __('bot.phone_validation'), $this->sendPhoneNumber());exit;
        }
}


 public function chooseTrip()
 {
    $trips= $this->trip->getTrips($this->user->language);
    // var_dump($trips);
    $data=[];
    foreach($trips as $trip)
    {
        $data[]=[$this->telegram->makeButton($trip->translate($this->user->language)->name)];
    } 
        return $data ;
 }
public function chooseCountry()
{
    $countries = $this->order->getPopularCountries($this->user->language);
    $data=[];
    
    if(count($countries)%2)
    {
            $last_option=count($countries)-1;

        for($i=0; $i<count($countries)-1; $i+=2 ){
            $data[]=
            [
                 $this->telegram->makeButton($countries[$i]?->country->translate($this->user->language)->name), 
                 $this->telegram->makeButton($countries[$i+1]?->country->translate($this->user->language)->name)
             ];
         }
         $data[]=[
                 $this->telegram->makeButton($countries[$last_option]->country->translate($this->user->language)->name),
                
                ]; 
    }else
    {
        for($i=0; $i<count($countries)-1; $i+=2 ){
            $data[]=
            [
                 $this->telegram->makeButton($countries[$i]?->country->translate($this->user->language)->name), 
                 $this->telegram->makeButton($countries[$i+1]?->country->translate($this->user->language)->name)
             ];
         } 
    }


       return $data;
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