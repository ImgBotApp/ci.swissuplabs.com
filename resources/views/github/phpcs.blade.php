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

        <div class="container">
            @foreach ($results as $title => $text)
                <h3>{{ $title }}</h3>
                <pre class="mb0">{{ ($text) ? $text : "OK" }}</pre>
            @endforeach
        </div>

        @include('analytics')
    </body>
</html>
