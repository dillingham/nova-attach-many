<?php

use Illuminate\Support\Facades\Route;
use NovaAttachMany\Http\Controllers\AttachController;

Route::get('/{resource}/attachable/{relationship}', [AttachController::class, 'create']);
Route::get('/{resource}/{resourceId}/attachable/{relationship}', [AttachController::class, 'edit']);
