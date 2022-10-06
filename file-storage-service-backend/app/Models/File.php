<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id 上傳者
 * @property string $folder 資料夾名稱
 * @property string $filename 檔案名稱
 * @property bool $is_valid 是否有效
 * @property \Carbon\Carbon $created_at 最後建立時間
 * @property \Carbon\Carbon $updated_at 最後更新時間
 */
class File extends Model
{
    use HasFactory;

    /**
     * 讀取的表格名稱
     *
     * @var string
     */
    protected $table = 'files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'folder',
        'filename',
        'is_valid',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_valid' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
