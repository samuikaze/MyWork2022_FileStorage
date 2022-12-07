<?php

namespace App\Repositories;

use App\Exceptions\EntityNotFoundException;
use App\Models\File;
use App\Repositories\Abstracts\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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

    /**
     * 取得檔案儲存資訊基礎
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function baseGetFile(): Builder
    {
        return $this->model
            ->where('files.is_valid', 1);
    }

    /**
     * 依檔案名稱取得檔案儲存資訊
     *
     * @param string $filename
     * @return \App\Models\File
     *
     * @throws \App\Exceptions\EntityNotFoundException
     */
    public function getSingleFileByFilename(string $filename): File
    {
        $file = $this->baseGetFile()
            ->where('files.original_filename', $filename)
            ->first();

        if (is_null($file)) {
            throw new EntityNotFoundException('找不到該檔案');
        }

        return $file;
    }

    /**
     * 依多筆檔案名稱取得檔案儲存資訊
     *
     * @param array<int, string> $filenames
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\File>
     */
    public function getFileByFilenames(array $filenames): Collection
    {
        return $this->baseGetFile()
            ->whereIn('files.original_filename', $filenames)
            ->get();
    }
}
