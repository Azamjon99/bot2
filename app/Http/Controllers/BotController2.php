<?php


namespace App\Http\Controllers;

use App\Category;
use App\Product;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Services\Telegram;
use Illuminate\Http\Request;
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


        if ($userId) {
            if (file_exists(public_path('tg-data/'.$userId.'.json'))) {
                $userData = @file_get_contents(public_path('tg-data/'.$userId.'.json'));
                $userData = $userData ? json_decode($userData, true): null;
            } else {
                $userData = [
                    'id' => $userId,
                    'name' => $userData['name'] ?? ($this->requestData['message']['from']['first_name'] ?? null),
                    'last_name' => $userData['last_name'] ?? ($this->requestData['message']['from']['last_name'] ?? null),
                    'username' => $userData['username'] ?? ($this->requestData['message']['from']['username'] ?? null),
                    'last_query' => $lastQuery ?? '/start',
                ];
                @file_put_contents(public_path('tg-data/'.$userId.'.json'), json_encode($userData));
            }
            $this->user = $userData;
            $this->l_query=$userData['last_query'];
        }

        if ($command == '/start') 
    {

            $this->telegram->sendMessage($this->chatId,
            "Assalomu alaykum " . $userData['name'] ." ecopen.uz saytining botiga xush kelibsiz ", $this->mainKeyboard()
            );
            

    } elseif($command=="Maxsulot turlari"){

        $this->telegram->sendMessage($this->chatId,
            "Kategoriya tanlang" , $this->categoriesKeyboard()
        );
    }
    // $category = Category::whereName($command)->first();
    
    // var_dump($products);exit;
  elseif($category=Category::where('name', $command)->first()){

    foreach($category->products as $product){
        $this->telegram->sendMessage($this->chatId,
            $product->name . " - $" . $product->price, $this->inlineOptionsKeyboard($product), true, true
        );
        
    }
  }

  elseif($this->callback_data)
  {
  
     $product=Product::where('id', $this->callback_data)->first();
     
     $this->telegram->editMessageText($this->call_Message_id, $this->message_id,
     $product->name . "  choosed  - $" . $product->price , $this->inlineOptionsKeyboard($product),  true, true
 );
  }

    elseif($command=="Ortga"){
        $this->telegram->sendMessage($this->chatId,
            "Muvaffaqqiyatli qaytildi", $this->mainKeyboard()
            );
    }


   

}














protected function mainKeyboard()
{
    $data[] = [
        $this->telegram->makeButton("Maxsulot turlari"),
        $this->telegram->makeButton("Мои каналы")
    ];
    return $data;
}
 public function categoriesKeyboard(){
    $categories= Category::all();
 
// $data=[];
  foreach($categories as $key => $category){
        $data[]=[
            $this->telegram->makeButton($category->name)
        ];
 } 
//  $data[]=[
//     $this->telegram->makeButton("Ortga")
// ];

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