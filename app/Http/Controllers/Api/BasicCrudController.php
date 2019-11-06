<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Symfony\Component\VarDumper\VarDumper;

abstract class BasicCrudController extends Controller
{
    protected abstract function model();
    protected abstract function ruleStorage();
    protected abstract function ruleUpdate();

    public function index()
    {
        $result = $this->model()::all();
        return $result;
    }

    public function store(Request $request)
    {
        $validateData = $this->validate($request,$this->ruleStorage());
        $obj = $this->model()::create($validateData);
        $obj->refresh();
        return $obj;

    }
    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = ( new $model )->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrfail();
    }

    public function show($category) // router model binding
    {
       return $this->findOrFail($category);
    }

    public function update(Request $request, $category)
    {

        $dataValidate = $this->validate($request,$this->ruleUpdate());
        $model = $this->findOrFail($category);
        $model->update($dataValidate);
        $model->refresh();
        return  $model;
    }

    public function destroy($category)
    {
       $model = $this->findOrFail($category);
       $model->delete();
       return response()->noContent();
    }
}
