<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>PHP CodeSniffer Result for {{ $repository }}/commit/{{ $sha }}</title>

        <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}"/>
    </head>
    <body>
        <pre class="mb0">
            {{ $text }}
        </pre>

        @include('analytics')
    </body>
</html>
