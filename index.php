<?php
require_once 'php/languageSetting.php';

if(!isset($_SESSION['userID'])){
  echo '<script type="text/javascript">';
  echo 'window.location.href = "login.html";</script>';
}
else{
  $user = $_SESSION['userID'];
  $stmt = $db->prepare("SELECT * from users where id = ?");
	$stmt->bind_param('s', $user);
	$stmt->execute();
	$result = $stmt->get_result();
  $role = 'NORMAL';
  $name = '';
  $language = 'en';
	
	if(($row = $result->fetch_assoc()) !== null){
    $role = $row['role_code'];
    $name = $row['name'];
    $language = $row['languages'];
  }

  $_SESSION['language'] = $language;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>DG Link</title>

  <link rel="icon" href="assets/logo.png" type="image">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- IonIcons -->
  <link rel="stylesheet" href="http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <!-- daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck for checkboxes and radio inputs -->
  
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Bootstrap Color Picker -->
  <link rel="stylesheet" href="plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- Bootstrap4 Duallistbox -->
  <link rel="stylesheet" href="plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
  <!-- Toastr -->
  <link rel="stylesheet" href="plugins/toastr/toastr.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css?v=3.2.0">
  
  <style>
    body {
      background: #eee;
      font-family: Assistant, sans-serif
    }
  
    .cell-1 {
      border-collapse: separate;
      border-spacing: 0 4em;
      background: #ffffff;
      border-bottom: 5px solid transparent;
      background-clip: padding-box;
      cursor: pointer
    }
  
    thead {
      /* background: #dddcdc */
      background-color: #007bff; 
      color:white;
    }
  
    .table-elipse {
      cursor: pointer
    }
  
    .expand-body {
      -webkit-transition: all 0.3s ease-in-out;
      -moz-transition: all 0.3s ease-in-out;
      -o-transition: all 0.3s 0.1s ease-in-out;
      transition: all 0.3s ease-in-out
    }
  
    .row-child {
      background-color: #000;
    }

    /*.hidden {
      display: none !important;
    }*/

    div.loading{
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(16, 16, 16, 0.5);
      z-index: 5;
    }

    @-webkit-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }

    @-webkit-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }

    @-moz-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }

    @-ms-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }

    @-moz-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }

    @-webkit-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }

    @-o-keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }

    @keyframes uil-ring-anim {
      0% {
        -ms-transform: rotate(0deg);
        -moz-transform: rotate(0deg);
        -webkit-transform: rotate(0deg);
        -o-transform: rotate(0deg);
        transform: rotate(0deg);
      }
      100% {
        -ms-transform: rotate(360deg);
        -moz-transform: rotate(360deg);
        -webkit-transform: rotate(360deg);
        -o-transform: rotate(360deg);
        transform: rotate(360deg);
      }
    }

    .uil-ring-css {
      margin: auto;
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
      width: 200px;
      height: 200px;
    }

    .uil-ring-css > div {
      position: absolute;
      display: block;
      width: 160px;
      height: 160px;
      top: 20px;
      left: 20px;
      border-radius: 80px;
      box-shadow: 0 6px 0 0 #ffffff;
      -ms-animation: uil-ring-anim 1s linear infinite;
      -moz-animation: uil-ring-anim 1s linear infinite;
      -webkit-animation: uil-ring-anim 1s linear infinite;
      -o-animation: uil-ring-anim 1s linear infinite;
      animation: uil-ring-anim 1s linear infinite;
    }

    /*.nav-item.has-treeview.menu-is-opening.menu-open {
      background-color: #00501a; /* Replace "your-color" with the desired background color 
    }*/

  </style>
</head>
<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to to the body tag
to get the desired effect
|---------------------------------------------------------|
|LAYOUT OPTIONS | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->
<body class="hold-transition sidebar-mini">
<div class="loading" id="spinnerLoading">
  <div class='uil-ring-css' style='transform:scale(0.79);'>
    <div></div>
  </div>
</div>

<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-primary navbar-light" style="background-color: white;">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars bg-success"></i></a>
      </li>
    </ul>
  </nav>
  <!-- Main Sidebar Container -->
  <!--aside class="main-sidebar sidebar-dark-primary elevation-4"  style="background-color: #ffffff;"-->
  <aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: #3d44c1;">
    <!-- Brand Logo -->
    <!--a href="#" class="brand-link logo-switch" style="line-height: 5;">
      <img src="assets/logoSmall.jpeg" alt="Sneakercube Logo" class="brand-image-xl logo-xs">
      <img src="assets/logoSmall.jpeg" alt="Sneakercube Logo" class="brand-image-xl logo-xl" style="width: 40%;max-height: max-content;">
    </a-->

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image" style="align-self: center;">
            <img src="assets/user-avatar.png" class="img-circle elevation-2" alt="User Image">
          </div>
          <div class="info" style="white-space: nowrap;">
            <p style="font-size:0.75rem; color:#E3E3E3; margin-bottom:0rem; color:#1888CA"><?=$languageArray['welcome_tab'][$language] ?></p>
            <a href="#myprofile" data-file="myprofile.php" id="goToProfile" class="d-block"><?=$name ?></a>
          </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" id="sideMenu" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
            with font-awesome or any other icon font library -->
          <li class="nav-item">
            <a href="#dashboard" data-file="dashboard.php" class="nav-link link">
              <i class="nav-icon fas fa-chart-bar"></i>
              <p><?=$languageArray['dashboard_code'][$language] ?></p>
            </a>
          </li>
          <!--li class="nav-item">
            <a href="#weight" data-file="weightPage.php" class="nav-link link">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p><?=$languageArray['weight_weighing_code'][$language] ?></p>
            </a>
          </li-->
          <li class="nav-item">
            <a href="#billboard" data-file="billboard.php" class="nav-link link">
              <i class="nav-icon fas fa-book"></i>
              <p><?=$languageArray['weighing_report_code'][$language] ?></p>
            </a>
          </li>
          <?php 
              if($role == "ADMIN"){
                echo '<!--li class="nav-item">
                <a href="#users" data-file="users.php" class="nav-link link">
                  <i class="nav-icon fas fa-user"></i>
                  <p>'.$languageArray['staff_code'][$language].'</p>
                </a>
              </li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-database"></i>
                  <p>'.$languageArray['master_data_code'][$language].'<i class="fas fa-angle-left right"></i></p>
                </a>
                <ul class="nav nav-treeview" style="display: none;">
                  <li class="nav-item">
                    <a href="#customers" data-file="customers.php" class="nav-link link">
                      <i class="nav-icon fas fa-book"></i>
                      <p>'.$languageArray['customer_code'][$language].'</p>
                    </a>
                  </li>';
                  /*<li class="nav-item">
                    <a href="#suppliers" data-file="suppliers.php" class="nav-link link">
                      <i class="nav-icon fas fa-book"></i>
                      <p>'.$languageArray['supplier_code'][$language].'</p>
                    </a>
                  </li>*/
                  echo '<li class="nav-item">
                    <a href="#products" data-file="products.php" class="nav-link link">
                      <i class="nav-icon fas fa-shopping-cart"></i>
                      <p>'.$languageArray['product_code'][$language].'</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#vehicles" data-file="vehicles.php" class="nav-link link">
                      <i class="nav-icon fas fa-car"></i>
                      <p>'.$languageArray['vehicle_code'][$language].'</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#transporters" data-file="transport.php" class="nav-link link">
                      <i class="nav-icon fas fa-box"></i>
                      <p>'.$languageArray['transporter_code'][$language].'</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#grades" data-file="grades.php" class="nav-link link">
                      <i class="nav-icon fas fa-balance-scale"></i>
                      <p>'.$languageArray['grade_code'][$language].'</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#farms" data-file="farms.php" class="nav-link link">
                      <i class="nav-icon fas fa-shopping-bag"></i>
                      <p>'.$languageArray['farm_code'][$language].'</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#message" data-file="message.php" class="nav-link link">
                      <i class="nav-icon fas fa-comments"></i>
                      <p>'.$languageArray['message_resource_code'][$language].'</p>
                    </a>
                  </li>
                </ul>
              </li-->';
                  /*<li class="nav-item">
                    <a href="#groups" data-file="groups.php" class="nav-link link">
                      <i class="nav-icon fas fa-store"></i>
                      <p>Groups</p>
                    </a>
                  </li>*/
                
              }
          ?>
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-cogs"></i>
              <p><?=$languageArray['setting_code'][$language] ?><i class="fas fa-angle-left right"></i></p>
            </a>
        
            <ul class="nav nav-treeview" style="display: none;">
              <?php 
                if($role == "ADMIN"){
                  echo '<!--li class="nav-item">
                  <a href="#company" data-file="company.php" class="nav-link link">
                    <i class="nav-icon fas fa-building"></i>
                    <p>'.$languageArray['company_profile_code'][$language].'</p>
                  </a>
                </li-->';
                }
              ?>

              <!--li class="nav-item">
                <a href="#myprofile" data-file="myprofile.php" class="nav-link link">
                  <i class="nav-icon fas fa-id-badge"></i>
                  <p><?=$languageArray['profile_code'][$language] ?></p>
                </a>
              </li-->
          
              <li class="nav-item">
                <a href="#changepassword" data-file="changePassword.php" class="nav-link link">
                  <i class="nav-icon fas fa-key"></i>
                  <p><?=$languageArray['change_password'][$language] ?></p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="php/logout.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p><?=$languageArray['logout_code'][$language] ?></p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" id="mainContents">
    
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2024 <a href="#">SyncWeight</a>.</strong>All rights reserved.<div class="float-right d-none d-sm-inline-block"><b>Version</b> 1.0.0 </div>
  </footer>
</div>
<!-- ./wrapper -->
<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/jquery-validation/jquery.validate.min.js"></script>
<!-- Bootstrap -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="dist/js/adminlte.js"></script>
<!-- OPTIONAL SCRIPTS -->
<script src="plugins/select2/js/select2.full.min.js"></script>
<script src="plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/inputmask/jquery.inputmask.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="plugins/toastr/toastr.min.js"></script>
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<script src="plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script src="plugins/chart.js/Chart.min.js"></script>
<script src="plugins/sheets/xlsx.full.min.js"></script>

<script>
$(function () {
  toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": false,
    "positionClass": "toast-top-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
  }
  
  $('#sideMenu').on('click', '.link', function(){
      $('#spinnerLoading').hide();
      var files = $(this).attr('data-file');
      $('#sideMenu').find('.active').removeClass('active');
      $(this).addClass('active');
      
      $.get(files, function(data) {
        $('#mainContents').html(data);
        $('#spinnerLoading').hide();
      });
  });

  $('#goToProfile').on('click', function(){
      $('#spinnerLoading').show();
      var files = $(this).attr('data-file');
      $('#sideMenu').find('.active').removeClass('active');
      $(this).addClass('active');
      
      $.get(files, function(data) {
          $('#mainContents').html(data);
          $('#spinnerLoading').hide();
      });
  });
  
  $("a[href='#<?=$_SESSION['page']?>']").click();
});

function formatDate(date) {
  var d = new Date(date),
  month = '' + (d.getMonth() + 1),
  day = '' + d.getDate(),
  year = d.getFullYear();

  if (month.length < 2) 
    month = '0' + month;

  if (day.length < 2) 
    day = '0' + day;

  return [year, month, day].join('-');
}

function formatDate2(date) {
  var d = new Date(date),
  month = '' + (d.getMonth() + 1),
  day = '' + d.getDate(),
  year = d.getFullYear();

  if (month.length < 2) 
    month = '0' + month;

  if (day.length < 2) 
    day = '0' + day;

  return [day, month, year].join('/');
}
</script>
</body>
</html>
