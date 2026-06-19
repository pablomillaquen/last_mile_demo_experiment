<?php

use App\Http\Controllers\DocsController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ExperimentController;
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

Route::get('/experiments', [ExperimentController::class, 'index']);
Route::get('/experiments/{id}', [ExperimentController::class, 'show'])->whereNumber('id');
Route::get('/experiments/{id}/report', [ExperimentController::class, 'report'])->whereNumber('id');
Route::get('/experiments/{id}/report.pdf', [ExperimentController::class, 'reportPdf'])->whereNumber('id');
Route::get('/experiments/{id}/assets/{filename}', [ExperimentController::class, 'asset'])->whereNumber('id');

Route::get('/evaluations/{id}/pdf', [EvaluationController::class, 'pdf'])->whereNumber('id');

Route::get('/docs/{path}', [DocsController::class, 'show'])->where('path', '.*');
