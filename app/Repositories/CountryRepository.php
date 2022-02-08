<?php

namespace App\Repositories;

use App\Interfaces\CountryRepositoryInterface;
use App\Models\Country;
use App\Models\UserAndCountry;
use Illuminate\Support\Facades\DB;

class CountryRepository implements CountryRepositoryInterface 
{
    public function getAllCountries($lang) 
    {
        return Country::translatedIn($lang)->get();
    }
   
    public function getCountryById($countryId) 
    {
        return Country::findOrFail($countryId);
    }

    public function getCountryByName($countryName) 
    {
       return $country = Country::whereTranslation('name', $countryName)->first();
    }


    public function getCountryBySimilar($countryName) 
    {
       
        return $country = Country::whereTranslationLike('name', "%" . $countryName . "%")->get();
    }

    // public function deleteCountry($countryId) 
    // {
    //     Country::destroy($countryId);
    // }

    public function createCountry(array $countryDetails) 
    {
        return Country::create($countryDetails);
    }

    // public function updateCountry($countryId, array $newDetails) 
    // {
    //     return Country::whereId($countryId)->update($newDetails);
    // }

    // public function getFulfilledCountrys() 
    // {
    //     return Country::where('is_fulfilled', true);
    // }
}