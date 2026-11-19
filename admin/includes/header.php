<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Path Creation Academy - Admin Dashboard" />
        <meta name="author" content="" />
        <title>Dashboard - Path Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <!-- Reference UI Admin Theme -->
        <link href="css/admin-theme.css?v=<?= file_exists(__DIR__ . '/../css/admin-theme.css') ? filemtime(__DIR__ . '/../css/admin-theme.css') : '1.0' ?>" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Chart.js CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- Summernote CSS - CDN Link -->
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
        <!-- //Summernote CSS - CDN Link -->
    </head>

    <body class="sb-nav-fixed">


       <?php  include('includes/navbar-top.php') ?>

       <div id="layoutSidenav">
         
          <?php  include('includes/sidebar.php') ?>

          <div id="layoutSidenav_content">
                <main>
