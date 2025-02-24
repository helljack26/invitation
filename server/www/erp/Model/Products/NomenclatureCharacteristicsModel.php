<?php
namespace Model;

use PDO;
use Service\CacheService;
use Exception;

/**
 * Класс NomenclatureCharacteristicsModel отвечает за управление характеристиками номенклатуры.
 */
class NomenclatureCharacteristicsModel
{
    protected $conn;
    protected $cacheService;

    public function __construct(PDO $conn, CacheService $cacheService)
    {
        $this->conn = $conn;
        $this->cacheService = $cacheService;

        if (!$cacheService) {
            throw new Exception("CacheService instance is not provided in NomenclatureCharacteristicsModel.");
        }
    }

    // Методы для добавления, редактирования, удаления и получения характеристик номенклатуры
    // Уже частично реализованы в NomenclatureModel
}
