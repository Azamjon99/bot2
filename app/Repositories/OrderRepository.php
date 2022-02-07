<?php

namespace App\Repositories;

use App\Interfaces\OrderRepositoryInterface;
use App\Models\Country;
use App\Models\UserAndCountry;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    public function getPopularCountries($lang) 
    {
        return UserAndCountry::select('country_id', DB::raw('COUNT(country_id) as count'))
        ->groupBy('country_id')
        ->orderBy('count', 'desc')
        ->get();   
    }
     public function getAllCountries()
     {
         return UserAndCountry::all();
     }

     public function updateOrder($array)
     {
        
     }
    public function addCountry($id,$countryId )
    {
        $order = new UserAndCountry();
        $order->user_id = $id;
        $order->country_id = $countryId;
        $order->save;

    }

    }