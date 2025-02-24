<?php

namespace Service\FileService;

use PDO;
use Exception;
use phpseclib3\Net\SFTP;
use Service\CacheService;

class ImageService
{
    protected PDO $conn;
    protected FileServerManager $fileServerManager;
    protected CacheService $cacheService;

    public function __construct(PDO $conn, FileServerManager $fileServerManager, CacheService $cacheService)
    {
        if (!$conn) {
            throw new Exception("PDO instance is required for ImageService.");
        }
        $this->conn = $conn;
        $this->fileServerManager = $fileServerManager;
        $this->cacheService = $cacheService;
    }

    /**
     * Загружает локальный файл (imagePath) на один из доступных серверов (SFTP),
     * формируя полный SSH-путь (remotePath), который сохраняется в БД (поле `path`).
     */
    public function uploadImageAsync(string $imagePath, array $metadata): array
    {
        \Resque::setBackend('redis-node1:6379');

        error_log("Starting uploadImageAsync");
        error_log("Received imagePath: {$imagePath}");
        error_log("Received metadata: " . json_encode($metadata));

        // 1) Проверка локального файла
        if (!file_exists($imagePath)) {
            error_log("[ERROR] Local file does not exist: {$imagePath}");
            throw new Exception("Не удалось найти локальный файл: {$imagePath}");
        }
        if (!is_readable($imagePath)) {
            error_log("[ERROR] Local file is not readable: {$imagePath}");
            throw new Exception("Локальный файл недоступен для чтения: {$imagePath}");
        }
        error_log("Local file exists and is readable: {$imagePath}");

        // 2) Определяем сервер
        $server = $this->fileServerManager->assignCompanyToServer($metadata['company_id']);
        if (!$server) {
            error_log("[ERROR] No available file servers found.");
            throw new Exception("Нет доступных файловых серверов для назначения.");
        }
        error_log("Assigned server: " . json_encode($server));

        // 3) Создаем директорию для компании, если её нет
        $companyFolder = rtrim($server['base_path'], '/') . '/' . (int)$metadata['company_id'];
        $sftp = new SFTP($server['host'], $server['port']);

        if (!$sftp->login($server['ssh_username'], $server['ssh_password'])) {
            error_log("[ERROR] SFTP login failed for server: {$server['host']}");
            throw new Exception("Не удалось подключиться к файловому серверу по SFTP.");
        }
        error_log("SFTP login successful for server: {$server['host']}");

        if (!$sftp->is_dir($companyFolder)) {
            $sftp->mkdir($companyFolder);
        }

        // 4) Генерируем уникальное имя и полный SSH-путь
        $remoteFilename = uniqid('img_') . '_' . basename($imagePath);
        $remotePath = $companyFolder . '/' . $remoteFilename;
        error_log("Generated remote path: {$remotePath}");

        // 5) Загружаем файл
        $stream = fopen($imagePath, 'r');
        if (!$stream) {
            error_log("[ERROR] Failed to open local file: {$imagePath}");
            throw new Exception("Не удалось открыть локальный файл: {$imagePath}");
        }
        $uploadSuccess = $sftp->put($remotePath, $stream, SFTP::SOURCE_LOCAL_FILE);
        fclose($stream);

        if (!$uploadSuccess) {
            error_log("[ERROR] Failed to upload file to server: {$remotePath}");
            throw new Exception("Не удалось загрузить файл на сервер: {$remotePath}");
        }
        error_log("File uploaded successfully to: {$remotePath}");

        // 6) Сохраняем запись в БД
        try {
            $imageId = $this->saveImage([
                'server_id'       => $server['id'],
                'path'            => $remotePath,
                'sort_order'      => $metadata['sort_order'] ?? 0,
                'nomenclature_id' => $metadata['nomenclature_id'] ?? null,
                'additional_info' => $metadata['additional_info'] ?? null
            ], $metadata['company_id']);

            error_log("Image saved in database with ID: {$imageId}");
        } catch (Exception $e) {
            error_log("[ERROR] Failed to save image metadata: " . $e->getMessage());
            throw $e;
        }

        error_log("uploadImageAsync completed successfully for image ID: {$imageId}");
        return [
            'status'   => 'Processing',
            'image_id' => $imageId
        ];
    }


    /**
     * Запись в таблицу NomenclatureImages. Поле `path` — SSH-путь.
     */
    public function saveImage(array $metadata, int $companyId): int
    {
        error_log("Saving image metadata: " . json_encode($metadata));

        $stmt = $this->conn->prepare("
            INSERT INTO NomenclatureImages 
              (nomenclature_id, path, sort_order, additional_info, server_id, company_id)
            VALUES 
              (:nomenclature_id, :path, :sort_order, :additional_info, :server_id, :company_id)
        ");

        $success = $stmt->execute([
            ':nomenclature_id' => $metadata['nomenclature_id'],
            ':path'            => $metadata['path'],
            ':sort_order'      => $metadata['sort_order'],
            ':additional_info' => $metadata['additional_info'],
            ':server_id'       => $metadata['server_id'],
            ':company_id'      => $companyId
        ]);

        if (!$success) {
            error_log("Failed to execute query for saving image.");
            error_log("Error info: " . json_encode($stmt->errorInfo()));
            throw new Exception("Не удалось сохранить данные изображения в базе.");
        }

        $imageId = (int)$this->conn->lastInsertId();
        error_log("Image metadata saved with ID: {$imageId}");

        return $imageId;
    }

    /**
     * Удаление изображения: читает `path` -> удаляет файл на SFTP -> удаляет запись.
     */
    public function deleteImage(int $imageId): void
    {
        $stmt = $this->conn->prepare("SELECT * FROM NomenclatureImages WHERE id = :id");
        $stmt->execute([':id' => $imageId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $remotePath = $result['path'] ?? null;
            $serverId   = $result['server_id'] ?? null;

            if ($remotePath && $serverId) {
                $server = $this->getServerById($serverId);
                if ($server) {
                    $sftp = new SFTP($server['host'], $server['port']);
                    if (!$sftp->login($server['ssh_username'], $server['ssh_password'])) {
                        throw new Exception("Не удалось подключиться к файловому серверу по SFTP для удаления файла.");
                    }

                    if (!$sftp->delete($remotePath)) {
                        throw new Exception("Не удалось удалить файл на сервере: {$remotePath}");
                    }
                }
            }

            $stmt = $this->conn->prepare("DELETE FROM NomenclatureImages WHERE id = :id");
            $stmt->execute([':id' => $imageId]);
            // Assuming you know the nomenclatureId related to this image
            $nomenclatureId = $result['nomenclature_id']; // Adjust this based on your schema

            // Remove the image data from Redis
            $cacheKey = "nomenclature:id:{$nomenclatureId}";
            $cachedData = $this->cacheService->jsonGet($cacheKey);

            // Check if the cached data is in the expected format (array)
            if ($cachedData && is_array($cachedData['images'])) {
                // Remove image from the cached array
                $updatedImages = array_filter($cachedData['images'], function ($image) use ($imageId) {
                    return $image['image_id'] != $imageId;
                });
                $updatedImages = array_values($updatedImages); // Reindex the array

                // Update Redis with the new list of images
                $this->cacheService->jsonSet($cacheKey, '.images', $updatedImages);
            } else {
                // Handle error if the cached data is not in the expected format
                throw new Exception("Ошибка: данные о изображениях в кеше Redis имеют неверный формат.");
            }
        }
    }

    // Обновляем метаданние изображения номенклатури
    public function updateImageMetadata(int $nomenclatureId, ?int $imageId, int $sortOrder, ?string $additionalInfo)
    {
        if (!$imageId) {
            error_log("[ERROR] Image ID is missing for update.");
            return;
        }

        $stmt = $this->conn->prepare("
            UPDATE NomenclatureImages 
            SET sort_order = :sort_order, additional_info = :additional_info
            WHERE nomenclature_id = :nomenclature_id AND id = :image_id
        ");

        $success = $stmt->execute([
            ':sort_order'      => $sortOrder,
            ':additional_info' => $additionalInfo,
            ':nomenclature_id' => $nomenclatureId,
            ':image_id'        => $imageId
        ]);

        if (!$success) {
            error_log("Failed to update image metadata.");
            throw new Exception("Не удалось обновить данные изображения в базе.");
        }

        error_log("Image metadata updated for ID: {$imageId}");
    }

    /**
     * Получение сервера (host, base_path, user, pass) по ID.
     */
    public function getServerById(int $serverId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT * 
            FROM file_servers 
            WHERE id = :id 
              AND status = 'active'
        ");
        $stmt->execute([':id' => $serverId]);
        $server = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($server && !empty($server['ssh_password'])) {
            return $server;
        }
        return null;
    }

    /**
     * Обновление пути к миниатюре (thumbnail_path).
     */
    public function updateThumbnailPath(int $imageId, string $thumbnailPath): void
    {
        $stmt = $this->conn->prepare("
            UPDATE NomenclatureImages 
            SET thumbnail_path = :thumbnail_path 
            WHERE id = :id
        ");
        $success = $stmt->execute([
            ':thumbnail_path' => $thumbnailPath,
            ':id'             => $imageId
        ]);

        if (!$success) {
            error_log("ImageService: Не удалось обновить путь к миниатюре для изображения ID {$imageId}.");
            throw new Exception("Не удалось обновить путь к миниатюре изображения в базе данных.");
        }

        // Обновляем кеш (если нужно)
        $cacheKey = "nomenclature_image:id:{$imageId}";
        $image = $this->getImagesByNomenclatureId($imageId);
        if ($image) {
            $this->cacheService->jsonSet($cacheKey, '.', $image);
        }
    }

    /**
     * Возвращаем список изображений: поле `path` (SSH),
     * и добавляем поле `docker_path` с заменой любого "base_path" на "/var/www/erp/upload".
     */
    public function getImagesByNomenclatureId(int $nomenclatureId): array
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM NomenclatureImages
            WHERE nomenclature_id = :id
            ORDER BY sort_order ASC
        ");
        $stmt->execute([':id' => $nomenclatureId]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$images) {
            return [];
        }

        // Подставим docker_path для каждого изображения
        foreach ($images as &$img) {
            $img['docker_path'] = $this->convertSSHToDocker($img['path']);
        }

        return $images;
    }

    /**
     * Находим запись по полю `path`.
     */
    public function getImageByPath(string $path): ?array
    {
        $sql = "
            SELECT *
            FROM NomenclatureImages
            WHERE path = :path
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':path', $path, \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        // При желании тоже можно формировать docker_path
        if ($row) {
            $row['docker_path'] = $this->convertSSHToDocker($row['path']);
        }
        return $row ?: null;
    }

    /**
     * Главное место: заменяем любой SSH-путь на "/var/www/erp/upload" + остаток.
     * 
     * ВАЖНО: т.к. у вас "распределённая" инфраструктура, 
     * путь может быть "/home/sashatankist/erp_2_0/...", "/mnt/other/...", "/srv/..." и т.д.
     * Поэтому нужно узнать все base_path.
     */
    private function convertSSHToDocker(string $sshPath): string
    {
        // 1) Список ВСЕХ серверов, чтобы проверить, на что заменить
        $allServers = $this->getAllServersBasePaths();

        // 2) Docker-база, куда заменить:
        $dockerBase = '/var/www/erp/upload';

        // 3) Ищем, начинается ли $sshPath с какого-нибудь base_path
        foreach ($allServers as $base) {
            $base = rtrim($base, '/');
            if (strpos($sshPath, $base) === 0) {
                // берем остаток пути после $base
                $relative = substr($sshPath, strlen($base));
                // и подставляем к dockerBase
                return rtrim($dockerBase, '/') . $relative;
            }
        }

        // Если не нашли совпадение, можно вернуть как есть 
        // или вернуть "/var/www/erp/upload" + basename($sshPath) — зависит от логики
        return $sshPath;
    }

    /**
     * Получаем ВСЕ base_path'ы из таблицы file_servers (status=active),
     * чтобы знать, какие пути могут быть в SSH-полном пути.
     */
    private function getAllServersBasePaths(): array
    {
        $sql = "SELECT base_path FROM file_servers WHERE status = 'active'";
        $stmt = $this->conn->query($sql);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (!$rows) {
            return [];
        }
        // Вернём массив строк (base_path)
        return array_column($rows, 'base_path');
    }
}
