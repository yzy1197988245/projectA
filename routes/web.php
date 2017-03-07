<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
Route::get('/', function () {
    return view('Welcome');
});

Route::get('test', 'MenuController@menu');

Route::get('phpinfo', function () {
    return phpinfo();
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::any('menu', 'AuthController@menu')->middleware('guest');
});

Route::group(['middleware' => ['guest']], function () {
    Route::group(['prefix' => 'menu'], function () {
        Route::post('create', 'MenuController@createOrUpdate');

        Route::get('detail', 'MenuController@getMenuInfo');
        Route::get('delete', 'MenuController@delete');
    });

    Route::group(['prefix' => 'project'], function () {
        Route::any('list', 'ProjectController@projectList');
        Route::post('createOrUpdate', 'ProjectController@createOrUpdate');
        Route::post('studentCreate', 'ProjectController@studentCreate');

        Route::post('studentSelectProject', 'ProjectController@studentSelectProject');
        Route::post('studentDeselectProject', 'ProjectController@studentDeselectProject');
        Route::any('studentProjectList', 'ProjectController@studentProjectList');
        Route::any('studentSelectedProjects', 'ProjectController@studentSelectedProjects');
        Route::any('getStudentCreateProjectBaseInfo', 'ProjectController@getStudentCreateProjectBaseInfo');
        Route::any('getProjectDetail', 'ProjectController@getProjectDetail');

        Route::any('getTeacherProjectList', 'ProjectController@getTeacherProjectList');
        Route::any('getTeacherProjectDetail', 'ProjectController@getTeacherProjectDetail');
        Route::any('deleteProject', 'ProjectController@deleteProject');

        Route::any('projectAutomaticDistribution', 'ProjectController@projectAutomaticDistribution');
        Route::any('getAdminProjectList', 'ProjectController@getAdminProjectList');
    });

    Route::group(['prefix' => 'role'], function () {
        Route::get('list', 'RoleController@roleList');
        Route::post('listWithUser', 'RoleController@listWithUser');
        Route::post('createOrUpdate', 'RoleController@createOrUpdate');
        Route::post('routes', 'RoleController@routes');
        Route::post('addPower', 'RoleController@addPower');
        Route::post('deletePower', 'RoleController@deletePower');

        Route::post('addRoleToUser', 'RoleController@addRoleToUser');
        Route::post('deleteUserRole', 'RoleController@deleteUserRole');
    });

    Route::group(['prefix' => 'user'], function () {
        Route::get('list', 'UserController@userList');
        Route::post('create', 'UserController@create');
        Route::post('delete', 'UserController@delete');
    });

    Route::group(['prefix' => 'file'], function () {
        Route::get('list', 'FileController@fileList');
        Route::post('upload', 'FileController@upload');
        Route::get('download', 'FileController@download');
    });

    Route::group(['prefix' => 'student'], function () {
        Route::get('test', 'StudentController@test');
        Route::post('generalScoreMultiImport', 'StudentController@generalScoreMultiImport');
        Route::get('exportToExcel', 'StudentController@exportToExcel');
        Route::post('updateDescription', 'StudentController@updateDescription');
        Route::post('listWithOrder', 'StudentController@studentListWithOrder');
        Route::post('simpleList', 'StudentController@simpleList');
        Route::post('detail', 'StudentController@detail');
        Route::post('updateOrder', 'StudentController@updateStudentOrder');
        Route::any('getStudentBaseInfo', 'StudentController@getStudentBaseInfo');

        Route::any('getStudentListWithoutSelectedProject', 'StudentController@getStudentListWithoutSelectedProject');
    });

    Route::group(['prefix' => 'studentScore'], function () {
        Route::post('generalScoreMultiImport', 'StudentScoreController@generalScoreMultiImport');
        Route::post('generalScoreWithStudentId', 'StudentScoreController@generalScoreWithStudentId');
        Route::get('updateStudentScore', 'StudentScoreController@updateStudentScore');
    });

    Route::group(['prefix' => 'teacher'], function () {
        Route::get('initStudentOrder', 'TeacherController@initStudentOrder');
        Route::post('multiImport', 'TeacherController@multiImport');
        Route::post('simpleList', 'TeacherController@simpleList');
    });

    Route::group(['prefix' => 'code'], function () {
        Route::get('professionalTitleList', 'CodeController@professionalTitleList');
        Route::get('interest', 'CodeController@interest');
    });

    Route::group(['prefix' => 'task'], function () {
       Route::any('getTaskList', 'TaskController@getTaskList');
    });
});

