<?php

namespace Config;

use CodeIgniter\Config\Services;
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes = Services::routes();

if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

/* ================== PUBLIC ================== */
$routes->get('/', 'Home::index');
$routes->get('news/feed', 'Home::newsFeed');
$routes->get('news/show/(:num)', 'Home::newsShow/$1');
$routes->get('profile', 'Profile::index');
$routes->get('layanan', 'Layanan::index');
$routes->get('jadwal/(:num)', 'JadwalController::getJadwal/$1');
$routes->get('kontak', 'Contact::index');
$routes->match(['post', 'get'], 'contact/send', 'Contact::send', ['as' => 'contact_send']);

/* ================== BOOKING (USER AUTH) ================== */
$routes->group('booking', ['filter' => ['auth']], static function (RouteCollection $routes) {
    $routes->get('/', 'Booking::create');
    $routes->get('create', 'Booking::create');
    $routes->get('(:num)', 'Booking::create/$1');
    $routes->post('store', 'Booking::store');
    $routes->get('detail/(:num)', 'Booking::detail/$1');
    $routes->post('cancel/(:num)', 'Booking::cancel/$1');
});

/* ================== AUTH ================== */
$routes->get('login', 'Auth::login');
$routes->post('auth/manual_login', 'Auth::manual_login');
$routes->get('register', 'Auth::register');
$routes->post('auth/register', 'Auth::registerPost');
$routes->get('logout', 'Auth::logout');
$routes->get('auth/LoginWithGoogle', 'Auth::LoginWithGoogle');  // legacy button
$routes->get('auth/callback', 'Auth::googleCallback');          // preferred callback
$routes->get('auth/googleCallback', 'Auth::googleCallback');    // legacy callback
$routes->get('auth/google', 'Auth::LoginWithGoogle');
$routes->get('forgot', 'Auth::forgot');
$routes->post('forgot', 'Auth::forgotPost');
$routes->get('reset-password', 'Auth::reset');
$routes->post('reset-password', 'Auth::resetPost');
$routes->get('auth/reset', 'Auth::reset', ['as' => 'password_reset']);
$routes->post('auth/reset', 'Auth::resetPost');

/* ================== STATIC VIEWS ================== */
$routes->get('login-warning', static function () {
    return view('auth/login_warning');
});
$routes->get('unauthorized', static function () {
    return view('errors/unauthorized');
});

/* ================== USER AREA ================== */
$routes->group('user', ['filter' => ['auth', 'role:user']], static function (RouteCollection $routes) {
    $routes->get('/', 'User\Dashboard::index');
    $routes->get('dashboard', 'User\Dashboard::index');
    $routes->get('profile', 'User\Profile::index');
    $routes->get('edit_profile', 'User\Profile::edit');
    $routes->post('profile/update', 'User\Profile::update');
    $routes->post('edit_profile', 'User\Profile::update');
});

/* ================== ADMIN (UMUM) ================== */
$routes->group('admin', ['filter' => ['auth', 'role:admin']], static function (RouteCollection $routes) {
    $routes->get('/', 'Admin\Dashboard::index');
    $routes->get('dashboard', 'Admin\Dashboard::index');

    // Profile
    $routes->get('profile', 'Admin\Profile::edit');
    $routes->get('profile_edit', 'Admin\Profile::edit');
    $routes->post('profile/update', 'Admin\Profile::update');

    // Booking approval (dashboard)
    $routes->post('approve/(:num)', 'Admin\Dashboard::approve/$1');
    $routes->post('reject/(:num)', 'Admin\Dashboard::reject/$1');
    $routes->post('booking/approve/(:num)', 'Admin\Dashboard::approve/$1');
    $routes->post('booking/reject/(:num)', 'Admin\Dashboard::reject/$1');

    // Slots
    $routes->get('slots', 'Admin\Dashboard::slot');
    $routes->get('slot', 'Admin\Dashboard::slot');
    $routes->get('dashboard/slots', 'Admin\Dashboard::slots');
    $routes->get('slot_detail/(:num)', 'Admin\Dashboard::slotDetail/$1');
    $routes->post('slot/store', 'Admin\Dashboard::slotStore');
    $routes->post('slot/delete/(:num)', 'Admin\Dashboard::slotDelete/$1');
    $routes->post('slot/complete/(:num)', 'Admin\Dashboard::slotComplete/$1');

    // Dashboard extra
    $routes->get('dashboard/summary', 'Admin\Dashboard::summary');
    $routes->get('dashboard/bookings', 'Admin\Dashboard::bookings');
    $routes->post('dashboard/booking-confirm/(:num)', 'Admin\Dashboard::bookingConfirm/$1');
    $routes->post('dashboard/booking-cancel/(:num)', 'Admin\Dashboard::bookingCancel/$1');
    $routes->get('dashboard/health', 'Admin\Dashboard::health');

    // Roles
    $routes->get('roles', 'Admin\Roles::index');
    $routes->post('roles/update', 'Admin\Roles::update', ['as' => 'admin_roles_update']);

    // Employees
    $routes->get('employees', 'Admin\Employees::index');
    $routes->get('employees/create', 'Admin\Employees::create');
    $routes->post('employees/store', 'Admin\Employees::store');
    $routes->get('employees/edit/(:num)', 'Admin\Employees::edit/$1');
    $routes->post('employees/update/(:num)', 'Admin\Employees::update/$1');
    $routes->post('employees/toggle/(:num)', 'Admin\Employees::toggle/$1');
    $routes->post('employees/delete/(:num)', 'Admin\Employees::delete/$1');

    // Panel & Company & Homepage
    $routes->get('panel/summary', 'Admin\Panel::summary');
    $routes->get('company', 'Admin\Company::index');
    $routes->get('company/(:alpha)', 'Admin\Company::index/$1');
    $routes->post('company/save', 'Admin\Company::save');
    $routes->post('company/save/(:alpha)', 'Admin\Company::save/$1');
    $routes->post('company/activate/(:num)', 'Admin\Company::activate/$1');
    $routes->get('homepage', 'Admin\Homepage::index');
    $routes->post('homepage/save', 'Admin\Homepage::save');
});

/* ===== Admin > Booking detail (tetap terpisah, tapi boleh disatukan ke grup di atas) ===== */
$routes->group('admin', static function ($routes) {
    $routes->group('booking', static function ($routes) {
        $routes->get('today', 'Booking::today');
        $routes->post('(:num)/confirm', 'Booking::confirm/$1');
        $routes->post('(:num)/cancel', 'Booking::cancel/$1');
        $routes->post('(:num)/complete', 'Booking::complete/$1');
        $routes->get('(:num)', 'Booking::detail/$1');
    });
});

/* ================== ADMIN: ARSIP (dengan filter admin) ================== */
$routes->group('admin/arsip', [
    'namespace' => 'App\Controllers\Admin',
    'filter' => ['auth', 'role:admin'],
], static function ($r) {
    $r->get('feed', 'Arsip::feed');
    $r->post('upload', 'Arsip::upload');
    $r->match(['delete', 'post'], 'delete/(:num)', 'Arsip::delete/$1');
    $r->post('update/(:num)', 'Arsip::update/$1');
    $r->get('report', 'Arsip::report');
});

/* ================== ADMIN: LAPORAN (dengan filter admin) ================== */
$routes->group('admin/laporan', [
    'namespace' => 'App\Controllers\Admin',
    'filter' => ['auth', 'role:admin'],
], static function ($r) {
    $r->get('list', 'Laporan::list');      // -> feed()
    $r->post('store', 'Laporan::store');   // -> upload()

    $r->get('feed', 'Laporan::feed');
    $r->post('upload', 'Laporan::upload');
    $r->get('download/(:num)', 'Laporan::download/$1');
    $r->get('export', 'Laporan::export');

    // >>> tambahkan ini <<<
    $r->get('open/(:num)', 'Laporan::open/$1');
    $r->get('media/(:num)', 'Media::view/$1');     // preview inline
    $r->post('update/(:num)', 'Laporan::update/$1'); // edit + kelola lampiran

    $r->delete('lampiran/(:num)', 'Laporan::deleteLampiran/$1');   // DELETE
    $r->post('lampiran/delete/(:num)', 'Laporan::deleteLampiran/$1');   // fallback POST

    $r->delete('(:num)', 'Laporan::delete/$1');
    $r->post('delete/(:num)', 'Laporan::delete/$1');
});

$routes->group('admin/pekerjaan', ['filter' => 'auth'], static function ($r) {
    $r->get('/', 'Admin\PekerjaanController::index');
    $r->get('create', 'Admin\PekerjaanController::create');
    $r->post('store', 'Admin\PekerjaanController::store');
    $r->get('edit/(:num)', 'Admin\PekerjaanController::edit/$1');
    $r->post('update/(:num)', 'Admin\PekerjaanController::update/$1');
    $r->post('delete/(:num)', 'Admin\PekerjaanController::delete/$1');
    $r->post('toggle/(:num)', 'Admin\PekerjaanController::toggle/$1');   // aktif/nonaktif
    $r->post('reorder', 'Admin\PekerjaanController::reorder');           // drag/sort optional
});


if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
