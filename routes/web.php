<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportExportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/', \App\Livewire\PublicBookingEngine::class)->name('home');
Route::get('/booking', \App\Livewire\PublicBookingEngine::class)->name('booking.public');
Route::get('/berita', \App\Livewire\PublicNewsList::class)->name('news.index');
Route::get('/berita/{slug}', \App\Livewire\PublicNewsDetail::class)->name('news.detail');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/rooms', \App\Livewire\RoomManagement::class)
    ->middleware(['auth', 'verified'])
    ->name('rooms.index');

Route::get('/reservations', \App\Livewire\ReservationManagement::class)
    ->middleware(['auth', 'verified'])
    ->name('reservations.index');

Route::get('/meeting-rooms', \App\Livewire\MeetingRoomManagement::class)
    ->middleware(['auth', 'verified'])
    ->name('meeting-rooms.index');

Route::get('/dining', \App\Livewire\DiningReservationManagement::class)
    ->middleware(['auth', 'verified'])
    ->name('dining.index');

Route::get('/calendar', \App\Livewire\UnifiedReservationCalendar::class)
    ->middleware(['auth', 'verified'])
    ->name('calendar.index');

Route::get('/cashier', \App\Livewire\CashierShiftManager::class)
    ->middleware(['auth', 'verified'])
    ->name('cashier.index');

Route::get('/expenses', \App\Livewire\ExpenseManager::class)
    ->middleware(['auth', 'verified'])
    ->name('expenses.index');

Route::get('/night-audit', \App\Livewire\NightAuditWizard::class)
    ->middleware(['auth', 'verified', 'role:Manager|Admin|admin'])
    ->name('night-audit.index');

Route::get('/inventory', \App\Livewire\InventoryManager::class)
    ->middleware(['auth', 'verified'])
    ->name('inventory.index');

Route::get('/assets', \App\Livewire\FixedAssetManager::class)
    ->middleware(['auth', 'verified'])
    ->name('assets.index');

Route::get('/reports', \App\Livewire\ReportDashboard::class)
    ->middleware(['auth', 'verified', 'role:Manager|Admin|admin'])
    ->name('reports.index');

Route::get('/settings', \App\Livewire\AppSettings::class)
    ->middleware(['auth', 'verified', 'role:Manager|Admin|admin'])
    ->name('settings.index');

Route::get('/users', \App\Livewire\UserManagement::class)
    ->middleware(['auth', 'verified', 'role:Manager|Admin|admin'])
    ->name('users.index');

Route::get('/roles', \App\Livewire\RoleManagement::class)
    ->middleware(['auth', 'verified', 'role:Manager|Admin|admin'])
    ->name('roles.index');

Route::get('/web-admin', \App\Livewire\WebAdministration::class)
    ->middleware(['auth', 'verified', 'role:Manager|Admin|admin'])
    ->name('web-admin.index');

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Billing & Receipt routes
Route::get('/invoice/{id}/pdf', [ReceiptController::class, 'downloadPdf'])->name('invoice.pdf')->middleware('auth');
Route::get('/invoice/{id}/thermal', [ReceiptController::class, 'printThermal'])->name('invoice.thermal')->middleware('auth');
Route::get('/meeting/{id}/pdf', [ReceiptController::class, 'downloadMeetingPdf'])->name('meeting.pdf')->middleware('auth');
Route::get('/meeting/{id}/thermal', [ReceiptController::class, 'printMeetingThermal'])->name('meeting.thermal')->middleware('auth');
Route::get('/dining/{id}/pdf', [ReceiptController::class, 'downloadDiningPdf'])->name('dining.pdf')->middleware('auth');
Route::get('/dining/{id}/thermal', [ReceiptController::class, 'printDiningThermal'])->name('dining.thermal')->middleware('auth');
Route::get('/verify-document/{token}', [ReceiptController::class, 'verifyPublic'])->name('document.verify');

// Report Export routes
Route::get('/reports/export/csv', [ReportExportController::class, 'exportCsv'])->name('reports.export.csv')->middleware('auth');
Route::get('/reports/export/pdf', [ReportExportController::class, 'exportPdf'])->name('reports.export.pdf')->middleware('auth');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
