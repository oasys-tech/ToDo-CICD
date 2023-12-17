<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToDo extends Model
{
    use HasFactory;

    /**
     * 子モデルリレーション
     * @return HasMany
     */
    public function toDoDetails(): HasMany
    {
        return $this->hasMany(ToDoDetail::class);
    }

    /**
     * モデルを削除する
     * @return bool
     */
    public function delete(): bool
    {
        // 関連するToDoDetailsのレコードを削除する
        $this->toDoDetails()->delete();

        // ToDoのレコードを削除する
        return parent::delete();

    }
}
