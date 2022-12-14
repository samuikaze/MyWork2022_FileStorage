<?php

namespace App\Enums;

enum IsFinish: int
{
    /**
     * 上傳中
     *
     * @var int
     */
    const UPLOADING = 0;

    /**
     * 上傳完成
     *
     * @var int
     */
    const FINISHED = 1;

    /**
     * 上傳被中止
     *
     * @var int
     */
    const TERMINATED = 2;
}
