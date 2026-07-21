<?php

use App\Http\Controllers\Company\DestroyCompanyController;
use App\Http\Controllers\Company\IndexCompanyController;
use App\Http\Controllers\Company\ShowCompanyController;
use App\Http\Controllers\Company\StoreCompanyController;
use App\Http\Controllers\Company\UpdateCompanyController;
use App\Http\Controllers\Department\DestroyDepartmentController;
use App\Http\Controllers\Department\IndexDepartmentController;
use App\Http\Controllers\Department\ShowDepartmentController;
use App\Http\Controllers\Department\StoreDepartmentController;
use App\Http\Controllers\Department\UpdateDepartmentController;
use App\Http\Controllers\Notification\GetUnreadCountNotificationController;
use App\Http\Controllers\Notification\GetUserNotificationsController;
use App\Http\Controllers\Notification\IndexNotificationController;
use App\Http\Controllers\Notification\ReadAllNotificationsController;
use App\Http\Controllers\Notification\ReadNotificationController;
use App\Http\Controllers\User\DestroyUserController;
use App\Http\Controllers\User\GetAuthenticatedUserController;
use App\Http\Controllers\User\GetUserTodaySchedulesAction;
use App\Http\Controllers\User\IndexUserController;
use App\Http\Controllers\User\LogoutController;
use App\Http\Controllers\User\ShowUserController;
use App\Http\Controllers\User\StoreUserController;
use App\Http\Controllers\User\UpdateAuthenticatedUserController;
use App\Http\Controllers\User\UpdateUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'own.user'])->group(function () {
    Route::get('/user', GetAuthenticatedUserController::class);
    Route::put('/user', UpdateAuthenticatedUserController::class);
    Route::post('/logout', LogoutController::class);

    // Notifications
    Route::get('/notifications', IndexNotificationController::class);
    Route::get('/notifications/unread-count', GetUnreadCountNotificationController::class);
    Route::patch('/notifications/read-all', ReadAllNotificationsController::class);
    Route::patch('/notifications/{id}/read', ReadNotificationController::class);
    Route::get('/users/{user}/notifications', GetUserNotificationsController::class);
});

Route::middleware(['auth:api', 'admin'])->group(function () {
    // Companies CRUD
    Route::get('/companies', IndexCompanyController::class);
    Route::post('/companies', StoreCompanyController::class);
    Route::get('/companies/{company}', ShowCompanyController::class);
    Route::put('/companies/{company}', UpdateCompanyController::class);
    Route::patch('/companies/{company}', UpdateCompanyController::class);
    Route::delete('/companies/{company}', DestroyCompanyController::class);

    // Departments CRUD
    Route::get('/departments', IndexDepartmentController::class);
    Route::post('/departments', StoreDepartmentController::class);
    Route::get('/departments/{department}', ShowDepartmentController::class);
    Route::put('/departments/{department}', UpdateDepartmentController::class);
    Route::patch('/departments/{department}', UpdateDepartmentController::class);
    Route::delete('/departments/{department}', DestroyDepartmentController::class);

    // Users CRUD
    Route::get('/users', IndexUserController::class);
    Route::post('/users', StoreUserController::class);
    Route::get('/users/{user}', ShowUserController::class);
    Route::put('/users/{user}', UpdateUserController::class);
    Route::patch('/users/{user}', UpdateUserController::class);
    Route::delete('/users/{user}', DestroyUserController::class);
});

Route::middleware('auth:api')->group(function () {
    // Equipment Module Routes
    if (file_exists(base_path('modules/Masterdata/Equipment/routes.php'))) {
        require base_path('modules/Masterdata/Equipment/routes.php');
    }

    if (file_exists(base_path('modules/Equipment/Checklist/routes.php'))) {
        require base_path('modules/Equipment/Checklist/routes.php');
    }

    if (file_exists(base_path('modules/Equipment/Maintenance/routes.php'))) {
        require base_path('modules/Equipment/Maintenance/routes.php');
    }

    if (file_exists(base_path('modules/Equipment/ErrorMonitoring/routes.php'))) {
        require base_path('modules/Equipment/ErrorMonitoring/routes.php');
    }

    if (file_exists(base_path('modules/Equipment/ParameterLog/routes.php'))) {
        require base_path('modules/Equipment/ParameterLog/routes.php');
    }

    Route::get('/user/schedules/today', GetUserTodaySchedulesAction::class);
});
