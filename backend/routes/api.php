<?php

use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\RouteAssignmentController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::apiResource('packages', PackageController::class);
Route::apiResource('routes', RouteController::class);
Route::post('routes/{route}/assign', [RouteAssignmentController::class, 'assign']);
Route::post('routes/{route}/unassign', [RouteAssignmentController::class, 'unassign']);
Route::get('metrics', [MetricsController::class, 'index']);
Route::get('settings', [SettingsController::class, 'index']);
Route::put('settings', [SettingsController::class, 'update']);

Route::post('/evaluations', [EvaluationController::class, 'store']);
Route::get('/evaluations', [EvaluationController::class, 'index']);
Route::get('/evaluations/{id}', [EvaluationController::class, 'show'])->whereNumber('id');
Route::get('/evaluations/{id}/files/{filename}', [EvaluationController::class, 'file'])->whereNumber('id');
