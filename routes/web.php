<?php

use App\Http\Controllers\Admin\AdminActuatorController;
use App\Http\Controllers\Admin\AdminAlertController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDeviceController;
use App\Http\Controllers\Admin\AdminSensorController;
use App\Http\Controllers\Admin\AdminThresholdController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVisionController;
use App\Http\Controllers\Auth\WebAuthController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Authentication Web Routes
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Protected Admin Panel Routes
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Users Management (CRUD)
    Route::resource('users', AdminUserController::class);

    // Devices Management (CRUD)
    Route::resource('devices', AdminDeviceController::class);

    // Sensors Monitoring & History
    Route::get('/sensors', [AdminSensorController::class, 'index'])->name('sensors.index');

    // Thresholds Configuration
    Route::get('/thresholds', [AdminThresholdController::class, 'index'])->name('thresholds.index');
    Route::put('/thresholds/{sensorType}', [AdminThresholdController::class, 'update'])->name('thresholds.update');
    Route::post('/thresholds/{sensorType}/reset', [AdminThresholdController::class, 'reset'])->name('thresholds.reset');

    // Actuators & Fan Control
    Route::get('/actuators', [AdminActuatorController::class, 'index'])->name('actuators.index');
    Route::post('/actuators/control', [AdminActuatorController::class, 'control'])->name('actuators.control');

    // Alerts & Notifications
    Route::get('/alerts', [AdminAlertController::class, 'index'])->name('alerts.index');
    Route::post('/alerts/{alert}/read', [AdminAlertController::class, 'markAsRead'])->name('alerts.read');
    Route::post('/alerts/read-all', [AdminAlertController::class, 'markAllAsRead'])->name('alerts.read_all');
    Route::delete('/alerts/{alert}', [AdminAlertController::class, 'destroy'])->name('alerts.destroy');

    // Vision Scans & Paddy Disease Detection
    Route::get('/vision', [AdminVisionController::class, 'index'])->name('vision.index');
    Route::delete('/vision/{scan}', [AdminVisionController::class, 'destroy'])->name('vision.destroy');
});
