<?php

namespace DavinBao\LaravelLogViewer;

use Exception;
use Illuminate\Support\Facades\File;
use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * Log Viewer Class
 *
 * @class  LogViewer
 */
class LogViewer {

    /**
     * @var string file
     */
    private static $file;
    private static $levels_classes = [
        'debug' => 'info',
        'info' => 'info',
        'notice' => 'info',
        'warning' => 'warning',
        'error' => 'danger',
        'critical' => 'danger',
        'alert' => 'danger',
        'emergency' => 'danger',
    ];
    private static $levels_imgs = [
        'debug' => 'info',
        'info' => 'info',
        'notice' => 'info',
        'warning' => 'warning',
        'error' => 'warning',
        'critical' => 'warning',
        'alert' => 'warning',
        'emergency' => 'warning',
    ];
    const MAX_FILE_SIZE = 52428800;

    /**
     * @param string $file
     */
    public static function setFile($file)
    {
        $file = self::pathToLogFile($file);
        if (File::exists($file)) {
            self::$file = $file;
        }
    }
    public static function pathToLogFile($file)
    {
        $logsPath = app('config')->get('laravellogviewer.log_path');
        if (File::exists($file)) { // try the absolute path
            return $file;
        }
        $file = $logsPath . '/' . $file;
        // check if requested file is really in the logs directory
        if (dirname($file) !== $logsPath) {
            throw new \Exception('No such log file');
        }
        return $file;
    }
    /**
     * @return string
     */
    public static function getFileName()
    {
        return basename(self::$file);
    }
    /**
     * @return array
     */
    public static function all()
    {
        $log = array();
        $log_levels = self::getLogLevels();
        $dateTimePatten = config('laravellogviewer.datetime_pattern');

        $pattern = '/'.$dateTimePatten.'.*/';
        if (!self::$file) {
            $log_file = self::getFileList();
            if(!count($log_file)) {
                return [];
            }
            reset($log_file);
            self::$file = current($log_file);
        }
        if (File::size(self::$file) > self::MAX_FILE_SIZE) return null;
        $initMemoryUsage = memory_get_usage();
        $file = File::get(self::$file);
        preg_match_all($pattern, $file, $headings);
        if (!is_array($headings)) return $log;
        $log_data = preg_split($pattern, $file);
        if ($log_data[0] < 1) {
            array_shift($log_data);
        }
        foreach ($headings as $h) {
            for ($i=0, $j = count($h); $i < $j; $i++) {
                foreach ($log_levels as $level_key => $level_value) {
                    if (strpos(strtolower($h[$i]), '.' . $level_value)) {
                        preg_match('/^'.$dateTimePatten.'.*?(\w+)\.' . $level_key . ': (.*?)( in .*?:[0-9]+)?$/', $h[$i], $current);
                        if (!isset($current[3])) continue;
                        $currentMemoryUsage = memory_get_usage();
                        if($currentMemoryUsage - $initMemoryUsage >= 134217728){
                            return null;
                        }
                        $log[] = array(
                            'context' => $current[2],
                            'level' => $level_value,
                            'level_class' => self::$levels_classes[$level_value],
                            'level_img' => self::$levels_imgs[$level_value],
                            'date' => $current[1],
                            'text' => $current[3],
                            'in_file' => isset($current[4]) ? $current[4] : null,
                            'stack' => preg_replace("/^\n*/", '', $log_data[$i])
                        );
                    }
                }
            }
        }
        return array_reverse($log);
    }
    /**
     * @param bool $basename
     * @return array
     * [
     *   {'2016-08-01': []},
     *   {'2016-08-02': []}
     * ]
     */
    public static function getFileList($basename = false){
        $files = glob(storage_path() . '/logs/*');
        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');
        if ($basename && is_array($files)) {
            foreach ($files as $k => $file) {
                $files[$k] = basename($file);
            }
        }
        return array_values($files);
    }

    public static function getFiles($basename = false)
    {
        $fileList = static::getFileList($basename);
        $result = [];

        $pattern = '/-\d{4}-\d{2}-\d{2}/';
        foreach($fileList as $file){
            $matches = [];
            if(preg_match($pattern, $file, $matches) == 1) {
                $key = current($matches);
                if(!isset($result[$key])){
                    $result[$key] = [];
                }
                $fileKey = str_replace($key . '.log', '', $file);

                $result[$key][$fileKey] = $file;
            }else{
                $result['global'][$file] = $file;
            }
        }
        krsort($result);

        return $result;
    }

    /**
     * Delete file
     * @param $file
     * @return mixed
     * @throws Exception
     */
    public static function deleteFile($file){
        return File::delete(static::pathToLogFile($file));
    }
    /**
     * @return array
     */
    private static function getLogLevels()
    {
        $class = new ReflectionClass(new LogLevel);
        return $class->getConstants();
    }
}