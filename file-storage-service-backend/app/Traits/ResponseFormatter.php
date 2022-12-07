<?php

namespace App\Traits;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait ResponseFormatter
{
    /**
     * 格式化返回的資料
     *
     * @param \Illuminate\Support\MessageBag|\Illuminate\Support\Collection|array|string|null $error 錯誤訊息
     * @param mixed $data 資料
     * @param int $status [200] 狀態碼
     * @param array<string,string> $headers [] 標頭
     * @return \Illuminate\Http\JsonResponse
     */
    protected function response($error = null, $data = null, int $status = 200, array $headers = []): JsonResponse
    {
        if ($error instanceof MessageBag) {
            $error = $this->processMessageBag($error);
        }

        if ($error instanceof Collection) {
            $error = $error->toArray();
        }

        if (is_array($error)) {
            $error = implode("、", $error);
        }

        $response = [
            'status' => $status,
            'message' => $error,
            'data' => $data,
        ];

        return response()->json($response, $status, $headers);
    }

    /**
     * 串流回應
     *
     * @param string $filepath 檔案完整路徑
     * @param ?string $real_filename 原始檔案名稱，未指定則以當前時間當作檔案名稱
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function streamResponse(string $filepath, string $real_filename = null)
    {
        if (! File::exists($filepath)) {
            return $this->response('指定的檔案不存在', null, Controller::HTTP_NOT_FOUND);
        }

        $real_filename = (is_null($real_filename)) ? Carbon::now()->toIso8601String() : $real_filename;
        $mime_type = File::mimeType($filepath);
        $stream = fopen($filepath, 'rb+');
        $headers = [
            'Content-Type' => $mime_type,
            'Content-Disposition' => 'attachment;filename="'.$real_filename.'"',
            'Cache-Control' => 'max-age=0',
        ];

        return response()->stream(
            function () use ($stream) {
                while (! feof($stream)) {
                    echo fread($stream, 1024);
                    flush();
                }
                fclose($stream);
            },
            Controller::HTTP_OK,
            $headers
        );
    }

    /**
     * 預處理驗證的錯誤訊息
     *
     * @param \Illuminate\Support\MessageBag $error
     * @return string
     */
    protected function processMessageBag(MessageBag $error): string
    {
        $error = $error->toArray();

        $error = collect($error)->map(function ($error) {
            return $error[0];
        })->values()->join('、');

        return $error;
    }
}
