<html>
<head>
    <title>@yield('title')</title>
    <link a href="{{asset('public/assets/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <script src="{{asset('public/js/jquery-2.0.3.min.js')}}"></script>
    <script src="{{asset('public/assets/bootstrap/js/bootstrap.min.js')}}"></script>
</head>
<style>
    body {
        font-family: sans-serif;
    }

    .content-box {
        padding: 10%;
    }

    .custom-button {
        min-width: 150px;
        margin: 5px;
    }

    .custom-color {
        background: white;
        border: none;
    }

    .footer {
        position: fixed;
        left: 0;
        bottom: 0;
        width: 100%;
        padding: 8px;
        background-color: #f2f2f2;
    }
</style>
<body>
<nav class="navbar navbar-default navbar-fixed-top" style="padding: 20px;">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="{{url('/')}}">
                <span class="glyphicon glyphicon-search" style="font-size: 20px;"></span>
                <span style="font-size: 20px;">DreamSearch</span>
            </a>
        </div>

        <ul class="nav navbar-nav">
            <li>
                <form method="GET" action="{{url('/search')}}" id="form-konten">
                    <input type="text" name="keyword" id="keyword" value="{{$keyword}}" placeholder="Type here..." class="form-control" style="padding: 20px; width: 450px;">
                </form>
            </li>
        </ul>

        <ul class="nav navbar-nav navbar-right" style="padding: 5px;">
            <li>
                <a href="#">
                    <span class="glyphicon glyphicon-th"></span>
                </a>
            </li>
            <li></li>
            <li>
                <img src="{{asset('public/img/profile.png')}}" style="padding: 1px" width="35" height="35" class="img-circle">
            </li>
        </ul>
    </div>
</nav>

<br><br>
<div class="col-xs-12" style="margin-top: 100px;">
    <div class="row">
        <div class="container">
            @if(count($data) == 0)
                <p>
                    Your search <b>{{$keyword}}</b> - did not match any documents.
                    <br>
                    Suggestions:
                    <li>Make sure that all words are spelled correctly.</li>
                    <li>Try different keywords.</li>
                    <li>Try more general keywords.</li>
                </p>
            @else
                @foreach($data as $num => $item)
                    <div class="row">
                        <div class="col-xs-12">
                            <h4>
                                <a href="{{$item['url']}}">
                                    {{$item['title']}}
                                </a>
                            </h4>
                            <span style="color: green;">{{$item['url']}}</span>
                            <p align="justify">
                                {{substr($item['content'], 0, 300)}} ...
                            </p>
                        </div>
                    </div>
                    <br>
                @endforeach
            @endif
        </div>
    </div>
</div>
</body>
</html>