<?php

namespace App\Repositories;

use App\Interfaces\TripRepositoryInterface;
use App\Models\Trip;

class TripRepository implements TripRepositoryInterface 
{
    public function getTrips($lang)
    
    {
        return Trip::translatedIn($lang)->get();
    }

    // public function getTripById($countryId) 
    // {
    //     return Trip::findOrFail($countryId);
    // }



    public function createTrip(array $countryDetails) 
    {
        return Trip::create($countryDetails);
    }



}