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
    <nav class="navbar navbar-default custom-color navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="#"><br></a>
            </div>

            <ul class="nav navbar-nav navbar-right" style="padding: 5px;">
                <li><a href="#"> DSMail</a></li>
                <li><a href="#"> Gambar</a></li>
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

    <div class="col-xs-12" style="margin-top: 80px;">
        <div class="row">
            <div class="container content-box">
                <p align="center">
                    <span class="glyphicon glyphicon-search" style="font-size: 80px;"></span>
                    <br>
                    <span style="font-size: 25px;">DreamSearch</span>
                </p>
                <form method="GET" action="{{url('/search')}}" id="form-konten">
                    <input type="text" name="keyword" id="keyword" placeholder="Type here..." class="form-control" style="padding: 20px;">

                    <p align="center" style="margin-top: 20px;">
                        <button type="submit" id="submit-button" class="btn btn-default custom-button" disabled>
                            DreamSearch
                        </button>

                        <input type="reset" class="btn btn-default custom-button" value="Reset">
                    </p>
                </form>

                <p align="center">
                    DreamSearch offered in : <a href="#">Indonesia</a>
                </p>

            </div>
        </div>
    </div>

    <div class="footer">
        <p>Indonesia</p>
    </div>

    <script>
        $(document).ready(
            function() {
                $('#keyword').keypress(
                    function() {
                        var value = document.getElementById('keyword').value;
                        if (value.length > 0) {
                            $('#submit-button').prop("disabled", false);
                        }
                 });

                $('#keyword').keydown(
                    function() {
                        var value = document.getElementById('keyword').value;
                        if (value.length < 2) {
                            $('#submit-button').prop("disabled", true);
                        }
                 });
        });
    </script>

    </body>
</html>