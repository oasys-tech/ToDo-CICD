<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToDoDetail extends Model
{
    use HasFactory;

    /**
     * 親モデルリレーション
     * @return BelongsTo
     */
    public function toDo(): BelongsTo
    {
        return $this->belongsTo(ToDo::class);
    }

    /**
     * 完了フラグアクセサ
     * @param  int  $value
     * @return bool
     */
    public function getCompletedFlagAttribute(int $value): bool
    {
        return $value == 1;
    }
}
