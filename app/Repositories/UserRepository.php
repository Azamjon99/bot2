<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface 
{
    // public function getAllOrders() 
    // {
    //     return User::all();
    // }

    public function getUserById($userId) 
    {
        return User::findOrFail($userId);
    }

    // public function deleteOrder($userId) 
    // {
    //     User::destroy($userId);
    // }

    public function createUser(array $userDetails) 
    {
        return User::create($userDetails);
    }

    // public function updateOrder($userId, array $newDetails) 
    // {
    //     return User::whereId($userId)->update($newDetails);
    // }

    // public function getFulfilledOrders() 
    // {
    //     return User::where('is_fulfilled', true);
    // }
}