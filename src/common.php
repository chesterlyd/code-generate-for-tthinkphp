<?php

use think\Console;
use think\Db;
use think\db\Query;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Env;
use think\facade\Route;
use think\Loader;

if (!class_exists('\\think\\Console')) {
    return;
}

Console::addDefaultCommands([
    'Generate\\Command\\Generate',
]);

$rootPath = Env::get('root_path');
if (!empty($rootPath) && file_exists($rootPath . '/generate.lock')) {
    Route::rules([
        'generate/showTables' => '\\Generate\\Controller\\Generate@showTables',
        'generate/getModelData' => '\\Generate\\Controller\\Generate@getModelData',
        'generate/getTableFieldData' => '\\Generate\\Controller\\Generate@getTableFieldData',
        'generate/generate' => '\\Generate\\Controller\\Generate@generate',
        'generate/generateRelation' => '\\Generate\\Controller\\Generate@generateRelation',
        'generate' => '\\Generate\\Controller\\Generate@index',
    ]);
}

//设置查询事件
$callback = function (Query $query) {
    $table = $query->getTable();
    $prefix = Config::get('database.prefix');
    $name = preg_replace('/^' . $prefix . '/', '', $table);
    $modelName = Loader::parseName($name, 1);
    echo $modelName, '已清除缓存', "\n";
    Cache::clear($modelName . '_cache_data');
};
Db::event('after_insert', $callback);
Db::event('after_update', $callback);
Db::event('after_delete', $callback);
