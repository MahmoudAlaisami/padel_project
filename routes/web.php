<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', fn () => view('welcome'))->name('home');

// Auth (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/signin',  [AuthController::class, 'showSignIn'])->name('signin');
    Route::post('/signin', [AuthController::class, 'signIn'])->name('signin.post');
    Route::get('/signup',  [AuthController::class, 'showSignUp'])->name('signup');
    Route::post('/signup', [AuthController::class, 'signUp'])->name('signup.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// User (authenticated, non-admin)
Route::middleware(['auth', 'user.only'])->group(function () {
    Route::get('/dashboard',    [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard',   [DashboardController::class, 'cancel'])->name('dashboard.cancel');
    Route::get('/reservation',  [ReservationController::class, 'create'])->name('reservation.create');
    Route::post('/reservation', [ReservationController::class, 'store'])->name('reservation.store');
});

// Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/',               [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reservations',   [Admin\ReservationController::class, 'index'])->name('reservations');
    Route::post('/reservations',  [Admin\ReservationController::class, 'action'])->name('reservations.action');
    Route::get('/items',          [Admin\ItemController::class, 'index'])->name('items');
    Route::post('/items/category',        [Admin\ItemController::class, 'addCategory'])->name('items.add_category');
    Route::post('/items/type',            [Admin\ItemController::class, 'addType'])->name('items.add_type');
    Route::post('/items/type/edit',       [Admin\ItemController::class, 'editType'])->name('items.edit_type');
    Route::post('/items/type/delete',     [Admin\ItemController::class, 'deleteType'])->name('items.delete_type');
    Route::post('/items/category/delete', [Admin\ItemController::class, 'deleteCategory'])->name('items.delete_category');
    Route::get('/users',          [Admin\UserController::class, 'index'])->name('users');
    Route::post('/users',         [Admin\UserController::class, 'action'])->name('users.action');
});
