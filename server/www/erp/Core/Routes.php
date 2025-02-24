<?php

namespace Router;

// Используйте автозагрузку Composer для загрузки всех классов
// require_once __DIR__ . '/../vendor/autoload.php';

// Импорт необходимых классов
use Model\Database;
use Middleware\AuthMiddleware;
use Middleware\AuthService;
use Controller\UserController;
use Controller\AuthController;
use Controller\SubscriptionController;
use Controller\CompaniesController;
use Controller\RoleController;
use Controller\TaxController;
use Controller\EnterpriseController;
use Controller\IntegrationController;
use Controller\TorgsoftController;
use Controller\AccountsController;
use Controller\ContactController;

use Controller\KvedsEnterprisesController;
use Controller\EnterpriseAccountPlansController;
use Controller\StandardAccountsPlanController;
use Service\TaxCalculator;

use Controller\NomenclatureController;
use Controller\ProductCardController;
use Controller\CategoryController;
use Controller\WarehouseController;

use Controller\DimensionRangeController;
use servvice\FileService\ImageService;
use servvice\FileService\FileServerManager;

// Создаём экземпляр Database, который автоматически инициализирует соединения с MySQL и Redis
$database = new Database();

// Получаем PDO соединение и сервис кеширования из экземпляра Database
$conn = $database->getConnection();
$cacheService = $database->getCacheService();
$imageService = $database->getImageService();

$fileServerManager  = new \Service\FileService\FileServerManager($conn);

// Создаём экземпляры промежуточных слоёв и сервисов
$authMiddleware = new AuthMiddleware();
$authService = new AuthService();

// Создаём экземпляры моделей, передавая необходимые зависимости
$enterpriseModel = new \Model\EnterpriseModel();
$integrationModel = new \Model\IntegrationModel();
$userModel = new \Model\UserModel($conn, $cacheService, $authService, $cacheService);
$subscriptionModel = new \Model\SubscriptionModel();
$roleModel = new \Model\RoleModel($conn, $cacheService);
$warehouseDocumentModel = new \Model\WarehouseDocumentModel();
$financialDocumentModel = new \Model\FinancialDocumentModel();
$taxModel = new \Model\TaxModel();
$KvedModel = new \Model\KvedModel($conn, $cacheService);
$enterpriseAccountPlansModel = new \Model\EnterpriseAccountPlansModel();
$standardAccountsPlanModel = new \Model\StandardAccountsPlanModel();
$accountsModel = new \Model\AccountsModel($conn, $cacheService);
$contactModel = new \Model\ContactModel($conn, $cacheService);
$companyModel = new \Model\CompanyModel($conn, $cacheService);
$dimensionRangeModel = new \Model\DimensionRangeModel($conn, $cacheService);

$nomenclatureModel = new \Model\NomenclatureModel($conn, $cacheService, $imageService);
$productCardModel = new \Model\ProductCardModel($conn, $cacheService);
$categoryModel = new \Model\CategoryModel($conn, $cacheService);
$warehouseModel = new \Model\WarehouseModel($conn, $cacheService);


// Создаём экземпляры контроллеров, передавая необходимые зависимости
$userController = new UserController($conn, $cacheService, $authMiddleware, $userModel);
$authController = new AuthController();
$subscriptionController = new SubscriptionController($subscriptionModel, $userModel);
$companiesController = new CompaniesController($companyModel, $authMiddleware, $userModel, $roleModel);
$roleController = new RoleController($roleModel, $userModel, $companyModel, $authMiddleware, $cacheService);
$taxCalculator = new TaxCalculator($warehouseDocumentModel, $financialDocumentModel, $enterpriseModel, $taxModel);
$taxController = new TaxController($taxCalculator, $warehouseDocumentModel, $financialDocumentModel, $authMiddleware, $roleModel, $userModel, $taxModel);
$enterpriseController = new EnterpriseController($enterpriseModel, $userModel, $authMiddleware, $cacheService);
$integrationController = new IntegrationController($integrationModel, $userModel, $authMiddleware);
$torgsoftController = new TorgsoftController($integrationModel, $userModel, $enterpriseModel, $enterpriseController, $warehouseDocumentModel, $financialDocumentModel);
$accountsController = new AccountsController($accountsModel, $authMiddleware, $userModel);
$contactController = new ContactController($contactModel, $authMiddleware, $userModel);
$kvedsEnterprisesController = new KvedsEnterprisesController($KvedModel, $authMiddleware, $userModel);
$enterpriseAccountPlansController = new EnterpriseAccountPlansController($enterpriseAccountPlansModel, $authMiddleware, $userModel);
$standardAccountsPlanController = new StandardAccountsPlanController($standardAccountsPlanModel, $authMiddleware, $userModel);
$nomenclatureController = new NomenclatureController($nomenclatureModel, $userModel, $companyModel, $dimensionRangeModel, $authMiddleware, $imageService);
$productCardController = new ProductCardController($productCardModel, $nomenclatureModel, $userModel, $companyModel, $authMiddleware);
$categoryController = new CategoryController($categoryModel, $userModel, $companyModel, $authMiddleware);
$warehouseController = new WarehouseController($warehouseModel, $userModel, $companyModel, $authMiddleware);
$dimensionRangeController = new DimensionRangeController($dimensionRangeModel, $categoryModel, $companyModel, $authMiddleware);
// Определение маршрутов
$routes = [
    '/api/user/list' => [$userController, 'list', 'auth' => true, 'subscription' => false],
    '/api/user/current' => [$userController, 'getCurrentUser', 'auth' => true, 'subscription' => false],
    '/api/company/create' => [$companiesController, 'createCompany', 'auth' => true, 'subscription' => false],
    '/api/company/info' => [$companiesController, 'getCompanyInfo', 'auth' => true, 'subscription' => true],
    '/api/company/employees' => [$companiesController, 'getCompanyEmployees', 'auth' => true, 'subscription' => true],
    '/api/company/update' => [$companiesController, 'updateCompany', 'auth' => true, 'subscription' => true],
    '/api/user/updateSelfInfo' => [$userController, 'updateSelfInfo', 'auth' => true, 'subscription' => false],
    '/api/user/switchToUser' => [$userController, 'switchToUser', 'subscription' => false],
    // Аутентификация
    '/api/auth/login' => [$authController, 'login'],
    '/api/auth/logout' => [$authController, 'logout'],
    '/api/auth/register' => [$authController, 'register'],
    '/api/auth/authenticate' => [$authController, 'authenticate'],
    // Подписка
    '/api/subscription/update' => [$subscriptionController, 'updateSubscription', 'auth' => true, 'subscription' => true],
    '/api/subscription/available' => [$subscriptionController, 'availableSubscriptions', 'auth' => true],
    '/api/subscription/subscribe' => [$subscriptionController, 'subscribe', 'auth' => true],
    '/api/subscription/vievall' => [$subscriptionController, 'viewAllSubscriptions', 'auth' => true],
    // Роли
    '/api/role/assign' => [$roleController, 'assignRoleToUser', 'auth' => true, 'subscription' => true],
    '/api/role/create' => [$roleController, 'createRole', 'auth' => true, 'subscription' => true],
    '/api/role/list' => [$roleController, 'listRoles', 'auth' => true, 'subscription' => true],
    '/api/role/details' => [$roleController, 'getRoleDetails', 'auth' => true, 'subscription' => true],
    '/api/role/test-bulk-create-roles' => [$roleController, 'testBulkCreateRoles', 'auth' => true, 'subscription' => true],
    '/api/role/getAllRolesDefault' => [$roleController, 'getAllRolesDefault', 'auth' => true, 'subscription' => true],
    '/api/role/getAllPermissions' => [$roleController, 'getAllPermissions', 'auth' => true, 'subscription' => true],
    '/api/role/getUserRole' => [$roleController, 'getUserRole', 'auth' => true, 'subscription' => false],
    '/api/role/assignPermissions' => [$roleController, 'assignPermissionsToRole', 'auth' => true, 'subscription' => true],
    '/api/role/delete' => [$roleController, 'deleteRole', 'auth' => true, 'subscription' => true],
    '/api/tax/calculate' => [$taxController, 'calculate', 'auth' => true, 'subscription' => true],
    // Налоги
    '/api/tax/insertF2' => [$taxController, 'saveOrUpdateFOP2TaxDocument', 'auth' => true, 'subscription' => true],
    '/api/tax/insertF3' => [$taxController, 'saveOrUpdateFOP3TaxDocument', 'auth' => true, 'subscription' => true],
    '/api/enterprise/all' => [$enterpriseController, 'getUserEnterprises', 'auth' => true, 'subscription' => true],
    '/api/torgsoft/sync' => [$torgsoftController, 'syncEnterprises', 'auth' => true],
    '/api/torgsoft/syncAll' => [$torgsoftController, 'syncAllData', 'auth' => true],
    '/api/torgsoft/syncEnterprises' => [$torgsoftController, 'syncEnterprises', 'auth' => true],
    '/api/torgsoft/syncInvoices' => [$torgsoftController, 'syncInvoices', 'auth' => true],
    '/api/torgsoft/syncFinancialDocuments' => [$torgsoftController, 'syncFinancialDocuments', 'auth' => true],
    '/api/integration/list' => [$integrationController, 'getIntegrations', 'auth' => true, 'subscription' => true],
    '/api/integration/add' => [$integrationController, 'addIntegration', 'auth' => true, 'permission' => 'manage_integrations'],
    '/api/integration/update' => [$integrationController, 'updateIntegration', 'auth' => true, 'permission' => 'manage_integrations'],
    '/api/integration/delete' => [$integrationController, 'deleteIntegration', 'auth' => true, 'permission' => 'manage_integrations'],
    // Лиды
    '/api/company/leads' => [$subscriptionController, 'leads', 'auth' => true, 'subscription' => true],
    '/api/kved/add' => [$kvedsEnterprisesController, 'addKvedAndAssociateWithEnterprise', 'auth' => true, 'subscription' => true],
    '/api/kved/delete' => [$kvedsEnterprisesController, 'deleteKvedFromEnterprise', 'auth' => true, 'subscription' => true],
    '/api/kved/get' => [$kvedsEnterprisesController, 'getKvedsByEnterprise', 'auth' => true, 'subscription' => true],
    '/api/DefoultAccountGroup/getAllStandardAccountGroup' => [$standardAccountGroupsController, 'getAllStandardAccountGroups', 'auth' => true, 'subscription' => true],
    '/api/DefoultAccountGroup/addStandardAccountGroup' => [$standardAccountGroupsController, 'addStandardAccountGroup', 'auth' => true, 'subscription' => true],
    '/api/DefoultAccountGroup/deleteStandardAccountGroup' => [$standardAccountGroupsController, 'deleteStandardAccountGroup', 'auth' => true, 'subscription' => true],
    '/api/DefoultAccountGroup/updateStandardAccountGroup' => [$standardAccountGroupsController, 'updateStandardAccountGroup', 'auth' => true, 'subscription' => true],
    '/api/DefoultAccountPlan/getAllStandardAccountsInPlan' => [$standardAccountsPlanController, 'getAllStandardAccountsInPlan', 'auth' => true, 'subscription' => true],
    '/api/DefoultAccountPlan/addStandardAccountPlan' => [$standardAccountsPlanController, 'addStandardAccountToPlan', 'auth' => true, 'subscription' => true],
    '/api/DefoultAccountPlan/deleteStandardAccountPlan' => [$standardAccountsPlanController, 'deleteStandardAccountFromPlan', 'auth' => true, 'subscription' => true],
    '/api/DefoultAccountPlan/updateStandardAccountPlan' => [$standardAccountsPlanController, 'updateStandardAccountInPlan', 'auth' => true, 'subscription' => true],
    '/api/EnterpriseAccountPlans/addEnterpriseAccountPlan' => [$enterpriseAccountPlansController, 'addEnterpriseAccountPlan', 'auth' => true, 'subscription' => true],
    '/api/EnterpriseAccountPlans/getAllEnterpriseAccountPlans' => [$enterpriseAccountPlansController, 'getAllEnterpriseAccountPlans', 'auth' => true, 'subscription' => true],
    '/api/EnterpriseAccountPlans/deleteEnterpriseAccountPlan' => [$enterpriseAccountPlansController, 'deleteEnterpriseAccountPlan', 'auth' => true, 'subscription' => true],
    '/api/EnterpriseAccountPlans/updateEnterpriseAccountPlan' => [$enterpriseAccountPlansController, 'updateEnterpriseAccountPlan', 'auth' => true, 'subscription' => true],
    // Акаунт
    '/api/account/create' => [$accountsController, 'createAccount', 'auth' => true, 'subscription' => true],
    '/api/account/list' => [$accountsController, 'getAccounts', 'auth' => true, 'subscription' => true],
    '/api/account/counterparty' => [$accountsController, 'getAccountById', 'auth' => true, 'subscription' => true],
    '/api/account/delete' => [$accountsController, 'delAccountById', 'auth' => true, 'subscription' => true],
    '/api/account/restore' => [$accountsController, 'restoreAccountById', 'auth' => true, 'subscription' => true],
    '/api/account/update' => [$accountsController, 'updateAccountById', 'auth' => true, 'subscription' => true],
    // Контакти
    '/api/contact/create' => [$contactController, 'createContact', 'auth' => true, 'subscription' => true],
    '/api/contact/list' => [$contactController, 'getContacts', 'auth' => true, 'subscription' => true],
    '/api/contact/counterparty' => [$contactController, 'getcontactById', 'auth' => true, 'subscription' => true],
    '/api/contact/delete' => [$contactController, 'delContactById', 'auth' => true, 'subscription' => true],
    '/api/contact/restore' => [$contactController, 'restoreContactById', 'auth' => true, 'subscription' => true],
    '/api/contact/deleteContactByAccountId' => [$contactController, 'deleteContactByAccountId', 'auth' => true, 'subscription' => true],
    '/api/contact/update' => [$contactController, 'updateContactById', 'auth' => true, 'subscription' => true],
    // Номеклатура
    '/api/nomenclature/create' => [$nomenclatureController, 'createNomenclature', 'auth' => true, 'subscription' => true],
    '/api/nomenclature/editNomenclature' => [$nomenclatureController, 'editNomenclature', 'auth' => true, 'subscription' => true],
    '/api/nomenclature/deleteNomenclature' => [$nomenclatureController, 'deleteNomenclature', 'auth' => true, 'subscription' => true],
    '/api/nomenclature/listNomenclatures' => [$nomenclatureController, 'listNomenclatures', 'auth' => true, 'subscription' => true],
    '/api/nomenclature/addCharacteristic' => [$nomenclatureController, 'addCharacteristic', 'auth' => true, 'subscription' => true],
    '/api/nomenclature/getById' => [$nomenclatureController, 'getNomenclatureById', 'auth' => true, 'subscription' => true],
    '/api/nomenclature/deleteNomenclatureImage' => [$nomenclatureController, 'deleteNomenclatureImage', 'auth' => true, 'subscription' => true],
    // Картинки Номеклатуры
    '/api/nomenclature/getImage' => [$nomenclatureController, 'getImage', 'auth' => true, 'subscription' => true],
    // Категории
    '/api/category/createCategory' => [$categoryController, 'createCategory', 'auth' => true, 'subscription' => true],
    '/api/category/editCategory' => [$categoryController, 'editCategory', 'auth' => true, 'subscription' => true],
    '/api/category/deleteCategory' => [$categoryController, 'deleteCategory', 'auth' => true, 'subscription' => true],
    '/api/category/listCategories' => [$categoryController, 'listCategories', 'auth' => true, 'subscription' => true],
    '/api/category/getCategoryById' => [$categoryController, 'getCategoryById', 'auth' => true, 'subscription' => true],
    // Характеристики категорий внутри категорий
    '/api/category/addCharacteristic' => [$categoryController, 'addCharacteristic', 'auth' => true, 'subscription' => true],
    // '/api/category/editCharacteristic' => [$categoryController, 'editCharacteristic', 'auth' => true, 'subscription' => true],
    '/api/category/getCharacteristics' => [$categoryController, 'getCharacteristics', 'auth' => true, 'subscription' => true],
    // Создание размерного ряда
    '/api/dimension_range/create' => [$dimensionRangeController, 'createDimensionRange', 'auth' => true, 'subscription' => true],
    '/api/dimension_range/add' => [$dimensionRangeController, 'addDimensionRangeValue', 'auth' => true, 'subscription' => true],
    '/api/dimension_range/get' => [
        $dimensionRangeController,
        'getDimensionRangesByCategoryCharacteristic',
        'auth' => true,
        'subscription' => true
    ],

    // Склад
    '/api/warehouse/createWarehouse' => [$warehouseController, 'createWarehouse', 'auth' => true, 'subscription' => true],
    '/api/warehouse/editWarehouse' => [$warehouseController, 'editWarehouse', 'auth' => true, 'subscription' => true],
    '/api/warehouse/deleteWarehouse' => [$warehouseController, 'deleteWarehouse', 'auth' => true, 'subscription' => true],
    '/api/warehouse/listWarehouses' => [$warehouseController, 'listWarehouses', 'auth' => true, 'subscription' => true],
    '/api/warehouse/getWarehouseById' => [$warehouseController, 'getWarehouseById', 'auth' => true, 'subscription' => true],

    // Товари
    '/api/productCard/createProductCard' => [$productCardController, 'createProductCard', 'auth' => true, 'subscription' => true],
    '/api/productCard/editProductCard' => [$productCardController, 'editProductCard', 'auth' => true, 'subscription' => true],
    '/api/productCard/deleteProductCard' => [$productCardController, 'deleteProductCard', 'auth' => true, 'subscription' => true],
    '/api/productCard/listProductCards' => [$productCardController, 'listProductCards', 'auth' => true, 'subscription' => true],
    '/api/productCard/getProductCardById' => [$productCardController, 'getProductCardById', 'auth' => true, 'subscription' => true],

];

// Обработка предзапросов (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Функция для обработки входящих запросов и маршрутизации их к соответствующим контроллерам.
 *
 * @param array $routes Массив маршрутов.
 * @param AuthMiddleware $authMiddleware Экземпляр класса AuthMiddleware.
 * @param SubscriptionController $subscriptionController Экземпляр SubscriptionController.
 * @param RoleController $roleController Экземпляр RoleController.
 */
function handleRequest($routes, $authMiddleware, $subscriptionController, $roleController)
{
    // Получаем текущий URI
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $queryParams = $_GET; // Получение GET-параметров

    if (array_key_exists($uri, $routes)) {
        $route = $routes[$uri];

        $userId = null;

        // Проверка авторизации, если это требуется маршрутом
        if (isset($route['auth']) && $route['auth']) {
            $authResult = $authMiddleware->authenticate();
            if (!is_array($authResult) || !isset($authResult['userId'])) {
                header("HTTP/1.1 401 Unauthorized");
                echo json_encode(["error" => "Unauthorized"]);
                exit();
            }
            $userId = $authResult['userId'];  // Получаем ID пользователя из декодированного JWT
        }

        // Если это POST-запрос, получаем данные из тела запроса
        $data = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
        }

        // Вызов метода контроллера
        if (isset($route)) {
            if (is_callable([$route[0], $route[1]])) {
                if ($data) {
                    // Если есть данные (POST-запрос с телом), передаём их
                    call_user_func([$route[0], $route[1]], $userId, $data);
                } else {
                    // Если нет данных, используем `queryParams` для GET-запросов
                    call_user_func([$route[0], $route[1]], $userId, $queryParams);
                }
            } else {
                header("HTTP/1.1 500 Internal Server Error");
                echo json_encode(["error" => "Controller method not callable"]);
                exit();
            }
        } else {
            header("HTTP/1.1 404 Not Found");
            echo json_encode(["error" => "Not Found"]);
            exit();
        }
    } else {
        header("HTTP/1.1 404 Not Found");
        echo json_encode(["error" => "Not Found"]);
        exit();
    }
}

// Вызов функции обработки запроса с передачей необходимых зависимостей
handleRequest($routes, $authMiddleware, $subscriptionController, $roleController);
