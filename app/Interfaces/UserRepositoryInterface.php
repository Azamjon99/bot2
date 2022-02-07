<?php

namespace App\Interfaces;

interface UserRepositoryInterface 
{
    // public function getUsers();
    public function getUserById($userId);
    // public function deleteUser($orderId);
    public function createUser(array $userDetails);
    // public function updateUser($orderId, array $newDetails);
    // public function getFulfilledUsers();
}