<?php
require_once('../utils/redirect.php');

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if(!isset($_SESSION['user_id'])) {
  redirect('./login/login.php');
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kingsoft Admin</title>
  <link rel="shortcut icon" type="image/png" href="./assets/images/logos/favicon.ico" />
  <link rel="stylesheet" href="./assets/css/styles.min.css" />
  <link rel="stylesheet" href="./assets/css/app.css">
  <script defer src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://kit.fontawesome.com/f95e1afe0c.js" crossorigin="anonymous"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@caneara/iodine@8.5.0/dist/iodine.min.umd.js"></script>
  <script src="https://kit.fontawesome.com/f95e1afe0c.js" crossorigin="anonymous"></script>
  <script src="assets/libs/moment.min.js"></script>
  <script src="assets/js/chart.js"></script>
  <script src="./assets/js/app.js"></script>
  <script src="./assets/js/store.js"></script>
  <script src="./assets/js/clients.js"></script>
</head>
<script>
  async function addPayment(e) {
    try {
          const formData = new FormData(e.target)
          const res = await axios.post(`../api/payments/add_payment.php`, formData)
          if(!res.data?.payment_id) {
              throw new Error('Uncaught error adding payment')
          }
          return res.data
      } catch (error) {
          throw new Error(error?.response?.data ?? error);
      }
  }
</script>
<body>
  <!--  Body Wrapper -->

  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <aside class="left-sidebar"  x-data="{page: new URLSearchParams(window.location.search).get('page')}">
      <!-- Sidebar scroll-->
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="../index.php" class="text-nowrap logo-img">
            <img src="./assets/images/logos/Kingsoft_Logo.png" width="180" height="100px" alt="" />
          </a>
          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
          </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
            <li class="nav-small-cap">
              <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
              <span class="hide-menu">Home</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" :class="(!page || page == 'dashboard') && 'active'" href="index.php?page=dashboard" aria-expanded="false">
                <span>
                  <i class="ti ti-layout-dashboard"></i>
                </span>
                <span class="hide-menu">Dashboard</span>
              </a>
            </li>
            <li class="sidebar-item mt-3">
              <a class="sidebar-link" href="index.php?page=analytics" aria-expanded="false" :class="(page == 'analytics') && 'active'">
                <span>
                <i class="fa-solid fa-chart-simple"></i>
                </span>
                <span class="hide-menu">Analytics</span>
              </a>
            </li>
            <li class="sidebar-item mt-3">
              <a class="sidebar-link" href="index.php?page=clients" aria-expanded="false" :class="(page == 'clients') && 'active'">
                <span>
                <i class="fa-solid fa-user"></i>
                </span>
                <span class="hide-menu">Clients</span>
              </a>
            </li>
            <li class="sidebar-item mt-3">
              <a class="sidebar-link" href="login/logout.php" aria-expanded="false">
                <span>
                <i class="fa-solid fa-right-from-bracket"></i>
                </span>
                <span class="hide-menu">Logout</span>
              </a>
            </li>
          </ul>
        </nav>
        <!-- End Sidebar navigation -->
      </div>
      <!-- End Sidebar scroll-->
    </aside>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <header class="app-header">
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
              <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                <i class="ti ti-menu-2"></i>
              </a>
            </li>
          </ul>
          <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
              <?php echo "Hello " . $_SESSION['username'];?>
            </ul>
          </div>
        </nav>
      </header>
      <!--  Header End -->
      <div class="container-fluid" x-data="{clientSearch: ''}">

        <?php
          if(!isset($_GET['page']) || $_GET['page'] == 'dashboard') {
            require_once('pages/dashboard.php');
          } else {
            require_once('pages/analytics.php');
          } else if(!isset($_GET['page']) || $_GET['page'] == 'analytics') {
            require_once('pages/analytics.php');
          } else if(!isset($_GET['page']) || $_GET['page'] == 'clients') {
            require_once('pages/clients.php');
          }
        ?>

        <!-- footer credits -->
        <div class="py-6 px-6 text-center">
          <p class="mb-0 fs-4">Kingsoft Company Limited @ <a href="../" class="pe-1 text-primary text-decoration-underline">Kingsoft.biz</a></p>
        </div>
      </div>
    </div>
  </div>
  <script src="./assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="./assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="./assets/js/sidebarmenu.js"></script>
  <script src="./assets/js/app.min.js"></script>
</body>

</html>