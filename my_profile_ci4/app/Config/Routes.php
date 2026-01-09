<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('portfolio', 'Portfolio::index');

// ====== API Routes ======

// CORS Preflight - 處理所有 OPTIONS 請求（支援巢狀路徑）
$routes->options('api/(.+)', function() {
    $response = service('response');
    $response->setStatusCode(200);
    return $response;
});

// API 群組
$routes->group('api', function($routes) {

    // 認證模組 (公開)
    $routes->group('auth', function($routes) {
        $routes->post('register', 'Api\AuthController::register');
        $routes->post('login', 'Api\AuthController::login');
        $routes->post('refresh', 'Api\AuthController::refresh');
        $routes->post('logout', 'Api\AuthController::logout');
    });

    // 認證模組 (需要認證)
    $routes->group('auth', ['filter' => 'auth'], function($routes) {
        $routes->get('me', 'Api\AuthController::me');
    });

    // 搜尋模組 (公開)
    $routes->group('search', function($routes) {
        $routes->get('salespersons', 'Api\SearchController::search');
        $routes->get('salespersons/(:num)', 'Api\SearchController::show/$1');
    });

    // 業務員模組 (需要認證 + salesperson 角色)
    $routes->group('salesperson', ['filter' => ['auth', 'role:salesperson']], function($routes) {
        $routes->get('profile', 'Api\SalespersonController::getProfile');
        $routes->put('profile', 'Api\SalespersonController::updateProfile');
        $routes->post('company', 'Api\SalespersonController::saveCompany');
        $routes->get('experiences', 'Api\SalespersonController::getExperiences');
        $routes->post('experiences', 'Api\SalespersonController::createExperience');
        $routes->delete('experiences/(:num)', 'Api\SalespersonController::deleteExperience/$1');
        $routes->get('certifications', 'Api\SalespersonController::getCertifications');
        $routes->post('certifications', 'Api\SalespersonController::createCertification');
        $routes->get('approval-status', 'Api\SalespersonController::getApprovalStatus');
    });

    // 管理員模組 (需要認證 + admin 角色)
    $routes->group('admin', ['filter' => ['auth', 'role:admin']], function($routes) {
        // 審核管理
        $routes->get('pending-approvals', 'Api\AdminController::getPendingApprovals');
        $routes->post('approve-user/(:num)', 'Api\AdminController::approveUser/$1');
        $routes->post('reject-user/(:num)', 'Api\AdminController::rejectUser/$1');
        $routes->post('approve-company/(:num)', 'Api\AdminController::approveCompany/$1');
        $routes->post('reject-company/(:num)', 'Api\AdminController::rejectCompany/$1');
        $routes->post('approve-certification/(:num)', 'Api\AdminController::approveCertification/$1');
        $routes->post('reject-certification/(:num)', 'Api\AdminController::rejectCertification/$1');

        // 使用者管理
        $routes->get('users', 'Api\AdminController::getUsers');
        $routes->put('users/(:num)/status', 'Api\AdminController::updateUserStatus/$1');
        $routes->delete('users/(:num)', 'Api\AdminController::deleteUser/$1');

        // 系統設定
        $routes->get('settings/industries', 'Api\AdminController::getIndustries');
        $routes->post('settings/industries', 'Api\AdminController::createIndustry');
        $routes->get('settings/regions', 'Api\AdminController::getRegions');
        $routes->post('settings/regions', 'Api\AdminController::createRegion');

        // 統計
        $routes->get('statistics', 'Api\AdminController::getStatistics');
    });

});

// Swagger API Documentation (Development Only)
$routes->group('api/docs', function($routes) {
    $routes->get('/', 'Api\SwaggerController::index');
    $routes->get('openapi.json', 'Api\SwaggerController::json');
});
