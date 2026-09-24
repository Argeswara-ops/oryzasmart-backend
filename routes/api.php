<?php

use App\Http\Controllers\Api\ActuatorController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CameraController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DetectionController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\SensorController;
use Illuminate\Support\Facades\Route;

// Public Endpoints
Route::post('/login', [AuthController::class, 'login']);

// Vision Service Endpoint (External Vision Python Service)
Route::post('/vision/detections', [DetectionController::class, 'store']);

// Authenticated Endpoints
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Devices
    Route::get('/devices', [DeviceController::class, 'index']);
    Route::get('/devices/{id}', [DeviceController::class, 'show']);
    Route::get('/devices/{id}/status', [DeviceController::class, 'status']);

    // Sensors
    Route::get('/sensors/latest', [SensorController::class, 'latest']);
    Route::get('/sensors/history', [SensorController::class, 'history']);

    // Cameras
    Route::get('/cameras', [CameraController::class, 'index']);
    Route::get('/cameras/{id}', [CameraController::class, 'show']);
    Route::get('/cameras/{id}/status', [CameraController::class, 'status']);

    // Detections
    Route::get('/detections', [DetectionController::class, 'index']);
    Route::get('/detections/latest', [DetectionController::class, 'latest']);
    Route::get('/detections/{id}', [DetectionController::class, 'show']);

    // Alerts
    Route::get('/alerts', [AlertController::class, 'index']);
    Route::get('/alerts/{id}', [AlertController::class, 'show']);
    Route::post('/alerts/{id}/read', [AlertController::class, 'markAsRead']);

    // Recommendations
    Route::get('/recommendations', [RecommendationController::class, 'index']);
    Route::get('/recommendations/latest', [RecommendationController::class, 'latest']);
    Route::get('/recommendations/{id}', [RecommendationController::class, 'show']);

    // Actuators
    Route::post('/actuators/fan/control', [ActuatorController::class, 'controlFan']);
    Route::get('/actuators/fan/status', [ActuatorController::class, 'statusFan']);
    Route::get('/actuators/logs', [ActuatorController::class, 'logs']);
});
