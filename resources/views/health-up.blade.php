<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=5.0">
    <title>{{ config('app.name', 'Application') }}</title>
    <style type="text/css">
        *{box-sizing:border-box}
        body{font-size:16px;line-height:1.25;margin:0;padding:0;background-color:black;color:white}
        main{width:100vw;height:100vh;display:flex;justify-content:center;align-items:center}
        .ui-wrap{margin:0 auto;width:calc(100% - 2rem);max-width:max-content}
        .ui-text{font-family:Verdana,sans-serif;padding:1rem;border-radius:1.5rem;background-color:darkslategray}
        small{font-size:0.6rem}
        p{padding:0 2.15rem;margin:0.5rem 0 0}
        h1{font-size:2rem;margin:0 0 0.85rem;padding-right:2.15rem}
        h1 small{display:block;font-size:1rem;padding-left:2.15rem}
        h1 span:first-child{color:lime}
        [data-state="issues"] h1 span:first-child{color:orangered}
    </style>
</head>
<body>
<main data-state="{{ $exception ? 'issues' : 'running' }}">
    <div class="ui-wrap">
        <div class="ui-text">
            <h1>
                <span>{{ $exception ? '✗' : '✓' }}</span>
                <span>{{ config('app.name', 'Application') }}</span>
                <small>{{ $exception ? 'experiencing problems' : 'running' }}</small>
            </h1>
            <p>HTTP request received.</p>
            @if(defined('LARAVEL_START'))
                <p><small>Response rendered in {{ round((microtime(true) - LARAVEL_START) * 1000) }}ms.</small></p>
            @endif
        </div>
    </div>
</main>
</body>
</html>
