<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/1/9
 * Time: 21:15
 */

namespace App\Http\Controllers;


use App\Data\Menu;
use App\Data\Route;
use App\Data\RouteData;
use App\MyResult;

use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function createOrUpdate(Request $request) {

        $route = Route::where('id', $request->get('id'))->first();
        if ($route == null) {
            $route = new Route();
        }

        if (!is_null($request->get('path')))
            $route->path = $request->get('path');

        if (!is_null($request->get('parent_id')))
            $route->parent_id = $request->get('parent_id');

        if (!is_null($request->get('start_time')))
            $route->start_time = $request->get('start_time');

        if (!is_null($request->get('end_time')))
            $route->end_time = $request->get('end_time');

        if (!is_null($request->get('long_time'))) {
            $long_time = $request->get('long_time');
            if ($long_time == true)
                $long_time = 1;
            else
                $long_time = 0;
            $route->long_time = $long_time;
        }

        $route_data = RouteData::where('id', $route->route_data_id)->first();
        if ($route_data == null)
            $route_data = new RouteData();

        $menu = Menu::where('id', $route_data->menu_id)->first();
        if ($menu == null)
            $menu = new Menu();

        if (!is_null($request->get('title')))
            $menu->title = $request->get('title');

        if (!is_null($request->get('icon')))
            $menu->icon = $request->get('icon');

        if (!is_null($request->get('selected')))
            $menu->selected = $request->get('selected');

        if (!is_null($request->get('expanded')))
            $menu->expanded = $request->get('expanded');

        if (!is_null($request->get('order')))
            $menu->order = $request->get('order');

        $menu->save();
        $route_data->menu_id = $menu->id;
        $route_data->save();
        $route['route_data_id'] = $route_data['id'];
        $route->save();

        return response()->json(new MyResult());
    }

    public function getMenuInfo(Request $request) {
        $id = $request->get('id');

        $route = Route::find($id);

        $routeData = RouteData::find($route['route_data_id']);
        $menu = Menu::find($routeData['menu_id']);

        $route['title'] = $menu['title'];
        $route['icon'] = $menu['icon'];
        $route['order'] = $menu['order'];

        return response()->json($route);
    }

    public function delete(Request $request) {

        $id = $request->get('id');
        $route = Route::find($id);
        $routeData = RouteData::find($route['route_data_id']);
        $menu = Menu::find($routeData['menu_id']);

        $menu->delete();
        $routeData->delete();
        $route->delete();

        return response()->json(new MyResult());
    }
}