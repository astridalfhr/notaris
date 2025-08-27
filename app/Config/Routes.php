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
$routes->get('auth/LoginWithGoogle', 'Auth::LoginWithGoogle');
$routes->get('auth/googleCallback', 'Auth::googleCallback');
$routes->get('forgot', 'Auth::forgot');
$routes->post('forgot', 'Auth::forgotPost');
$routes->get('reset-password', 'Auth::reset');
$routes->post('reset-password', 'Auth::resetPost');
$routes->get('auth/reset', 'Auth::reset', ['as' => 'password_reset']);
$routes->post('auth/reset', 'Auth::resetPost');

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

$routes->group('admin', ['filter' => ['auth', 'role:admin']], static function (\CodeIgniter\Router\RouteCollection $routes) {
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
    $routes->post('slot/store', 'Admin\Dashboard::slotStore');
    $routes->post('slot/delete/(:num)', 'Admin\Dashboard::slotDelete/$1');
    $routes->get('slot/detail/(:num)', 'Admin\Dashboard::slotDetail/$1');
    $routes->post('slot/complete/(:num)', 'Admin\Dashboard::slotComplete/$1');
    $routes->get('kerja', 'Admin\Kerja::index');
    $routes->get('/', 'Admin\Kerja::index');
    $routes->get('kerja/(:segment)', 'Admin\Kerja::index/$1');
    $routes->get('kerja/(:segment)/(:segment)', 'Admin\Kerja::item/$1/$2');
    $routes->post('upload', 'Admin\Kerja::upload');
    $routes->post('delete', 'Admin\Kerja::delete');
});

$routes->group('multiuser', ['filter' => ['auth', 'role:multiuser']], static function (RouteCollection $routes) {
    $routes->get('/', 'Multiuser\Dashboard::index');
    $routes->get('dashboard', 'Multiuser\Dashboard::index');
    $routes->get('profile', 'Multiuser\Profile::edit');
    $routes->get('profile_edit', 'Multiuser\Profile::edit');
    $routes->post('profile/update', 'Multiuser\Profile::update');
    $routes->get('users', 'Multiuser\Dashboard::users');
    $routes->post('users/role/(:num)', 'Multiuser\Dashboard::setRole/$1');
    $routes->get('employees', 'Multiuser\Employees::index');
    $routes->get('employees/create', 'Multiuser\Employees::create');
    $routes->post('employees/store', 'Multiuser\Employees::store');
    $routes->get('employees/edit/(:num)', 'Multiuser\Employees::edit/$1');
    $routes->post('employees/update/(:num)', 'Multiuser\Employees::update/$1');
    $routes->post('employees/toggle/(:num)', 'Multiuser\Employees::toggle/$1');
    $routes->post('employees/delete/(:num)', 'Multiuser\Employees::delete/$1');
    $routes->get('company', 'Multiuser\Company::index');
    $routes->post('company/save', 'Multiuser\Company::save');
    $routes->get('slots', 'Multiuser\Dashboard::slot');
    $routes->get('slot', 'Multiuser\Dashboard::slot');
    $routes->post('slot/store', 'Multiuser\Dashboard::slotStore');
    $routes->post('slot/delete/(:num)', 'Multiuser\Dashboard::slotDelete/$1');
    $routes->get('slot/detail/(:num)', 'Multiuser\Dashboard::slotDetail/$1');
    $routes->post('slot/complete/(:num)', 'Multiuser\Dashboard::slotComplete/$1');
    $routes->post('dashboard/booking-confirm/(:num)', 'Multiuser\Dashboard::bookingConfirm/$1');
    $routes->get('multiuser/slot/detail/(:num)', 'Multiuser\Dashboard::slotDetail/$1', ['filter' => 'auth']);
    $routes->get('company', 'Multiuser\Company::index');
    $routes->get('company/(:alpha)', 'Multiuser\Company::index/$1');
    $routes->post('company/save', 'Multiuser\Company::save');
    $routes->post('company/save/(:alpha)', 'Multiuser\Company::save/$1');
    $routes->post('company/activate/(:num)', 'Multiuser\Company::activate/$1');
    $routes->get('homepage', 'Multiuser\Homepage::index', ['filter' => 'auth']);
    $routes->post('homepage/save', 'Multiuser\Homepage::save', ['filter' => 'auth']);
    $routes->get('hero', 'Multiuser\Hero::index');
    $routes->get('hero/create', 'Multiuser\Hero::create');
    $routes->post('hero/store', 'Multiuser\Hero::store');
    $routes->get('hero/edit/(:num)', 'Multiuser\Hero::edit/$1');
    $routes->post('hero/update/(:num)', 'Multiuser\Hero::update/$1');
    $routes->post('hero/delete/(:num)', 'Multiuser\Hero::delete/$1');
    $routes->post('hero/toggle/(:num)', 'Multiuser\Hero::toggle/$1');
    $routes->post('hero/move-up/(:num)', 'Multiuser\Hero::moveUp/$1');
    $routes->post('hero/move-down/(:num)', 'Multiuser\Hero::moveDown/$1');
    $routes->get('news', 'Multiuser\News::index');
    $routes->get('news/create', 'Multiuser\News::create');
    $routes->post('news/store', 'Multiuser\News::store');
    $routes->get('news/edit/(:num)', 'Multiuser\News::edit/$1');
    $routes->post('news/update/(:num)', 'Multiuser\News::update/$1');
    $routes->post('news/delete/(:num)', 'Multiuser\News::delete/$1');
    $routes->post('news/feature/(:num)', 'Multiuser\News::toggleFeature/$1');
    $routes->post('news/publish/(:num)', 'Multiuser\News::togglePublish/$1');
    $routes->get('roles', 'Multiuser\Roles::index');
    $routes->post('roles/update', 'Multiuser\Roles::update', ['as' => 'multiuser_roles_update']);
    $routes->group('kerja', static function (RouteCollection $routes) {
        $routes->get('/', 'Multiuser\Kerja::index');
        $routes->post('upload', 'Multiuser\Kerja::upload');
        $routes->get('(:segment)/(:segment)', 'Multiuser\Kerja::item/$1/$2');
        $routes->post('delete', 'Multiuser\Kerja::delete');
    });
});

$routes->addRedirect('multi', 'multiuser');
$routes->addRedirect('multi/(:any)', 'multiuser/$1');

if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
