<?php

namespace App\Commons;

use App\Enums\PathType;
use Carbon\Carbon;
use Exception;

class Utils
{
    /**
     * 從完整路徑或檔案路徑取得檔案名稱
     *
     * @param string $path
     * @return string
     */
    public static function getFilenameFromPath(string $path): string
    {
        $filename = explode(DIRECTORY_SEPARATOR, $path);

        $items = count($filename);
        $filename = $filename[$items - 1];

        return $filename;
    }

    /**
     * 從檔案名稱取得副檔名
     *
     * @param string $filename 原始檔案名稱
     * @return string
     */
    public static function getExtensionsFromFilename(string $filename): string
    {
        if (stripos('/', $filename) !== false || stripos('\\', $filename) !== false) {
            $filename = self::getFilenameFromPath($filename);
        }

        $exploded = explode('.', $filename);
        $length = count($exploded);

        if ($length == 0) {
            return '';
        }

        return $exploded[$length - 1];
    }

    /**
     * 取得資料夾路徑
     *
     * @param string $full_path
     * @return string
     */
    public static function getPathFromFullPath(string $full_path)
    {
        $full_path = explode(DIRECTORY_SEPARATOR, $full_path);
        array_pop($full_path);

        $full_path = implode(DIRECTORY_SEPARATOR, $full_path);

        return $full_path;
    }

    /**
     * 檢查資料夾存不存在，不存在則建立
     *
     * @param string $path
     * @return bool
     */
    public static function checkIfDirectoryExists(string $path): bool
    {
        $result = true;

        $path_exists = file_exists($path);
        if (! $path_exists) {
            $result = mkdir($path, 0777, true);
        }

        return $result;
    }

    /**
     * 將檔名切到允許的長度
     *
     * @param string $original_filename 原始檔名
     * @param ?int $max_length 最大允許長度
     * @return string
     */
    public static function trimFilename(string $original_filename, int $max_length = null): string
    {
        if (is_null($max_length)) {
            return $original_filename;
        }

        $trim_chars = '...';
        $allowance = 5;

        $extensions = self::getExtensionsFromFilename($original_filename);
        $allow_length = $max_length - strlen($trim_chars) - strlen($extensions) - $allowance;

        $filename = $original_filename;
        if (strlen($filename) > $allow_length) {
            // 先把副檔名拿掉
            $filename = str_replace('.'.$extensions, '', $filename);
            $filename = substr($filename, 0, $allow_length);
            $filename = $filename.'....'.$extensions;
        }

        return $filename;
    }

    /**
     * 全域垃圾收集
     *
     * @return bool
     */
    public static function GC(): bool
    {
        $perform_gc = (bool) config('file.perform_gc', true);
        if (! $perform_gc) {
            return true;
        }

        // 清除緩存
        clearstatcache();
        $now = Carbon::now();
        $result = true;

        try {
            // 清除過期分塊檔案
            $temp_expired_at = (int) config('file.temp_expired_at');
            $temp_paths = config('file.temp_folder').
                DIRECTORY_SEPARATOR.
                '*';
            $dirs = glob($temp_paths);
            if ($dirs !== false) {
                foreach ($dirs as $dir) {
                    // 比較資料夾建立時間，若資料夾建立時間已經大於可存活時間，進行檔案與資料夾移除
                    $created_at = Carbon::parse(filectime($dir));
                    $diff = $created_at->diffInHours($now, false);
                    if ($diff > $temp_expired_at) {
                        $files = glob($dir);
                        foreach ($files as $file) {
                            unlink($file);
                        }
                        rmdir($dir);
                    }
                }
            } else {
                $result = false;
            }

            // 清除過期壓縮檔
            $zip_expired_at = (int) config('file.zip_expired_at');
            $zip_paths = config('file.zip_folder').
                DIRECTORY_SEPARATOR.
                '*';
            $files = glob($zip_paths);
            if ($files !== false) {
                foreach ($files as $file) {
                    // 比較壓縮檔建立時間，若已超過可存活時間，進行壓縮檔刪除
                    $created_at = Carbon::parse(filectime($file));
                    $diff = $created_at->diffInHours($now, false);
                    if ($diff > $zip_expired_at) {
                        unlink($file);
                    }
                }

                $operation_result['zips'] = true;
            } else {
                $result = false;
            }
        } catch (Exception $e) {
            // 遇錯誤寫 Log，但不進行異常拋出，相對的是返回 false
            // 垃圾收集不應拋錯
            report($e);

            return false;
        }

        return $result;
    }

    /**
     * 針對指定路徑進行垃圾收集
     *
     * @return bool
     */
    public static function GCSpecificPath(string $fullpath): bool
    {
        $perform_gc = (bool) config('file.perform_gc', true);
        if (! $perform_gc) {
            return true;
        }

        $paths = $fullpath.DIRECTORY_SEPARATOR.'*';
        /** @var array<int, string>|false */
        $dirs_or_files = glob($paths);
        if ($dirs_or_files === false) {
            return false;
        }

        try {
            foreach ($dirs_or_files as $dir_or_file) {
                // 如果是資料夾
                if (is_dir($dir_or_file)) {
                    /** @var array<int, string>|false */
                    $files = glob($dir_or_file.DIRECTORY_SEPARATOR.'*');
                    if ($files === false) {
                        continue;
                    }

                    foreach ($files as $file) {
                        unlink($file);
                    }
                    rmdir($dir_or_file);
                }

                // 如果是檔案
                if (is_file($dir_or_file)) {
                    unlink($dir_or_file);
                }
            }
        } catch (Exception $e) {
            report($e);

            return false;
        }

        return true;
    }

    /**
     * 組成完整路徑
     *
     * @param \App\Enums\PathType $type 路徑種類
     * @param ?string $folder 資料夾
     * @param ?string $filename 檔案名稱
     * @return string
     */
    public static function composePath(PathType $type, string $folder = null, string $filename = null): string
    {
        $path = (string) config('file.'.$type->value);

        if (! is_null($folder)) {
            $path .=
                DIRECTORY_SEPARATOR.
                $folder;
        }

        if (! is_null($filename)) {
            $path .=
                DIRECTORY_SEPARATOR.
                $filename;
        }

        return $path;
    }

    /**
     * 將檔案大小計算為人類可讀的資訊
     *
     * @param int $level 單位等級
     * @param float $size 檔案大小
     * @return string
     */
    public static function calculateFileSize(int $level, float $size): string
    {
        $levels = ['bytes', 'KB', 'MB', 'GB', 'TB'];

        if ($size >= 1024) {
            $size /= 1024;
            $level += 1;
        }

        if ($size > 1024) {
            return self::calculateFileSize($level, $size);
        }

        $size = round($size, 3);
        $unit = $levels[$level];
        return ((string) $size) . $unit;
    }
}
