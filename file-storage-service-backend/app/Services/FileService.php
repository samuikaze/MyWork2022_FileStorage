<?php

namespace App\Services;

use App\Commons\IsFinish;
use App\Commons\Utils;
use App\Exceptions\EntityNotFoundException;
use App\Exceptions\IOException;
use App\Repositories\FileRepository;
use App\Repositories\FileUploadRepository;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FileService
{
    /**
     * FileUploadRepository
     *
     * @var \App\Repositories\FileUploadRepository
     */
    protected $file_upload_repository;

    /**
     * FileRepository
     *
     * @var \App\Repositories\FileRepository
     */
    protected $file_repository;

    /**
     * 檔案儲存資料夾名稱
     *
     * @var string
     */
    protected $save_folder;

    /**
     * 暫存資料夾根目錄
     *
     * @var string
     */
    protected $temp_folder;

    /**
     * 建構方法
     *
     * @param \App\Repositories\FileRepository $file_repository
     * @param \App\Repositories\FileUploadRepository $file_upload_repository
     * @return void
     */
    public function __construct(
        FileRepository $file_repository,
        FileUploadRepository $file_upload_repository
    ) {
        $this->file_repository = $file_repository;
        $this->file_upload_repository = $file_upload_repository;
        $this->save_folder = config('file.save_folder', storage_path('app/files'));
        $this->temp_folder = config('file.temp_folder', storage_path('app/temps'));
    }

    /**
     * 分塊上傳檔案
     *
     * @param string $filename 檔案名稱
     * @param \Illuminate\Http\UploadedFile $chunk 分塊檔案
     * @param int $count 分塊計數器
     * @param bool $is_last 是否為最後一塊分塊
     * @return void
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function chunkFileUpload(string $filename, UploadedFile $chunk, int $count, bool $is_last): void
    {
        $upload_record = $this->file_upload_repository->findUploadRecordByFilename($filename);
        if (is_null($upload_record)) {
            $folder = Str::random(32);
            $temp_folder = Str::random(32);
            /** @var \App\Models\FileUpload */
            $upload_record = $this->file_upload_repository->create([
                'user_id' => 1,
                'temp' => $temp_folder,
                'folder' => $folder,
                'filename' => $filename,
            ]);
        } else {
            $folder = $upload_record->folder;
        }

        if ($is_last) {
            $this->file_upload_repository->safeUpdateRecord(
                $upload_record->id,
                ['is_finished' => IsFinish::FINISHED]
            );
        }

        $this->moveToTempFolder(
            $filename,
            $count,
            $upload_record->temp,
            $chunk
        );
    }

    /**
     * 移動到暫存資料夾
     *
     * @param string $filename 檔案名稱
     * @param int $count 分塊計數器
     * @param string $tmp_folder 暫存資料夾名稱
     * @param \Illuminate\Http\UploadedFile $chunk 分塊檔案
     * @return void
     *
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    protected function moveToTempFolder(string $filename, int $count, string $tmp_folder, UploadedFile $chunk): void
    {
        $temp_directory = $this->temp_folder.
            DIRECTORY_SEPARATOR.
            $tmp_folder;

        $tmp_filename = $filename.'.chunked-'.$count.'.tmp';
        $chunk->move($temp_directory, $tmp_filename);
    }

    /**
     * 合併分塊並移動檔案到指定路徑
     *
     * @param int $user_id
     * @param string $filename
     * @return void
     *
     * @throws \App\Exceptions\EntityNotFoundException
     * @throws \App\Exceptions\IOException
     */
    public function mergeFile(int $user_id, string $filename): void
    {
        $file = $this->file_upload_repository->findUploadRecordByUserIdAndFilename(
            $user_id, $filename
        );

        if (is_null($file)) {
            throw new EntityNotFoundException('找不到該檔案資料');
        }

        $tmp_files = $this->temp_folder.
            DIRECTORY_SEPARATOR.
            $file->temp.
            DIRECTORY_SEPARATOR.
            '*.tmp';

        $final_file = $this->save_folder.
            DIRECTORY_SEPARATOR.
            $file->folder.
            DIRECTORY_SEPARATOR.
            $file->filename;

        $chunks = glob($tmp_files);
        natsort($chunks);
        $chunks = array_values($chunks);

        $counts = count($chunks);

        $gc_path =  $tmp_files = $this->temp_folder.
            DIRECTORY_SEPARATOR.
            $file->temp;

        for ($i = 0; $i < $counts; $i++) {
            $full_path = $chunks[$i];
            if ($i == 0) {
                $check_path = Utils::getPathFromFullPath($final_file);
                Utils::checkIfDirectoryExists($check_path);
                File::move($full_path, $final_file);
            } else {
                try {
                    $buff = $this->readFile($full_path);
                    $this->writeFile($final_file, $buff);
                } catch (Exception $e) {
                    $this->GC($gc_path);
                    throw $e;
                }
            }
        }

        $this->file_repository->create([
            'user_id' => $file->user_id,
            'folder' => $file->folder,
            'filename' => $file->filename,
            'is_valid' => 1,
        ]);

        $this->GC($gc_path);
        $this->file_upload_repository->deleteRecord($file->id);
    }

    /**
     * 讀取檔案並返回
     *
     * @param string $full_path 完整檔案路徑
     * @param int|null $read_size 讀取大小
     * @return string
     *
     * @throws \App\Exceptions\IOException
     */
    protected function readFile(string $full_path, int $read_size = null): string
    {
        if (is_null($read_size)) {
            $read_size = filesize($full_path);
        }

        $chunk = fopen($full_path, 'rb');
        $buff = fread($chunk, $read_size);
        fclose($chunk);

        if ($buff === false) {
            throw new IOException('讀取分塊檔案失敗');
        }

        return $buff;
    }

    /**
     * 寫入檔案
     *
     * @param string $file 要寫入的檔案
     * @param string $content 要寫入的內容
     * @return bool
     *
     * @throws \App\Exceptions\IOException
     */
    protected function writeFile(string $file, string $content): bool
    {
        $final = fopen($file, 'ab');
        $write = fwrite($final, $content);
        fclose($final);

        if ($write === false) {
            throw new IOException('寫入檔案失敗');
        }

        return true;
    }

    /**
     * 垃圾收集，傳入資料夾會將該資料夾下所有的檔案刪除，傳入完整路徑則只會刪除該檔案
     *
     * @param ?string $directory
     * @param ?string $full_path
     * @return bool
     */
    protected function GC(string $directory = null, string $full_path = null): bool
    {
        if (! is_null($directory)) {
            $paths = $directory.DIRECTORY_SEPARATOR.'*';
            $files = glob($paths);
            foreach ($files as $file) {
                unlink($file);
            }

            rmdir($directory);

            return true;
        }

        if (! is_null($full_path)) {
            unlink($full_path);

            return true;
        }

        $temp_paths = $this->temp_folder.
            DIRECTORY_SEPARATOR.
            '*';
        $files = glob($temp_paths);
        foreach ($files as $file) {
            unlink($file);
        }

        return true;
    }
}
