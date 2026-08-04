<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'CompanyProfileController::index');
$routes->get('monitoring-server', 'CompanyProfileController::monitoringServer');
$routes->get('jasa-monitoring-server', 'CompanyProfileController::monitoringServer');

$routes->get('register', 'AuthController::register');
$routes->post('register', 'AuthController::register');

$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::login');

$routes->get('verify-otp', 'AuthController::verifyOtp');
$routes->post('verify-otp', 'AuthController::verifyOtp');
$routes->post('resend-otp', 'AuthController::resendOtp');

$routes->post('logout', 'AuthController::logout');

$routes->get('dashboard', 'DashboardController::index');
$routes->get('dashboard/domains/(:num)', 'DashboardController::showDomain/$1');
$routes->post('dashboard/domains', 'DashboardController::storeDomain');
$routes->post('dashboard/domains/(:num)/update', 'DashboardController::updateDomain/$1');
$routes->post('dashboard/domains/(:num)/toggle', 'DashboardController::toggleDomain/$1');
$routes->post('dashboard/domains/(:num)/check', 'DashboardController::checkDomain/$1');
$routes->post('dashboard/check-all', 'DashboardController::checkAllDomains');
$routes->post('dashboard/domains/(:num)/delete', 'DashboardController::deleteDomain/$1');
$routes->post('dashboard/domains/(:num)/contacts', 'DashboardController::storeContact/$1');
$routes->post('dashboard/contacts/(:num)/update', 'DashboardController::updateContact/$1');
$routes->post('dashboard/contacts/(:num)/delete', 'DashboardController::deleteContact/$1');

// Undangan komentar
$routes->get('api/undangan/komentar',  'UndanganController::getKomentar');
$routes->post('api/undangan/komentar', 'UndanganController::simpanKomentar');
