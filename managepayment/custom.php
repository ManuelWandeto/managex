<?php

require_once("../utils/redirect.php");
 
require_once("../db/db.inc.php");
require_once("../db/queries.inc.php");
require_once("../utils/ip_localle.php");
require_once("../utils/parse_url.php");
require_once("../utils/random.php");

if(empty($_GET['email']) && empty($_GET['managex_code'])) {
    redirect("../index.php?error=missing+required+parameters");
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(isset($_SESSION['payment_request'])) {
    unset($_SESSION['payment_request']);
}
$origin = $_ENV['APP_ENV'] == 'development' ? 'http://localhost:3000' : 'https://kingsoft.biz';
// Get the request URI (path and query string)
$request_uri = $_SERVER['REQUEST_URI'];
// Combine all parts to form the full URL
$full_url = $origin . $request_uri;

try {
    $_SESSION['localle'] = getIpLocalle($logger);
    
    $sql = 
        "SELECT * FROM payment_requests WHERE request_email = ? OR request_mgx_code = ? ORDER BY creation_date DESC LIMIT 1;";

    $stmt = $pdo_conn->prepare($sql);
    $pageError = null;
    $stmt->execute([
        !empty($_GET["email"]) ? $_GET["email"] : "-",
        !empty($_GET["managex_code"]) ? $_GET["managex_code"] : "-"
    ]);
    $paymentRequest = $stmt->fetch(PDO::FETCH_ASSOC);
    if(empty($paymentRequest)) {
        $paymentRequest = null;
        $pageError = ["message" => "Payment details for the given parameters not found!", "code" => 404];
    }
    if(!empty($paymentRequest) && strtolower($paymentRequest['is_paid']) == 'yes') {
        $pageError =  ["message" => "You have already made this payment!", "code" => 400];
    }
    
    if(!$pageError) {
        $parsedReferrer = getUrlParts($full_url);
        if(empty($paymentRequest['tracking_id'])) {
            $stmt = $pdo_conn->prepare("UPDATE payment_requests SET tracking_id = ? WHERE request_id = ?;");
            $stmt->execute([getRandomString(40), $paymentRequest['request_id']]);
            $paymentRequest = $pdo_conn->query("SELECT * FROM payment_requests WHERE request_id = {$paymentRequest['request_id']}")->fetch(PDO::FETCH_ASSOC);
        }
        $_SESSION['callback_redirect'] = $parsedReferrer['url'];
        if($parsedReferrer['query']) {
            $_SESSION['callback_redirect_query'] = $parsedReferrer['query'];
        }

        if(!empty($_GET['email'])) {
            $_SESSION['callback_redirect_query']['email'] = $_GET['email'];
        }
        if(!empty($_GET['managex_code'])) {
            $_SESSION['callback_redirect_query']['managex_code'] = $_GET['managex_code'];
        }
    }
} catch (Exception $e) {
    $pageError = ["message" => "Internal Error occured!, please reload the page and try again", "code" => 500];
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Managex Custom Pay</title>
    <meta name="description"
        content="managex for SMEs featuring inventory, order, CRM, accounting, analytics and more.">
    <meta name="keywords"
        content="erp software, inventory management software, order management system, accounting software, analytics, dashboard, crm, small business software, smb software, manage inventory, purchase orders, sales orders, invoicing, financial reporting, profit and loss, accounts payable, accounts receivable, contact management, pipeline management, marketing automation, forecasts, pos integration, payroll">
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,700">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/styles-merged.min.css">
    <link rel="stylesheet" href="../css/template-style.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/checkout.css">
    <script src="https://kit.fontawesome.com/f95e1afe0c.js" crossorigin="anonymous"></script>
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"
    ></script>
    <script src="../js/app.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@caneara/iodine@8.5.0/dist/iodine.min.umd.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="../js/vendor/moment.min.js"></script>
</head>
<script>
    let currentStep = <?php echo json_encode(!empty($_SESSION['step']) ? $_SESSION['step'] : 1);?>;
    let redirectUrl = <?php echo json_encode(!empty($_SESSION['redirectUrl']) ? $_SESSION['redirectUrl'] : NULL);?>;
    let paymentRequest = <?php echo json_encode(!empty($paymentRequest) ? $paymentRequest : NULL);?>;
    let checkoutRes = <?php echo json_encode(!empty($_SESSION['checkout_response']) ? $_SESSION['checkout_response'] : NULL);?>;
    let pageError = <?php echo json_encode($pageError);?>;
    let currency = <?php echo json_encode(!empty($paymentRequest['currency']) ? $paymentRequest['currency'] : NULL);?>;
    const transactionError = <?php echo json_encode(!empty($_SESSION['transaction_error']) ? $_SESSION['transaction_error'] : NULL);?>;

    async function submitOrderRequest() {
        try {
            const formdata = new FormData();
            formdata.set("referrer", window.location.href)
            if(paymentRequest.request_email) {
                formdata.set("email", paymentRequest.request_email)
            }
            if(paymentRequest.request_mgx_code) {
                formdata.set("managex_code", paymentRequest.request_mgx_code)
            }
            const res = await axios.post("../api/process_payment_request.php", formdata)
            if(!res.data) {
                throw new Error('Uncaught getting order request')
            }
            return res.data
        } catch (error) {
            console.error(error?.response?.data ?? error)
            throw error;
        }
    }
</script>
<style>
    html, body, main {
        height: 100%;
    }
    main.error {
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 2rem;
        justify-content: center;
    }
    main.error img.error-illustration {
        width: 500px;
        display: block;
        margin: 0rem auto;
    }
</style>
<body x-data="{currentStep, paymentRequest}">
    <nav class="navbar navbar-expand-lg fixed-top probootstrap-megamenu navbar-light probootstrap-navbar py-3" style="box-shadow: none;">
        <div class="container">
            <a class="navbar-brand mx-auto" href="../index.php" title="Managex" style="background-image: url(../img/mgx_logo.png); width: 50%; background-size: contain;"></a>
            
        </div>
    </nav>
    <template x-data x-if="paymentRequest && !pageError">
        <main>
            <div class="checkout-nav py-4 px-sm-4">
                <div class="checkout-progress mx-auto">
                    <div class="step" :class="currentStep >= 1 && 'complete'" style="flex: 2;">
                        <h4>1</h4>
                        <p>Payment</p>
                        <hr>
                    </div>
                    <div class="step" :class="currentStep >= 2 && 'complete'">
                        <h4>2</h4>
                        <p>Confirmation</p>
                    </div>
                </div>
            </div>
            <div class="container" x-data="{error: transactionError}" x-show="currentStep === 1" x-cloak x-transition x-init="()=>{
                // if(checkoutRes && checkoutRes.status_code != 1) {
                //     error = checkoutRes.description
                // }
            }">
                <template x-if="!checkoutRes && !error">
                    <div class="row justify-content-center text-center">
                        <div class="col-md-6">
                            <h3 class="mb-2">Checkout via M-PESA</h3>
                            <p>We curently support mpesa payments only, other methods coming soon.</p>
                            <div class="card">
                                <div class="card-header">
                                    <h3>Payment details</h3>
                                </div>
                                <div class="card-body">
                                    <div class="summary">
                                        <div class="summary-info">
                                            <span>Paybill</span>
                                            <h4>522522</h4>
                                        </div>
                                        <div class="summary-info">
                                            <span>Account</span>
                                            <h4>5889112</h4>
                                        </div>
                                        <div class="summary-info">
                                            <span>Amount</span>
                                            <h4 x-text="formatCurrency(paymentRequest.amount, currency)"></h4>
                                        </div>
                                    </div>
                                    <hr>
                                    <form :action="`../controllers/process_payment.php?tracking_id=${paymentRequest.tracking_id}&type=custom`" method="POST">
                                        <div class="form-group">
                                            <label for="confirmation">Your Confirmation Code</label>
                                            <input type="text" id="confirmation" class="form-control" placeholder="M-pesa code" required name="confirmation_code">
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                                    </form>
                                </div>
                                <div class="card-footer">
                                    <small style="font-size: 14px;">
                                        <i class="fa-regular fa-lightbulb mr-1"></i> 
                                        Please <strong>do not overpay or underpay</strong>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="checkoutRes && checkoutRes.status_code != 1 && !error">
                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <div class="card mb-3 summary">
                                <div class="card-body">
                                    <div class="card-title">
                                        <i class="icon icon-cross mr-1 text-danger" style="font-size: 1.6rem;"></i>
                                        <h4>Payment not complete</h4>
                                        <p x-text="error"></p>
                                    </div>
                                    <hr>
                                    <div class="summary-info">
                                        <span>Status</span>
                                        <h4 class="m-0" x-text="checkoutRes.payment_status_description"></h4>
                                    </div>
                                    <div class="summary-info">
                                        <span>Confirmation</span>
                                        <h4 class="m-0" x-text="checkoutRes.confirmation_code"></h4>
                                    </div>
                                    <div class="summary-info">
                                        <span>Amount</span>
                                        <h4 class="m-0" x-text="formatCurrency(checkoutRes.amount, currency)"></h4>
                                    </div>
                                    <hr>
                                    <div class="summary-info">
                                        <span>Date</span>
                                        <h4 class="m-0" x-text="moment(checkoutRes.created_date).format('YYYY-MM-DD [at:] h:mm A')"></h4>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        <div class="col-md-6 col-lg-8 card align-items-center" x-data="{retrying: false}">
                            
                            <dotlottie-player x-show="retrying" src="https://lottie.host/28a2c7cf-1e27-4cd7-adfc-5a682eca04b9/o9QtdNt6N5.json" 
                                background="transparent" speed="1" style="width: 100%; height: 300px;" loop autoplay></dotlottie-player>
    
                            <dotlottie-player x-show="!retrying" src="https://lottie.host/3ced0611-23a9-4043-8d37-7ec8dc24091f/la75DsTAcR.json" 
                                background="transparent" speed="1" style="width: 100%; height: 300px;" loop autoplay></dotlottie-player>
                            <button class="btn btn-primary mt-2 py-2 px-3 rounded" @click="()=>{
                                retrying = true;
                                retryPayment(paymentRequest.tracking_id, 'custom', '../api').then(data => {
                                    paymentRequest = data
                                    error = null
                                    checkoutRes = null
                                }).catch((e)=>{
                                    error = e
                                }).finally(()=> {
                                    retrying = false
                                })
                            }">Retry</button>
                            <!-- <h5 class="text-center mt-4">Please try again later</h5>  -->
                            
                        </div>
                        
                    </div>
                </template>
                <template x-if="error">
                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <div class="card mb-3 summary">
                                <div class="card-body">
                                    <div class="card-title">
                                        <i class="icon icon-cross mr-1 text-danger" style="font-size: 1.6rem;"></i>
                                        <h4>An Error Occured</h4>
                                        <p x-text="error"></p>
                                    </div>
                                    <template x-if="checkoutRes && checkoutRes.status_code == 1">
                                        <div>
                                            <hr>
                                            <p>Despite this error <strong>your payment has been received</strong>. please <strong>do not repay.</strong></p>
                                            <p>Kindly email the Confirmation message to our <a href="mailto:customercare@kingsoft.biz">Customer Care</a> Line.</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                        </div>
                        <div class="col-md-6 col-lg-8 card align-items-center" x-data="{retrying: false}">
                            
                            <dotlottie-player x-show="retrying" src="https://lottie.host/28a2c7cf-1e27-4cd7-adfc-5a682eca04b9/o9QtdNt6N5.json" 
                                background="transparent" speed="1" style="width: 100%; height: 300px;" loop autoplay></dotlottie-player>
    
                            <dotlottie-player x-show="!retrying" src="https://lottie.host/3ced0611-23a9-4043-8d37-7ec8dc24091f/la75DsTAcR.json" 
                                background="transparent" speed="1" style="width: 100%; height: 300px;" loop autoplay></dotlottie-player>
                            <button class="btn btn-primary mt-1 mb-4 py-2 px-3 rounded" @click="()=>{
                                retrying = true;
                                retryPayment(paymentRequest.tracking_id, `custom`, '../api').then(data => {
                                    paymentRequest = data
                                    error = null
                                    checkoutRes = null
                                }).catch((e)=>{
                                    error = e
                                }).finally(()=> {
                                    retrying = false
                                })
                            }">Retry</button>
                            <!-- <h5 class="text-center mt-4">Please try again later</h5>  -->
                            
                        </div>
                        
                    </div>
                </template>
            </div>
            <div class="container" x-show="currentStep === 2" x-cloak x-transition>
                <template x-if="currentStep === 2">
                    <div class="row mb-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="card mb-3 summary">
                                <div class="card-body">
                                    <div class="card-title">
                                        <i class="icon icon-checkmark mr-1" style="font-size: 1.6rem; color: #7ed321;"></i>
                                        <h4>Success!</h4>
                                        <p x-text="checkoutRes.description"></p>
                                    </div>
                                    <hr>
                                    <div class="summary-info">
                                        <span>Status</span>
                                        <h4 class="m-0" x-text="checkoutRes.payment_status_description"></h4>
                                    </div>
                                    <div class="summary-info">
                                        <span>Confirmation</span>
                                        <h4 class="m-0" x-text="checkoutRes.confirmation_code"></h4>
                                    </div>
                                    <div class="summary-info">
                                        <span>Amount</span>
                                        <h4 class="m-0" x-text="formatCurrency(checkoutRes.amount, currency)"></h4>
                                    </div>
                                    <hr>
                                    <div class="summary-info">
                                        <span>Date</span>
                                        <h4 class="m-0" x-text="moment(checkoutRes.created_date).format('YYYY-MM-DD [at:] h:mm A')"></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-8 card align-items-center justify-content-center px-4">
                            <dotlottie-player src="https://lottie.host/ca8332e5-9e15-432e-a013-9505a9fc89b8/SuBAKi2tTR.json" background="transparent" speed="1" style="width: 100%; height: 300px;" loop autoplay></dotlottie-player>
                            
                            <h5 class="text-center mt-4">Your confirmation has been received, thank you!</h5> 
                        </div>
                    </div>
                </template>
            </div>
        </main>
    </template>
    
    <template x-data x-if="pageError">
        <main x-data class="error">
            <img :src="pageError.code == 404 ? '../img/illustration/404.svg' : pageError.code == 400 ? '../img/illustration/400_Bad_Request.svg' : '../img/illustration/server_error.svg'" class="error-illustration mb-3" :alt="`Error code + ${pageError.code} + 'Illustration`">
            <h1 class="my-4 text-center" x-text="pageError.message"></h1>
        </main>
    </template>
    
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script> 
</body>

</html>