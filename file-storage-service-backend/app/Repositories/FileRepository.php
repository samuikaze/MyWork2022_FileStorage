<?php

namespace App\Repositories;

use App\Models\File;
use App\Repositories\Abstracts\BaseRepository;

class FileRepository extends BaseRepository
{
    protected function name(): string
    {
        return '檔案資訊';
    }

    /**
     * 建構方法
     *
     * @param \App\Models\File $model
     * @return void
     */
    public function __construct(File $model)
    {
        $this->model = $model;
    }
}
