<?php

namespace App\Http\Controllers;

use App\Exceptions\EntityNotFoundException;
use App\Exceptions\IOException;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * FileController
 *
 * @OA\Tag(
 *   name="FileStorage v1",
 *   description="負責檔案儲存與下載的 API"
 * )
 */
class FileController extends Controller
{
    /**
     * FileService
     *
     * @var \App\Services\FileService
     */
    protected $file_service;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\FileService $file_service
     * @return void
     */
    public function __construct(FileService $file_service)
    {
        $this->file_service = $file_service;
    }

    /**
     * 分塊上傳檔案
     *
     * @param \Illuminate\Http\Request $request
     * @param string $category
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *   path="/api/v1/file/chunk",
     *   summary="分塊上傳檔案",
     *   tags={"FileStorage v1"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       ref="#/components/schemas/ChunkFileUploadRequestDTO"
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="分塊上傳成功",
     *     @OA\JsonContent(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/BaseResponseDTO"),
     *         @OA\Schema(
     *           @OA\Property(
     *             property="data",
     *             type="object"
     *           )
     *         )
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response="400",
     *     description="資料格式不正確或上傳檔案過程中發生可預期的錯誤",
     *   ),
     *   @OA\Response(
     *     response="500",
     *     description="上傳檔案過程中發生不可預期的錯誤",
     *   )
     * )
     */
    public function chunkUploadFile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filename' => ['required', 'string'],
            'chunk' => ['required', 'file'],
            'count' => ['required', 'numeric'],
            'isLast' => ['required', 'string', 'in:true,false'],
        ]);

        if ($validator->fails()) {
            return $this->response(
                // '給定的資料有誤或未上傳分塊檔案 ',
                $validator->errors(),
                null,
                self::HTTP_BAD_REQUEST
            );
        }

        $filename = $request->input('filename');
        $chunk = $request->file('chunk');

        $is_last = (bool) $request->input('isLast');
        $count = (int) $request->input('count');

        try {
            $this->file_service->chunkFileUpload($filename, $chunk, $count, $is_last);
        } catch (FileException $e) {
            return $this->response($e->getMessage(), null, self::HTTP_BAD_REQUEST);
        }

        return $this->response();
    }

    /**
     * 合併分塊檔案
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *   path="/api/v1/file/chunk/merge",
     *   summary="合併分塊檔案",
     *   tags={"FileStorage v1"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       ref="#/components/schemas/MergeChunksRequestDTO"
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="分塊合併成功",
     *     @OA\JsonContent(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/BaseResponseDTO"),
     *         @OA\Schema(
     *           @OA\Property(
     *             property="data",
     *             type="object",
     *             example={}
     *           )
     *         )
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response="400",
     *     description="請求資料有誤或合併過程中發生已預期的錯誤",
     *   ),
     *   @OA\Response(
     *     response="500",
     *     description="合併檔案過程中發生不可預期的錯誤",
     *   )
     * )
     */
    public function mergeChunks(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filename' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->response(
                '給定的檔案名稱格式不正確',
                null,
                self::HTTP_BAD_REQUEST
            );
        }

        $user_id = 1;
        $filename = (string) $request->input('filename');

        try {
            $this->file_service->mergeFile($user_id, $filename);
        } catch (IOException|EntityNotFoundException $e) {
            return $this->response($e->getMessage(), null, self::HTTP_BAD_REQUEST);
        }

        return $this->response();
    }

    /**
     * 取得檔案資訊
     *
     * @param string $filename
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *   path="/api/v1/file/info/{filename}",
     *   summary="取得檔案資訊",
     *   tags={"FileStorage v1"},
     *   @OA\Parameter(
     *     name="filename",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *       example="test.jpg"
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="取得單一檔案",
     *     @OA\JsonContent(
     *       allOf={
     *         @OA\Schema(ref="#/components/schemas/BaseResponseDTO"),
     *         @OA\Schema(
     *           @OA\Property(
     *             property="data",
     *             type="object",
     *             ref="#/components/schemas/FileInformationResponseDTO"
     *           )
     *         )
     *       }
     *     )
     *   ),
     *   @OA\Response(
     *     response="404",
     *     description="未給出檔案名稱或找不到檔案",
     *   ),
     *   @OA\Response(
     *     response="500",
     *     description="下載檔案過程中發生不可預期的錯誤",
     *   )
     * )
     */
    public function getFileInformation(string $filename): JsonResponse
    {
        try {
            $information = $this->file_service->getFileInformation($filename);
        } catch (EntityNotFoundException $e) {
            return $this->response(
                error: $e->getMessage(),
                status: self::HTTP_NOT_FOUND
            );
        }

        return $this->response(data: $information);
    }

    /**
     * 取得單一檔案
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @OA\Get(
     *   path="/api/v1/file/{filename}",
     *   summary="取得單一檔案",
     *   tags={"FileStorage v1"},
     *   @OA\Parameter(
     *     name="filename",
     *     in="path",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *       example="test.jpg"
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="取得單一檔案"
     *   ),
     *   @OA\Response(
     *     response="404",
     *     description="未給出檔案名稱或找不到檔案",
     *   ),
     *   @OA\Response(
     *     response="500",
     *     description="下載檔案過程中發生不可預期的錯誤",
     *   )
     * )
     */
    public function getSingleFile(string $filename = null): JsonResponse | StreamedResponse
    {
        $validator = Validator::make(['filename' => $filename], [
            'filename' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->response(null, null, self::HTTP_NOT_FOUND);
        }

        try {
            [
                'fullpath' => $fullpath,
                'real_filename' => $real_filename,
            ] = $this->file_service->getSingleFile($filename);
        } catch (EntityNotFoundException $e) {
            return $this->response(null, $e->getMessage(), self::HTTP_NOT_FOUND);
        }

        return $this->streamResponse($fullpath, $real_filename);
    }

    /**
     * 多檔包 Zip 下載
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @OA\Post(
     *   path="/api/v1/files/download",
     *   summary="多檔包 Zip 下載",
     *   tags={"FileStorage v1"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       ref="#/components/schemas/MultipleFilesDownloadRequestDTO"
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="取得單一檔案"
     *   ),
     *   @OA\Response(
     *     response="404",
     *     description="未給出檔案名稱或找不到檔案",
     *   ),
     *   @OA\Response(
     *     response="500",
     *     description="下載檔案過程中發生不可預期的錯誤",
     *   )
     * )
     */
    public function getMultipleFiles(Request $request): JsonResponse | StreamedResponse
    {
        $validator = Validator::make($request->all(), [
            'filename' => ['nullable', 'string'],
            'files' => ['required', 'array'],
            'files.*' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->response(
                '請確實指定要下載的檔案',
                null,
                self::HTTP_BAD_REQUEST
            );
        }

        $filenames = $request->input('files');
        try {
            // 實作壓縮下載
            [
                'fullpath' => $zip_path,
                'real_filename' => $zip_name,
            ] = $this->file_service->zipMultipleFiles($filenames);
        } catch (EntityNotFoundException| IOException $e) {
            return $this->response($e->getMessage(), null, self::HTTP_NOT_FOUND);
        }

        $request_zip_name = null;
        if ($request->has('filename')) {
            $request_zip_name = (strlen($request->input('filename')) > 0)
                ? $request->input('filename')
                : $zip_name;
        }

        return $this->streamResponse($zip_path, $request_zip_name);
    }
}
