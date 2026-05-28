<?php

use App\Http\Controllers\Api\ViesController;
use Illuminate\Support\Facades\Route;

Route::get('/vies/{nif}', [ViesController::class, 'lookup']);