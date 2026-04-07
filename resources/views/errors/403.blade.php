<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>403 Access Denied</title>
    <link rel="stylesheet" href="{{ asset('admin/assets/css/app.css') }}">
</head>
<body class="bg-white">
    <div class="container ">
        <div class="row justify-content-center d-flex align-items-center" style="height: 100vh;">
            <div class="col-md-6">
                <div class="card-1">
                    <div class="card-body">
                        <h1 class="card-title text-center" style="font-size: 50px;">403</h1>
                        <p class="card-text text-center m-0">
                            {{ $exception->getMessage() ?: 'Forbidden' }}
                        </p>
                        <p class="text-muted text-center">Please contact the Super Admin if you believe this is an error.</p>
                        <a href="{{ url()->previous() }}" class="btn btn-primary d-flex justify-content-center w-25 text-center mx-auto">Go Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
