<?php

require_once("utils/redirect.php");
require_once("utils/logger.php");
require_once("utils/ip_localle.php");
require_once("utils/currency_convert.php");
require_once("db/db.inc.php");
require_once("db/queries.inc.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(empty($_GET['plan'])) {
    redirect("./index.html");
}
$plan_id = $_GET['plan'];
$_SESSION['plan'] = !empty($_SESSION['plan']) ? $_SESSION['plan'] : getPlan($pdo_conn, $plan_id, $dbLogger);
$_SESSION['localle'] = !empty($_SESSION['localle']) ? $_SESSION['localle'] : getIpLocalle($dbLogger);
$_SESSION['locallePrice'] = !empty($_SESSION['locallePrice']) ? $_SESSION['locallePrice'] : convert_currrency(
    $_SESSION['localle']['country']['currency'], 
    $_SESSION['plan']['price'],
    $dbLogger
);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Managex Checkout</title>
    <meta name="description"
        content="managex for SMEs featuring inventory, order, CRM, accounting, analytics and more.">
    <meta name="keywords"
        content="erp software, inventory management software, order management system, accounting software, analytics, dashboard, crm, small business software, smb software, manage inventory, purchase orders, sales orders, invoicing, financial reporting, profit and loss, accounts payable, accounts receivable, contact management, pipeline management, marketing automation, forecasts, pos integration, payroll">
    <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,700">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles-merged.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/checkout.css">
    <script src="https://kit.fontawesome.com/f95e1afe0c.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@caneara/iodine@8.5.0/dist/iodine.min.umd.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="js/vendor/moment.min.js"></script>
    <script src="js/checkout.js"></script>
</head>
<script>
    const plan = <?php echo json_encode($_SESSION['plan']);?>;
    const localle = <?php echo json_encode($_SESSION['localle']);?>;
    const locallePrice = <?php echo json_encode($_SESSION['locallePrice']);?>;
    const formattedPrice = formatCurrency(locallePrice, localle.country.currency, `${localle.country.languages[0].iso_code}-${localle.country.iso_code}`)
    const startDate = moment()
    let endDate =null
    if(plan.payment_frequency !== 'ONETIME') {
        endDate = moment().add(1, plan.payment_frequency[0])
    }
    let currentStep = <?php echo json_encode(!empty($_SESSION['step']) ? $_SESSION['step'] : 1);?>;
    let redirectUrl = <?php echo json_encode(!empty($_SESSION['redirectUrl']) ? $_SESSION['redirectUrl'] : NULL);?>;
    let checkoutRes = <?php echo json_encode(!empty($_SESSION['checkout_response']) ? $_SESSION['checkout_response'] : NULL);?>;
    let customer = <?php echo json_encode(!empty($_SESSION['customer']) ? $_SESSION['customer'] : NULL);?>;
    if(checkoutRes) {
        console.log(checkoutRes)
    }
</script>
<body x-data="{currentStep, redirectUrl}">
    <nav class="navbar navbar-expand-lg fixed-top probootstrap-megamenu navbar-light probootstrap-navbar py-3" style="box-shadow: none;">
        <div class="container">
            <a class="navbar-brand" href="index.html" title="Kingsoft" :class="currentStep === 3 && 'mx-auto'"></a>
            <button class="btn text-icon-button" type="button" data-toggle="modal" data-target="#cancel-purchase-modal" x-show="currentStep < 3" x-cloak>
                <i class="fa-solid fa-xmark text-danger"></i>
                <span>Cancel</span>
            </button>
        </div>
    </nav>
    <div class="modal fade " id="cancel-purchase-modal" tabindex="-1" role="dialog" >
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header">
                <h4 class="modal-title">Are you sure?</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Any information you have given so far will be discarded</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a type="button" class="btn btn-primary" href="./index.html">Cancel Purchase</a>
            </div>
            </div>
        </div>
    </div>
    <div class="checkout-nav py-4 px-sm-4">
        <!-- TODO: Design an error state -->
        <div class="checkout-progress mx-auto">
            <div class="step" :class="currentStep >= 1 && 'complete'">
                <h4>1</h4>
                <p>Personal</p>
                <hr>
            </div>
            <div class="step" :class="currentStep >= 2 && 'complete'">
                <h4>2</h4>
                <p>Payment</p>
                <hr>
            </div>
            <div class="step" :class="currentStep >= 3 && 'complete'">
                <h4>3</h4>
                <p>Download</p>
                <!-- TODO: Design the download page -->
            </div>
        </div>
    </div>
    <div class="container" x-data="{...checkoutFormData(), formLoading: false, error: null}" x-show="currentStep === 1" x-cloak x-transition>
        <!-- Add a top div with a progress bar -->
        <div class="row">
            <div class="col-md-6 col-lg-8">
                <h3 x-show="!formLoading && !error">Billing Address</h3>

                <form id="billing-details" x-show="!formLoading && !error" x-transition action="api/order.php" method="POST" @submit.prevent="() => {
                    formLoading = true
                    submit($event).then(data => {
                        redirectUrl = data.redirect_url
                        currentStep++
                    }).catch(e => {
                        error = e
                    }).finally(()=>{
                        formLoading = false
                    })
                }">
                    <div class="form-group">
                        <label for="fname"><i class="fa fa-user"></i> Full Name *</label>
                        <input type="text" id="fname" name="fullname" placeholder="John M. Doe" class="form-control"
                            x-model="fields.fullName.value" 
                            :class="fields.fullName.error && 'border-danger'"
                            @blur="validateField(fields.fullName)"
                            required
                        >
                        <span class="text-danger" x-text="fields.fullName.error"></span>
                    </div>
                    <div class="form-group">
                        <label for="email"><i class="fa fa-envelope"></i> Email *</label>
                        <input type="text" id="email" name="email" placeholder="john@example.com" class="form-control"
                            x-model="fields.email.value" 
                            :class="fields.email.error && 'border-danger'"
                            @blur="validateField(fields.email)"
                            required
                        >
                        <span class="text-danger" x-text="fields.email.error"></span>

                    </div>
                    <div class="form-group">
                        <label for="phone"><i class="fa fa-phone"></i> Phone</label>
                        <input type="text" id="phone" name="phone" placeholder="0723927876" class="form-control"
                            x-model="fields.phone.value" 
                            :class="fields.phone.error && 'border-danger'"
                            @blur="validateField(fields.phone)"
                        >
                        <span class="text-danger" x-text="fields.phone.error"></span>
                    </div>
                    <div class="form-group">
                        <label for="adr"><i class="fa fa-address-card-o"></i> Address</label>
                        <input type="text" id="adr" name="address" placeholder="542 W. 15th Street" class="form-control"
                            x-model="fields.address.value" 
                            :class="fields.address.error && 'border-danger'"
                            @blur="validateField(fields.address)"
                        >
                        <span class="text-danger" x-text="fields.address.error"></span>
                    </div>
                    <div class="form-group">
                        <label for="city"><i class="fa-solid fa-city"></i> City</label>
                        <input type="text" id="city" name="city" placeholder="New York" class="form-control"
                            x-model="fields.city.value" 
                            :class="fields.city.error && 'border-danger'"
                            @blur="validateField(fields.city)"
                        >
                        <span class="text-danger" x-text="fields.city.error"></span>
                    </div>
                    <div class="form-group">
                        <label for="state"> <i class="fa-solid fa-flag"></i> State</label>
                        <input type="text" id="state" name="state" placeholder="NY" class="form-control"
                            x-model="fields.state.value" 
                            :class="fields.state.error && 'border-danger'"
                            @blur="validateField(fields.state)"
                        >
                        <span class="text-danger" x-text="fields.state.error"></span>
                    </div>
                    <div class="form-group">
                        <label for="postal-code"><i class="fa-solid fa-location-dot"></i> Postal Code</label>
                        <input type="text" id="postal-code" name="postal_code" placeholder="10001" class="form-control"
                            x-model="fields.postalCode.value" 
                            :class="fields.postalCode.error && 'border-danger'"
                            @blur="validateField(fields.postalCode)"
                        >
                        <span class="text-danger" x-text="fields.postalCode.error"></span>
                    </div>
                    <small style="font-size: 14px;"><i class="fa-regular fa-lightbulb mr-1"></i> Fields marked with (*) are <strong>required</strong></small>
                </form>
                <div x-show="formLoading" x-transition>
                    <dotlottie-player src="https://lottie.host/28a2c7cf-1e27-4cd7-adfc-5a682eca04b9/o9QtdNt6N5.json" background="transparent" speed="1" style="width: 100%; height: 600px;" loop autoplay></dotlottie-player>
                </div>
                <div x-show="!formLoading && error" class="text-center">
                    <img src="img/error_illustration.png" alt="Error illustration: Crashed rocket ship" style="width: 100%; height: 400px;">
                    <h3 class="mb-3">Error occured!</h3>
                    <template x-if="error">
                        <p x-text="error?.response?.data ?? error" style="font-size: 1.2rem;"></p>
                    </template>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card mb-3 summary">
                    <div class="card-body">
                        <div class="card-title">
                            <i class="icon icon-credit-card mr-1" style="font-size: 1.6rem;"></i>
                            <h4>Summary</h4>
                        </div>
                        <hr>
                        <div class="summary-info">
                            <span>Plan</span>
                            <h4 class="m-0" x-text="plan.name"></h4>
                        </div>
                        <div class="summary-info">
                            <span>Starts</span>
                            <h4 class="m-0" x-text="startDate.format('DD-MM-YYYY')"></h4>
                        </div>
                        <div class="summary-info">
                            <span>Ends</span>
                            <h4 class="m-0" x-text="plan.payment_frequency === 'ONETIME' ? 'Never' : endDate.format('DD-MM-YYYY')"></h4>
                        </div>
                        <hr>
                        <div class="summary-info">
                            <span>Price</span>
                            <h4 class="m-0" x-text="formattedPrice"></h4>
                        </div>
                        <!-- <div class="summary-info">
                            <span>V.A.T</span>
                            <h4 class="m-0">KES 1,584</h4>
                        </div> -->
                        <!-- <div class="summary-info">
                            <span>Total</span>
                            <h4 class="m-0">KES 9,900</h4>
                        </div> -->
                    </div>
                </div>
                <div class="card py-4 px-3" x-data="{agreed: false}">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="" id="terms" x-model="agreed">
                        <label class="form-check-label" for="terms">
                            By clicking this, I agree to <a href="#">managex terms and conditions</a> and <a href="#">privacy policy</a>
                        </label>
                    </div>
                    
                    <button type="button" class="btn btn-primary" @click="()=>{
                        const form = document.getElementById('billing-details')
                        if(error) {
                            error = null;
                            form.reset()
                            return;
                        }
                        if(isFormValid()) {
                            form.dispatchEvent(new Event('submit', { bubbles: true }))
                        }
                    }" x-text="!error ? 'Continue to checkout' : 'Retry'" :disabled="!agreed"></button>
                </div>
            </div>
            
        </div>
    </div>
    <div class="container" x-show="currentStep === 2" x-cloak x-transition>
        <h3>Checkout with pesapal</h3>
        <template x-if="currentStep === 2 && redirectUrl">
            <iframe :src="redirectUrl" width="100%" height="800px">
                <!-- Alternative content for browsers that do not support iframes -->
                <p>Your browser does not support iframes.</p>
            </iframe>
        </template>
    </div>
    <div class="container" x-show="currentStep === 3" x-cloak x-transition>
        <template x-if="currentStep === 3">
            <div class="row">
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
                                <span>Method</span>
                                <h4 class="m-0" x-text="checkoutRes.payment_method"></h4>
                            </div>
                            <div class="summary-info">
                                <span>Account</span>
                                <h4 class="m-0" x-text="checkoutRes.payment_account"></h4>
                            </div>
                            <div class="summary-info">
                                <span>Amount</span>
                                <h4 class="m-0" x-text="formatCurrency(checkoutRes.amount, checkoutRes.currency, `${localle.country.languages[0].iso_code}-${localle.country.iso_code}`)"></h4>
                            </div>
                            <hr>
                            <div class="summary-info">
                                <span>Date</span>
                                <h4 class="m-0" x-text="moment(checkoutRes.created_date).format('YYYY-MM-DD [at:] h:mm A')"></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-8 card align-items-center">
                    <dotlottie-player src="https://lottie.host/fad7868a-fe30-449e-a16e-5cae26c9eb4f/haWLqbDK3w.json" background="transparent" speed="1" style="width: 100%; height: 300px;" loop autoplay></dotlottie-player>
                    <button class="btn-primary mt-2 py-2 px-3 rounded">Download Managex v4.19</button>
                    <h5 class="text-center mt-4">You have been enrolled to the <strong x-text="plan.name"></strong> plan.</h5> 
                    <p class="text-center mt-1">
                        The download link has also been sent to your email: <strong x-text="customer.email"></strong>
                    </p>
                </div>
            </div>
        </template>
    </div>
    <script
        src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"
    >
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script> 
</body>

</html>