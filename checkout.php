<?php

require_once("utils/redirect.php");
 
require_once("db/db.inc.php");
require_once("db/queries.inc.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(empty($_GET['plan'])) {
    redirect("./index.php");
}

function get_plan(array $plans, int $plan_id) {
    $filteredPlan = array_filter($_SESSION['plans'], function ($plan) use($plan_id) {
        return $plan['id'] == $plan_id;
    });
    return array_values($filteredPlan)[0];
}

$plan_id = $_GET['plan'];
$_SESSION['plan'] = get_plan($_SESSION['plans'], $plan_id);

$_SESSION['discounts'] = !empty($_SESSION['discounts']) ? array_filter($_SESSION['discounts'], function ($discount) {
    return new DateTime() < new DateTime($discount['expiry']);
}) : [];
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
    <script src="https://cdn.jsdelivr.net/npm/@caneara/iodine@8.5.0/dist/iodine.min.umd.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="js/vendor/moment.min.js"></script>
    <script src="js/checkout.js"></script>
</head>
<script>
    const plan = <?php echo json_encode($_SESSION['plan']);?>;
    const checkoutDiscounts = <?php echo json_encode($_SESSION['discounts']);?>;
    const transactionError = <?php echo json_encode(!empty($_SESSION['transaction_error']) ? $_SESSION['transaction_error'] : NULL);?>;
    console.log(transactionError)
    const businessTypes = <?php echo json_encode($_SESSION['business_types']); ?>;
    const currency = 'KES'
    const startDate = moment()
    let endDate =null
    let currentStep = <?php echo json_encode(!empty($_SESSION['step']) ? $_SESSION['step'] : 1);?>;
    let order = <?php echo json_encode(!empty($_SESSION['order']) ? $_SESSION['order'] : NULL);?>;
    console.log('order', order)
    let checkoutRes = <?php echo json_encode(!empty($_SESSION['checkout_response']) ? $_SESSION['checkout_response'] : NULL);?>;

    let customer = <?php echo json_encode(!empty($_SESSION['customer']) ? $_SESSION['customer'] : NULL);?>;
    document.addEventListener('alpine:init', () => {
        Alpine.store('price', {
          price: plan.price,
          discounts: [...checkoutDiscounts],
          get total() {
            const totalFraction = 1 - this.discounts.reduce((acc, discount) => {
                return acc + parseFloat(discount.fraction)
            }, 0)

            return this.price * totalFraction
          }
        })
    })
</script>
<body x-data="{currentStep, order}">
    <nav class="navbar navbar-expand-lg fixed-top probootstrap-megamenu navbar-light probootstrap-navbar py-3" style="box-shadow: none;">
        <div class="container">
            <a class="navbar-brand" href="index.php" title="Kingsoft" :class="currentStep === 3 && 'mx-auto'" 
                style="background-image: url('img/mgx_logo.png'); background-size: contain; width: 200px;"></a>
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
                <a type="button" class="btn btn-primary" href="./controllers/cancel_payment.php" >Cancel Purchase</a>
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
            </div>
        </div>
    </div>
    <div class="container" x-data="{...checkoutFormData(), formLoading: false, error: null}" x-show="currentStep === 1" x-cloak x-transition>
        <!-- Add a top div with a progress bar -->
        <div class="row mb-3">
            <div class="col-md-6 col-lg-8">
                <h3 x-show="!formLoading && !error">Your Details</h3>
                <p x-show="!formLoading && !error">We need your details for support reference</p>

                <form id="billing-details" x-show="!formLoading && !error" x-transition action="api/order.php" method="POST" @submit.prevent="() => {
                    formLoading = true
                    submit($event).then(data => {
                        order = data
                        currentStep++
                    }).catch(e => {
                        error = e
                    }).finally(()=>{
                        formLoading = false
                    })
                }">
                    <div class="form-group">
                        <label for="fname"><i class="fa fa-user"></i> Business Name *</label>
                        <input type="text" id="fname" name="business_name" placeholder="Urban Gigs Ltd" class="form-control"
                            x-model="fields.businessName.value" 
                            :class="fields.businessName.error && 'border-danger'"
                            @blur="validateField(fields.businessName)"
                            required
                        >
                        <span class="text-danger" x-text="fields.businessName.error"></span>
                    </div>
                    <div class="form-group">
                        <label for="btype"><i class="fa fa-user"></i> Business Type *</label>
                        <select name="business_type" id="btype" class="form-control"
                            x-model="fields.businessType.value" 
                            @blur="validateField(fields.businessType)"
                            :class="fields.businessType.error"
                        >
                            <option value="" selected disabled>Select an option</option>
                            <template x-for="type in businessTypes">
                            <option :value="type.id" x-text="type.name"></option>
                            </template>
                        </select>
                        <span class="text-danger" x-text="fields.businessType.error"></span>
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
                            <h4 class="m-0" x-text="endDate ? endDate.format('DD-MM-YYYY') : 'Never'"></h4>
                        </div>
                        <div class="summary-info">
                            <span>Price</span>
                            <h4 class="m-0" x-text="formatCurrency(plan.price, currency)"></h4>
                        </div>
                        <hr>
                        <div class="card-title">
                            <i><i class="fa-solid fa-tags"></i></i>
                            <h4>Discounts</h4>
                        </div>
                        
                        <template x-for="discount in $store.price.discounts" x-show="false">
                            <div class="summary-info">
                                <span x-text="discount.code"></span>
                                <h4 class="m-0" x-text="`${(100 * discount.fraction).toFixed(0)}% OFF!`"></h4>
                            </div>
                        </template>
                        <hr>
                        <div class="summary-info">
                            <span>Total</span>
                            <h4 class="m-0" x-text="formatCurrency($store.price.total, currency)"></h4>
                        </div>
                    </div>
                </div>
                <!-- <div class="card mb-3 discount-ad p-3">
                    <i><i class="fa-solid fa-tags"></i></i>
                    <p class="mb-0">Use the code <strong style="color: #17a2b8;">EARLYBIRD</strong> to get <strong>5% OFF</strong> your price</p>
                </div> -->
                <div class="card py-4 px-3" x-data="{agreed: false}" @agree-license.window="()=>{
                    agreed = true
                }">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" value="" id="terms" x-model="agreed">
                        <label class="form-check-label" for="terms">
                            By clicking this, I agree to <a href="#" data-toggle="modal" data-target="#terms-and-conditions-modal">managex terms and conditions.</a>
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
            <?php require_once('views/terms_and_conditions_modal.php') ?>
        </div>
    </div>
    <div class="container" x-data="{error: transactionError}" x-show="currentStep === 2" x-cloak x-transition x-init="()=>{
        if(checkoutRes && checkoutRes.status_code != 1) {
            error = checkoutRes.description
        }
    }">
        <template x-if="order && !checkoutRes && !error">
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
                                    <h4 x-text="formatCurrency(order.invoice_amount - order.paid_amount, currency)"></h4>
                                </div>
                            </div>
                            <hr>
                            <form :action="`controllers/process_payment.php?tracking_id=${order.tracking_id}`" method="POST">
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
                <!-- <div class="col-md-6">
                    <h3 class="mb-2">Your Confirmation Code</h3>
                    <div class="card">
                        <div class="card-header">
                            <h3>Confirmation Code</h3>
                        </div>
                        <div class="card-body">
                            <form action="">
                                <div class="form-group">
                                    <label for="confirmation">Your Confirmation Code</label>
                                    <input type="text" id="confirmation" class="form-control">
                                </div>
                            </form>
                        </div>
                    </div>
                </div> -->
            </div>
        </template>
       
        <template x-if="checkoutRes && error">
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
                        retryPayment(order.tracking_id).then(data => {
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
        <template x-if="!checkoutRes && error">
            <div class="row">
                <div class="col-md-6 col-lg-4">
                    <div class="card mb-3 summary">
                        <div class="card-body">
                            <div class="card-title">
                                <i class="icon icon-cross mr-1 text-danger" style="font-size: 1.6rem;"></i>
                                <h4>An Error Occured</h4>
                                <p x-text="error"></p>
                            </div>
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
                        retryPayment(order.tracking_id).then(data => {
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
    <div class="container" x-show="currentStep === 3" x-cloak x-transition>
        <template x-if="currentStep === 3">
            <div class="row mb-3">
                <?php
                    if(isset($_GET["error"])) {
                        // display login errors
                        if($_GET["error"] == "download error") {
                            echo 
                                "<div class='alert alert-warning alert-dismissible fade show' style='position: absolute; top: 32px; right: 32px; z-index: 9999;' role='alert'>
                                    An error occured with your download, please try again.
                                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                        <span aria-hidden='true'>&times;</span>
                                    </button>
                                </div>";
                        }
                    }
                ?>
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
                    <template x-if="false">
                        <div class="card mb-3 summary">
                            <div class="card-body">
                                <div class="card-title">
                                    <i class="fa-solid fa-share-nodes mb-3" style="font-size: 1.6rem;"></i>
                                    <h4>Share Your Code</h4>
                                </div>
                                <p class="m-0">
                                    Your P.O.S Code is: 
                                    <div class="my-2" style="font-size: 1.2rem;">
                                        <strong x-text="referralDiscount.code" >
                                        </strong> 
                                        <i style="cursor: pointer;" id="copy-code" class="fa-solid fa-copy" 
                                        @click="()=>{
                                            navigator.clipboard.writeText(referralDiscount.code).then(()=>{
                                                document.getElementById('copy-code').style.color = 'blue'
                                                setTimeout(() => {
                                                    document.getElementById('copy-code').style.color = '#8b8e94'
                                                }, 3000);
                                            })
                                        }">
                                        </i>
                                    </div>
                                    Share this code with your peers and they will get 5% OFF their checkout price.
                                    <span x-data x-show="model !== 'ONETIME'">You too will get a <strong>5% discount on your next invoice!</strong></span>
                                </p>
                                <div class="card-footer">
                                    <small style="font-size: 14px;"><i class="fa-regular fa-lightbulb mr-1"></i> Discount is only valid until <strong x-text="moment(referralDiscount.expiry).format('YYYY-MM-DD [at:] h:mm A')"></strong></small>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="col-md-6 col-lg-8 card align-items-center justify-content-center px-4">
                    <dotlottie-player src="https://lottie.host/fad7868a-fe30-449e-a16e-5cae26c9eb4f/haWLqbDK3w.json" background="transparent" speed="1" style="width: 100%; height: 300px;" loop autoplay></dotlottie-player>
                    <button class="btn-primary mt-2 py-2 px-3 rounded mt-3" @click="()=>{
                        window.location.href = `controllers/download.php?download_id=${checkoutRes.download_id}`
                    }">Download Managex</button>
                    <p class="text-center mt-1" style="font-size: 1rem;">
                        The download link has also been sent to your email: <strong x-text="customer.email"></strong>
                    </p>
                </div>
            </div>
        </template>
    </div>
    
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script> 
</body>

</html>