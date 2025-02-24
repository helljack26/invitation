<?php

namespace Service\FileService;

use Exception;
use phpseclib3\Net\SFTP;
use Service\FileService\ImageService;

class ImageProcessor
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Метод, вызываемый из Resque для обработки изображения.
     *
     * @param array $args Аргументы задачи, ожидаются 'image_id'
     */
    public function perform(array $args): void
    {
        $imageId = $args['image_id'] ?? null;

        if (!$imageId) {
            error_log("ImageProcessor: Не передан image_id.");
            return;
        }

        try {
            // Получаем метаданные изображения из базы данных
            $image = $this->imageService->getImageById($imageId);
            if (!$image) {
                throw new Exception("ImageProcessor: Изображение с ID {$imageId} не найдено.");
            }

            $remotePath = $image['path'];
            $serverId = $image['server_id'];

            // Получаем информацию о сервере
            $server = $this->imageService->getServerById($serverId);
            if (!$server) {
                throw new Exception("ImageProcessor: Сервер с ID {$serverId} не найден или не активен.");
            }

            // Инициализируем соединение SFTP
            $sftp = new SFTP($server['host'], $server['port']);
            if (!$sftp->login($server['ssh_username'], $server['ssh_password'])) {
                throw new Exception("ImageProcessor: Не удалось подключиться к SFTP-серверу {$server['host']}.");
            }
            error_log("ImageProcessor: Успешное подключение к SFTP-серверу {$server['host']}.");

            // Скачиваем изображение на локальный временный путь для обработки
            $localTempPath = sys_get_temp_dir() . '/' . basename($remotePath);
            if (!$sftp->get($remotePath, $localTempPath)) {
                throw new Exception("ImageProcessor: Не удалось скачать файл с SFTP-сервера: {$remotePath}.");
            }
            error_log("ImageProcessor: Файл скачан на локальный путь: {$localTempPath}.");

            // Создаём миниатюру (thumbnail) изображения
            $thumbnailPath = sys_get_temp_dir() . '/thumb_' . basename($remotePath);
            if (!$this->createThumbnail($localTempPath, $thumbnailPath, 200, 200)) {
                throw new Exception("ImageProcessor: Не удалось создать миниатюру для файла: {$localTempPath}.");
            }
            error_log("ImageProcessor: Миниатюра создана по пути: {$thumbnailPath}.");

            // Формируем удалённый путь для миниатюры
            $remoteThumbnailPath = $this->generateThumbnailPath($remotePath);
            error_log("ImageProcessor: Формируем удалённый путь для миниатюры: {$remoteThumbnailPath}.");

            // Загружаем миниатюру обратно на SFTP-сервер
            if (!$sftp->put($remoteThumbnailPath, $thumbnailPath, SFTP::SOURCE_LOCAL_FILE)) {
                throw new Exception("ImageProcessor: Не удалось загрузить миниатюру на SFTP-сервер: {$remoteThumbnailPath}.");
            }
            error_log("ImageProcessor: Миниатюра успешно загружена на SFTP-сервер: {$remoteThumbnailPath}.");

            // Обновляем базу данных, добавляя путь к миниатюре (если необходимо)
            $this->imageService->updateThumbnailPath($imageId, $remoteThumbnailPath);
            error_log("ImageProcessor: Путь к миниатюре обновлён в базе данных для изображения ID: {$imageId}.");

            // Очистка локальных временных файлов
            if (!unlink($localTempPath)) {
                error_log("ImageProcessor: Warning: Не удалось удалить локальный файл: {$localTempPath}.");
            } else {
                error_log("ImageProcessor: Локальный файл удалён: {$localTempPath}.");
            }

            if (!unlink($thumbnailPath)) {
                error_log("ImageProcessor: Warning: Не удалось удалить локальный файл миниатюры: {$thumbnailPath}.");
            } else {
                error_log("ImageProcessor: Локальный файл миниатюры удалён: {$thumbnailPath}.");
            }

            error_log("ImageProcessor: Обработка изображения ID {$imageId} завершена успешно.");

        } catch (Exception $e) {
            error_log("ImageProcessor: Ошибка при обработке изображения ID {$imageId}: " . $e->getMessage());
            // Дополнительно можно уведомить администратора или предпринять другие действия
        }
    }

    /**
     * Создаёт миниатюру изображения.
     *
     * @param string $sourcePath Путь к исходному изображению
     * @param string $thumbPath Путь для сохранения миниатюры
     * @param int $width Ширина миниатюры
     * @param int $height Высота миниатюры
     * @return bool Успешность операции
     */
    protected function createThumbnail(string $sourcePath, string $thumbPath, int $width, int $height): bool
    {
        // Определяем тип изображения
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            error_log("ImageProcessor: Невозможно получить информацию об изображении: {$sourcePath}.");
            return false;
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mime = $imageInfo['mime'];

        // Создаём исходное изображение на основе типа
        switch ($mime) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            default:
                error_log("ImageProcessor: Не поддерживаемый тип изображения: {$mime}.");
                return false;
        }

        if (!$sourceImage) {
            error_log("ImageProcessor: Не удалось создать исходное изображение из файла: {$sourcePath}.");
            return false;
        }

        // Вычисляем размеры миниатюры с сохранением пропорций
        $aspectRatio = $sourceWidth / $sourceHeight;
        if ($width / $height > $aspectRatio) {
            $newHeight = $height;
            $newWidth = (int)($height * $aspectRatio);
        } else {
            $newWidth = $width;
            $newHeight = (int)($width / $aspectRatio);
        }

        // Создаём пустое изображение для миниатюры
        $thumbImage = imagecreatetruecolor($newWidth, $newHeight);
        if (!$thumbImage) {
            error_log("ImageProcessor: Не удалось создать миниатюрное изображение.");
            imagedestroy($sourceImage);
            return false;
        }

        // Сохраняем прозрачность для PNG и GIF
        if (in_array($mime, ['image/png', 'image/gif'])) {
            imagecolortransparent($thumbImage, imagecolorallocatealpha($thumbImage, 0, 0, 0, 127));
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
        }

        // Копируем и изменяем размер исходного изображения в миниатюру
        if (!imagecopyresampled(
            $thumbImage,
            $sourceImage,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            $sourceWidth,
            $sourceHeight
        )) {
            error_log("ImageProcessor: Не удалось скопировать и изменить размер изображения для миниатюры.");
            imagedestroy($sourceImage);
            imagedestroy($thumbImage);
            return false;
        }

        // Сохраняем миниатюру на диск
        $saveSuccess = false;
        switch ($mime) {
            case 'image/jpeg':
                $saveSuccess = imagejpeg($thumbImage, $thumbPath, 85); // Качество 85%
                break;
            case 'image/png':
                $saveSuccess = imagepng($thumbImage, $thumbPath);
                break;
            case 'image/gif':
                $saveSuccess = imagegif($thumbImage, $thumbPath);
                break;
        }

        // Освобождаем память
        imagedestroy($sourceImage);
        imagedestroy($thumbImage);

        if (!$saveSuccess) {
            error_log("ImageProcessor: Не удалось сохранить миниатюру по пути: {$thumbPath}.");
            return false;
        }

        return true;
    }

    /**
     * Генерирует удалённый путь для миниатюры на основе исходного пути.
     *
     * @param string $originalPath Исходный путь на SFTP-сервере
     * @return string Путь для миниатюры
     */
    protected function generateThumbnailPath(string $originalPath): string
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
    }
}
?>
