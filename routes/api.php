<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;

// Routes publiques (non authentifiées)
Route::prefix('v1')->group(function () {
    // Établissements publics
    Route::get('/etablissements', [PublicController::class, 'etablissements']);
    Route::get('/etablissements/nearby', [PublicController::class, 'nearby']);
    Route::get('/etablissements/{id}', [PublicController::class, 'show']);
    
    // Statistiques publiques
    Route::get('/statistics', [PublicController::class, 'statistics']);
    
    // Données de référence
    Route::get('/references/{type}', [PublicController::class, 'references']);
});

// Routes d'authentification
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');
});

// Routes admin (authentifiées)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    
    // CRUD Établissements
    Route::get('/etablissements', [AdminController::class, 'etablissements']);
    Route::post('/etablissements', [AdminController::class, 'store']);
    Route::put('/etablissements/{id}', [AdminController::class, 'update']);
    Route::delete('/etablissements/{id}', [AdminController::class, 'destroy']);
    
    // Actions en masse
    Route::delete('/etablissements/bulk-delete', [AdminController::class, 'bulkDelete']);
    Route::put('/etablissements/bulk-update', [AdminController::class, 'bulkUpdate']);
    
    // Import/Export
    Route::post('/etablissements/import', [AdminController::class, 'import']);
    Route::get('/etablissements/export', [AdminController::class, 'export']);
    
    // Gestion utilisateurs
    Route::get('/users', [AdminController::class, 'users']);
    Route::post('/users', [AdminController::class, 'createUser']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
});

// Routes de compatibilité (pour l'ancien contrôleur)
// À supprimer plus tard quand le frontend sera migré
use App\Http\Controllers\EtablissementController;

Route::get('/etablissements-legacy', [EtablissementController::class, 'index']);
Route::get('/etablissements-legacy/search', [EtablissementController::class, 'search']);
Route::get('/etablissements-legacy/map', [EtablissementController::class, 'map']);
Route::get('/etablissements-legacy/{id}', [EtablissementController::class, 'show']);