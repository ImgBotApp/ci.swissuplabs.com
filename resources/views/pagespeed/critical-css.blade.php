<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Critical CSS</title>

        <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}"/>
    </head>
    <body class="pt4">
        <div class="container text-center">
            <h1 class="title">
                Critical CSS
            </h1>
            <p class="large muted">
                Generate critical css styles for your website
            </p>

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="message error w50 w100-sm push-center" data-component="message">
                        {{ $error }}
                    </div>
                @endforeach
            @endif

            <form id="critical-css" method="get" action="{{ action('Pagespeed\CriticalCssController@generate') }}" class="form">
                <div class="form-item">
                    <div class="message focus fixed" data-component="message">
                        Made by <a href="https://swissuplabs.com" title="Magento extensions and themes">swissuplabs.com</a>
                        team for <a href="https://docs.swissuplabs.com/m1/extensions/pagespeed/" title="Pagespeed module for Magento">pagespeed</a>
                        users.
                    </div>
                    <div class="append w50 w100-sm push-center">
                        <input type="url" name="website" placeholder="https://example.com" required/>
                        <button id="submit" class="button outline" type="submit">Generate</button>
                    </div>
                </div>
            </form>
        </div>

        <script>
            var form = document.getElementById('critical-css'),
                button = document.getElementById('submit');

            form.addEventListener('submit', function() {
                button.classList.add('spinner');
                button.disabled = true;
            });
        </script>

        @include('analytics')
    </body>
</html>
