<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Open Graph Meta-->
    <title>SysSoft Integra - Cpe Sunat</title>
    <meta name="description" content="Sistema de ventas">

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="images/icon.ico">
    <link rel="stylesheet" type="text/css" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="./css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="./css/sweetalert.min.css">
    <link rel="stylesheet" type="text/css" href="./css/select2.min.css">
</head>

<body class="app sidebar-mini">
    <!-- Navbar-->
    <header class="app-header">
        <a class="app-header__logo" href="index.php">SysSoft Integra</a>
        <!-- Sidebar toggle button-->
        <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
        <!-- Navbar Right Menu-->
    </header>

    <!-- Sidebar menu-->
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
        <div class="app-sidebar__user">
            <div class="m-2">
                <img class="img-fluid" src="./images/logo.png" alt="User Image">
            </div>
        </div>
    </aside>

    <main class="app-content">
        @yield('content')
    </main>

    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/plugins/pace.min.js"></script>
    <script src="js/plugins/bootstrap-notify.min.js"></script>
    <script src="js/plugins/sweetalert.min.js"></script>
    <script src="js/plugins/chart.js"></script>
    <script src="js/tools.js"></script>
    <script src="js/plugins/select2.min.js"></script>

    @yield('script')
</body>

</html>
