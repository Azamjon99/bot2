<?php

namespace App\Interfaces;

interface TripRepositoryInterface 
{
    public function getTrips($lang);
    public function getTripByName($name);

    public function createTrip(array $userDetails);

}