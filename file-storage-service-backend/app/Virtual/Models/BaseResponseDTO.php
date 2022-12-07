<?php

namespace App\Virtual\Models;

/**
 * 共通回應格式
 *
 * @OA\Schema(
 *   title="共通回應",
 *   description="請求回傳的共通酬載",
 *   type="object"
 * )
 */
class BaseResponseDTO
{
    /**
     * HTTP 狀態碼
     *
     * @var int
     *
     * @OA\Property(
     *   type="integer",
     *   description="HTTP 狀態碼",
     *   example="200"
     * )
     */
    public $status;

    /**
     * 錯誤訊息
     *
     * @var string|null
     *
     * @OA\Property(
     *   type="string",
     *   description="錯誤訊息",
     *   example="錯誤訊息"
     * )
     */
    public $message;

    /**
     * 回應資料
     *
     * @var object|array|null
     *
     * @OA\Proerty(
     *   type="object",
     *   description="回應資料",
     * )
     */
    public $data;
}
