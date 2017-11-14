<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recent Activities / Dashboard</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}"/>
</head>
<body class="pt2">
    <div class="container">
        <div class="row mb3">
            <div class="col col-8">
                <a href="{{ url('dashboard/activity') }}" class="button round mb1" role="button">Activity</a>
                <a href="{{ url('dashboard/trailing-commits') }}" class="button round outline mb1" role="button">Trailing Commits</a>
                <a href="{{ url('dashboard/changelog') }}" class="button round outline mb1" role="button">Changelog</a>
                <a href="{{ url('dashboard/releases') }}" class="button round outline mb1" role="button">Releases</a>
            </div>
            <div class="col col-4">
                <form class="form">
                    <div class="form-item">
                        <input type="text" class="search" title="Search">
                    </div>
                </form>
            </div>
        </div>

        <h1 class="text-center">Recent Activities</h1>

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="message error w50 w100-sm push-center" data-component="message">
                    {{ $error }}
                </div>
            @endforeach
        @endif

        <table class="bordered mt2">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Latest Activity<span class="caret down"></span></th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($repositories as $repository)
                <tr>
                    <td>{{ $repository->owner }} / {{ $repository->name }}</td>
                    <td>
                        <a href="#" data-component="dropdown" data-target="#project-{{ $repository->id }}">
                            {{ \Carbon\Carbon::parse($repository->commits[0]->created_at)->diffForHumans() }}<span class="caret down"></span>
                        </a>
                        <div class="dropdown hide p2" id="project-{{ $repository->id }}">
                            <a href="" class="close show-sm"></a>
                            <h6 class="mb1">Recent activities:</h6>
                            <ul>
                                @foreach ($repository->commits as $commit)
                                    <li class="py1 smaller">
                                        <b>{{ $commit->created_at }}</b><br/>
                                        {{ $commit->data['message'] }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </td>
                    <td><span class="label success outline">Success</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
