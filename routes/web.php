<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SMSBulkController;

// routes/web.php  (or api.php if you prefer)
 
Route::view('/sms-upload', 'sms-upload');       // GET – shows the upload form
Route::post('/send-sms-excel', [SMSBulkController::class, 'sendFromExcel']); // POST – handles the upload
