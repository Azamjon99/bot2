<?php

namespace App\Repositories;

use App\Interfaces\CountryRepositoryInterface;
use App\Models\Country;

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



    public function createCountry(array $countryDetails) 
    {
        return Country::create($countryDetails);
    }


}