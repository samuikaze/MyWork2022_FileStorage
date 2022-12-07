<?php

return [
    /**
     * 檔案儲存資料夾名稱
     *
     * @var string
     */
    'save_folder' => storage_path('app'.DIRECTORY_SEPARATOR.'files'),

    /**
     * 檔案上傳的暫存資料夾
     *
     * @var string
     */
    'temp_folder' => storage_path('app'.DIRECTORY_SEPARATOR.'temps'),

    /**
     * 壓縮檔儲存路徑
     *
     * @var string
     */
    'zip_folder' => storage_path('app'.DIRECTORY_SEPARATOR.'zips'),

    /**
     * 是否執行垃圾收集
     *
     * ※ 請注意，關閉此項後所有與垃圾收集的函式將皆不會執行
     *
     * @var bool
     */
    'perform_gc' => true,

    /**
     * 暫存檔案存活時間 (小時)
     *
     * @var int
     */
    'temp_expired_at' => env('TEMP_EXPIRED_AT', 24),

    /**
     * 壓縮檔案存活時間 (小時)
     *
     * @var int
     */
    'zip_expired_at' => env('ZIP_EXPIRED_AT', 24),
];
