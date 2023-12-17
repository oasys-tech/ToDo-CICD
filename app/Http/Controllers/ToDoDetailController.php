<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToDoDetail\StoreRequest;
use App\Http\Requests\ToDoDetail\UpdateRequest;
use App\Models\ToDoDetail;

class ToDoDetailController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest  $request
     */
    public function store(StoreRequest $request): void
    {
        // 新規のToDoDetailモデルを作成する
        $toDoDetail = new ToDoDetail();

        // ToDoDetailに値を設定する
        $toDoDetail->to_do_id       = $request->get('to_do_id');
        $toDoDetail->name           = $request->get('name');
        $toDoDetail->completed_flag = false;

        // DBにデータを登録する
        $toDoDetail->save();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  int  $id
     * @return void
     */
    public function update(UpdateRequest $request, int $id): void
    {
        // IDに紐づくToDoDetailモデルを取得する
        $toDoDetail = ToDoDetail::find($id);

        // ネームをToDoDetailモデルに設定する
        $toDoDetail->name           = $request->get('name');
        $toDoDetail->completed_flag = $request->get('completed_flag');

        // ToDoDetailテーブルを更新する
        $toDoDetail->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return void
     */
    public function destroy(int $id): void
    {
        // IDに紐づくToDoDetailモデルを取得する
        $toDoDetail = ToDoDetail::find($id);

        // ToDoDetailテーブルから対象のレコードを削除する
        $toDoDetail->delete();
    }
}
