<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>API documentation</title>
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('img/favicon.png')}}"> 
</head>
<body>
    <div id="app">
        <v-app>
            <documentation-page> </documentation-page>
        </v-app>
    </div>
    <script src="{{ asset('js/manifest.js') }}"></script>
    <script src="{{ asset('js/vendor.js') }}"></script>
    <script src="{{ asset('js/documentation_page.js?v=3') }}"></script>
</body>
</html>