<?php

namespace App\Commons;

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
}
