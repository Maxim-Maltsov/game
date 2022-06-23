<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ env('APP_NAME') }}</title>

     

    {{-- Style Bootstrap 5.1 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    

    <!-- Styles Laravel Mix -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

</head>

<body>
    
    @include('layouts.my-header')
   
    <main id="app">
        <div class="container pt-5">

            @yield('content')
            
        </div>
    </main>

    {{-- JS Bootstrap 5.1 --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    
    
    <!-- JS Laravel Mix-->
    <script src="{{ asset('js/app.js') }}" defer></script>
    
</body>

</html>