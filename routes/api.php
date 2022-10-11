<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/login', [App\Http\Controllers\Api\AuthenticationController::class, 'login']);
Route::post('/register', [App\Http\Controllers\Api\AuthenticationController::class, 'register']);

Route::group(['middleware'=>['auth:api']], function() 
{

    Route::post('/logout', [App\Http\Controllers\Api\AuthenticationController::class, 'logout']);
    Route::post('/refresh', [App\Http\Controllers\Api\AuthenticationController::class, 'refresh']);
    Route::post('/profile', [App\Http\Controllers\Api\AuthenticationController::class, 'profile']);
    
    Route::post('/follow/{username}', [App\Http\Controllers\Api\FollowerController::class, 'follow']);
    Route::post('/unfollow/{username}', [App\Http\Controllers\Api\FollowerController::class, 'unfollow']);
});
