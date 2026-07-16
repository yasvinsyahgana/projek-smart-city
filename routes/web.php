<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;

// ===== LOGIN ROUTES (PUBLIC - TANPA MIDDLEWARE) =====
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ===== PROTECTED ROUTES (HARUS LOGIN - DENGAN MIDDLEWARE) =====
Route::middleware('admin')->group(function () {
    
    // Redirect root ke dashboard
    Route::get('/', function () {
        return redirect('/smart-lamp');
    });

    // Dashboard Pages
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/smart-lamp', [DashboardController::class, 'smartLamp'])->name('smart-lamp');
    Route::get('/smart-waste', [DashboardController::class, 'smartWaste'])->name('smart-waste');
    Route::get('/smart-parking', [DashboardController::class, 'smartParking'])->name('smart-parking');
    
    // Control Center
    Route::get('/control', [DashboardController::class, 'control'])->name('control');
    Route::get('/control-center', [DashboardController::class, 'control'])->name('control-center');

    // ===== API ENDPOINTS (DATA) =====
    Route::get('/api/lamp', [DashboardController::class, 'apiLampData']);
    Route::get('/api/waste', [DashboardController::class, 'apiWasteData']);
    Route::get('/api/parking', [DashboardController::class, 'apiParkingData']);
    Route::get('/api/dashboard', [DashboardController::class, 'apiDashboardData']);

    // ===== API ENDPOINTS (CONTROL) =====
    Route::post('/api/lamp/control', [DashboardController::class, 'controlLamp']);
    Route::post('/api/control/mode', [DashboardController::class, 'setControlMode']);
    Route::post('/api/sensor-level', [DashboardController::class, 'setSensorLightLevel']);

    // ===== AUTO SETTINGS =====
    Route::get('/api/auto-settings', [DashboardController::class, 'getAutoSettings']);
    Route::post('/api/auto-settings', [DashboardController::class, 'updateAutoSettings']);
    
    // Toggle Auto Schedule ON/OFF
    Route::post('/api/auto-schedule/toggle', [DashboardController::class, 'toggleAutoSchedule']);
    
    // Toggle Auto Sensor ON/OFF
    Route::post('/api/auto-sensor/toggle', [DashboardController::class, 'toggleAutoSensor']);
    
});