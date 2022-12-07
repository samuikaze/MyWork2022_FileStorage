<?php

namespace App\Enums;

/**
 * 路徑種類
 */
enum PathType: string
{
    /**
     * 檔案儲存路徑
     *
     * @var string
     */
    case SAVE_PATH = 'save_folder';

    /**
     * 暫存檔儲存路徑
     *
     * @var string
     */
    case TEMP_PATH = 'temp_folder';

    /**
     * 壓縮檔儲存路徑
     *
     * @var string
     */
    case ZIP_PATH = 'zip_folder';
}
