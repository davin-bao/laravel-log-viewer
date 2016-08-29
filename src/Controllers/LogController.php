<?php namespace DavinBao\LaravelLogViewer\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use DavinBao\LaravelLogViewer\LogViewer;

/**
 * Class LogController
 * @package DavinBao\LaravelLogViewer\Controllers
 *
 * @author davin.bao
 * @since 2016.8.18
 */
class LogController extends BaseController
{

    public function index(Request $request){

        if ($request->get('view')) {
            LogViewer::setFile(base64_decode($request->get('view')));
        }

        if ($request->get('download')) {
            return Response::download(LogViewer::pathToLogFile(base64_decode($request->get('download'))));
        } elseif ($request->has('delete')) {
            LogViewer::deleteFile(base64_decode($request->get('delete')));
            return Redirect::to($request->url());
        }
        $logs = LogViewer::all();

        return view('LaravelLogViewer::index', [
            'route_prefix' => config('laravellogviewer.route_prefix'),
            'logs' => $logs,
            'files' => LogViewer::getFiles(true),
            'current_file' => LogViewer::getFileName()
        ]);
    }
}
