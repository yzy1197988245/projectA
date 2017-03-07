<?php
/**
 * Created by PhpStorm.
 * User: yzy
 * Date: 2017/2/7
 * Time: 22:40
 */

namespace App\Http\Controllers;


use App\Data\File;
use App\MyResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function fileList() {
        return File::all();
    }

    public function upload(Request $request) {
        $result = new MyResult();
        $files = $request->allFiles();

        if (count($files) == 0) {
            $result->code = 100;
            $result->message = '没有文件';
            return response()->json($result);
        }
        else {
            foreach ($files as $file) {
                $path = $file->store('');
                $fileData = File::where('hashName', $file->hashName())->first();
                if ($fileData == null)
                    $fileData = new File();
                $fileData['path'] = $path;
                $fileData['hashName'] = $file->hashName();
                $fileData['extension'] = $file->extension();
                $fileData['clientExtension'] = $file->getClientOriginalExtension();
                $fileData['mimeType'] = $file->getClientMimeType();
                $fileData['fileName'] = $file->getClientOriginalName();
                $fileData->save();
            }
            return '文件接收成功';
        }
    }

    public function download(Request $request) {
        $id = $request->get('id');
        if ($id != null) {
            $fileData = File::find($id);

            if ($fileData != null) {
                $file = Storage::get($fileData['path']);
                return response(
                    $file,
                    200,
                    [
                        'Content-Type' => $fileData['mimeType'],
                        'Content-Disposition' => 'attachment; filename='.$fileData['fileName']
                    ]);
            }
            else
                return 'file not found';
        }
        return 'file not found';
    }
}