<?php

namespace App\Virtual\Models;

/**
 * 範例回應 DTO
 *
 * @OA\Schema(
 *      title="請求標題",
 *      description="請求描述",
 *      type="object"
 * )
 */
class ExampleResponseDTO
{
    /**
     * 帳號
     *
     * @var string
     *
     * @OA\Property(
     *      title="帳號",
     *      description="使用者帳號",
     *      example="user_1"
     * )
     */
    public $userAccount;

    /**
     * 帳號角色
     *
     * @var string|null
     *
     * @OA\Property(
     *      title="帳號角色",
     *      description="使用者帳號的角色",
     *      example="Member"
     * )
     */
    public $userRole;
}
