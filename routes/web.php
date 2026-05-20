<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;

// Root
Route::get('/', fn() => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

// Auth
Route::get( '/login',  [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login/request-code',  [LoginController::class, 'requestCode'])->name('login.request-code');
Route::get( '/login/verify', [LoginController::class, 'showVerifyForm'])->name('login.verify.show');
Route::post('/login/verify', [LoginController::class, 'verifyCode'])->name('login.verify');
Route::post('/login/resend-code', [LoginController::class, 'resendCode'])->name('login.resend-code');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global search (AJAX)
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // Books
    Route::resource('books', BookController::class);

    // Members
    Route::resource('members', MemberController::class);

    // Borrowings
    Route::resource('borrowings', BorrowingController::class)->only(['index','create','store','show']);
    Route::post('borrowings/{borrowing}/return',   [BorrowingController::class, 'return'])  ->name('borrowings.return');
    Route::post('borrowings/{borrowing}/pay-fine', [BorrowingController::class, 'payFine']) ->name('borrowings.payFine');

    // Reservations
    Route::resource('reservations', ReservationController::class)->only(['index','create','store']);
    Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');

    // Categories
    Route::resource('categories', CategoryController::class);

    // Reports
    Route::get('reports',          [ReportController::class, 'index'])  ->name('reports.index');
    Route::get('reports/daily',    [ReportController::class, 'daily'])  ->name('reports.daily');
    Route::get('reports/monthly',  [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('reports/overdue',  [ReportController::class, 'overdue'])->name('reports.overdue');

    // CSV Exports
    Route::get('reports/export/overdue',    [ReportController::class, 'exportOverdueCsv'])   ->name('reports.export.overdue');
    Route::get('reports/export/borrowings', [ReportController::class, 'exportBorrowingsCsv'])->name('reports.export.borrowings');
    Route::get('reports/export/members',    [ReportController::class, 'exportMembersCsv'])   ->name('reports.export.members');
});
