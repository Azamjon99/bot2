<?php

namespace App\Interfaces;

interface OrderRepositoryInterface 
{
    public function getPopularCountries($lang);
    public function getAllCountries();
    public function updateOrder($array);
    public function addCountry($id, $countrId);
    public function addHotel($id, $hotelId);
   
  
}