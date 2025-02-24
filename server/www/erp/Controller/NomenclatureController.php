<?php

namespace Controller;

use Model\NomenclatureModel;
use Model\UserModel;
use Model\CompanyModel;
use Model\DimensionRangeModel;
use Middleware\AuthMiddleware;
use Service\FileService\ImageService;
use Exception;

class NomenclatureController
{
    private NomenclatureModel $nomenclatureModel;
    private UserModel $userModel;
    private CompanyModel $companyModel;
    private DimensionRangeModel $dimensionRangeModel;
    private AuthMiddleware $authMiddleware;
    private ImageService $imageService;

    public function __construct(
        NomenclatureModel $nomenclatureModel,
        UserModel $userModel,
        CompanyModel $companyModel,
        DimensionRangeModel $dimensionRangeModel,
        AuthMiddleware $authMiddleware,
        ImageService $imageService
    ) {
        $this->nomenclatureModel = $nomenclatureModel;
        $this->userModel = $userModel;
        $this->companyModel = $companyModel;
        $this->dimensionRangeModel = $dimensionRangeModel;
        $this->authMiddleware = $authMiddleware;
        $this->imageService = $imageService; // Инициализация ImageService
    }

    /**
     * Метод для создания номенклатуры через API с загрузкой изображений.
     */
    public function createNomenclature()
    {
        header('Content-Type: application/json');

        // 1. Аутентификация
        try {
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                http_response_code(401);
                echo json_encode([
                    "status" => "error",
                    "message" => "Аутентификация не удалась."
                ]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "Аутентификация не удалась: " . $e->getMessage()
            ]);
            exit;
        }

        // 2. Получение ID компании пользователя
        try {
            $companyId = $this->companyModel->getUserCompanyId($userId);
            if (!$companyId) {
                throw new Exception("Компания пользователя не найдена.");
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Ошибка при получении компании: " . $e->getMessage()
            ]);
            exit;
        }
        // 3. Получение данных из $_POST
        $categoryId = intval($_POST['category_id'] ?? 0);
        $groupName = $_POST['group_name'] ?? null;
        $name = $_POST['name'] ?? null;
        $description = $_POST['description'] ?? '';
        $type = $_POST['type'] ?? null;
        $unitOfMeasurement = $_POST['unit_of_measurement'] ?? null;
        $articleCode = $_POST['articleCode'] ?? null;
        $barcode = $_POST['barcode'] ?? null;
        $uktzed_dkpp = $_POST['uktzed_dkpp'] ?? null;
        $priceRounding = $_POST['priceRounding'] ?? null;

        // For integer fields (use intval):
        $minOrderQuantity = boolval($_POST['minOrderQuantity'] ?? 0);  // Changed to boolval
        $barcodeContainsQuantity = boolval($_POST['barcodeContainsQuantity'] ?? 0);  // Changed to boolval
        $markupWholesale = boolval($_POST['markupWholesale'] ?? 0);  // Changed to boolval
        $vatApplicable = boolval($_POST['vatApplicable'] ?? 0);  // Changed to boolval
        $fiscal = boolval($_POST['fiscal'] ?? 0);  // Changed to boolval
        $excise = boolval($_POST['excise'] ?? 0);  // Changed to boolval
        $isWeighted = boolval($_POST['isWeighted'] ?? 0);  // Changed to boolval
        $extraChargeSet = boolval($_POST['extraChargeSet'] ?? 0);
        $maxDiscountSet = boolval($_POST['maxDiscountSet'] ?? 0);
        $askQuantityOnSale = boolval($_POST['askQuantityOnSale'] ?? 0);

        // For float fields (use floatval):
        $barcodeQuantityCoefficient = floatval($_POST['barcodeQuantityCoefficient'] ?? 0);
        $markupFromPrice = floatval($_POST['markupFromPrice'] ?? 0);
        $roundingMinusOne = floatval($_POST['roundingMinusOne'] ?? 0);
        $discountPercent = floatval($_POST['discountPercent'] ?? 0);
        $markupPercent = floatval($_POST['markupPercent'] ?? 0);

        $purchasePrice = floatval($_POST['purchasePrice'] ?? 0);
        $retailPrice = floatval($_POST['retailPrice'] ?? 0);
        $internetPrice = floatval($_POST['internetPrice'] ?? 0);
        $wholesalePrice = floatval($_POST['wholesalePrice'] ?? 0);
        // For string fields (no conversion needed):
        $vatRateCode = $_POST['vatRateCode'] ?? null;
        $benefitCode = $_POST['benefitCode'] ?? null;
        $vatExemptionReason = $_POST['vatExemptionReason'] ?? null;

        // 3.1 Специфические характеристики
        $specificCharacteristics = $_POST['specific_characteristics'] ?? [];
        if (is_string($specificCharacteristics)) {
            $specificCharacteristics = json_decode($specificCharacteristics, true) ?? [];
        }
        // 3.2 Размерные ряды
        $dimensionRanges = $_POST['dimension_ranges'] ?? [];
        if (is_string($dimensionRanges)) {
            $dimensionRanges = json_decode($dimensionRanges, true) ?? [];
        }

        // 3.3 Файлы-изображения
        $images = $_FILES['images'] ?? null;
        $processedImages = [];

        if ($images && is_array($images)) {
            // Check if we need to wrap the images into an array for each image
            $image_array = [];
            foreach ($images['name'] as $key => $image_name) {
                // For each image property, create an associative array for each image
                $image_array[] = [
                    'name' => $images['name'][$key],
                    'type' => $images['type'][$key],
                    'tmp_name' => $images['tmp_name'][$key],
                    'error' => $images['error'][$key],
                    'size' => $images['size'][$key],
                ];
            }

            // Merge with existed images or just use $image_array if no existed images
            $images = $image_array;
        }
        // 4. Валидация (пример)
        try {
            $this->validateCreateNomenclatureData($_POST, $_FILES);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
            exit;

            // 5. Обработка изображений

        }
        if (!empty($images)) {
            try {
                // Возвращает массив с информацией об обработанных файлах
                $processedImages = $this->processImages($images);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    "status" => "error",
                    "message" => "Ошибка при обработке изображений: " . $e->getMessage()
                ]);
                exit;
            }
        };
        // 6. Создание номенклатуры (с размерными рядами или без)
        try {
            if (!empty($dimensionRanges)) {
                // Создание номенклатур по размерным рядам
                $created = $this->nomenclatureModel->createNomenclaturesWithDimensionRanges(
                    $companyId,
                    $barcode,
                    $articleCode,
                    $name,
                    $groupName,
                    $type,
                    $unitOfMeasurement,
                    $description,
                    $categoryId,
                    $specificCharacteristics,
                    $dimensionRanges,
                    $processedImages,
                    // Новые параметры
                    $minOrderQuantity,
                    $barcodeContainsQuantity,
                    $barcodeQuantityCoefficient,
                    $markupFromPrice,
                    $maxDiscountSet,
                    $extraChargeSet,
                    $priceRounding,
                    $roundingMinusOne,
                    $discountPercent,
                    $markupWholesale,
                    $markupPercent,
                    $fiscal,
                    $excise,
                    $vatApplicable,
                    $vatRateCode,
                    $benefitCode,
                    $vatExemptionReason,
                    $uktzed_dkpp,
                    $isWeighted,
                    $purchasePrice,
                    $retailPrice,
                    $internetPrice,
                    $wholesalePrice,
                    $askQuantityOnSale
                );
            } else {
                // Создание одной номенклатуры
                $created = $this->nomenclatureModel->createNomenclature(
                    $companyId,
                    $barcode,
                    $articleCode,
                    $name,
                    $groupName,
                    $type,
                    $unitOfMeasurement,
                    $description,
                    $categoryId,
                    $specificCharacteristics,
                    $processedImages,
                    // Новые параметры
                    $minOrderQuantity,
                    $barcodeContainsQuantity,
                    $barcodeQuantityCoefficient,
                    $markupFromPrice,
                    $maxDiscountSet,
                    $extraChargeSet,
                    $priceRounding,
                    $roundingMinusOne,
                    $discountPercent,
                    $markupWholesale,
                    $markupPercent,
                    $fiscal,
                    $excise,
                    $vatApplicable,
                    $vatRateCode,
                    $benefitCode,
                    $vatExemptionReason,
                    $uktzed_dkpp,
                    $isWeighted,
                    $purchasePrice,
                    $retailPrice,
                    $internetPrice,
                    $wholesalePrice,
                    $askQuantityOnSale
                );
            }

            echo json_encode([
                "status" => "success",
                "data" => $created
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Ошибка при создании номенклатуры: " . $e->getMessage()
            ]);
        }
    }



    /**
     * Валидация данных для создания номенклатуры.
     *
     * @param array $post Данные из $_POST
     * @param array $files Данные из $_FILES
     * @throws \InvalidArgumentException
     */
    protected function validateCreateNomenclatureData(array $post, array $files): void
    {
        // Список обязательных полей
        $requiredFields = ['articleCode', 'name', 'group_name', 'type', 'unit_of_measurement', 'category_id', 'barcode'];

        foreach ($requiredFields as $field) {
            if (empty($post[$field])) {
                throw new \InvalidArgumentException("Поле '{$field}' обязательно для заполнения.");
            }
        }

        // Проверка, что category_id является числом
        if (!is_numeric($post['category_id'])) {
            throw new \InvalidArgumentException("Поле 'category_id' должно быть числом.");
        }

        // Проверка изображений, если они есть
        if (!empty($files['images'])) {
            foreach ($files['images']['error'] as $key => $error) {
                if ($error !== UPLOAD_ERR_OK) {
                    throw new \InvalidArgumentException("Ошибка при загрузке изображения: " . $this->codeToMessage($error));
                }

                // Дополнительные проверки (например, тип файла, размер и т.д.)
                $fileType = mime_content_type($files['images']['tmp_name'][$key]);
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

                if (!in_array($fileType, $allowedTypes)) {
                    throw new \InvalidArgumentException("Недопустимый тип файла для изображения: {$files['images']['name'][$key]}");
                }

                // Проверка размера файла (например, не более 5MB)
                if ($files['images']['size'][$key] > 5 * 1024 * 1024) {
                    throw new \InvalidArgumentException("Размер изображения не должен превышать 5MB: {$files['images']['name'][$key]}");
                }
            }
        }
    }
    /**
     * Генерация уникального имени файла.
     *
     */
    public function generateUniqueFileName(string $originalName): string
    {
        // Получение расширения файла
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);

        // Генерация уникального имени с префиксом 'img_' и сохранением расширения
        return uniqid('img_', true) . '.' . $extension;
    }

    /**
     * Обработчик изображений
     *
     * @param array $images 
     * @return array Обработанние изображения
     */
    protected function processImages(array $images): array
    {
        $processedImages = [];

        if (empty($images)) {
            return $processedImages;
        }

        $tempDir = '/tmp/nomenclature_images';
        if (!is_dir($tempDir) && !mkdir($tempDir, 0755, true)) {
            throw new \Exception("Не удалось создать временную директорию для изображений.");
        }

        foreach ($images as $key => $image) {
            $tmpName = $image['tmp_name'] ?? null;
            $originalName = basename($image['name'] ?? '');

            if (!$tmpName || !file_exists($tmpName)) {
                $processedImages[] = [
                    'image_id' => $image['image_id'] ?? null,
                    'name' => $originalName,
                    'sort_order' => $image['sort_order'] ?? null,
                    'additional_info' => $image['additional_info'] ?? null
                ];
                continue;
            }

            $uniqueName = $this->generateUniqueFileName($originalName);
            $tempFilePath = $tempDir . '/' . $uniqueName;

            if (!is_uploaded_file($tmpName)) {
                throw new \Exception("Файл {$tmpName} не является загруженным через HTTP.");
            }

            if (!move_uploaded_file($tmpName, $tempFilePath)) {
                throw new \Exception("Не удалось переместить загруженное изображение: {$originalName}");
            }

            $sortOrder = isset($_POST['image_sort_order'][$key]) ? (int)$_POST['image_sort_order'][$key] : $key;
            $additionalInfo = $_POST['image_additional_info'][$key] ?? null;

            $processedImages[] = [
                'tmp_path' => $tempFilePath,
                'name' => $originalName,
                'sort_order' => $sortOrder,
                'additional_info' => $additionalInfo
            ];
        }

        return $processedImages;
    }


    /**
     * Преобразование кода ошибки загрузки файла в сообщение.
     *
     * @param int $code Код ошибки
     * @return string Сообщение об ошибке
     */
    protected function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "Загруженный файл превышает допустимый размер (upload_max_filesize).";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "Загруженный файл превышает допустимый размер (MAX_FILE_SIZE).";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "Файл был загружен только частично.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "Файл не был загружен.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Отсутствует временная папка.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Не удалось записать файл на диск.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "Загрузка файла остановлена расширением.";
                break;
            default:
                $message = "Неизвестная ошибка при загрузке файла.";
                break;
        }
        return $message;
    }


    /**
     * Метод для редактирования номенклатуры через API (без dimension_range).
     */
    public function editNomenclature()
    {
        header('Content-Type: application/json');

        // 1. Аутентификация
        try {
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                http_response_code(401);
                echo json_encode([
                    "status" => "error",
                    "message" => "Аутентификация не удалась."
                ]);
                exit;
            }
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "Аутентификация не удалась: " . $e->getMessage()
            ]);
            exit;
        }

        // 2. Получение ID компании пользователя
        try {
            $companyId = $this->companyModel->getUserCompanyId($userId);
            if (!$companyId) {
                throw new Exception("Компания пользователя не найдена.");
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Ошибка при получении компании: " . $e->getMessage()
            ]);
            exit;
        }

        // 3. Получение данных из $_POST
        $nomenclatureId = intval($_POST['id']);
        $categoryId = intval($_POST['category_id'] ?? 0);
        $groupName = $_POST['group_name'] ?? null;
        $name = $_POST['name'] ?? null;
        $description = $_POST['description'] ?? '';
        $type = $_POST['type'] ?? null;
        $unitOfMeasurement = $_POST['unit_of_measurement'] ?? null;
        $articleCode = $_POST['articleCode'] ?? null;
        $barcode = $_POST['barcode'] ?? null;
        $uktzed_dkpp = $_POST['uktzed_dkpp'] ?? null;
        $priceRounding = $_POST['priceRounding'] ?? null;

        // For integer fields (use intval):
        $minOrderQuantity = intval($_POST['minOrderQuantity'] ?? 0);  // Changed to intval
        $barcodeContainsQuantity = intval($_POST['barcodeContainsQuantity'] ?? 0);  // Changed to intval
        $markupWholesale = intval($_POST['markupWholesale'] ?? 0);  // Changed to intval
        $vatApplicable = intval($_POST['vatApplicable'] ?? 0);  // Changed to intval
        $fiscal = intval($_POST['fiscal'] ?? 0);  // Changed to intval
        $excise = intval($_POST['excise'] ?? 0);  // Changed to intval
        $isWeighted = intval($_POST['isWeighted']);  // Changed to intval
        $extraChargeSet = intval($_POST['extraChargeSet'] ?? 0);
        $maxDiscountSet = intval($_POST['maxDiscountSet'] ?? 0);
        $askQuantityOnSale = intval($_POST['askQuantityOnSale'] ?? 0);
        echo basename(__FILE__) . ' (Line ' . __LINE__ . ') - $askQuantityOnSale: ';
        var_dump($_POST['askQuantityOnSale']);
        // For float fields (use floatval):
        $barcodeQuantityCoefficient = floatval($_POST['barcodeQuantityCoefficient'] ?? 0);
        $markupFromPrice = floatval($_POST['markupFromPrice'] ?? 0);
        $roundingMinusOne = floatval($_POST['roundingMinusOne'] ?? 0);
        $discountPercent = floatval($_POST['discountPercent'] ?? 0);
        $markupPercent = floatval($_POST['markupPercent'] ?? 0);

        $purchasePrice = floatval($_POST['purchasePrice'] ?? 0);
        $retailPrice = floatval($_POST['retailPrice'] ?? 0);
        $internetPrice = floatval($_POST['internetPrice'] ?? 0);
        $wholesalePrice = floatval($_POST['wholesalePrice'] ?? 0);
        // For string fields (no conversion needed):
        $vatRateCode = $_POST['vatRateCode'] ?? null;
        $benefitCode = $_POST['benefitCode'] ?? null;
        $vatExemptionReason = $_POST['vatExemptionReason'] ?? null;


        // 3.1 Специфические характеристики (JSON)
        $specificCharacteristics = $_POST['specific_characteristics'] ?? null;
        if (is_string($specificCharacteristics)) {
            $specificCharacteristics = json_decode($specificCharacteristics, true);
            if (!is_array($specificCharacteristics)) {
                $specificCharacteristics = null;
            }
        }

        // 3.2 Обработка (существующих) изображений + $_FILES
        $existedImagesJson = $_POST['existed_images'] ?? '[]';
        $existedImages = json_decode($existedImagesJson, true);
        if (!is_array($existedImages)) {
            $existedImages = [];
        }

        // Объединяем загруженные изображения (если есть) и существующие
        $images = $_FILES['images'] ?? null;
        $mergedImages = [];
        if ($images && is_array($images['name'] ?? null)) {
            $imageArray = [];
            foreach ($images['name'] as $i => $filename) {
                $imageArray[] = [
                    'name'     => $images['name'][$i],
                    'type'     => $images['type'][$i],
                    'tmp_name' => $images['tmp_name'][$i],
                    'error'    => $images['error'][$i],
                    'size'     => $images['size'][$i],
                    // возможно, sort_order/ additional_info тоже берем из $_POST
                    'sort_order'       => $_POST['image_sort_order'][$i]       ?? $i,
                    'additional_info'  => $_POST['image_additional_info'][$i]   ?? null,
                ];
            }
            $mergedImages = array_merge($existedImages, $imageArray);
        } else {
            // только существующие (без новых загружаемых)
            $mergedImages = $existedImages;
        }

        // 4. Валидация (пример - можно упростить или расширить)
        //    Если у вас есть отдельная специфичная валидация для "edit",
        //    можете сделать validateEditNomenclatureData(). Или
        //    повторно использовать validateCreateNomenclatureData() с поправками.
        try {
            // Если хотите более мягкую валидацию (частичное обновление),
            // скорректируйте ниже или создайте отдельный метод.
            $this->validateEditNomenclatureData($_POST, $_FILES, $nomenclatureId);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
            exit;
        }

        // 5. Обработка изображений (если есть)
        $processedImages = [];
        if (!empty($mergedImages)) {
            try {
                $processedImages = $this->processImages($mergedImages);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    "status" => "error",
                    "message" => "Ошибка при обработке изображений: " . $e->getMessage()
                ]);
                exit;
            }
        }
        echo basename(__FILE__) . ' (Line ' . __LINE__ . ') - $processedImages: ';
        var_dump($processedImages);
        // 6. Вызываем метод модели
        try {
            $updated = $this->nomenclatureModel->editNomenclature(
                $companyId,                // int
                $nomenclatureId,               // ?int
                $categoryId,           // int
                $articleCode,              // ?string
                $barcode,                  // ?string
                $name,                     // ?string
                $groupName,                // ?string
                $type,                     // ?string
                $unitOfMeasurement,        // ?string
                $description,              // ?string
                $specificCharacteristics,  // ?array
                $processedImages,          // ?array
                $minOrderQuantity,         // ?int
                $barcodeContainsQuantity,  // ?bool
                $barcodeQuantityCoefficient, // ?float
                $markupFromPrice,          // ?float
                $maxDiscountSet,           // ?int
                $extraChargeSet,           // ?float
                $priceRounding,            // ?string
                $roundingMinusOne,         // ?float
                $discountPercent,          // ?float
                $markupWholesale,          // ?float
                $markupPercent,            // ?float
                $fiscal,                   // ?bool
                $excise,                   // ?bool
                $vatApplicable,            // ?bool
                $vatRateCode,              // ?string
                $benefitCode,              // ?string
                $vatExemptionReason,       // ?string
                $uktzed_dkpp,              // ?string
                $isWeighted,               // ?int
                $purchasePrice,            // ?float
                $retailPrice,              // ?float
                $internetPrice,            // ?float
                $wholesalePrice,           // ?float
                $askQuantityOnSale         // ?int
            );

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Номенклатура успешно обновлена",
                "data"    => $updated
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "status"  => "error",
                "message" => "Ошибка при обновлении номенклатуры: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Пример базовой валидации для редактирования (упрощенно).
     */
    protected function validateEditNomenclatureData(array $post, array $files, int $nomenclatureId): void
    {
        if ($nomenclatureId <= 0) {
            throw new \InvalidArgumentException("Некорректный идентификатор номенклатуры.");
        }
        // Если у вас жесткое требование, что articleCode, barcode не могут быть пустыми даже при редактировании:
        if (isset($post['articleCode']) && trim($post['articleCode']) === '') {
            throw new \InvalidArgumentException("Артикул не может быть пустым.");
        }
        if (isset($post['barcode']) && trim($post['barcode']) === '') {
            throw new \InvalidArgumentException("Штрих-код не может быть пустым.");
        }

        // Проверить, что category_id - число, если он есть
        if (isset($post['category_id']) && !is_numeric($post['category_id'])) {
            throw new \InvalidArgumentException("Поле 'category_id' должно быть числом.");
        }

        // При необходимости - валидация изображений, как в validateCreateNomenclatureData()
        // ...
    }


    /**
     * Удаление номенклатуры
     */
    public function deleteNomenclature(): void
    {
        try {
            // Получение и декодирование данных из входного запроса
            $data = json_decode(file_get_contents('php://input'), true);

            if (!is_array($data)) {
                throw new \InvalidArgumentException("Некорректный формат данных.");
            }

            // Аутентификация пользователя
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            // Получение ID компании пользователя
            $companyId = $this->companyModel->getUserCompanyId($userId);

            if (empty($data['nomenclature_id'])) {
                throw new \InvalidArgumentException("ID номенклатуры обязательно.");
            }
            $nomenclature_id = intval($data['nomenclature_id']);
            // Удаляем номенклатуру через модель
            $result = $this->nomenclatureModel->deleteNomenclature($companyId, $nomenclature_id);

            // Возврат успешного ответа
            http_response_code(200);
            echo json_encode([
                "message" => "Номенклатура удалена успешно",
                "details" => $result
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось удалить номенклатуру", "details" => $e->getMessage()]);
        }
    }


    /**
     * Удаление фотографии по айдишнику фото и номенклари
     */
    public function deleteNomenclatureImage(): void
    {
        try {

            // Получение и декодирование данных из входного запроса
            $data = json_decode(file_get_contents('php://input'), true);

            if (!is_array($data)) {
                throw new \InvalidArgumentException("Некорректный формат данных.");
            }
            if (!isset($data['image_id']) || !isset($data['nomenclature_id'])) {
                throw new \InvalidArgumentException("Параметры 'image_id' и 'nomenclature_id' обязательны.");
            }
            $imageId = intval($data['image_id']);
            $nomenclatureId = intval($data['nomenclature_id']);

            if ($imageId <= 0 || $nomenclatureId <= 0) {
                throw new \InvalidArgumentException("Некорректные ID изображения или номенклатуры.");
            }

            // Аутентификация пользователя
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            // Получение ID компании пользователя
            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Получаем данные о номенклатуре из модели
            $nomenclature = $this->nomenclatureModel->getNomenclatureById($userId, $nomenclatureId);

            if (!$nomenclature) {
                throw new \Exception("Номенклатура не найдена.");
            }
            // Удаляем изображение через сервис
            $this->nomenclatureModel->deleteNomenclatureImage($companyId, $imageId, $nomenclatureId);

            // Успешный ответ
            http_response_code(200);
            echo json_encode(["message" => "Image deleted successfully"]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось удалить изображение", "details" => $e->getMessage()]);
        }
    }


    /**
     * Получение списка номенклатур
     */
    public function listNomenclatures(): void
    {
        try {
            // Аутентификация пользователя
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            // Получение ID компании пользователя
            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Получаем список номенклатур через модель
            $nomenclatures = $this->nomenclatureModel->getNomenclaturesByCompany($companyId);

            // Возврат успешного ответа
            http_response_code(200);
            echo json_encode([
                "nomenclatures" => $nomenclatures
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось получить список номенклатур", "details" => $e->getMessage()]);
        }
    }

    /**
     * Получение номенклатуры по ID
     */
    public function getNomenclatureById(): void
    {
        try {
            // Получение и декодирование данных из входного запроса
            $data = json_decode(file_get_contents('php://input'), true);

            if (!is_array($data)) {
                throw new \InvalidArgumentException("Некорректный формат данных.");
            }

            // Получение параметров из URL (например, /api/nomenclature/getById?id=123)
            if (!isset($data['id'])) {
                throw new \InvalidArgumentException("Параметр 'id' обязателен.");
            }
            $nomenclatureId = intval($data['id']);

            if ($nomenclatureId <= 0) {
                throw new \InvalidArgumentException("Некорректный ID номенклатуры.");
            }

            // Аутентификация пользователя
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;

            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            // Получение ID компании пользователя
            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Получение номенклатуры по ID через модель
            $nomenclature = $this->nomenclatureModel->getNomenclatureById($companyId, $nomenclatureId);

            if (!$nomenclature) {
                throw new \Exception("Номенклатура не найдена.");
            }

            // Получение связанных изображений через ImageService
            $images = $this->imageService->getImagesByNomenclatureId($nomenclatureId);
            $nomenclature['images'] = $images;

            // Возврат успешного ответа
            http_response_code(200);
            echo json_encode([
                "nomenclature" => $nomenclature
            ]);
        } catch (\InvalidArgumentException $e) {
            // Обработка ошибок валидации
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            // Обработка общих ошибок
            http_response_code(500);
            echo json_encode(["error" => "Не удалось получить номенклатуру", "details" => $e->getMessage()]);
        }
    }

    // Получения путей картинок
    public function getImage(): void
    {
        try {
            if (!isset($_GET['path'])) {
                throw new \InvalidArgumentException("Параметр 'path' обязателен.");
            }

            // Аутентификация
            $authData = $this->authMiddleware->authenticate();
            $userId = $authData['userId'] ?? null;
            if (!$userId) {
                throw new \Exception("Аутентификация не удалась.");
            }

            // Получение ID компании пользователя
            $companyId = $this->companyModel->getUserCompanyId($userId);

            // Путь, пришедший с фронта
            $dockerPath = $_GET['path'];
            error_log("DEBUG: dockerPath = " . $dockerPath);

            // Проверяем путь в Docker
            $realDockerPath = realpath($dockerPath);
            if ($realDockerPath === false) {
                throw new \Exception("Файл не существует: {$dockerPath}");
            }
            error_log("DEBUG: realDockerPath = " . $realDockerPath);

            // Конвертируем путь в "хостовый" вариант
            $hostPath = $this->convertDockerPathToHost($realDockerPath);
            error_log("DEBUG: hostPath = " . $hostPath);

            // Проверяем наличие записи в БД
            $imageRecord = $this->imageService->getImageByPath($hostPath);
            if (!$imageRecord) {
                throw new \Exception("Изображение в БД не найдено (path={$hostPath}).");
            }
            error_log("DEBUG: Нашли запись в БД: " . json_encode($imageRecord));

            // Проверка прав доступа
            if (!isset($imageRecord['company_id']) || (int)$imageRecord['company_id'] !== (int)$companyId) {
                throw new \Exception("Это не ваше фото.");
            }

            // Проверяем файл
            $altPath = str_replace('/var/www/erp/upload', '/mnt/shared/uploads', $realDockerPath);
            if (!file_exists($realDockerPath) && !file_exists($altPath)) {
                throw new \Exception("Физический файл не найден: {$realDockerPath} или {$altPath}");
            }

            // Определяем MIME-тип
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($realDockerPath);

            header("Content-Type: {$mimeType}");
            header("Content-Length: " . filesize($realDockerPath));
            header("Content-Disposition: inline; filename=\"" . basename($realDockerPath) . "\"");

            readfile($realDockerPath);
            exit;
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(["error" => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Не удалось получить изображение111", "details" => $e->getMessage()]);
        }
    }

    // Функция конвертации путей
    private function convertDockerPathToHost(string $dockerPath): string
    {
        return str_replace('/var/www/erp/upload', '/home/dima/Project/erp_2_0/server/www/erp/upload', $dockerPath);
    }
}
