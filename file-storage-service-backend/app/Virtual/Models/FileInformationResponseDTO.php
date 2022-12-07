<?php

namespace App\Virtual\Models;

/**
 * 取得檔案資訊回應酬載
 *
 * @OA\Schema(
 *   title="檔案資訊回應酬載",
 *   description="取得檔案資訊回應酬載",
 *   type="object"
 * )
 */
class FileInformationResponseDTO
{
    /**
     * 檔案名稱
     *
     * @var string
     *
     * @OA\Property(
     *   description="檔案名稱",
     *   example="example.jpg"
     * )
     */
    public $filename;

    /**
     * 檔案大小
     *
     * @var string
     *
     * @OA\Property(
     *   description="檔案大小",
     *   example="3.25MB"
     * )
     */
    public $filesize;

    /**
     * 檔案建立時間
     *
     * @var string
     *
     * @OA\Property(
     *   description="檔案建立時間",
     *   type="date-time",
     *   example="2022-12-07T19:16:21.921Z"
     * )
     */
    public $createdAt;

    /**
     * 檔案最後更新時間
     *
     * @var string
     *
     * @OA\Property(
     *   description="檔案最後更新時間",
     *   type="date-time",
     *   example="2022-12-07T19:16:21.921Z"
     * )
     */
    public $updatedAt;
}
