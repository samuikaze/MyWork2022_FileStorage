<?php

namespace App\Models;

use App\Commons\IsFinish;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id 上傳者
 * @property string $temp 暫存資料夾名稱
 * @property string $folder 資料夾名稱
 * @property string $filename 檔案名稱
 * @property \App\Commons\IsFinish $is_finished 是否已上傳完成
 * @property \Carbon\Carbon $created_at 最後建立時間
 * @property \Carbon\Carbon $updated_at 最後更新時間
 */
class FileUpload extends Model
{
    use HasFactory;

    /**
     * 讀取的表格名稱
     *
     * @var string
     */
    protected $table = 'file_uploads';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'temp',
        'folder',
        'filename',
        'is_finished',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
