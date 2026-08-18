<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <!-- export routes for ziggy js -->
    @routes
    <!-- Load Vite assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Inertia head manager -->
    @inertiaHead
  </head>
  <body class="antialiased">
    <!-- Where Vue mounts -->
    @inertia
  </body>
</html>