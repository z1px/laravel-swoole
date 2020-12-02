<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HTTP server configurations.
    |--------------------------------------------------------------------------
    |
    | @see https://www.swoole.co.uk/docs/modules/swoole-server/configuration
    |
    */
    'server' => [
        'host' => env('SWOOLE_HTTP_HOST', '127.0.0.1'),
        'port' => env('SWOOLE_HTTP_PORT', '1215'),
        'public_path' => base_path('public'),
        // Determine if to use swoole to respond request for static files
        'handle_static_files' => env('SWOOLE_HANDLE_STATIC', true),
        'access_log' => env('SWOOLE_HTTP_ACCESS_LOG', false),
        // You must add --enable-openssl while compiling Swoole
        // Put `SWOOLE_SOCK_TCP | SWOOLE_SSL` if you want to enable SSL
        'socket_type' => SWOOLE_SOCK_TCP,
        'process_type' => SWOOLE_PROCESS,
        'opcode' => WEBSOCKET_OPCODE_BINARY,
        'options' => [
            'pid_file' => env('SWOOLE_HTTP_PID_FILE', base_path('storage/logs/swoole_http.pid')),
            'log_file' => env('SWOOLE_HTTP_LOG_FILE', base_path('storage/logs/swoole_http.log')),
            'daemonize' => env('SWOOLE_HTTP_DAEMONIZE', false),
            // Normally this value should be 1~4 times larger according to your cpu cores.
            'reactor_num' => env('SWOOLE_HTTP_REACTOR_NUM', swoole_cpu_num()),
            'worker_num' => env('SWOOLE_HTTP_WORKER_NUM', swoole_cpu_num()),
            'task_worker_num' => env('SWOOLE_HTTP_TASK_WORKER_NUM', swoole_cpu_num()),
            // The data to receive can't be larger than buffer_output_size.
            'package_max_length' => 20 * 1024 * 1024,
            // The data to send can't be larger than buffer_output_size.
            'buffer_output_size' => 10 * 1024 * 1024,
            // Max buffer size for socket connections
            'socket_buffer_size' => 128 * 1024 * 1024,
            // Worker will restart after processing this number of requests
            'max_request' => 3000,
            // Enable coroutine send
            'send_yield' => true,
            // You must add --enable-openssl while compiling Swoole
            'ssl_cert_file' => null,
            'ssl_key_file' => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable to turn on websocket server.
    |--------------------------------------------------------------------------
    */
    'websocket' => [
        'enabled' => env('SWOOLE_HTTP_WEBSOCKET', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Hot reload configuration
    |--------------------------------------------------------------------------
    */
    'hot_reload' => [
        'enabled' => env('SWOOLE_HOT_RELOAD_ENABLE', false),
        'recursively' => env('SWOOLE_HOT_RELOAD_RECURSIVELY', true),
        'directory' => env('SWOOLE_HOT_RELOAD_DIRECTORY', base_path()),
        'log' => env('SWOOLE_HOT_RELOAD_LOG', true),
        'filter' => env('SWOOLE_HOT_RELOAD_FILTER', '.php'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Console output will be transferred to response content if enabled.
    |--------------------------------------------------------------------------
    */
    'ob_output' => env('SWOOLE_OB_OUTPUT', true),

    /*
    |--------------------------------------------------------------------------
    | Pre-resolved instances here will be resolved when sandbox created.
    |--------------------------------------------------------------------------
    */
    'pre_resolved' => [
        'view', 'files', 'session', 'session.store', 'routes',
        'db', 'db.factory', 'cache', 'cache.store', 'config', 'cookie',
        'encrypter', 'hash', 'router', 'translator', 'url', 'log', 'auth',
    ],

    /*
    |--------------------------------------------------------------------------
    | Instances here will be cleared on every request.
    |--------------------------------------------------------------------------
    */
    'instances' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Providers here will be registered on every request.
    |--------------------------------------------------------------------------
    */
    'providers' => [
        Illuminate\Pagination\PaginationServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetters for sandbox app.
    |--------------------------------------------------------------------------
    */
    'resetters' => [
        Z1px\Http\Server\Resetters\ResetConfig::class,
        Z1px\Http\Server\Resetters\ResetSession::class,
        Z1px\Http\Server\Resetters\ResetCookie::class,
        Z1px\Http\Server\Resetters\ClearInstances::class,
        Z1px\Http\Server\Resetters\BindRequest::class,
        Z1px\Http\Server\Resetters\RebindKernelContainer::class,
        Z1px\Http\Server\Resetters\RebindRouterContainer::class,
        Z1px\Http\Server\Resetters\RebindViewContainer::class,
        Z1px\Http\Server\Resetters\ResetProviders::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Define your swoole tables here.
    |
    | @see https://www.swoole.co.uk/docs/modules/swoole-table
    |--------------------------------------------------------------------------
    */
    'tables' => [
        // 'table_name' => [
        //     'size' => 1024,
        //     'columns' => [
        //         ['name' => 'column_name', 'type' => Table::TYPE_STRING, 'size' => 1024],
        //     ]
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Define your swoole timer here.
    |
    | @see https://www.swoole.co.uk/docs/modules/swoole-timer
    |--------------------------------------------------------------------------
    */
    'timer' => [
        'enable' => true, // 启用Timer
        'ticks'   => [ // 注册的定时任务类列表
            [
                // 'interval' => 3000, // 指定时间，单位毫秒
                // 'job' => function(int $timer_id, ...$params) {}, // 时间到期后所执行的函数，必须是可以调用的
                // 'data' => [], // 给执行函数传递数据【此参数也为可选参数】
                // 'worker_id' => null, // 指定任务进程，不指定的话，默认最后一个task进程
            ]
        ],
        'max_wait_time' => 5, // Reload时最大等待时间
    ],
];
