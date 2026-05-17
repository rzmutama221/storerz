<?php
/**
 * RZDK Store - Main Entry Point
 * 
 * All requests are routed through this file via .htaccess.
 * This file bootstraps the application and dispatches routes.
 */

// ============================================================
// 1. DEFINE BASE PATH
// ============================================================
define('BASE_PATH', __DIR__);

// ============================================================
// 2. ERROR HANDLING
// ============================================================
$appConfig = require BASE_PATH . '/config/app.php';

if ($appConfig['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', BASE_PATH . '/storage/logs/error.log');
}

// ============================================================
// 3. SET TIMEZONE
// ============================================================
date_default_timezone_set($appConfig['timezone']);

// ============================================================
// 4. LOAD CORE FILES
// ============================================================
require_once BASE_PATH . '/core/Session.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Middleware.php';
require_once BASE_PATH . '/core/Validator.php';
require_once BASE_PATH . '/core/Mailer.php';
require_once BASE_PATH . '/core/Helper.php';

// ============================================================
// 5. START SESSION
// ============================================================
Session::start();

// ============================================================
// 6. DEFINE ROUTES
// ============================================================
$router = new Router();

// --- Public Routes ---
$router->get('/', 'LandingController', 'index');
$router->get('/products', 'LandingController', 'products');
$router->get('/products/{slug}', 'LandingController', 'productDetail');

// --- Auth Routes (Guest only) ---
$router->get('/login', 'AuthController', 'showLogin', ['guest']);
$router->post('/login', 'AuthController', 'processLogin', ['guest']);
$router->get('/register', 'AuthController', 'showRegister', ['guest']);
$router->post('/register', 'AuthController', 'processRegister', ['guest']);
$router->get('/forgot-password', 'AuthController', 'showForgotPassword', ['guest']);
$router->post('/forgot-password', 'AuthController', 'processForgotPassword', ['guest']);
$router->get('/reset-password', 'AuthController', 'showResetPassword', ['guest']);
$router->post('/reset-password', 'AuthController', 'processResetPassword', ['guest']);
$router->get('/verify', 'AuthController', 'verifyEmail');
$router->get('/verify-notice', 'AuthController', 'verifyNotice');

// --- Auth Routes (Authenticated) ---
$router->get('/logout', 'AuthController', 'logout', ['auth']);

// --- Customer Routes ---
$router->get('/dashboard', 'customer/DashboardController', 'index', ['auth', 'customer']);
$router->get('/dashboard/products', 'customer/ProductController', 'index', ['auth', 'customer']);
$router->get('/dashboard/products/{slug}', 'customer/ProductController', 'show', ['auth', 'customer']);
$router->post('/dashboard/order', 'customer/OrderController', 'create', ['auth', 'customer']);
$router->get('/dashboard/orders', 'customer/OrderController', 'index', ['auth', 'customer']);
$router->get('/dashboard/orders/{id}', 'customer/OrderController', 'show', ['auth', 'customer']);
$router->get('/dashboard/payment/{id}', 'customer/OrderController', 'payment', ['auth', 'customer']);
$router->post('/dashboard/payment/{id}', 'customer/OrderController', 'uploadProof', ['auth', 'customer']);
$router->get('/dashboard/accounts', 'customer/AccountController', 'index', ['auth', 'customer']);
$router->get('/dashboard/warranty', 'customer/WarrantyController', 'index', ['auth', 'customer']);
$router->post('/dashboard/warranty', 'customer/WarrantyController', 'create', ['auth', 'customer']);
$router->get('/dashboard/profile', 'customer/AccountController', 'profile', ['auth', 'customer']);
$router->post('/dashboard/profile', 'customer/AccountController', 'updateProfile', ['auth', 'customer']);
$router->post('/dashboard/change-password', 'customer/AccountController', 'changePassword', ['auth', 'customer']);

// --- Admin Routes ---
$router->get('/admin/dashboard', 'admin/DashboardController', 'index', ['auth', 'admin']);

// Admin: Products
$router->get('/admin/categories', 'admin/ProductController', 'categories', ['auth', 'admin']);
$router->post('/admin/categories', 'admin/ProductController', 'storeCategory', ['auth', 'admin']);
$router->post('/admin/categories/{id}/update', 'admin/ProductController', 'updateCategory', ['auth', 'admin']);
$router->post('/admin/categories/{id}/delete', 'admin/ProductController', 'deleteCategory', ['auth', 'admin']);
$router->get('/admin/products', 'admin/ProductController', 'index', ['auth', 'admin']);
$router->get('/admin/products/create', 'admin/ProductController', 'create', ['auth', 'admin']);
$router->post('/admin/products/create', 'admin/ProductController', 'store', ['auth', 'admin']);
$router->get('/admin/products/{id}/edit', 'admin/ProductController', 'edit', ['auth', 'admin']);
$router->post('/admin/products/{id}/edit', 'admin/ProductController', 'update', ['auth', 'admin']);
$router->post('/admin/products/{id}/delete', 'admin/ProductController', 'delete', ['auth', 'admin']);
$router->get('/admin/products/{id}/variants', 'admin/ProductController', 'variants', ['auth', 'admin']);
$router->post('/admin/products/{id}/variants', 'admin/ProductController', 'storeVariant', ['auth', 'admin']);
$router->post('/admin/variants/{id}/update', 'admin/ProductController', 'updateVariant', ['auth', 'admin']);
$router->post('/admin/variants/{id}/delete', 'admin/ProductController', 'deleteVariant', ['auth', 'admin']);
$router->get('/admin/stock/{variantId}', 'admin/ProductController', 'stock', ['auth', 'admin']);
$router->post('/admin/stock/{variantId}', 'admin/ProductController', 'addStock', ['auth', 'admin']);

// Admin: Orders
$router->get('/admin/orders', 'admin/OrderController', 'index', ['auth', 'admin']);
$router->get('/admin/orders/{id}', 'admin/OrderController', 'show', ['auth', 'admin']);
$router->post('/admin/orders/{id}/approve', 'admin/OrderController', 'approve', ['auth', 'admin']);
$router->post('/admin/orders/{id}/reject', 'admin/OrderController', 'reject', ['auth', 'admin']);
$router->post('/admin/orders/{id}/verify-payment', 'admin/OrderController', 'verifyPayment', ['auth', 'admin']);
$router->post('/admin/orders/{id}/fulfill', 'admin/OrderController', 'fulfill', ['auth', 'admin']);
$router->post('/admin/orders/{id}/complete', 'admin/OrderController', 'complete', ['auth', 'admin']);

// Admin: Customers
$router->get('/admin/customers', 'admin/CustomerController', 'index', ['auth', 'admin']);
$router->get('/admin/customers/{id}', 'admin/CustomerController', 'show', ['auth', 'admin']);
$router->post('/admin/customers/{id}/suspend', 'admin/CustomerController', 'suspend', ['auth', 'admin']);
$router->post('/admin/customers/{id}/activate', 'admin/CustomerController', 'activate', ['auth', 'admin']);

// Admin: Finance
$router->get('/admin/finance', 'admin/FinanceController', 'index', ['auth', 'admin']);
$router->get('/admin/finance/export', 'admin/FinanceController', 'export', ['auth', 'admin']);

// Admin: Vouchers
$router->get('/admin/vouchers', 'admin/VoucherController', 'index', ['auth', 'admin']);
$router->post('/admin/vouchers', 'admin/VoucherController', 'store', ['auth', 'admin']);
$router->post('/admin/vouchers/{id}/update', 'admin/VoucherController', 'update', ['auth', 'admin']);
$router->post('/admin/vouchers/{id}/delete', 'admin/VoucherController', 'delete', ['auth', 'admin']);

// Admin: Announcements
$router->get('/admin/announcements', 'admin/AnnouncementController', 'index', ['auth', 'admin']);
$router->post('/admin/announcements', 'admin/AnnouncementController', 'store', ['auth', 'admin']);
$router->post('/admin/announcements/{id}/update', 'admin/AnnouncementController', 'update', ['auth', 'admin']);
$router->post('/admin/announcements/{id}/delete', 'admin/AnnouncementController', 'delete', ['auth', 'admin']);

// Admin: Netflix Management
$router->get('/admin/netflix', 'admin/NetflixController', 'index', ['auth', 'admin']);
$router->post('/admin/netflix/accounts', 'admin/NetflixController', 'storeAccount', ['auth', 'admin']);
$router->post('/admin/netflix/accounts/{id}/update', 'admin/NetflixController', 'updateAccount', ['auth', 'admin']);
$router->post('/admin/netflix/slots/{id}/assign', 'admin/NetflixController', 'assignSlot', ['auth', 'admin']);
$router->post('/admin/netflix/slots/{id}/release', 'admin/NetflixController', 'releaseSlot', ['auth', 'admin']);

// Admin: ChatGPT Management
$router->get('/admin/chatgpt', 'admin/ChatGPTController', 'index', ['auth', 'admin']);
$router->post('/admin/chatgpt/accounts', 'admin/ChatGPTController', 'storeAccount', ['auth', 'admin']);
$router->post('/admin/chatgpt/accounts/{id}/update', 'admin/ChatGPTController', 'updateAccount', ['auth', 'admin']);
$router->post('/admin/chatgpt/members/{id}/assign', 'admin/ChatGPTController', 'assignMember', ['auth', 'admin']);
$router->post('/admin/chatgpt/members/{id}/release', 'admin/ChatGPTController', 'releaseMember', ['auth', 'admin']);

// Admin: Warranties
$router->get('/admin/warranties', 'admin/OrderController', 'warranties', ['auth', 'admin']);
$router->post('/admin/warranties/{id}/respond', 'admin/OrderController', 'respondWarranty', ['auth', 'admin']);

// Admin: Settings
$router->get('/admin/settings', 'admin/SettingController', 'index', ['auth', 'admin']);
$router->post('/admin/settings', 'admin/SettingController', 'update', ['auth', 'admin']);
$router->post('/admin/settings/qris', 'admin/SettingController', 'uploadQris', ['auth', 'admin']);

// --- API Routes (No auth - for payment gateway callbacks) ---
$router->post('/api/payment/callback', 'PaymentCallbackController', 'handle', []);

// ============================================================
// 7. DISPATCH REQUEST
// ============================================================
$router->dispatch();
