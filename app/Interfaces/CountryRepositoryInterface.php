<?php

namespace App\Interfaces;

interface CountryRepositoryInterface 
{
    public function getAllCountries($lang);
    public function getCountryById($countryId);
    public function getCountryByName($countryName);
    public function getCountryBySimilar($countryName);
    // public function deleteUser($orderId);
    public function createCountry(array $countryDetails);
    
    // public function updateUser($orderId, array $newDetails);
    // public function getFulfilledUsers();
}