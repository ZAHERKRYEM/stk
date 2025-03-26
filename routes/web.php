<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});




Route::get('/server-files', function () {
    $rootPath = base_path(); // جذر مشروع Laravel
    return getDirectoryTree($rootPath);
});

function getDirectoryTree($dir)
{
    $result = [];
    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {
        $filePath = "$dir/$file";
        $result[] = is_dir($filePath) ? [
            'name' => $file,
            'type' => 'directory',
            'children' => getDirectoryTree($filePath) // استدعاء تكراري للمجلدات
        ] : [
            'name' => $file,
            'type' => 'file'
        ];
    }

    return response()->json($result);
}


use Illuminate\Support\Facades\Artisan;

Route::get('/run-storage-link', function () {
    try {
        Artisan::call('storage:link');
        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء الرابط الرمزي بنجاح! 🎯'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء إنشاء الرابط الرمزي!',
            'error' => $e->getMessage()
        ], 500);
    }
});
