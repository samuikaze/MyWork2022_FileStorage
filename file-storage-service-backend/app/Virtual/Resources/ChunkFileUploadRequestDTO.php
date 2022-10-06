<?php

namespace App\Virtual\Resources;

/**
 * 分塊上傳檔案請求 DTO
 *
 * @OA\Schema(
 *   title="分塊上傳檔案請求",
 *   description="分塊上傳檔案請求",
 *   type="object",
 *   required={"fileName", "chunk", "isLast"}
 * )
 */
class ChunkFileUploadRequestDTO
{
    /**
     * 檔案名稱
     *
     * @var string
     *
     * @OA\Property(
     *   title="檔案名稱",
     *   description="原始檔案名稱",
     *   example="example.zip"
     * )
     */
    public $fileName;

    /**
     * 檔案分塊
     *
     * @var \Illuminate\Http\UploadedFile
     *
     * @OA\Property(
     *   title="檔案分塊",
     *   description="經切塊後的檔案",
     * )
     */
    protected $chunk;

    /**
     * 是否為最後一塊
     *
     * @var bool
     *
     * @OA\Property(
     *   title="是否為最後一塊",
     *   description="此分塊是否為最後一個分塊",
     *   example="true"
     * )
     */
    protected $isLast;


}
