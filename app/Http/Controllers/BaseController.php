<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class BaseController extends Controller
{
    

public function sendPhoneNumber()
{
    $data[] =  [$this->telegram->makeButton("send my number", true)];
    return $data;
}

public function setCountry($name)
{
    $country= $this->country->getCountryByName($name);
    if($country)
    {
        $countryId= $country->id;
    }
    else
    {
        $country= $this->country->getCountryBySimilar($name);
        $this->showSimilar($country);
        exit;
    }
    $this->setCountries($countryId);
}
// public function setHotel($name)
// {
//     $this->order->addHotel($countryId );
// }



protected function chooseLanguage()
{
    $data[] = [$this->telegram->makeButton("Russian"),$this->telegram->makeButton("Uzbek(Latin)")];
    return $data;
}
protected function nextButton()
{
    $data[] = [$this->telegram->makeButton(__('bot.next'))];
    return $data;
}
 public function chooseTrip()
 {
    $trips= $this->trip->getTrips($this->user->lang);
    $data=[];
    foreach($trips as $trip)
    {
        $data[]=[$this->telegram->makeButton($trip->translate($this->user->lang)->name)];
    } 
        return $data ;
 }
 public function checkChildren()
 {
    $data[] = [$this->telegram->makeButton(__('bot.yes')),$this->telegram->makeButton(__('bot.no'))];
    return $data;
 }
public function chooseCountry()
{
    $countries = $this->order->getPopularCountries($this->user->lang);
    $data=[];
    
    if(count($countries)%2)
    {
            $last_option=count($countries)-1;

        for($i=0; $i<count($countries)-1; $i+=2 ){
            $data[]=
            [
                 $this->telegram->makeButton($countries[$i]?->country->translate($this->user->lang)->name), 
                 $this->telegram->makeButton($countries[$i+1]?->country->translate($this->user->lang)->name)
             ];
         }
         $data[]=[
                 $this->telegram->makeButton($countries[$last_option]->country->translate($this->user->lang)->name),
                ]; 
    }
    else
    {
        for($i=0; $i<count($countries)-1; $i+=2 ){
            $data[]=[$this->telegram->makeButton($countries[$i]?->country->translate($this->user->lang)->name), 
            $this->telegram->makeButton($countries[$i+1]?->country->translate($this->user->lang)->name)];
         } 
    }
       return $data;
}


public function showSimilar($country)
{
    $this->telegram->sendMessage($this->chatId, "Bu mamlakat topilmadi unga o'xshashlaridan tanlang", $this->chooseSimilar($country));
    
}
public function chooseSimilar($countries)
{
    if(!$countries->isEmpty()){
        foreach($countries as $country)
        {
            $data[]=[$this->telegram->makeButton($country->translate($this->user->lang)->name)];
        }
        return $data; 
    } else{
       return $this->chooseCountry();
    }
   
    
       
}
public function setTrip($trip)
{
    $trip= $this->trip->getTripByName($trip);
    if($trip)
    {
        $tripId= $trip->id;
    }
    else
    {
    $this->telegram->sendMessage($this->chatId, "Bu sayohat turi topilmadi");
       
    }
    $this->user->trip = $tripId;
    $this->putFile(); 
}
public function setHotel($hotel)
{
    $this->user->hotel = $hotel;
    $this->putFile(); 
}
public function setDate($date)
{
    $this->user->start_date = $date;
    $this->putFile(); 
}
public function setNights($night)
{
    $this->user->night = $night;
    $this->putFile(); 
}
public function setNumber($number)
{
    $this->user->number = $number;
    $this->putFile(); 
}
public function setNumberChildren($number)
{
    $this->user->numberChildren = $number;
    $this->putFile(); 
}
public function setCountries($country)
{
    $this->user->country = $country;
    $this->putFile(); 
}
public function setLastQuery($lastQuery)
{
    $this->user->last_query = $lastQuery;
    $this->putFile();
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
    $this->putFile();
    
}
public function setLanguage($lang)
{
    App::setLocale($lang);
    $this->user->lang= $lang;
    $this->putFile();
}
public function getFromFile($userId)
{
    $json=@file_get_contents(public_path('tg-data/'.$userId.'.json'));
    return json_decode($json); 
}

public function putFile()
{
    @file_put_contents(public_path('tg-data/'.$this->user->id.'.json'), json_encode($this->user));
}




public function validatePhone($command)
{
    $nameArr = ['name'=>$command];
    $validator = Validator::make($nameArr, ['name' => 'regex:/^[0-9]+$/'], ['name.regex' => 'invalid name']);
        if ($validator->fails()) 
        {
             $this->telegram->sendMessage($this->chatId, __('bot.phone_validation'), $this->sendPhoneNumber());exit;
        }
}
public function validateData($command)
{
    $nameArr = ['date'=>$command];
    $validator = Validator::make($nameArr, ['date' => 'date_format:d.m.y'], ['date.regex' => 'invalid date']);
     var_dump($validator); exit;

        if ($validator->fails()) 
        {
             $this->telegram->sendMessage($this->chatId, __('bot.date_validation'));exit;
        }
}
}