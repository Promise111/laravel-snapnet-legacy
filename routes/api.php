<?php

use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::post('/employees', [EmployeeController::class, 'store']);

