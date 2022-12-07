<?php

namespace App\Virtual\Resources;

/**
 * 合併分塊檔案請求 DTO
 *
 * @OA\Schema(
 *   title="合併分塊檔案請求",
 *   description="合併分塊檔案請求",
 *   type="object",
 *   required={"fileName"}
 * )
 */
class MergeChunksRequestDTO
{
    /**
     * 檔案名稱
     *
     * @var string
     *
     * @OA\Property(
     *   title="檔案名稱",
     *   description="檔案名稱",
     *   example="example.zip"
     * )
     */
    public $filename;
}
