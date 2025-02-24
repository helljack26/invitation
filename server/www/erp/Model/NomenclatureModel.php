<?php

namespace Model;

use PDO;
use Service\CacheService;
use Service\FileService\ImageService;
use Exception;

/**
 * Класс NomenclatureModel отвечает за управление номенклатурой в системе с использованием кеширования Redis.
 */
class NomenclatureModel
{
    protected PDO $conn;
    protected CacheService $cacheService;
    protected ImageService $imageService;

    public function __construct(PDO $conn, CacheService $cacheService, ImageService $imageService)
    {
        $this->conn = $conn;
        $this->cacheService = $cacheService;
        $this->imageService = $imageService;

        if (!$cacheService) {
            throw new Exception("CacheService instance is not provided in NomenclatureModel.");
        }
    }

    /**
     * Создаёт несколько номенклатур по переданным размерным рядам,
     * прикрепляя к каждой одни и те же изображения.
     *
     * @param int         $companyId              ID компании
     * @param string|null $barcode               Штрихкод (может быть null)
     * @param string|null $articleCode           Артикул (может быть null)
     * @param string      $name                  Название номенклатуры
     * @param string      $groupName             Группа номенклатуры
     * @param string      $type                  Тип номенклатуры
     * @param string      $unitOfMeasurement     Единица измерения
     * @param string      $description           Описание номенклатуры
     * @param int         $categoryId            ID категории (целое число)
     * @param array       $specificCharacteristics Массив специфических характеристик
     * @param array       $dimensionRanges       Массив размерных рядов
     * @param array       $processedImages       Массив изображений, прикрепляемых к номенклатуре
     * @param int|null    $minOrderQuantity      Минимальное количество для заказа (целое число или null)
     * @param int|null    $barcodeContainsQuantity Признак наличия количества в штрихкоде (целое число или null)
     * @param int|null    $barcodeQuantityCoefficient Коэффициент для штрихкода (целое число или null)
     * @param int|null    $markupFromPrice       Наценка от цены (целое число или null)
     * @param int|null    $maxDiscountSet        Максимальная скидка (целое число или null)
     * @param int|null    $extraChargeSet        Дополнительная плата (целое число или null)
     * @param string|null $priceRounding         Правило округления цены (строка или null)
     * @param int|null    $roundingMinusOne      Признак округления на минус один (целое число или null)
     * @param int|null    $discountPercent       Процент скидки (целое число или null)
     * @param int|null    $markupWholesale       Наценка для оптовых покупателей (целое число или null)
     * @param int|null    $markupPercent         Процент наценки (целое число или null)
     * @param int|null    $fiscal                Признак фискальности (целое число или null)
     * @param int|null    $excise                Признак акциза (целое число или null)
     * @param int|null    $vatApplicable         Признак применения НДС (целое число или null)
     * @param string|null $vatRateCode           Код ставки НДС (строка или null)
     * @param string|null $benefitCode           Код льготы (строка или null)
     * @param string|null $vatExemptionReason    Причина освобождения от НДС (строка или null)
     * 
     * @return array
     * @throws Exception
     */
    public function createNomenclaturesWithDimensionRanges(
        int $companyId,
        ?string $barcode,
        ?string $articleCode,
        string $name,
        string $groupName,
        string $type,
        string $unitOfMeasurement,
        string $description,
        int $categoryId,
        array $specificCharacteristics = [],
        array $dimensionRanges = [],
        array $processedImages = [],  // Обязательно в сигнатуре
        ?int $minOrderQuantity = null,
        ?int $barcodeContainsQuantity = null,
        ?int $barcodeQuantityCoefficient = null,
        ?int $markupFromPrice = null,
        ?int $maxDiscountSet = null,
        ?int $extraChargeSet = null,
        ?string $priceRounding = null,
        ?int $roundingMinusOne = null,
        ?int $discountPercent = null,
        ?int $markupWholesale = null,
        ?int $markupPercent = null,
        ?int $fiscal = null,
        ?int $excise = null,
        ?int $vatApplicable = null,
        ?string $vatRateCode = null,
        ?string $benefitCode = null,
        ?string $vatExemptionReason = null,
        ?string $uktzed_dkpp = null,
        ?int $isWeighted = null,
        ?int $purchasePrice = null,
        ?int $retailPrice = null,
        ?int $internetPrice = null,
        ?int $wholesalePrice = null,
        ?int $askQuantityOnSale = null
    ): array {
        // 1. Проверяем, что размерные ряды действительно переданы
        if (empty($dimensionRanges)) {
            throw new Exception("Размерный ряд не задан.");
        }

        // 2. Генерируем все возможные комбинации (декартово произведение)
        $combinations = $this->generateCombinations($dimensionRanges);
        if (empty($combinations)) {
            throw new Exception("Нет доступных комбинаций размерных рядов.");
        }

        // 3. Массив созданных номенклатур
        $createdNomenclatures = [];

        // 4. Перебираем каждую комбинацию
        foreach ($combinations as $combination) {
            // Сливаем специфические характеристики с текущими «размерными»
            $newSpecificCharacteristics = $specificCharacteristics;
            foreach ($combination as $dimensionName => $value) {
                $newSpecificCharacteristics[$dimensionName] = $value;
            }
            $barcode = $this->generateBarcode();

            // Создаём номенклатуру (одна запись),
            // обязательно передаём $processedImages, чтобы прикрепить одни и те же фото
            $created = $this->createNomenclature(
                $companyId,
                $barcode,
                $articleCode,
                $name,
                $groupName,
                $type,
                $unitOfMeasurement,
                $description,
                $categoryId,
                $newSpecificCharacteristics,
                $processedImages,
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

            $createdNomenclatures[] = $created;
        }

        return [
            "message" => "Номенклатуры созданы успешно.",
            "nomenclatures" => $createdNomenclatures
        ];
    }

    /**
     * Генерирует все возможные комбинации значений размерных рядов.
     *
     * @param array $dimensionRanges Массив размерных рядов
     * @return array Массив комбинаций
     */
    private function generateCombinations(array $dimensionRanges): array
    {
        $result = [[]];
        foreach ($dimensionRanges as $dimensionRange) {
            $temp = [];
            foreach ($result as $combination) {
                foreach ($dimensionRange['values'] as $value) {
                    $temp[] = array_merge($combination, [
                        $dimensionRange['name'] => $value
                    ]);
                }
            }
            $result = $temp;
        }
        return $result;
    }

    /**
     * Генерирует уникальный артикул, если не задан.
     *
     * @return string
     * @throws Exception
     */
    protected function generateBarcode(): string
    {
        // Пример генерации артикула: ART-20240101-0001
        $datePart = date('Ymd');
        $sequence = $this->getNextBarcodeSequence($datePart);
        return "{$datePart}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
    /**
     * Получает следующий номер последовательности артикула для заданной даты.
     *
     * @param string $datePart
     * @return int
     */
    protected function getNextBarcodeSequence(string $datePart): int
    {
        $query = "SELECT COUNT(*) as count FROM Nomenclature WHERE nomenclature_code LIKE :prefix";
        $prefix = "{$datePart}%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':prefix', $prefix, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] + 1;
    }
    /**
     * Генерация уникального кода номенклатуры в формате '00000028'.
     *
     * @param int $companyId
     * @return string
     * @throws Exception
     */
    protected function generateUniqueNomenclatureCode(int $companyId): string
    {
        // Получение максимального существующего кода для компании
        $query = "SELECT MAX(CAST(nomenclature_code AS UNSIGNED)) as max_code FROM Nomenclature WHERE company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxCode = $result['max_code'] ?? 0;

        // Увеличение на 1
        $newCodeInt = $maxCode + 1;
        // Форматирование кода до 8 цифр с ведущими нулями
        $newCode = str_pad((string)$newCodeInt, 8, '0', STR_PAD_LEFT);

        // Проверка уникальности
        if ($this->getNomenclatureByCode($companyId, $newCode)) {
            throw new Exception("Сгенерированный код номенклатуры уже существует. Попробуйте снова.");
        }

        return $newCode;
    }

    /**
     * Создаёт одну номенклатуру и при необходимости прикрепляет к ней изображения.
     *
     * @param int         $companyId                  Идентификатор компании.
     * @param string      $barcode                    Штрих-код.
     * @param string      $articleCode               Внутренний код артикула.
     * @param string      $name                       Название номенклатуры.
     * @param string      $groupName                  Название группы.
     * @param string      $type                       Тип номенклатуры.
     * @param string      $unitOfMeasurement          Единица измерения.
     * @param string|null $description                Описание номенклатуры (необязательно).
     * @param int         $categoryId                 Идентификатор категории.
     * @param array       $specificCharacteristics    Массив дополнительных характеристик.
     * @param array       $images                     Массив с изображениями номенклатуры (необязательно).
     *                                                Каждый элемент может содержать данные об изображении:
     *                                                [
     *                                                    'name'      => 'filename.jpg',
     *                                                    'content'   => '<base64-encoded-image-data>',
     *                                                    'mime_type' => 'image/jpeg',
     *                                                    ...
     *                                                ]
     * @param int|null    $minOrderQuantity           Минимальное количество для заказа.
     * @param bool|null   $barcodeContainsQuantity    Признак, указывает на включение количества в штрих-код.
     * @param float|null  $barcodeQuantityCoefficient Коэффициент количества, указанного в штрих-коде.
     * @param float|null  $markupFromPrice            Наценка от цены.
     * @param int|null    $maxDiscountSet             Максимальная скидка, %.
     * @param float|null  $extraChargeSet             Дополнительная наценка, %.
     * @param string|null $priceRounding              Способ округления цены.
     * @param float|null  $roundingMinusOne           Шаг округления при уменьшении цены.
     * @param float|null  $discountPercent            Уровень скидки, %.
     * @param float|null  $markupWholesale            Наценка для оптовой цены, %.
     * @param float|null  $markupPercent              Наценка, %.
     * @param bool|null   $fiscal                     Фискальный признак.
     * @param bool|null   $excise                     Акцизный признак.
     * @param bool|null   $vatApplicable              Признак обложения НДС.
     * @param string|null $vatRateCode                Код ставки НДС.
     * @param string|null $benefitCode                Код льготы по НДС.
     * @param string|null $vatExemptionReason         Причина освобождения от НДС.
     * @param string|null $uktzed_dkpp                Код УКТВЭД/ДКПП.
     * @param int|null    $isWeighted                 Признак весового товара.
     * @param float|null  $purchasePrice              Закупочная цена.
     * @param float|null  $retailPrice                Розничная цена.
     * @param float|null  $internetPrice              Интернет-цена.
     * @param float|null  $wholesalePrice             Оптовая цена.
     * @param int|null    $askQuantityOnSale          Запрашивать количество при продаже.
     *
     * @return array Данные о созданной номенклатуре в формате массива.
     * @throws \Exception
     */
    public function createNomenclature(
        int $companyId,
        string $barcode,
        string $articleCode,
        string $name,
        string $groupName,
        string $type,
        string $unitOfMeasurement,
        ?string $description = null,
        int $categoryId,
        array $specificCharacteristics = [],
        array $images = [],
        ?int $minOrderQuantity = null,
        ?bool $barcodeContainsQuantity = null,
        ?float $barcodeQuantityCoefficient = null,
        ?float $markupFromPrice = null,
        ?int $maxDiscountSet = null,
        ?float $extraChargeSet = null,
        ?string $priceRounding = null,
        ?float $roundingMinusOne = null,
        ?float $discountPercent = null,
        ?float $markupWholesale = null,
        ?float $markupPercent = null,
        ?bool $fiscal = null,
        ?bool $excise = null,
        ?bool $vatApplicable = null,
        ?string $vatRateCode = null,
        ?string $benefitCode = null,
        ?string $vatExemptionReason = null,
        ?string $uktzed_dkpp = null,
        ?int $isWeighted,
        ?float $purchasePrice = null,
        ?float $retailPrice = null,
        ?float $internetPrice = null,
        ?float $wholesalePrice = null,
        ?int $askQuantityOnSale = null
    ): array {
        // Логирование входных данных
        error_log("Создание номенклатуры: артикул={$articleCode}, barcode={$barcode}");

        // Проверка обязательных полей
        if (
            empty($articleCode) || empty($name) || empty($groupName) || empty($type)
            || empty($unitOfMeasurement) || empty($categoryId)
        ) {
            throw new \InvalidArgumentException("Все поля номенклатуры обязательны.");
        }

        // Генерация текущей даты
        $createdAt = date('Y-m-d H:i:s');

        // Генерация уникального кода номенклатуры
        $nomenclatureCode = $this->generateUniqueNomenclatureCode($companyId);

        // Начало транзакции
        $this->conn->beginTransaction();

        try {
            // Проверка дубликата: номенклатуры с таким кодом
            if ($this->getNomenclatureByCode($companyId, $nomenclatureCode)) {
                throw new \Exception("Номенклатура с кодом {$nomenclatureCode} уже существует.");
            }

            // Получаем характеристики категории из Redis
            $categoryModel = new CategoryModel($this->conn, $this->cacheService);
            $categoryCharacteristics = $categoryModel->getCategoryCharacteristics($companyId, $categoryId);

            // Объединяем характеристики категории и переданные характеристики
            $mergedCharacteristics = $this->mergeCharacteristics($categoryCharacteristics[0]['options'] ?? [], $specificCharacteristics, []);

            // Генерация длинного названия
            $longName = $this->generateLongName($groupName, $name, $mergedCharacteristics);

            $lowercaseType = strtolower($type);

            // Преобразуем характеристики в JSON
            $characteristicsJson = json_encode($mergedCharacteristics, JSON_UNESCAPED_UNICODE);
            if ($characteristicsJson === false) {
                throw new \Exception("Ошибка при кодировании характеристик в JSON: " . json_last_error_msg());
            }

            // Вставляем основную запись в таблицу Nomenclature
            $query = "INSERT INTO Nomenclature 
            (company_id, nomenclature_code, barcode, articleCode, name, group_name, type, 
             unit_of_measurement, description, category_id, long_name, characteristics, 
             created_at, updated_at, purchasePrice, retailPrice, internetPrice, askQuantityOnSale, wholesalePrice, 
             minOrderQuantity, barcodeContainsQuantity, barcodeQuantityCoefficient, 
             markupFromPrice, maxDiscountSet, extraChargeSet, priceRounding, roundingMinusOne,
             discountPercent, markupWholesale, markupPercent, fiscal, excise, vatApplicable, 
             vatRateCode, benefitCode, vatExemptionReason, isWeighted, uktzed_dkpp)
            VALUES 
            (:companyId, :nomenclatureCode, :barcode, :articleCode, :name, :groupName, :type, 
             :unitOfMeasurement, :description, :categoryId, :longName, :characteristics, 
             :createdAt, :updatedAt, :purchasePrice, :retailPrice, :internetPrice, :askQuantityOnSale, :wholesalePrice, 
             :minOrderQuantity, :barcodeContainsQuantity, :barcodeQuantityCoefficient, 
             :markupFromPrice, :maxDiscountSet, :extraChargeSet, :priceRounding, :roundingMinusOne,
             :discountPercent, :markupWholesale, :markupPercent, :fiscal, :excise, :vatApplicable, 
             :vatRateCode, :benefitCode, :vatExemptionReason, :isWeighted, :uktzed_dkpp)";

            $stmt = $this->conn->prepare($query);
            // Binding the parameters for camelCase column names
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->bindParam(':nomenclatureCode', $nomenclatureCode, PDO::PARAM_STR);
            $stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
            $stmt->bindParam(':articleCode', $articleCode, PDO::PARAM_STR);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':groupName', $groupName, PDO::PARAM_STR);
            $stmt->bindParam(':type', $lowercaseType, PDO::PARAM_STR);
            $stmt->bindParam(':unitOfMeasurement', $unitOfMeasurement, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':longName', $longName, PDO::PARAM_STR);
            $stmt->bindParam(':characteristics', $characteristicsJson, PDO::PARAM_STR);
            $stmt->bindParam(':createdAt', $createdAt, PDO::PARAM_STR);
            $stmt->bindParam(':updatedAt', $createdAt, PDO::PARAM_STR);
            $stmt->bindParam(':purchasePrice', $purchasePrice, PDO::PARAM_INT);
            $stmt->bindParam(':retailPrice', $retailPrice, PDO::PARAM_INT);
            $stmt->bindParam(':internetPrice', $internetPrice, PDO::PARAM_INT);
            $stmt->bindParam(':wholesalePrice', $wholesalePrice, PDO::PARAM_INT);
            $stmt->bindParam(':askQuantityOnSale', $askQuantityOnSale, PDO::PARAM_INT);
            $stmt->bindParam(':minOrderQuantity', $minOrderQuantity, PDO::PARAM_INT);
            $stmt->bindParam(':barcodeContainsQuantity', $barcodeContainsQuantity, PDO::PARAM_INT);
            $stmt->bindParam(':barcodeQuantityCoefficient', $barcodeQuantityCoefficient, PDO::PARAM_STR);
            $stmt->bindParam(':markupFromPrice', $markupFromPrice, PDO::PARAM_STR);
            $stmt->bindParam(':maxDiscountSet', $maxDiscountSet, PDO::PARAM_INT);
            $stmt->bindParam(':extraChargeSet', $extraChargeSet, PDO::PARAM_INT);
            $stmt->bindParam(':priceRounding', $priceRounding, PDO::PARAM_STR);
            $stmt->bindParam(':roundingMinusOne', $roundingMinusOne, PDO::PARAM_INT);
            $stmt->bindParam(':discountPercent', $discountPercent, PDO::PARAM_STR);
            $stmt->bindParam(':markupWholesale', $markupWholesale, PDO::PARAM_STR);
            $stmt->bindParam(':markupPercent', $markupPercent, PDO::PARAM_STR);
            $stmt->bindParam(':fiscal', $fiscal, PDO::PARAM_INT);
            $stmt->bindParam(':excise', $excise, PDO::PARAM_INT);
            $stmt->bindParam(':vatApplicable', $vatApplicable, PDO::PARAM_INT);
            $stmt->bindParam(':vatRateCode', $vatRateCode, PDO::PARAM_STR);
            $stmt->bindParam(':benefitCode', $benefitCode, PDO::PARAM_STR);
            $stmt->bindParam(':vatExemptionReason', $vatExemptionReason, PDO::PARAM_STR);
            $stmt->bindParam(':isWeighted', $isWeighted, PDO::PARAM_INT);
            $stmt->bindParam(':uktzed_dkpp', $uktzed_dkpp, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new \Exception("Не удалось сохранить номенклатуру в базе данных.");
            }

            // Получаем ID вставленной номенклатуры
            $nomenclatureId = intval($this->conn->lastInsertId());

            // Фиксируем транзакцию
            $this->conn->commit();

            $nomenclature = $this->getNomenclatureById($companyId, $nomenclatureId);

            $nomenclatureData = array_merge([
                'images' => $images // Make sure to include the images if available
            ], $nomenclature);

            // Сохраняем в Redis (по ID)
            $cacheKey = "nomenclature:id:{$nomenclatureId}";
            $this->cacheService->jsonSet($cacheKey, '.', $nomenclatureData);
            $this->cacheService->addToStream('category_creation_stream', $nomenclatureData);

            $cacheKey = "company:{$companyId}:nomenclatures";
            // Remove any existing occurrences first
            $this->cacheService->rPush($cacheKey, (string) $nomenclatureId);

            // Асинхронная загрузка изображений (если массив $images не пуст)
            if (!empty($images)) {
                foreach ($images as $index => $image) {
                    // Проверяем наличие tmp_path и прочего
                    if (!isset($image['tmp_path']) || !isset($image['name'])) {
                        error_log("Некорректный формат изображения для номенклатуры ID {$nomenclatureId}");
                        continue;
                    }

                    $imagePath = $image['tmp_path'];
                    $imageMetadata = [
                        'company_id'       => $companyId,
                        'nomenclature_id'  => $nomenclatureId,
                        'sort_order'       => $image['sort_order'] ?? $index,
                        'additional_info'  => $image['additional_info'] ?? null
                    ];

                    try {
                        // Допустим, imageService->uploadImageAsync() сохранит запись о связи в БД
                        $uploadResponse = $this->imageService->uploadImageAsync($imagePath, $imageMetadata);

                        // Фиксируем в массиве возвращаемых данных
                        $nomenclatureData['images'][] = [
                            'status'   => $uploadResponse['status'],
                            'image_id' => $uploadResponse['image_id']
                        ];
                    } catch (Exception $e) {
                        error_log("Ошибка при загрузке изображения: " . $e->getMessage());
                    }
                }

                // Обновляем кеш Redis, добавив поле 'images'
                $this->cacheService->jsonSet($cacheKey, '.images', $nomenclatureData['images']);
            }

            // Возвращаем данные номенклатуры
            return $nomenclatureData;
        } catch (\Exception $e) {
            // Откат транзакции в случае ошибки
            $this->conn->rollBack();
            error_log("Ошибка при создании номенклатуры: " . $e->getMessage());
            throw $e;
        }
    }


    public function editNomenclature(
        int     $companyId,
        int     $nomenclatureId,
        ?int    $categoryId              = null,
        ?string $articleCode             = null,
        ?string $barcode                 = null,
        ?string $name                    = null,
        ?string $groupName               = null,
        ?string $type                    = null,
        ?string $unitOfMeasurement       = null,
        ?string $description             = null,
        ?array  $specificCharacteristics = null,
        ?array  $images                  = null,
        ?int    $minOrderQuantity        = null,
        ?bool   $barcodeContainsQuantity = null,
        ?float  $barcodeQuantityCoefficient = null,
        ?float  $markupFromPrice         = null,
        ?int    $maxDiscountSet          = null,
        ?float  $extraChargeSet          = null,
        ?string $priceRounding           = null,
        ?float  $roundingMinusOne        = null,
        ?float  $discountPercent         = null,
        ?float  $markupWholesale         = null,
        ?float  $markupPercent           = null,
        ?bool   $fiscal                  = null,
        ?bool   $excise                  = null,
        ?bool   $vatApplicable           = null,
        ?string $vatRateCode             = null,
        ?string $benefitCode             = null,
        ?string $vatExemptionReason      = null,
        ?string $uktzed_dkpp             = null,
        ?int    $isWeighted              = null,
        ?float  $purchasePrice           = null,
        ?float  $retailPrice             = null,
        ?float  $internetPrice           = null,
        ?float  $wholesalePrice          = null,
        ?int    $askQuantityOnSale       = null
    ): array {
        // 1) Validate input
        if (empty($nomenclatureId)) {
            throw new \InvalidArgumentException("Nomenclature ID is required for editNomenclature.");
        }

        // 2) Fetch old record
        $oldNomenclature = $this->getNomenclatureById($companyId, $nomenclatureId);
        if (!$oldNomenclature) {
            throw new \Exception("Nomenclature not found (ID={$nomenclatureId}).");
        }

        // We'll need the old values if user didn't provide new ones:
        $oldArticle = $oldNomenclature['articleCode'];
        $oldBarcode = $oldNomenclature['barcode'];

        // 3) Check duplicates if articleCode or barcode changed
        //    (a) articleCode
        $finalArticleCode = $articleCode ?? $oldArticle; // if null, reuse old
        if ($articleCode !== null && $finalArticleCode !== $oldArticle) {
            // Check uniqueness
            $existing = $this->getNomenclatureByArcticle($companyId, $finalArticleCode);
            if ($existing) {
                throw new \Exception("A nomenclature with article '{$finalArticleCode}' already exists.");
            }
        }

        //    (b) barcode
        $finalBarcode = $barcode ?? $oldBarcode;
        if ($barcode !== null && $finalBarcode !== $oldBarcode) {
            // Check uniqueness
            $existing = $this->getNomenclatureByBarcode($companyId, $finalBarcode);
            if ($existing) {
                throw new \Exception("A nomenclature with barcode '{$finalBarcode}' already exists.");
            }
        }

        // 4) Merge characteristics if category & specific char. are given
        $finalCategoryId = $categoryId ?? $oldNomenclature['category_id'];
        $mergedCharacteristics = null;

        // We read old characteristics from DB
        $oldCharacteristics = is_string($oldNomenclature['characteristics'] ?? null)
            ? json_decode($oldNomenclature['characteristics'], true)
            : ($oldNomenclature['characteristics'] ?? []);

        if ($categoryId !== null && $specificCharacteristics !== null) {
            $categoryModel = new CategoryModel($this->conn, $this->cacheService);
            $categoryCharacteristics = $categoryModel->getCategoryCharacteristics($companyId, $finalCategoryId);

            $mergedCharacteristics = $this->mergeCharacteristics(
                $categoryCharacteristics[0]['options'] ?? [],
                $specificCharacteristics,
                $oldCharacteristics
            );
        } elseif ($oldCharacteristics) {
            // Keep old if user did not supply new ones
            $mergedCharacteristics = $oldCharacteristics;
        }

        // 5) Build final values for each column
        //    (If new param is null, fall back to old. Some booleans need careful handling.)
        $finalName              = $name        ?? $oldNomenclature['name'];
        $finalGroupName         = $groupName   ?? $oldNomenclature['group_name'];
        $finalType              = $type        ?? $oldNomenclature['type'];
        $finalUnit              = $unitOfMeasurement ?? $oldNomenclature['unit_of_measurement'];
        $finalDescription       = $description ?? $oldNomenclature['description'];
        $finalMinOrderQty       = $minOrderQuantity ?? $oldNomenclature['minOrderQuantity'];

        // For booleans we do something like:
        $finalBarcodeContainsQty = ($barcodeContainsQuantity !== null)
            ? (int)$barcodeContainsQuantity
            : (int)$oldNomenclature['barcodeContainsQuantity'];

        // Same for numeric fields – if user doesn't pass, reuse old. 
        $finalBarcodeQtyCoeff = $barcodeQuantityCoefficient ?? $oldNomenclature['barcodeQuantityCoefficient'];
        $finalMarkupFromPrice = $markupFromPrice ?? $oldNomenclature['markupFromPrice'];
        $finalMaxDiscountSet  = $maxDiscountSet  ?? $oldNomenclature['maxDiscountSet'];
        $finalExtraChargeSet  = $extraChargeSet  ?? $oldNomenclature['extraChargeSet'];
        $finalPriceRounding   = $priceRounding   ?? $oldNomenclature['priceRounding'];
        $finalRoundingMinusOne = $roundingMinusOne ?? $oldNomenclature['roundingMinusOne'];
        $finalDiscountPercent  = $discountPercent  ?? $oldNomenclature['discountPercent'];
        $finalMarkupWholesale  = $markupWholesale  ?? $oldNomenclature['markupWholesale'];
        $finalMarkupPercent    = $markupPercent    ?? $oldNomenclature['markupPercent'];
        $finalFiscal           = ($fiscal !== null) ? (int)$fiscal : (int)$oldNomenclature['fiscal'];
        $finalExcise           = ($excise !== null) ? (int)$excise : (int)$oldNomenclature['excise'];
        $finalVatApplicable    = ($vatApplicable !== null) ? (int)$vatApplicable : (int)$oldNomenclature['vatApplicable'];
        $finalVatRateCode      = $vatRateCode      ?? $oldNomenclature['vatRateCode'];
        $finalBenefitCode      = $benefitCode      ?? $oldNomenclature['benefitCode'];
        $finalVatExemptReason  = $vatExemptionReason ?? $oldNomenclature['vatExemptionReason'];
        $finalUktzedDkpp       = $uktzed_dkpp     ?? $oldNomenclature['uktzed_dkpp'];
        $finalIsWeighted       = ($isWeighted !== null) ? $isWeighted : $oldNomenclature['isWeighted'];

        $finalPurchasePrice  = $purchasePrice  ?? $oldNomenclature['purchasePrice'];
        $finalRetailPrice    = $retailPrice    ?? $oldNomenclature['retailPrice'];
        $finalInternetPrice  = $internetPrice  ?? $oldNomenclature['internetPrice'];
        $finalWholesalePrice = $wholesalePrice ?? $oldNomenclature['wholesalePrice'];
        $finalAskQuantityOnSale = $askQuantityOnSale ?? $oldNomenclature['askQuantityOnSale'];
        echo basename(__FILE__) . ' (Line ' . __LINE__ . ') - $askQuantityOnSale: ';
        var_dump($askQuantityOnSale);
        // 6) Generate longName if characteristics changed (or name/group changed)
        //    For simplicity, if *any* key part changed, regenerate:
        if ($mergedCharacteristics !== null) {
            $longName = $this->generateLongName($finalGroupName, $finalName, $mergedCharacteristics);
        } else {
            $longName = $oldNomenclature['long_name'];
        }

        // 7) Encode characteristics
        if ($mergedCharacteristics !== null) {
            $charJson = json_encode($mergedCharacteristics, JSON_UNESCAPED_UNICODE);
            if ($charJson === false) {
                throw new \Exception("json_encode error: " . json_last_error_msg());
            }
        } else {
            // Keep old
            $charJson = $oldNomenclature['characteristics'];
        }

        // 8) Build and execute UPDATE statement (single statement with placeholders)
        $updatedAt = date('Y-m-d H:i:s');

        $sql = "UPDATE Nomenclature
                SET
                  articleCode                = :articleCode,
                  barcode                    = :barcode,
                  name                       = :name,
                  group_name                 = :groupName,
                  type                       = :type,
                  unit_of_measurement        = :unitOfMeasurement,
                  description               = :description,
                  category_id               = :categoryId,
                  characteristics           = :characteristics,
                  long_name                 = :longName,
                  updated_at                = :updatedAt,
                  minOrderQuantity          = :minOrderQuantity,
                  barcodeContainsQuantity   = :barcodeContainsQuantity,
                  barcodeQuantityCoefficient = :barcodeQuantityCoefficient,
                  markupFromPrice           = :markupFromPrice,
                  maxDiscountSet            = :maxDiscountSet,
                  extraChargeSet            = :extraChargeSet,
                  priceRounding             = :priceRounding,
                  roundingMinusOne          = :roundingMinusOne,
                  discountPercent           = :discountPercent,
                  markupWholesale           = :markupWholesale,
                  markupPercent             = :markupPercent,
                  fiscal                    = :fiscal,
                  excise                    = :excise,
                  vatApplicable             = :vatApplicable,
                  vatRateCode               = :vatRateCode,
                  benefitCode               = :benefitCode,
                  vatExemptionReason        = :vatExemptionReason,
                  isWeighted                = :isWeighted,
                  uktzed_dkpp               = :uktzed_dkpp,
                  purchasePrice             = :purchasePrice,
                  retailPrice               = :retailPrice,
                  internetPrice             = :internetPrice,
                  wholesalePrice            = :wholesalePrice,
                  askQuantityOnSale         = :askQuantityOnSale
                WHERE id = :id
                  AND company_id = :companyId";

        $this->conn->beginTransaction();
        try {
            $stmt = $this->conn->prepare($sql);

            // Bind parameters in the same style as createNomenclature
            $stmt->bindParam(':articleCode',              $finalArticleCode,       PDO::PARAM_STR);
            $stmt->bindParam(':barcode',                  $finalBarcode,           PDO::PARAM_STR);
            $stmt->bindParam(':name',                     $finalName,              PDO::PARAM_STR);
            $stmt->bindParam(':groupName',                $finalGroupName,         PDO::PARAM_STR);
            $stmt->bindParam(':type',                     $finalType,              PDO::PARAM_STR);
            $stmt->bindParam(':unitOfMeasurement',        $finalUnit,              PDO::PARAM_STR);
            $stmt->bindParam(':description',              $finalDescription,       PDO::PARAM_STR);
            $stmt->bindParam(':categoryId',               $finalCategoryId,        PDO::PARAM_INT);
            $stmt->bindParam(':characteristics',          $charJson,               PDO::PARAM_STR);
            $stmt->bindParam(':longName',                 $longName,               PDO::PARAM_STR);
            $stmt->bindParam(':updatedAt',                $updatedAt,              PDO::PARAM_STR);

            $stmt->bindParam(':minOrderQuantity',         $finalMinOrderQty,       PDO::PARAM_INT);
            $stmt->bindParam(':barcodeContainsQuantity',  $finalBarcodeContainsQty, PDO::PARAM_INT);

            // Some floats or decimals can be bound as STR or left as float.
            // Typically, PDO::PARAM_STR is used for decimal in MySQL to be safe.
            $stmt->bindParam(':barcodeQuantityCoefficient', $finalBarcodeQtyCoeff, PDO::PARAM_STR);
            $stmt->bindParam(':markupFromPrice',          $finalMarkupFromPrice,   PDO::PARAM_STR);
            $stmt->bindParam(':maxDiscountSet',           $finalMaxDiscountSet,    PDO::PARAM_INT);
            $stmt->bindParam(':extraChargeSet',           $finalExtraChargeSet,    PDO::PARAM_STR);
            $stmt->bindParam(':priceRounding',            $finalPriceRounding,     PDO::PARAM_STR);
            $stmt->bindParam(':roundingMinusOne',         $finalRoundingMinusOne,  PDO::PARAM_STR);
            $stmt->bindParam(':discountPercent',          $finalDiscountPercent,   PDO::PARAM_STR);
            $stmt->bindParam(':markupWholesale',          $finalMarkupWholesale,   PDO::PARAM_STR);
            $stmt->bindParam(':markupPercent',            $finalMarkupPercent,     PDO::PARAM_STR);

            $stmt->bindParam(':fiscal',                   $finalFiscal,            PDO::PARAM_INT);
            $stmt->bindParam(':excise',                   $finalExcise,            PDO::PARAM_INT);
            $stmt->bindParam(':vatApplicable',            $finalVatApplicable,     PDO::PARAM_INT);
            $stmt->bindParam(':vatRateCode',              $finalVatRateCode,       PDO::PARAM_STR);
            $stmt->bindParam(':benefitCode',              $finalBenefitCode,       PDO::PARAM_STR);
            $stmt->bindParam(':vatExemptionReason',       $finalVatExemptReason,   PDO::PARAM_STR);
            $stmt->bindParam(':isWeighted',               $finalIsWeighted,        PDO::PARAM_INT);
            // If uktzеd_dkpp is not necessarily numeric, keep as STR
            $stmt->bindParam(':uktzed_dkpp',              $finalUktzedDkpp,        PDO::PARAM_STR);

            // Prices
            $stmt->bindParam(':purchasePrice',            $finalPurchasePrice,     PDO::PARAM_STR);
            $stmt->bindParam(':retailPrice',              $finalRetailPrice,       PDO::PARAM_STR);
            $stmt->bindParam(':internetPrice',            $finalInternetPrice,     PDO::PARAM_STR);
            $stmt->bindParam(':wholesalePrice',           $finalWholesalePrice,    PDO::PARAM_STR);
            $stmt->bindParam(':askQuantityOnSale',        $finalAskQuantityOnSale, PDO::PARAM_INT);

            // Where
            $stmt->bindParam(':id',                       $nomenclatureId,         PDO::PARAM_INT);
            $stmt->bindParam(':companyId',                $companyId,              PDO::PARAM_INT);

            if ($stmt->execute()) {
                $cacheKey = "nomenclature:id:{$nomenclatureId}";
                $this->cacheService->del($cacheKey);

                // 9) Reload from DB to ensure we have the newest data
                $updatedNomenclature = $this->getNomenclatureById($companyId, $nomenclatureId);
                if (!$updatedNomenclature) {
                    throw new \Exception("Nomenclature disappeared after UPDATE (ID={$nomenclatureId}).");
                }
                // 10) Update Redis cache
                $resRedis = $this->cacheService->jsonSet($cacheKey, '.', $updatedNomenclature);
                if (!$resRedis) {
                    throw new \Exception("Ошибка при обновлении кеша Redis.");
                }

                // If articleCode changed, update relevant cache keys
                if ($finalArticleCode !== $oldArticle) {
                    $oldKey = "nomenclature:articleCode:{$oldArticle}";
                    $this->cacheService->del($oldKey);

                    $newKey = "nomenclature:articleCode:{$finalArticleCode}";
                    $this->cacheService->set($newKey, $nomenclatureId);
                }

                // If barcode changed, update relevant cache keys
                if ($finalBarcode !== $oldBarcode) {
                    $oldBcKey = "nomenclature:barcode:{$oldBarcode}";
                    $this->cacheService->del($oldBcKey);

                    $newBcKey = "nomenclature:barcode:{$finalBarcode}";
                    $this->cacheService->set($newBcKey, $nomenclatureId);
                }

                // 11) Handle images (similar logic to createNomenclature)
                // Асинхронная загрузка изображений (если массив $images не пуст)
                if (!empty($images)) {
                    foreach ($images as $index => $image) {
                        // Если нет tmp_path то изображение уже загружено и нужно обновить его данние
                        if (!isset($image['tmp_path'])) {
                            // Update existing image metadata in MySQL
                            try {
                                $this->imageService->updateImageMetadata(
                                    intval($nomenclatureId),
                                    intval($image['image_id']) ?? null, // You might need to pass image_id from frontend
                                    intval($image['sort_order']),
                                    $image['additional_info'] ?? null
                                );
                            } catch (Exception $e) {
                                error_log("Ошибка при обновлении метаданных изображения: " . $e->getMessage());
                            }

                            // Update Redis cache
                            $nomenclatureData['images'][] = [
                                'status'   => 'Updated',
                                'image_id' => $image['image_id'] ?? null
                            ];
                            continue; // Skip upload logic for existing images
                        }


                        $imagePath = $image['tmp_path'];
                        $imageMetadata = [
                            'company_id'       => $companyId,
                            'nomenclature_id'  => $nomenclatureId,
                            'sort_order'       => $image['sort_order'] ?? $index,
                            'additional_info'  => $image['additional_info'] ?? null
                        ];

                        try {
                            // Допустим, imageService->uploadImageAsync() сохранит запись о связи в БД
                            $uploadResponse = $this->imageService->uploadImageAsync($imagePath, $imageMetadata);

                            // Фиксируем в массиве возвращаемых данных
                            $nomenclatureData['images'][] = [
                                'status'   => $uploadResponse['status'],
                                'image_id' => $uploadResponse['image_id']
                            ];
                        } catch (Exception $e) {
                            error_log("Ошибка при загрузке изображения: " . $e->getMessage());
                        }
                    }

                    // Обновляем кеш Redis, добавив поле 'images'
                    $this->cacheService->jsonSet($cacheKey, '.images', $nomenclatureData['images']);
                }

                // 12) Return the final updated nomenclature
                return $updatedNomenclature;
            } else {
                throw new \Exception("Failed to update nomenclature in the database.");
            }
        } catch (\Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }


    /**
     * Удаление номенклатуры.
     *
     * @param int $companyId
     * @param int $nomenclatureId
     * @return array
     * @throws Exception
     */
    public function deleteNomenclature(int $companyId, int $nomenclatureId): array
    {
        if (empty($nomenclatureId)) {
            throw new \InvalidArgumentException("ID номенклатуры обязательно.");
        }

        // Проверка существования номенклатуры
        $nomenclature = $this->getNomenclatureById($companyId, $nomenclatureId);
        if (!$nomenclature) {
            throw new Exception("Номенклатура не найдена.");
        }

        // Проверка наличия карточек товаров, связанных с номенклатурой
        $query = "SELECT COUNT(*) as count FROM ProductCards WHERE nomenclature_id = :nomenclature_id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nomenclature_id', $nomenclatureId, PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            throw new Exception("Невозможно удалить номенклатуру, так как с ней связаны карточки товаров.");
        }

        // Удаляем номенклатуру из MySQL
        $query = "DELETE FROM Nomenclature WHERE id = :nomenclature_id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nomenclature_id', $nomenclatureId, PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Удаляем из Redis только под ключом по ID
            $cacheKey = "nomenclature:id:{$nomenclatureId}";
            $this->cacheService->del($cacheKey);

            // Удаляем индексы по коду и артикулу
            $codeCacheKey = "nomenclature:code:{$nomenclature['nomenclature_code']}";
            $this->cacheService->del($codeCacheKey);

            $articleCodeCacheKey = "nomenclature:articleCode:{$nomenclature['articleCode']}";
            $this->cacheService->del($articleCodeCacheKey);

            // Удаляем ID номенклатуры из списка номенклатур компании
            $nomenclatureListKey = "company:{$companyId}:nomenclatures";
            $this->cacheService->lRem($nomenclatureListKey, (string)$nomenclatureId, 0);

            return ["message" => "Номенклатура удалена успешно."];
        }

        throw new Exception("Не удалось удалить номенклатуру.");
    }

    /**
     * Удаление номенклатуры.
     *
     * @param int $companyId
     * @param int $imageId
     * @param int $nomenclatureId
     * @return array
     * @throws Exception
     */

    public function deleteNomenclatureImage(int $companyId, int $imageId, int $nomenclatureId): void
    {
        // Удаление изображения через сервис
        $this->imageService->deleteImage($imageId);

        // Optionally, you can refresh the nomenclature data if needed
        // If the nomenclature data changes significantly (i.e., images removed), you can fetch fresh data from the DB
        $nomenclature = $this->getNomenclatureById($companyId, $nomenclatureId);
        $this->cacheService->jsonSet("nomenclature:id:{$nomenclatureId}", '.', $nomenclature);
    }

    /**
     * Получение номенклатуры по ID.
     *
     * @param int $companyId
     * @param int $nomenclatureId
     * @return array|null
     */
    public function getNomenclatureById(int $companyId, int $nomenclatureId): ?array
    {
        $cacheKey = "nomenclature:id:{$nomenclatureId}";

        // Попытка получить данные из Redis
        $cachedNomenclature = $this->cacheService->jsonGet($cacheKey, '.');
        if ($cachedNomenclature) {
            $decoded = json_decode($cachedNomenclature, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            } else {
                // Логирование ошибки декодирования JSON
                error_log("Ошибка декодирования JSON для ключа {$cacheKey}: " . json_last_error_msg());
            }
        }

        // Если данных нет в Redis, обращаемся к MySQL
        $query = "SELECT * FROM Nomenclature WHERE id = :nomenclature_id AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nomenclature_id', $nomenclatureId, PDO::PARAM_INT);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $nomenclature = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($nomenclature) {
            // Преобразование характеристик из JSON в массив
            $nomenclature['characteristics'] = json_decode($nomenclature['characteristics'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $nomenclature['characteristics'] = [];
                error_log("Ошибка декодирования характеристик для номенклатуры ID {$nomenclatureId}: " . json_last_error_msg());
            }

            // Генерация long_name
            $nomenclature['long_name'] = $this->generateLongName($nomenclature['group_name'], $nomenclature['name'], $nomenclature['characteristics']);

            // Сохранение в Redis только под ключом по ID
            $this->cacheService->jsonSet($cacheKey, '.', $nomenclature);

            return $nomenclature;
        }

        return null;
    }

    /**
     * Получение номенклатуры по коду.
     *
     * @param int $companyId
     * @param string $nomenclatureCode
     * @return array|null
     */
    public function getNomenclatureByCode(int $companyId, string $nomenclatureCode): ?array
    {
        $cacheKey = "nomenclature:code:{$nomenclatureCode}";

        // Попытка получить ID номенклатуры по коду из Redis
        $cachedId = $this->cacheService->get($cacheKey);
        if ($cachedId) {
            return $this->getNomenclatureById($companyId, (int)$cachedId);
        }

        // Если данных нет в Redis, обращаемся к MySQL
        $query = "SELECT id FROM Nomenclature WHERE nomenclature_code = :nomenclature_code AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nomenclature_code', $nomenclatureCode, PDO::PARAM_STR);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && isset($result['id'])) {
            // Кешируем ID номенклатуры по коду
            $this->cacheService->set($cacheKey, (string)$result['id']);
            return $this->getNomenclatureById($companyId, (int)$result['id']);
        }

        return null;
    }

    /**
     * Получение номенклатуры по articleCode.
     *
     * @param int $companyId
     * @param string $articleCode
     * @return array|null
     */
    public function getNomenclatureByArcticle(int $companyId, string $articleCode): ?array
    {
        $cacheKey = "nomenclature:articleCode:{$articleCode}";

        // Попытка получить ID номенклатуры по articleCode из Redis
        $cachedId = $this->cacheService->get($cacheKey);
        if ($cachedId) {
            return $this->getNomenclatureById($companyId, (int)$cachedId);
        }

        // Если данных нет в Redis, обращаемся к MySQL
        $query = "SELECT id FROM Nomenclature WHERE articleCode = :articleCode AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':articleCode', $articleCode, PDO::PARAM_STR);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && isset($result['id'])) {
            // Кешируем ID номенклатуры по articleCode
            $this->cacheService->set($cacheKey, (string)$result['id']);
            return $this->getNomenclatureById($companyId, (int)$result['id']);
        }

        return null;
    }
    /**
     * Получение номенклатуры по barcode.
     *
     * @param int $companyId
     * @param string $barcode
     * @return array|null
     */
    public function getNomenclatureBybarcode(int $companyId, string $barcode): ?array
    {
        $cacheKey = "nomenclature:barcode:{$barcode}";

        // Попытка получить ID номенклатуры по barcode из Redis
        $cachedId = $this->cacheService->get($cacheKey);
        if ($cachedId) {
            return $this->getNomenclatureById($companyId, (int)$cachedId);
        }

        // Если данных нет в Redis, обращаемся к MySQL
        $query = "SELECT id FROM Nomenclature WHERE barcode = :barcode AND company_id = :company_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
        $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && isset($result['id'])) {
            // Кешируем ID номенклатуры по barcode
            $this->cacheService->set($cacheKey, (string)$result['id']);
            return $this->getNomenclatureById($companyId, (int)$result['id']);
        }

        return null;
    }
    /**
     * Получение списка номенклатур компании.
     *
     * @param int $companyId
     * @return array
     */

    public function getNomenclaturesByCompany(int $companyId): array
    {
        $cacheKey = "company:{$companyId}:nomenclatures";

        // Попытка получить список номенклатур из Redis
        $nomenclatureIds = $this->cacheService->lRange($cacheKey, 0, -1);
        if (empty($nomenclatureIds)) {
            // Если данных нет в Redis, обращаемся к MySQL
            $query = "SELECT id FROM Nomenclature WHERE company_id = :company_id ORDER BY name";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':company_id', $companyId, PDO::PARAM_INT);
            $stmt->execute();

            $nomenclatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Кеширование списка ID номенклатур компании
            foreach ($nomenclatures as $nomenclature) {
                $this->cacheService->rPush($cacheKey, (string)$nomenclature['id']);
            }

            $nomenclatureIds = array_column($nomenclatures, 'id');
        }

        // Проверяем, есть ли хотя бы один ID
        if (empty($nomenclatureIds)) {
            return []; // Нет номенклатур для данной компании
        }

        // Получаем данные номенклатур по ID из Redis
        $nomenclatures = [];
        foreach ($nomenclatureIds as $id) {
            $nomenclature = $this->getNomenclatureById($companyId, (int)$id);
            if ($nomenclature) {
                $nomenclatures[] = $nomenclature;
            } else {
                // Логирование отсутствия данных
                error_log("Номенклатура с ID {$id} не найдена.");
            }
        }

        return $nomenclatures;
    }

    /**
     * Объединение характеристик категории с специфичными характеристиками.
     *
     * @param array $categoryCharacteristics
     * @param array $specificCharacteristics
     * @param array $existingCharacteristics
     * @return array
     */
    private function mergeCharacteristics(array $categoryCharacteristics, array $specificCharacteristics, array $existingCharacteristics): array
    {
        $mergedCharacteristics = [];
        $existingCharacteristicNames = [];

        // Добавление характеристик из категории

        foreach ($categoryCharacteristics as $char) {
            $charName = $char['characteristic_name'];
            $charValue = $specificCharacteristics[$charName] ?? ($this->findCharacteristicValue($existingCharacteristics, $charName) ?? null);

            // Убедимся, что значение является скалярным, а не массивом
            if (is_array($charValue)) {
                // Если значение массив, берем первое значение или объединяем его в строку
                // В зависимости от логики вашей системы
                $charValue = implode(', ', $charValue);
            }

            $mergedCharacteristics[] = [
                'id' => $char['id'],
                'name' => $charName,
                'type' => $char['characteristic_type'],
                'value' => $charValue,
                'order' => $char['id'] // Можно использовать другой способ сортировки
            ];
            $existingCharacteristicNames[] = $charName;
        }

        // Добавление новых специфичных характеристик, не связанных с категорией
        foreach ($specificCharacteristics as $name => $value) {
            if (!in_array($name, $existingCharacteristicNames)) {
                // Убедимся, что значение является скалярным
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $mergedCharacteristics[] = [
                    'id' => $this->generateCharacteristicId(),
                    'name' => $name,
                    'type' => 'text', // По умолчанию или определить тип по логике
                    'value' => $value,
                    'order' => count($mergedCharacteristics) + 1
                ];
            }
        }
        return $mergedCharacteristics;
    }


    /**
     * Генерация длинного названия на основе характеристик.
     *
     * @param string $category
     * @param string $productName
     * @param array $characteristics
     * @return string
     */
    /**
     * Генерация длинного названия на основе характеристик.
     *
     * @param string $category
     * @param string $productName
     * @param array $characteristics
     * @return string
     */
    private function generateLongName(string $category, string $productName, array $characteristics): string
    {
        // Фильтруем элементы, чтобы учитывать только массивы с ключами 'order' и 'value'
        $characteristics = array_filter($characteristics, function ($char) {
            return is_array($char) && isset($char['order']) && isset($char['value']);
        });

        // Сортировка характеристик по ключу 'order'
        usort($characteristics, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        // Создаем части длинного названия
        $longNameParts = array_map(function ($char) {
            return (string)$char['value']; // Преобразуем значение в строку
        }, $characteristics);

        // Добавляем категорию и название продукта в начало списка
        array_unshift($longNameParts, $category, $productName);

        // Объединяем части в строку, исключая пустые значения
        return implode(' ', array_filter($longNameParts));
    }



    /**
     * Вспомогательный метод для генерации уникального ID характеристики.
     *
     * @return int
     */
    protected function generateCharacteristicId(): int
    {
        // В данной реализации используем AUTO_INCREMENT в базе данных
        return 0;
    }

    /**
     * Вспомогательный метод для поиска значения характеристики по имени.
     *
     * @param array $characteristics
     * @param string $name
     * @return mixed|null
     */
    protected function findCharacteristicValue(array $characteristics, string $name)
    {
        foreach ($characteristics as $char) {
            if ($char['name'] === $name) {
                return $char['value'] ?? null;
            }
        }
        return null;
    }
}
