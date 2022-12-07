<?php

namespace App\Services;

use App\Commons\Utils;
use App\Enums\IsFinish;
use App\Enums\PathType;
use App\Exceptions\EntityNotFoundException;
use App\Exceptions\IOException;
use App\Repositories\FileRepository;
use App\Repositories\FileUploadRepository;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use ZipArchive;

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

        $check_exists = [
            config('file.save_folder', storage_path('app'.DIRECTORY_SEPARATOR.'files')),
            config('file.temp_folder', storage_path('app'.DIRECTORY_SEPARATOR.'temps')),
            config('file.zip_folder', storage_path('app'.DIRECTORY_SEPARATOR.'zips')),
        ];
        foreach ($check_exists as $check) {
            Utils::checkIfDirectoryExists($check);
        }
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
        $temp_directory = Utils::composePath(type: PathType::TEMP_PATH, folder: $tmp_folder);

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

        $save_filename = Uuid::uuid4()->toString().'.'.Utils::getExtensionsFromFilename($filename);

        $tmp_files = Utils::composePath(PathType::TEMP_PATH, $file->temp, '*.tmp');
        $final_file = Utils::composePath(PathType::SAVE_PATH, $file->folder, $save_filename);

        $chunks = glob($tmp_files);
        natsort($chunks);
        $chunks = array_values($chunks);

        $counts = count($chunks);

        $gc_path = Utils::composePath(PathType::TEMP_PATH, $file->temp);

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
                    Utils::GCSpecificPath($gc_path);
                    throw $e;
                }
            }
        }

        $this->file_repository->create([
            'user_id' => $file->user_id,
            'folder' => $file->folder,
            'filename' => $save_filename,
            'original_filename' => Utils::trimFilename($file->filename),
            'is_valid' => 1,
        ]);

        Utils::GCSpecificPath($gc_path);
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
     * 取得單一檔案完整路徑
     *
     * @param string $filename 檔案名稱
     * @return array<string, string>
     *
     * @throws \App\Exceptions\EntityNotFoundException
     */
    public function getSingleFile(string $filename): array
    {
        $filename = urldecode($filename);
        $file = $this->file_repository->getSingleFileByFilename($filename);

        $fullpath = Utils::composePath(PathType::SAVE_PATH, $file->folder, $file->filename);
        $real_filename = $file->original_filename;

        return [
            'fullpath' => $fullpath,
            'real_filename' => $real_filename,
        ];
    }

    /**
     * 多檔包成壓縮檔，並返回完整路徑與檔名
     *
     * @param array<int, string> $files 檔案名稱
     * @return array<string, string>
     */
    public function zipMultipleFiles(array $filenames): array
    {
        $file_infos = $this->file_repository->getFileByFilenames($filenames);
        if ($file_infos->count() === 0) {
            throw new EntityNotFoundException('依據給定的檔名找不到任何檔案');
        }

        $zip = new ZipArchive();
        $zip_name = Uuid::uuid4()->toString().'.zip';

        $zip_file = Utils::composePath(type: PathType::ZIP_PATH, filename: $zip_name);

        $result = $zip->open($zip_file, ZipArchive::CREATE);
        if ($result === false) {
            throw new IOException('壓縮檔建立失敗，請再試一次');
        }

        $index = 0;
        foreach ($file_infos as $info) {
            $fullpath = Utils::composePath(PathType::SAVE_PATH, $info->folder, $info->filename);

            if (! File::exists($fullpath)) {
                continue;
            }

            $filename = $info->original_filename;
            if ($zip->locateName($filename, ZipArchive::FL_NODIR) !== false) {
                $extensions = Utils::getExtensionsFromFilename($filename);
                $filename =
                    str_replace('.'.$extensions, '', $filename).
                    ' ('.$index.').'.
                    $extensions;
            }

            $zip->addFile($fullpath, $filename);
            $index++;
        }

        $zip->close();

        Utils::GC();

        return [
            'fullpath' => $zip_file,
            'real_filename' => $zip_name,
        ];
    }

    /**
     * 取得檔案資訊
     *
     * @param string $filename 檔案名稱
     * @return array<string, string>
     *
     * @throws \App\Exceptions\EntityNotFoundException
     */
    public function getFileInformation(string $filename): array
    {
        $filename = urldecode($filename);
        $file = $this->file_repository->getSingleFileByFilename($filename);

        $fullpath = Utils::composePath(PathType::SAVE_PATH, $file->folder, $file->filename);
        $size = Utils::calculateFileSize(0, filesize($fullpath));

        return [
            'filename' => $file->original_filename,
            'filesize' => $size,
            'createdAt' => $file->created_at->toISOString(),
            'updatedAt' => $file->updated_at->toISOString(),
        ];
    }
}
