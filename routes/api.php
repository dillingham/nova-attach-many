<?php

Route::get('/{resource}/attachable/{relationship}', '\NovaAttachMany\Http\Controllers\AttachController@create');
Route::get('/{resource}/{resourceId}/attachable/{relationship}', '\NovaAttachMany\Http\Controllers\AttachController@edit');