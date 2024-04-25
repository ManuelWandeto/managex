<?php

require_once("utils/redirect.php");
 
require_once("db/db.inc.php");
require_once("db/queries.inc.php");
require_once ("utils/load_env.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$download_id = isset($_GET['download_id']) ? $_GET['download_id'] : NULL;
$download = $pdo_conn->query("SELECT * FROM downloads WHERE id = $download_id;")->fetch(PDO::FETCH_ASSOC);
if(empty($download)) {
    redirect('index.php?error=download+not+found');
}
if(!$download_id) {
    redirect('index.php?error=download+id+not+given');
}
$megaUser = ["username" => $_ENV['MEGA_USERNAME'], "password" => $_ENV['MEGA_PASSWORD']];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Managex Download</title>
    <meta name="description"
        content="managex for SMEs featuring inventory, order, CRM, accounting, analytics and more.">
    <meta name="keywords"
        content="erp software, inventory management software, order management system, accounting software, analytics, dashboard, crm, small business software, smb software, manage inventory, purchase orders, sales orders, invoicing, financial reporting, profit and loss, accounts payable, accounts receivable, contact management, pipeline management, marketing automation, forecasts, pos integration, payroll">
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,700">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles-merged.min.css">
    <link rel="stylesheet" href="css/template-style.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/checkout.css">
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"
    ></script>
    <script src="js/app.js"></script>
    <script src="https://kit.fontawesome.com/f95e1afe0c.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="js/vendor/main.browser-umd.js"></script>
    <script src="js/download.js"></script>
</head>
<script>

    const mgxDefaultVersion = <?php echo json_encode($_SESSION['default_version']); ?>;
    const downloadId = <?php echo json_encode($download_id); ?>;
    const megaUser = <?php echo json_encode($megaUser); ?>;

</script>
<body>
    <nav class="navbar navbar-expand-lg fixed-top probootstrap-megamenu navbar-light probootstrap-navbar py-3" style="box-shadow: none;">
        <div class="container">
            <a class="navbar-brand mx-auto" href="index.php" title="Kingsoft" 
                style="background-image: url('img/mgx_logo.png'); background-size: contain; width: 200px;"></a>
        </div>
    </nav>


    <div class="container mt-5">
        <div class="row mb-3 justify-content-center">
            <div class="col-md-6 col-lg-8 pb-5 card align-items-center justify-content-center px-4" x-data="{downloadLoading: false}">
                <dotlottie-player src="https://lottie.host/fad7868a-fe30-449e-a16e-5cae26c9eb4f/haWLqbDK3w.json" background="transparent" speed="1" style="width: 100%; height: 300px;" loop autoplay></dotlottie-player>
                
                <div><span class="loader" x-show="downloadLoading" x-cloak x-transition></span></div>
                
                <a class="btn btn-primary position-relative mt-3" x-data="{progress: null}" @download-progress.window="()=>{
                    progress = $event.detail
                }" href="#" @click="()=>{
                    downloadLoading = true
                    downloadManagex(mgxDefaultVersion.link, megaUser).then(() => {
                        updateDownloadStatus(downloadId).catch(e => console.error(e))
                    })
                }" style="background-color: #C3EE95;" @download-started.window="downloadLoading = false"
                >
                    <span style="position: relative; z-index: 1000;">
                        Download <span x-data x-text="mgxDefaultVersion.full_name"></span>
                        <span class="ml-2" x-show="progress" x-cloak x-text="`(${Math.round(progress?.percentComplete)}%)`"></span>
                    </span>
                    <div class="overlay position-absolute" style="top: 0; bottom: 0; left: 0; background-color: #7ed321; display: flex; justify-content: center; align-items: center;" :style="{width: `${progress?.percentComplete || 0}%`}"></div>
                </a>
                <a href="#" data-toggle="modal" data-target="#change-log-modal" class="mt-2">View Changelog</a>
                <a href="#" data-toggle="modal" data-target="#tutorialModal" class="mt-2">View Installation tutorial</a>

            </div>
        </div>
    </div>
    <div class="modal fade" id="change-log-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span x-data x-text="mgxDefaultVersion.full_name.replace(/\.[^/.]+$/, '');"></span> Changelog</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p x-data x-text="mgxDefaultVersion.upgrade_info"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
    </div>
    <?php require_once('views/tutorial_modal.php') ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script> 
</body>

</html>