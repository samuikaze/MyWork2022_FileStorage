<?php

namespace App\Http\Controllers;

use App\Exceptions\EntityNotFoundException;
use App\Exceptions\IOException;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * FileController
 *
 * @OA\OpenApi(
 *   @OA\Info(
 *     title="檔案儲存 API",
 *     version="1.0"
 *   ),
 *   @OA\Servers(
 *     @OA\Server(
 *       url="http://localhost:15210/api/",
 *       description="開發環境"
 *     )
 *   )
 *   @OA\Tag(
 *     name="檔案儲存相關 API",
 *     description="負責檔案儲存與下載的 API"
 *   )
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
     *   path="v1/file/chunk",
     *   summary="分塊上傳檔案",
     *   tags={"檔案儲存相關 API"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\Schema(ref="#/components/schemas/ChunkFileUploadRequestDTO")
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="分塊上傳成功"
     *   ),
     *   @OA\Response(
     *     response="400",
     *     description="資料格式不正確或上傳檔案過程中發生可預期的錯誤",
     *   ),
     *   @OA\Response(
     *     response="500",
     *     description="上傳檔案過程中發生不可預期的錯誤",
     *   ),
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

        return response()->json();
    }

    /**
     * 合併分塊檔案
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *   path="v1/file/chunk/merge",
     *   summary="合併分塊檔案",
     *   tags={"檔案儲存相關 API"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="string",
     *       required={"filename"},
     *       @OA\Property(property="filename", type="string")
     *     )
     *   ),
     *   @OA\Response(
     *     response="200",
     *     description="分塊合併成功"
     *   ),
     *   @OA\Response(
     *     response="400",
     *     description="請求資料有誤或合併過程中發生已預期的錯誤",
     *   ),
     *   @OA\Response(
     *     response="500",
     *     description="合併檔案過程中發生不可預期的錯誤",
     *   ),
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
}
