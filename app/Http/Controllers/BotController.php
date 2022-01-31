<?php


namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Services\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Null_;

class BotController extends Controller
{
    protected $telegram;
    protected $requestData;
    protected $chatId;
    protected $callback_data;
    protected $call_Message_id;
    protected $message_id;
    protected $user;
    protected $l_query;
   
    public function __construct()
    {
        $this->telegram = new Telegram();
    }

    public function index(Request $request){

        $this->requestData = $this->telegram->requestData($request->all());
        $this->chatId = $this->requestData['message']['chat']['id'] ?? null;
        $command = $this->requestData['message']['text'] ?? null;
        $userId = $this->requestData['message']['from']['id'] ?? null;
        $forwardFromChat = $this->requestData['message']['forward_from_chat'] ?? null;
        $this->callback_data = $this->requestData['callback_query']['data'] ?? null;
        $this->call_Message_id = $this->requestData['callback_query']['message']['chat']['id'] ?? null;
        $this->message_id = $this->requestData['callback_query']['message']['message_id'] ?? null;
        // dd('sss');
// var_dump('ddd');exit;

        if ($userId) {
            if (User::where('id' , $userId )->exists()) {
                        
                $user = User::find($userId);
                App::setLocale($user->language);

                } else {
                $userData = [
                    'id' => $userId,
                    // 'username' => $userData['username'] ?? ($this->requestData['message']['from']['username'] ?? null),
                    'last_query' => $lastQuery ?? '/start',
                ];
                $user = new User;
                $user->create($userData);
            }
       
        }

        // if ($command == '/start') 
        // {
        //     // dd('dd');
          

        // } 

        switch($command){
            case "/start":
                $this->telegram->sendMessage($this->chatId,
                "Выберите язык", $this->chooseLanguage()
                );
                $this->telegram->sendMessage($this->chatId,
                "Tilni tanlang", $this->chooseLanguage()
                );
                break;
            case "Russian":

                 $this->telegram->sendMessage($this->chatId,
                 __('bot.language')
                    );
                $this->telegram->sendMessage($this->chatId,
                    __('bot.name')
                    );
                break;    
            case "Uzbek(Latin)":
                $user->language= 'uz';
                $user->save();
                App::setLocale('uz');

                $this->telegram->sendMessage($this->chatId,
                __('bot.language')
                );
                $this->telegram->sendMessage($this->chatId,
                __('bot.name')
                );
                break;    
            default:
                echo "No information available for that day.";
                break;
        }

   

}














protected function chooseLanguage()
{
    $data[] = [
        $this->telegram->makeButton("Russian"),
        $this->telegram->makeButton("Uzbek(Latin)")
    ];
    return $data;
}
 public function categoriesKeyboard()
 {
    $categories= Category::all();
 
  foreach($categories as $key => $category)
  {
        $data[]=[
            $this->telegram->makeButton($category->name)
        ];
  } 


     return $data;

 }
    public function productsKeyboard($category)
    {
        //  $categories = Category::all();
        // foreach($categories as $category){
        foreach($category->products as $product) {
            $data[] = [
                $this->telegram->makeButton($product->name),
            ];
        }
        return $data;

    }


    public function inlineOptionsKeyboard($product){

        $options=$product->productOptionValues;
        $optionPrice=$product->productValues;
        $keyboard = [
            
        ];
if(count($options)%2){
    $last_option=count($options)%2;
    for($i=0; $i<count($options)-1; $i+=2 ){
        $keyboard[]=[
            $this->telegram->makeInlineButton($options[$i]['name'] . " - " . $optionPrice[$i]['value'] ,
            "https://core.telegram.org/bots/api#inlinekeyboardbutton" ),
            $this->telegram->makeInlineButton($options[$i+1]['name'] . " - " . $optionPrice[$i]['value'] ,
            "https://core.telegram.org/bots/api#inlinekeyboardbutton" ),
    
        ];
    }
    $keyboard[]=[
        $this->telegram->makeInlineButton($options[$last_option]['name'],"https://core.telegram.org/bots/api#inlinekeyboardbutton" ),
    
    ];
}else{
for($i=0; $i<count($options)-1; $i+=2 ){
    $keyboard[]=[
        $this->telegram->makeInlineButton1($options[$i]['name']. " - " . $optionPrice[$i]['value'],
        $optionPrice[$i]['product_id']  ),
       $this->telegram->makeInlineButton1($options[$i+1]['name']. " - " . $optionPrice[$i]['value'],
       $optionPrice[$i+1]['product_id']),

    ];
}
}
$keyboard[]=[
    $this->telegram->makeInlineButton("orqaga","https://core.telegram.org/bots/api#inlinekeyboardbutton" ),

];

        return $keyboard;
     
    }








    public function setLastQuery($lastQuery)
    {
        $this->l_query = $lastQuery;
        @file_put_contents(public_path('tg-data/'.$this->user['id'].'.json'), json_encode($this->user));
    }


    
}