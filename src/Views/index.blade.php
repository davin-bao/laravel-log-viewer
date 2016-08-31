<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>Log Viewer</title>

    <link rel="stylesheet" href="{{ url($route_prefix.'/assets/css/bootstrap.min') }}" />
    <link rel="stylesheet" href="{{ url($route_prefix.'/assets/css/bootstrap-theme.min') }}" />
    <link rel="stylesheet" href="{{ url($route_prefix.'/assets/css/dataTables.bootstrap') }}" />
    <link rel="stylesheet" href="{{ url($route_prefix.'/assets/css/site') }}" />

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="{{ url($route_prefix.'/assets/javascript/html5shiv.min') }}"></script>
    <script src="{{ url($route_prefix.'/assets/javascript/respond.min') }}"></script>
    <![endif]-->
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-3 col-lg-2 sidebar">
            <h1><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> {{ trans('LaravelLogViewer::log.title') }}</h1>
            <p class="text-muted"><i></i></p>
            <div class="panel-group" id="menu" role="tablist" aria-multiselectable="true">
                @foreach($files as $key=>$fileList)
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="headingOne">
                        <h4 class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#menu" href="#{{ $key }}" aria-expanded="{{ in_array($current_file, $fileList) ? 'true' : 'false' }}" class="{{ in_array($current_file, $fileList) ? '' : 'collapsed' }}" aria-controls="{{ $key }}">
                                {{ str_limit($key, 30) }}
                            </a>
                        </h4>
                    </div>
                    <div id="{{ $key }}" class="panel-collapse collapse {{ in_array($current_file, $fileList) ? 'in' : '' }}" aria-expanded="{{ in_array($current_file, $fileList) ? 'true' : 'false' }}" role="tabpanel" aria-labelledby="headingOne">
                        <div class="panel-body">
                            <div class="list-group">
                                @foreach($fileList as $fileKey=>$file)
                                    <a href="?view={{ base64_encode($file) }}" class="list-group-item @if ($current_file == $file) llv-active @endif">
                                        {{ str_limit($fileKey, 36) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
        <div class="col-sm-9 col-md-9 col-lg-10 table-container">
            <h3>{{ $current_file }}</h3>
            @if ($logs === null)
                <div>
                    {{ trans('LaravelLogViewer::log.download_tip') }}
                </div>
            @else
                <table id="table-log" class="table table-striped">
                    <thead>
                    <tr>
                        <th class="level">{{ trans('LaravelLogViewer::log.level') }}</th>
                        <th class="context">{{ trans('LaravelLogViewer::log.context') }}</th>
                        <th class="date">{{ trans('LaravelLogViewer::log.date') }}</th>
                        <th class="content">{{ trans('LaravelLogViewer::log.content') }}</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($logs as $key => $log)
                        <tr>
                            <td class="text-{{ $log['level_class'] }}"><span class="glyphicon glyphicon-{{ $log['level_img'] }}-sign" aria-hidden="true"></span> &nbsp;{{$log['level']}}</td>
                            <td class="text">{{ $log['context'] }}</td>
                            <td class="date">{{ $log['date'] }}</td>
                            <td class="text">
                                @if ($log['stack']) <a class="pull-right expand btn btn-default btn-xs" data-display="stack{{ $key }}"><span class="glyphicon glyphicon-search"></span></a>@endif
                                {!! str_replace(PHP_EOL, '<br/>', $log['text']) !!}
                                @if (isset($log['in_file'])) <br />{{$log['in_file']}}@endif
                                @if ($log['stack']) <div class="stack" id="stack{{$key}}" style="display: none; white-space: pre-wrap;">{{ trim($log['stack']) }}</div>@endif
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            @endif
            <div>
                <a href="?download={{ base64_encode($current_file) }}"><span class="glyphicon glyphicon-download-alt"></span>{{ trans('LaravelLogViewer::log.download_file') }}</a>
                -
                <a id="delete-log" href="?delete={{ base64_encode($current_file) }}"><span class="glyphicon glyphicon-trash"></span>{{ trans('LaravelLogViewer::log.remove_file') }}</a>
            </div>
        </div>
    </div>
</div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->

<script type="text/javascript" src="{{ url($route_prefix.'/assets/javascript/jquery.1.11.3.min') }}"></script>
<script type="text/javascript" src="{{ url($route_prefix.'/assets/javascript/bootstrap.min') }}"></script>
<script type="text/javascript" src="{{ url($route_prefix.'/assets/javascript/jquery.dataTables.min') }}"></script>
<script type="text/javascript" src="{{ url($route_prefix.'/assets/javascript/dataTables.bootstrap') }}"></script>

<script>
    $(document).ready(function(){
        $('#table-log').DataTable({
            "order": [ 3, 'desc' ],
            "stateSave": true,
            "stateSaveCallback": function (settings, data) {
                window.localStorage.setItem("datatable", JSON.stringify(data));
            },
            "stateLoadCallback": function (settings) {
                var data = JSON.parse(window.localStorage.getItem("datatable"));
                if (data) data.start = 0;
                return data;
            }
        });
        $('.table-container').on('click', '.expand', function(){
            $('#' + $(this).data('display')).toggle();
        });
        $('#delete-log').click(function(){
            return confirm('{{ trans('LaravelLogViewer::log.remove_file_confirm') }}');
        });
    });
</script>
</body>
</html>