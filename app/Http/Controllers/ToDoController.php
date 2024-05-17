<?php

namespace App\Http\Controllers;

use App\Http\Requests\ToDo\StoreRequest;
use App\Http\Requests\ToDo\UpdateRequest;
use App\Models\ToDo;
use App\Models\ToDoDetail;
use Illuminate\Support\Facades\DB;

class ToDoController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(): \Illuminate\Database\Eloquent\Collection
    {
        // ToDoを取得する
        $toDos = ToDo::with('toDoDetails')->get();

        // 取得したToDoを返却する
        return $toDos;
    }

    /**
     * Store a newly created resource in storage.
     * @param  StoreRequest  $request
     */
    public function store(StoreRequest $request): void
    {
        // 新規のToDoモデルを作成する
        $toDo = new ToDo();

        // タイトルをToDoモデルに設定する
        $toDo->title = $request->get('title');

        // 空のToDoDetailを作成する
        $toDoDetail                 = new ToDoDetail();
        $toDoDetail->name           = null;
        $toDoDetail->completed_flag = false;

        // DBにデータを登録する
        DB::transaction(function () use ($toDo, $toDoDetail) {
            $toDo->save();
            $toDo->toDoDetails()->save($toDoDetail);
        });
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest  $request
     * @param  int  $id
     * @return void
     */
    public function update(UpdateRequest $request, $id): void
    {
        // IDに紐づくToDoモデルを取得する
        $toDo = ToDo::find($id);

        // タイトルをToDoモデルに設定する
        $toDo->title = $request->get('title');
        $toDo->color = $request->get('color');

        // ToDoデータベースを更新する
        $toDo->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return void
     */
    public function destroy($id): void
    {
        // IDに紐づくToDoモデルを取得する
        $toDo = ToDo::find($id);

        // ToDoデータベースから対象のレコードを削除する
        $toDo->delete();
    }
}
