<?php

namespace Config;

use CodeIgniter\Config\Services;
use CodeIgniter\Router\RouteCollection;

$routes = Services::routes();

if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

$routes->get('/', 'Home::index');
$routes->get('news/feed', 'Home::newsFeed');
$routes->get('news/show/(:num)', 'Home::newsShow/$1');
$routes->get('profile', 'Profile::index');
$routes->get('layanan', 'Layanan::index');
$routes->get('jadwal/(:num)', 'JadwalController::getJadwal/$1');
$routes->get('kontak', 'Contact::index');
$routes->match(['post', 'get'], 'contact/send', 'Contact::send', ['as' => 'contact_send']);

$routes->group('booking', ['filter' => ['auth']], static function (RouteCollection $routes) {
    $routes->get('/', 'Booking::create');
    $routes->get('create', 'Booking::create');
    $routes->get('(:num)', 'Booking::create/$1');
    $routes->post('store', 'Booking::store');
    $routes->get('detail/(:num)', 'Booking::detail/$1');
    $routes->post('cancel/(:num)', 'Booking::cancel/$1');
});

$routes->get('login', 'Auth::login');
$routes->post('auth/manual_login', 'Auth::manual_login');
$routes->get('register', 'Auth::register');
$routes->post('auth/register', 'Auth::registerPost');
$routes->get('logout', 'Auth::logout');
$routes->get('auth/LoginWithGoogle', 'Auth::LoginWithGoogle');  // legacy button
$routes->get('auth/callback', 'Auth::googleCallback');          // preferred callback
$routes->get('auth/googleCallback', 'Auth::googleCallback');    // legacy callback (tetap didukung)
$routes->get('auth/google', 'Auth::LoginWithGoogle');
$routes->get('forgot', 'Auth::forgot');
$routes->post('forgot', 'Auth::forgotPost');
$routes->get('reset-password', 'Auth::reset');
$routes->post('reset-password', 'Auth::resetPost');
$routes->get('auth/reset', 'Auth::reset', ['as' => 'password_reset']);
$routes->post('auth/reset', 'Auth::resetPost');
$routes->get('auth/diag', 'Auth::diag');

$routes->get('login-warning', static function () {
    return view('auth/login_warning');
});

$routes->get('unauthorized', static function () {
    return view('errors/unauthorized');
});

$routes->group('user', ['filter' => ['auth', 'role:user']], static function (RouteCollection $routes) {
    $routes->get('/', 'User\Dashboard::index');
    $routes->get('dashboard', 'User\Dashboard::index');
    $routes->get('profile', 'User\Profile::index');
    $routes->get('edit_profile', 'User\Profile::edit');
    $routes->post('profile/update', 'User\Profile::update');
    $routes->post('edit_profile', 'User\Profile::update');
});

$routes->group('admin', ['filter' => ['auth', 'role:admin']], static function (RouteCollection $routes) {
    $routes->get('/', 'Admin\Dashboard::index');
    $routes->get('dashboard', 'Admin\Dashboard::index');
    $routes->get('profile', 'Admin\Profile::edit');
    $routes->get('profile_edit', 'Admin\Profile::edit');
    $routes->post('profile/update', 'Admin\Profile::update');
    $routes->post('approve/(:num)', 'Admin\Dashboard::approve/$1');
    $routes->post('reject/(:num)', 'Admin\Dashboard::reject/$1');
    $routes->post('booking/approve/(:num)', 'Admin\Dashboard::approve/$1');
    $routes->post('booking/reject/(:num)', 'Admin\Dashboard::reject/$1');
    $routes->get('slots', 'Admin\Dashboard::slot');
    $routes->get('slot', 'Admin\Dashboard::slot');
    $routes->get('dashboard/slots', 'Admin\Dashboard::slots');
    $routes->get('dashboard/summary', 'Admin\Dashboard::summary');
    $routes->get('dashboard/bookings', 'Admin\Dashboard::bookings');
    $routes->post('dashboard/booking-confirm/(:num)', 'Admin\Dashboard::bookingConfirm/$1');
    $routes->post('dashboard/booking-cancel/(:num)', 'Admin\Dashboard::bookingCancel/$1');
    $routes->get('dashboard/health', 'Admin\Dashboard::health');
    $routes->get('roles', 'Admin\Roles::index');
    $routes->post('roles/update', 'Admin\Roles::update', ['as' => 'admin_roles_update']);
    $routes->get('employees', 'Admin\Employees::index');
    $routes->get('employees/create', 'Admin\Employees::create');
    $routes->post('employees/store', 'Admin\Employees::store');
    $routes->get('employees/edit/(:num)', 'Admin\Employees::edit/$1');
    $routes->post('employees/update/(:num)', 'Admin\Employees::update/$1');
    $routes->post('employees/toggle/(:num)', 'Admin\Employees::toggle/$1');
    $routes->post('employees/delete/(:num)', 'Admin\Employees::delete/$1');
    $routes->post('slot/store', 'Admin\Dashboard::slotStore');
    $routes->post('slot/delete/(:num)', 'Admin\Dashboard::slotDelete/$1');
    $routes->get('slot_detail/(:num)', 'Admin\Dashboard::slotDetail/$1');
    $routes->post('slot/complete/(:num)', 'Admin\Dashboard::slotComplete/$1');
    $routes->get('kerja/feed', 'Admin\WorkController::feed');
    $routes->post('kerja/upload', 'Admin\WorkController::upload');
    $routes->post('kerja/update/(:num)', 'Admin\WorkController::update/$1');
    $routes->delete('kerja/delete/(:num)', 'Admin\WorkController::delete/$1');
    $routes->get('kerja/download/(:num)', 'Admin\WorkController::download/$1');
    $routes->get('kerja/preview/(:num)', 'Admin\WorkController::preview/$1');
    $routes->get('panel/summary', 'Admin\Panel::summary');
    $routes->get('company', 'Admin\Company::index');
    $routes->get('company/(:alpha)', 'Admin\Company::index/$1');
    $routes->post('company/save', 'Admin\Company::save');
    $routes->post('company/save/(:alpha)', 'Admin\Company::save/$1');
    $routes->post('company/activate/(:num)', 'Admin\Company::activate/$1');
    $routes->get('homepage', 'Admin\Homepage::index', ['filter' => 'auth']);
    $routes->post('homepage/save', 'Admin\Homepage::save', ['filter' => 'auth']);
});

$routes->group('admin', static function ($routes) {
    $routes->group('booking', static function ($routes) {
        $routes->get('today', 'Booking::today');
        $routes->post('(:num)/confirm', 'Booking::confirm/$1');
        $routes->post('(:num)/cancel', 'Booking::cancel/$1');
        $routes->post('(:num)/complete', 'Booking::complete/$1');
        $routes->get('(:num)', 'Booking::detail/$1');
    });
});

$routes->group('admin/arsip', ['namespace' => 'App\Controllers\Admin'], static function ($r) {
    $r->get('feed', 'Arsip::feed');
    $r->post('upload', 'Arsip::upload');
    $r->match(['delete', 'post'], 'delete/(:num)', 'Arsip::delete/$1');
    $r->post('update/(:num)', 'Arsip::update/$1');
    $r->get('report', 'Arsip::report');
});


if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
