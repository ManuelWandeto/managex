<!DOCTYPE html>
<html lang="en">
<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once ("db/db.inc.php");
require_once ("db/queries.inc.php");
require_once ("utils/logger.php");
require_once ("utils/ip_localle.php");
require_once ("utils/load_env.php");

$_SESSION['referer'] = !empty ($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL;
$_SESSION['localle'] = getIpLocalle($dbLogger);
$_SESSION['currency'] = explode(',', $_SESSION['localle']['country']['currency'])[0] == 'KES' ? 'KES' : 'USD';
$_SESSION['plans'] = getPlans($pdo_conn, $_SESSION['currency'], $logger);
$_SESSION['busuness_types'] = getBusinessTypes($pdo_conn, $logger);
$_SESSION['discounts'] = [
  [
    "valid" => true,
    "discount_id" => 2,
    "code" => "EARLYBIRD",
    "fraction" => 0.14,
    "expiry" => "2024-12-01 00:00:00"
  ]
];
$_SESSION['default_version'] = $pdo_conn->query("SELECT * FROM versions WHERE is_default = 1 ORDER BY creation_date DESC LIMIT 1;")->fetch(PDO::FETCH_ASSOC);
$megaUser = ["username" => $_ENV['MEGA_USERNAME'], "password" => $_ENV['MEGA_PASSWORD']];
?>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kingsoft Co LTD</title>
  <meta name="description" 
    content="Your all-in-one solution for efficient business management. From Point of Sale (POS) to Inventory Management, 
    streamline operations with our customizable software. Explore finance tools, generate invoices, and gain insights into your business performance. 
    discover the power of Managex today!"
  >
  <meta name="keywords"
    content="Point of Sale software, Point of Sale software in Kenya, Sales system, inventory management software, Finance and accounting system, Business finance management software,
    quotations and invoicing software, Financial tools, Retail software solution, Efficient business management software,
    order management system, analytics, dashboard, CRM, small business management software, SME management software manage inventory, purchase orders, sales orders, invoicing, 
    financial reporting, profit and loss, accounts payable, accounts receivable, contact management, pipeline management, marketing automation, forecasts, 
    pos integration, payroll">
  <link rel="icon" type="image/x-icon" href="/img/favicon.ico">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:400,500,700">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css"
    integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
  <link rel="stylesheet" href="css/styles-merged.min.css">
  <link rel="stylesheet" href="css/template-style.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="https://kit.fontawesome.com/f95e1afe0c.js" crossorigin="anonymous"></script>
  
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>
   <!-- Make sure you put this AFTER Leaflet's CSS -->
 <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>

  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@caneara/iodine@8.5.0/dist/iodine.min.umd.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script defer src="js/vendor/main.browser-umd.js"></script>
  <script src="js/download.js"></script>
  <script src="js/checkout.js"></script>
  <script src="js/store.js"></script>
  <!--[if lt IE 9]>
      <script src="js/vendor/html5shiv.min.js"></script>
      <script src="js/vendor/respond.min.js"></script>
    <![endif]-->
</head>
<script>
  const plans = <?php echo json_encode($_SESSION['plans']); ?>;
  console.log(plans)
  const userCurrency = <?php echo json_encode($_SESSION['currency']);?>;
  console.log(userCurrency)
  const localle = <?php echo json_encode($_SESSION['localle']); ?>;
<<<<<<< Updated upstream
  const businessTypes = <?php echo json_encode($_SESSION['busuness_types']); ?>;
  console.log(businessTypes)
=======
  const businessTypes = <?php echo json_encode($_SESSION['business_types']); ?>;
  const mgxDefaultVersion = <?php echo json_encode($_SESSION['default_version']); ?>;
  const megaUser = <?php echo json_encode($megaUser); ?>;

  const currency = 'KES'
>>>>>>> Stashed changes
  document.addEventListener('alpine:init', () => {
    Alpine.store('plans', {
      list: plans.map(p => {

        return {
          id: p.id,
          name: p.name,
          _model: Object.keys(p.pricing[0])[0],
          pricing: p.pricing,
          set model(name) {
            this._model = name
          },
          get price() {
            return this.pricing.find(pr => pr[this._model]).localle_price
          },
          get rawPrice() {
            return this.pricing.find(pr => pr[this._model])[this._model]
          },
          get url() {
            return new URLSearchParams(`plan=${p.id}&pricing=${this._model}`).toString()
          }
        }
      }),
      getPlan(name) {
        return this.list.find(p => p.name == name)
      },
      selectedTrial: 'SILVER',
    })
  })
  const videos = [
    {
      title: "Quick sale with 2 levels of units",
      link: "https://youtu.be/JTic_X52774?si=JMk0zubHxQHV-Wnc"
    },
    {
      title: "Creating users",
      link: "https://youtu.be/foGLwdL8Jkc?si=fweJZFeiCRwZMQRX"
    },
    {
      title: "Registering a product (goods/services)",
      link: "https://youtu.be/tq5V6Cg72yQ?si=2pZ8CpLzJ6RQpAlo"
    },
    {
      title: "Registering a product (goods/services)",
      link: "https://youtu.be/tq5V6Cg72yQ?si=2pZ8CpLzJ6RQpAlo"
    },
    {
      title: "Store item balances & other stocks reports",
      link: "https://youtu.be/V3T9JT_gEYw?si=axQfuAOEDs4FHf9L"
    },
    {
      title: "Store item balances & other stocks reports",
      link: "https://youtu.be/V3T9JT_gEYw?si=axQfuAOEDs4FHf9L"
    }
  ]
</script>

<body>
  <!-- Fixed navbar -->
  <!-- navbar-fixed-top  -->
  <nav class="navbar navbar-expand-lg fixed-top probootstrap-megamenu navbar-light bg-light probootstrap-navbar">
    <div class="container">
      <a href="index.html" title="" class="navbar-brand"></a>

      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-collapse"
        aria-controls="navbar-collapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <!-- <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
        </div> -->

      <div id="navbar-collapse" class="navbar-collapse collapse">
        <hr class="d-lg-none">
        <ul class="navbar-nav ml-auto ">
          <li class="nav-item active"><a href="#hero" class="nav-link">User Manual</a></li>
          <li class="nav-item"><a href="#features" class="nav-link">How to install</a></li>
          <li class="nav-item"><a href="#pricing" class="nav-link">How to activate</a></li>
          <li class="nav-item"><a href="#pricing" class="nav-link">How to pay</a></li>
          <!-- <li class="nav-item"><a href="#reviews" class="nav-link">Reviews</a></li> -->
          <!-- <li class="nav-item"><a href="#faq" class="nav-link">FAQ</a></li> -->
          <li class="nav-item"><a href="managex_training_docs" class="nav-link" target="_blank">User Manual</a></li>
          <li class="nav-item"><a href="docs" class="nav-link" target="_blank">Hotelex Manual</a></li>
          <li class="nav-item"><a href="#contact" class="nav-link">Contact</a></li>

        </ul>
      </div>
    </div>
  </nav>

  <section class="probootstrap-hero" id="hero">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-10 col-sm-10 text-center probootstrap-hero-text probootstrap-animate"
          data-animate-effect="fadeIn">
          <div class="mgx-logo"></div>
        </div>
      </div>
    </div>
  </section>

  <section class="probootstrap-section probootstrap-bg-white probootstrap-pricing-table">
    <div class="container-xl">
      <div class="row probootstrap-gutter0">
        <div class="col-md-4 probootstrap-pricing-wrap ">

          <div class="probootstrap-pricing bronze probootstrap-animate" data-animate-effect="fadeIn" x-data>
            <h3>BRONZE</h3>
            <div class="probootstrap-price-wrap">
              <span class="probootstrap-price" x-data x-text="$store.plans.getPlan('BRONZE').price"></span>
              <span class="probootstrap-price-per-month">ONETIME</span>
              <hr>
              or
              <span style="font-size: 1rem; font-weight: 500;">KES 300 / 3$ per week</span>
            </div>
            <ul>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                P.O.S Sales
              </li>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                Stocks &amp; Inventory
              </li>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                Purchases
              </li>
              <li>
                <i style="color:darkgrey" class="icon icon-cross"></i>
                C.R.M (credit sales & invoicing)
              </li>
              <li>
                <i style="color:darkgrey" class="icon icon-cross"></i>
                Finance
              </li>
            </ul>

            <p>Supports 1 computer</p>
            <div class="cta">
              <a :href="`checkout.php?${$store.plans.getPlan('BRONZE').url}`" class="btn btn-bronze btn-lg">
                <i class="icon icon-cart"></i>
                Buy Now
              </a>
              <a href="#" class="btn btn-secondary btn-lg" data-toggle="modal" data-target="#free-trial-modal" @click="()=>{
                $store.plans.selectedTrial = 'BRONZE'
              }">Start
                Free Trial</a>
            </div>

          </div>
        </div>

        <div class="col-md-4 probootstrap-pricing-wrap">
          <div class="probootstrap-pricing silver popular probootstrap-animate" data-animate-effect="fadeIn" x-data>
            <h3>SILVER</h3>

            <div class="probootstrap-price-wrap">
              <span class="probootstrap-price" x-data x-text="$store.plans.getPlan('SILVER').price"></span>
              <span class="probootstrap-price-per-month">ONETIME</span>
              <hr>
              or
              <span style="font-size: 1rem; font-weight: 500;">KES 400 / 4$ per week</span>
            </div>
            <ul>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                P.O.S Sales
              </li>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                Stocks &amp; Inventory
              </li>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                Purchases
              </li>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                C.R.M (credit sales & invoicing)
              </li>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                Finance
              </li>
            </ul>

            <p>Supports 1 computer</p>
            <div class="cta" style="margin-bottom: 40px;">
              <a :href="`checkout.php?${$store.plans.getPlan('SILVER').url}`" class="btn btn-silver btn-lg">
                <i class="icon icon-cart"></i>
                Buy Now
              </a>
              <a href="#" class="btn btn-secondary btn-lg" data-toggle="modal" data-target="#free-trial-modal" @click="()=>{
                $store.plans.selectedTrial = 'SILVER'
              }">Start
                Free Trial</a>
            </div>
            <!-- <p><a href="#" class="btn btn-secondary btn-lg" data-toggle="modal" data-target="#free-trial-modal">Start Free Trial</a></p> -->
          </div>
        </div>
        <div class="col-md-4 probootstrap-pricing-wrap">
          <div class="probootstrap-pricing gold probootstrap-animate" data-animate-effect="fadeIn" x-data>

            <h3>GOLD</h3>
            <div class="probootstrap-price-wrap">
              <span class="probootstrap-price" x-data x-text="$store.plans.getPlan('GOLD').price"></span>
              <span class="probootstrap-price-per-month">ONETIME</span>
              <hr>
              or
              <span style="font-size: 1rem; font-weight: 500;">KES 500 / 5$ per week</span>
            </div>
            <ul>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                P.O.S Sales
              </li>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                Stocks &amp; Inventory
              </li>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                Purchases
              </li>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                C.R.M (credit sales & invoicing)
              </li>
              <li>
                <i style="color:limegreen" class="icon icon-checkmark"></i>
                Finance
              </li>
            </ul>

            <p>Supports <strong>upto 3 computers</strong></p>
            <div class="cta">
              <a :href="`checkout.php?${$store.plans.getPlan('GOLD').url}`" class="btn btn-gold btn-lg">
                <i class="icon icon-cart"></i>
                Buy Now
              </a>
              <a href="#" class="btn btn-secondary btn-lg" data-toggle="modal" data-target="#free-trial-modal" @click="()=>{
                $store.plans.selectedTrial = 'GOLD'
              }">Start
                Free Trial</a>
            </div>
          </div>
        </div>
      </div>
      <!-- END row -->
    </div>
  </section>
  <!-- PRICING PLANS -->


  <!-- FEATURES -->
  <section class="probootstrap-section probootstrap-bg-white  probootstrap-zindex-above-showcase position-relative" id="features">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-md-offset-3 text-center section-heading probootstrap-animate"
          data-animate-effect="fadeIn">
          <h2>Platform Features</h2>
          <p class="lead">The following features are available on all plans of Managex</p>
        </div>
      </div>
      <!-- END row -->
      <div class="row probootstrap-gutter60 features"
        x-data="{scrollable: null, scrollItemSize: (380 + 60), atStart: true, atEnd: false}"
        x-init="()=>{
          if(!scrollable) {
                $nextTick(()=>{
                    scrollable = document.querySelector(`div.row.probootstrap-gutter60.features`)
                    $(scrollable).on('scroll', ()=> {
                        if(scrollable.scrollLeft > 0 && atStart) {
                            atStart = false
                        }
                        if(scrollable.scrollLeft == 0) {
                            atStart = true
                        }
                        if(scrollable.scrollLeft + scrollable.clientWidth >= scrollable.scrollWidth) {
                            atEnd = true
                        } else {
                            if(atEnd) {
                                atEnd = false
                            }
                        }
                    })
                })
            }
        }">
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeInLeft">
          <div class="service text-center">
            <div class="icon"><i class="icon-clipboard"></i></div>
            <div class="text">
              <h3>Inventory Management</h3>
              <p>Add and track inventory items, track stock with barcodes, manage stock levels & reorder points, add
                supplier accounts, receive stock and track supplier balances, payments & statements</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeIn">
          <div class="service text-center">
            <div class="icon"><i class="icon-cart"></i></div>
            <div class="text">
              <h3>Order Management</h3>
              <p>Take customer orders, credit/invoice sales, accept payments, generate discounts, view daily sales
                summary, sales per product reports, discount reports, sales per person and payments per day reports</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeInRight">
          <div class="service text-center">
            <div class="icon"><i class="icon-credit-card"></i></div>
            <div class="text">
              <h3>Payments</h3>
              <p>Process customer payments easily including cash, mobile payments, credit cards, PDQ receipts and bank
                transfers all within Managex.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeInLeft">
          <div class="service text-center">
            <div class="icon"><i class="icon-bubbles4"></i></div>
            <div class="text">
              <h3>C.R.M Tools</h3>
              <p>Add and manage customer accounts, credit/debit payments to customer accounts, track recurring invoices,
                debtor balances, client statements and aged debts</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeIn">
          <div class="service text-center">
            <div class="icon"><i class="icon-calculator"></i></div>
            <div class="text">
              <h3>Accounting</h3>
              <p>Generate invoices and track payments, log expenses and link to accounts, generate balance sheets, cash
                flow reports, view recurring income/expenses reports, register assets and their value, manage vendor and
                customer accounts</p>
            </div>
          </div>
        </div>

        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeInRight">
          <div class="service text-center">
            <div class="icon"><i class="icon-stats-bars"></i></div>
            <div class="text">
              <h3>Analytics & Insights</h3>
              <p>Realtime visibility into key performance indicators, view reports for operations, sales and accounting,
                make data driven decisions</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeInLeft">
          <div class="service text-center">
            <div class="icon"><i class="icon-books"></i></div>
            <div class="text">
              <h3>Learning material</h3>
              <p>Inbuilt guides and illustration videos available for all features of the system every step of the way
                to help you utilize it to the fullest</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeIn">
          <div class="service text-center">
            <div class="icon"><i class="icon-profile"></i></div>
            <div class="text">
              <h3>Administrative operations</h3>
              <p>Register unlimited staff accounts, manage their rights, assign roles accordingly and be in the loop
                about their actions via the user action logs.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeInRight">
          <div class="service text-center">
            <div class="icon"><i class="icon-shield"></i></div>
            <div class="text">
              <h3>Superior Support</h3>
              <p>Get up and running quickly with built-in training resources. Ongoing support is available should you
                get stuck anywhere and to help maximize your use of Managex.</p>
            </div>
          </div>
        </div>

        <button type="button" class="btn-control prev" x-cloak x-data x-show="!atStart" x-transition @click="()=>{
          console.log('left')
            $(scrollable).css('scroll-snap-type', 'none')
            $(scrollable).animate({scrollLeft: `-=${scrollItemSize}`}, 300, 'easeInCubic', ()=>{
                $(scrollable).css('scroll-snap-type', 'x mandatory')
            })
        }">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button type="button" class="btn-control next" x-cloak x-data x-show="!atEnd" x-transition @click="()=>{
          console.log('right')

            if(scrollable) {
                $(scrollable).css('scroll-snap-type', 'none')
                $(scrollable).animate({scrollLeft: `+=${scrollItemSize}`}, 300, 'easeInCubic', ()=>{
                    $(scrollable).css('scroll-snap-type', 'x mandatory')
                })
            } else {
              console.log('no scrollable')
            }
        }">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>
    </div>
  </section>

  <!-- Modal FREESECTION-->
  <div class="modal fade" id="free-trial-modal" tabindex="-1" role="dialog" aria-hidden="true" x-data="{...checkoutFormData(), loading: false, error: null, customer: null}">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Start Free Trial</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" x-show="!customer && !error" x-transition>
          <div class="discount-ad mb-3" style="background-color: #7ed321; padding: 1.4rem 2rem; color: white; font-size: 1.2rem; border-radius: 8px;">
            <p class="m-0">Get <strong>14% OFF</strong> and only pay <strong x-text="formatCurrency($store.plans.getPlan($store.plans.selectedTrial).rawPrice * .86, userCurrency)"></strong> if you 
              <a 
                :href="`checkout.php?${$store.plans.getPlan($store.plans.selectedTrial).url}`" 
                style="color: white; text-decoration: underline;"
              ><strong>PAY NOW!</strong></a>
            </p>
          </div>
          <h2>Your Details</h2>
          <form id="personal-details" x-transition method="POST" @submit.prevent="()=>{
            loading = true
            submitTrial($event, $store.plans.getPlan($store.plans.selectedTrial).id).then(res => {
              customer = res
            }).catch(e => error = e).finally(() => {
              loading = false
            })
          }">
            <div class="form-group">
              <label for="fname"><i class="fa fa-user"></i> Business Name *</label>
              <input type="text" id="fname" name="business_name" placeholder="Urban Gigs Ltd" class="form-control"
                x-model="fields.businessName.value" :class="fields.businessName.error && 'border-danger'"
                @blur="validateField(fields.businessName)" required>
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
                x-model="fields.email.value" :class="fields.email.error && 'border-danger'"
                @blur="validateField(fields.email)" required>
              <span class="text-danger" x-text="fields.email.error"></span>
            </div>
            <div class="form-group">
              <label for="phone"><i class="fa fa-phone"></i> Phone</label>
              <input type="text" id="phone" name="phone" placeholder="0723927876" class="form-control"
                x-model="fields.phone.value" :class="fields.phone.error && 'border-danger'"
                @blur="validateField(fields.phone)">
              <span class="text-danger" x-text="fields.phone.error"></span>
            </div>
            <small style="font-size: 14px;"><i class="fa-regular fa-lightbulb mr-1"></i> Fields marked with (*) are
              <strong>required</strong></small>
          </form>
        </div>
        <div class="modal-body" x-show="customer && !error" x-data="{downloadLoading: false}">
          <div class="row justify-content-center ">
            <div class="col-10">
              <dotlottie-player src="https://lottie.host/fad7868a-fe30-449e-a16e-5cae26c9eb4f/haWLqbDK3w.json" background="transparent" speed="1" style="width: 100%; height: 300px;" loop autoplay></dotlottie-player>
            </div>
            <div class="col-10"><span class="loader" x-show="downloadLoading" x-cloak x-transition></span></div>
            <p class="w-75 text-center">Download managex trial version through the link below, the link has also been emailed to you</p>
            <a class="btn btn-primary position-relative" x-data="{progress: null}" @download-progress.window="()=>{
              progress = $event.detail
            }" href="#" @click="()=>{
              downloadLoading = true
              downloadManagex(mgxDefaultVersion.link, megaUser).then(() => {
                updateDownloadStatus(customer?.download_id).catch(e => console.error(e))
              })
            }" style="background-color: #C3EE95;" @download-started.window="downloadLoading = false">
              <span style="position: relative; z-index: 1000;">
                Download <span x-data x-text="mgxDefaultVersion.full_name"></span>
                <span class="ml-2" x-show="progress" x-cloak x-text="`(${Math.round(progress?.percentComplete)}%)`"></span>
              </span>
              <div class="overlay position-absolute" style="top: 0; bottom: 0; left: 0; background-color: #7ed321; display: flex; justify-content: center; align-items: center;" :style="{width: `${progress?.percentComplete || 0}%`}"></div>
            </a>
            
            <a data-toggle="collapse" href="#changelog" role="button" aria-expanded="false" aria-controls="changelog" class="col-12 text-center mt-2">View Changelog</a>
            <a href="#" data-toggle="modal" data-target="#tutorialModal" class="col-12 text-center mt-2">View Installation tutorial</a>
            <div class="collapse m-3" id="changelog">
              <div class="card card-body">
                <p x-text="mgxDefaultVersion.upgrade_info"></p>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-body" x-show="error" x-transition>
          <div class="row justify-content-center ">
            <div class="col-10">
              <dotlottie-player src="https://lottie.host/3ced0611-23a9-4043-8d37-7ec8dc24091f/la75DsTAcR.json" 
                  background="transparent" speed="1" style="width: 100%; height: 300px;" loop autoplay></dotlottie-player>
            </div>
            <p class="text-center px-4">Oops! An error occured registering your details, please try again</p>
            <button class="btn btn-primary px-3 py-2" @click="()=> {
              customer =null
              error = null
            }">
              Retry
            </button>
          </div>
        </div>
        <div class="modal-footer" x-show="!customer && !error" x-transition>
          <span class="loader" x-show="loading" x-cloak x-transition></span>
          <button class="btn btn-secondary"  @click="()=>{
            if(isFormValid()) {
                document.getElementById('personal-details').dispatchEvent(new Event('submit', { bubbles: true }))
            }
          }">Continue To Trial</button>
        </div>
      </div>
    </div>
  </div>

<?php require_once('views/tutorial_modal.php') ?>
  <!-- VIDEO ILLUSTRATIONS -->
  <section class="probootstrap-section position-relative" style="padding-bottom: 7em;" id="video-illustrations" >
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-md-offset-3 text-center section-heading probootstrap-animate"
          data-animate-effect="fadeIn">
          <h2>Video Illustrations</h2>
          <p class="lead">Walkthroughs of managex features in action</p>
        </div>
      </div>
    </div>
    <div class="position-relative px-5 px-sm-0" x-data="{scrollable: null, scrollItemSize: (560 + 32), atStart: true, atEnd: false}">
      <div class="container">
        <div class="row probootstrap-animate illustrations" x-data="{videos, getThumbnail(videoLink) {
           // Extract the video ID from the YouTube link
          const videoId = videoLink.split('/').pop().split('?')[0];
          // Construct the thumbnail URL
          const thumbnailUrl = `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
          return thumbnailUrl;
        }}"
          x-init="()=>{
            if(!scrollable) {
              $nextTick(()=>{
                scrollable = document.querySelector(`div.row.illustrations`)
                $(scrollable).on('scroll', ()=> {
                  if(scrollable.scrollLeft > 0 && atStart) {
                    atStart = false
                  }
                  if(scrollable.scrollLeft == 0) {
                    atStart = true
                  }
                  if(scrollable.scrollLeft + scrollable.clientWidth >= scrollable.scrollWidth) {
                    atEnd = true
                  } else {
                    if(atEnd) {
                        atEnd = false
                    }
                  }
                })
              })
            }
          }"
        >
          <template x-for="video in videos">
            <div class="probootstrap-animate" data-animate-effect="fadeIn">
              <a :href="video.link" target="_blank" class="video-link">
                <div class="youtube-video-link position-relative" :style="{backgroundImage: `url(${getThumbnail(video.link)})`}">
                  <span><i class="fa-brands fa-youtube"></i></span>
                </div>
                <h4 x-text="video.title" class="text-center mt-2"></h4>
              </a>
            </div>
          </template>
        </div>
      </div>

      <button type="button" class="btn-control prev" x-cloak x-data x-show="!atStart" x-transition @click="()=>{
          $(scrollable).css('scroll-snap-type', 'none')
          $(scrollable).animate({scrollLeft: `-=${scrollItemSize}`}, 300, 'easeInCubic', ()=>{
              $(scrollable).css('scroll-snap-type', 'x mandatory')
          })
      }">
          <i class="fa-solid fa-chevron-left"></i>
      </button>
      <button type="button" class="btn-control next" x-cloak x-data x-show="!atEnd" x-transition @click="()=>{
  
          if(scrollable) {
              $(scrollable).css('scroll-snap-type', 'none')
              $(scrollable).animate({scrollLeft: `+=${scrollItemSize}`}, 300, 'easeInCubic', ()=>{
                  $(scrollable).css('scroll-snap-type', 'x mandatory')
              })
          } else {
            console.log('no scrollable')
          }
      }">
          <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>
  </section>

<<<<<<< Updated upstream
  <!-- CLIENTS SECTIONS -->
  <!-- <section class="probootstrap-section proboostrap-clients probootstrap-bg-white probootstrap-border-top">
    <div class="container">
      <div class="row">
        <div class="col-md-6 section-heading probootstrap-animate">
          <h2>Our Clients</h2>
          <p class="lead">A list of some of our clients</p>
         
        </div>
      </div>
     
      <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-6 text-center client-logo probootstrap-animate"
          data-animate-effect="fadeIn">
          <img src="img/client_1.png" class="img-responsive" alt="Free Bootstrap Template by uicookies.com">
        </div>
        <div class="col-md-3 col-sm-6 col-xs-6 text-center client-logo probootstrap-animate"
          data-animate-effect="fadeIn">
          <img src="img/client_2.png" class="img-responsive" alt="Free Bootstrap Template by uicookies.com">
        </div>
        <div class="clearfix visible-sm-block visible-xs-block"></div>
        <div class="col-md-3 col-sm-6 col-xs-6 text-center client-logo probootstrap-animate"
          data-animate-effect="fadeIn">
          <img src="img/client_3.png" class="img-responsive" alt="Free Bootstrap Template by uicookies.com">
        </div>
        <div class="col-md-3 col-sm-6 col-xs-6 text-center client-logo probootstrap-animate"
          data-animate-effect="fadeIn">
          <img src="img/client_4.png" class="img-responsive" alt="Free Bootstrap Template by uicookies.com">
        </div>

      </div>
    </div>
  </section> -->

  <!-- TESTIMONIALS -->
  <!-- <section class="probootstrap-section probootstrap-border-top probootstrap-bg-white" id="reviews">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 col-md-offset-3 text-center section-heading probootstrap-animate">
          <h2>What People Say</h2>
          <p class="lead">Some of the things our clients have said about it</p>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 probootstrap-animate" data-animate-effect="fadeIn">
          <div class="probootstrap-testimony-wrap text-center">
            <figure>
              <img src="img/person_1.jpg" alt="Free Bootstrap Template by uicookies.com">
            </figure>
            <blockquote class="quote">&ldquo;Thanks to managex and its simplicity, I've been able to grow my business to
              an average revenue of 22 million a year&rdquo; <cite class="author">&mdash; Super mama mboga <br>
                <span>Director</span></cite></blockquote>

          </div>
        </div>
        <div class="col-md-4 probootstrap-animate" data-animate-effect="fadeIn">
          <div class="probootstrap-testimony-wrap text-center">
            <figure>
              <img src="img/person_2.jpg" alt="Free Bootstrap Template by uicookies.com">
            </figure>
            <blockquote class="quote">&ldquo;This app is like my second brain when it comes to business, I get to keep
              track of everything in one neat place&rdquo; <cite class="author">&mdash; Steve Ajira <br>
                <span>Accountant at Big Square</span></cite></blockquote>
          </div>
        </div>
        <div class="col-md-4 probootstrap-animate" data-animate-effect="fadeIn">
          <div class="probootstrap-testimony-wrap text-center">
            <figure>
              <img src="img/person_3.jpg" alt="Free Bootstrap Template by uicookies.com">
            </figure>
            <blockquote class="quote">&ldquo;Managex gives my business more than enough room to grow, The unlimited
              monthly plan meets my needs adequately&rdquo; <cite class="author">&mdash; Frank Chimero <br>
                <span>Director Noliyam fisheries</span></cite></blockquote>
          </div>
        </div>

      </div>
    </div>
  </section> -->

  <!-- F.A.Q SECTION -->
  <!-- <section class="probootstrap-section probootstrap-bg-white probootstrap-zindex-above-showcase" id="faq">
    <div class="container probootstrap-border-top">
      <div class="row justify-content-center">
        <div class="col-md-12 text-left section-heading probootstrap-animate">
          <h2>Frequently Ask Questions</h2>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-6 probootstrap-animate" data-animate-effect="fadeIn">
          <h3>Lorem ipsum dolor sit amet?</h3>
          <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Sunt repellendus minima reprehenderit tempore
            nihil necessitatibus impedit dolorum non maiores totam amet. Eveniet voluptatum odio dolorem corrupti
            nesciunt sed cum veniam.</p>
          <h3>Incidunt earum quibusdam dolores?</h3>
          <p>Mollitia repellendus quaerat consequuntur? Vero vel sint quia reprehenderit ex tempore soluta corrupti
            voluptate quae cupiditate iusto perspiciatis eveniet culpa nesciunt modi rem. Officiis tempora sequi aut
            enim laudantium quos.</p>
          <h3>Hic a atque quibusdam?</h3>
          <p>Maxime quaerat sapiente provident iste corporis aut eum dolorum sunt a ad tenetur sit non eligendi labore.
            Illo animi accusantium nihil deserunt qui fugit officia quisquam sint ea amet. Sit?</p>
          <h3>Cumque fugit repellat magni?</h3>
          <p>Laudantium illo beatae nesciunt odio eligendi vitae quis molestias illum quas facilis. Voluptatum sunt
            totam tempore a sit repellat aperiam culpa ratione cum explicabo beatae facere aspernatur maiores est
            consectetur.</p>

        </div>
        <div class="col-md-6 probootstrap-animate" data-animate-effect="fadeIn">
          <h3>Quibusdam voluptate?</h3>
          <p>Alias optio? Eum beatae ea reiciendis laboriosam animi consequuntur quidem quia libero aliquam deserunt
            ipsam officiis saepe ad dicta accusamus et earum commodi doloribus ipsum cupiditate dolorum ullam nulla
            necessitatibus.</p>
          <h3>Eveniet qui accusantium aspernatur?</h3>
          <p>Possimus exercitationem odio repellendus quae repudiandae dolores delectus doloribus debitis maxime ab
            atque cum expedita minima suscipit quis perspiciatis impedit sit ratione qui beatae corrupti neque nostrum
            asperiores. Suscipit ullam.</p>
          <h3>Maxime consequatur ipsam?</h3>
          <p>Quam expedita sed velit id saepe aliquid natus sunt eum modi et dolorum ex incidunt debitis itaque
            voluptatem voluptatum perspiciatis molestiae cupiditate nemo reiciendis laborum tenetur placeat cum! Ducimus
            recusandae.</p>
        </div>
      </div>

    </div>
  </section> -->
=======
  <!-- HOTELEX SECTION -->
<section class="probootstrap-section probootstrap-bg-white  probootstrap-zindex-above-showcase">
  <div class="container">
      <div class="row justify-content-center" style="margin-bottom: 4rem;">
        <div class="col-md-6 col-md-offset-3 text-center section-heading probootstrap-animate"
          data-animate-effect="fadeIn">
          <h6>Other Products</h6>
          <h2>Hotele<span style="text-decoration: underline;">x</span></h2>
          <p class="lead">
            Hotelex is a point of sale software solution designed for restaurants/clubs/lounges to streamline operations and boost profitability. 
            Whether you're looking to improve sales accuracy, track inventory more efficiently or gain better insight into your sales data, 
            Hotelex has you covered.
          </p>
        </div>
      </div>

      <div class="row probootstrap-gutter60 features hotelex"
        x-data="{scrollable: null, scrollItemSize: (380 + 60), atStart: true, atEnd: false}"
        x-init="()=>{
          if(!scrollable) {
                $nextTick(()=>{
                    scrollable = document.querySelector(`div.row.probootstrap-gutter60.hotelex.features`)
                    $(scrollable).on('scroll', ()=> {
                        if(scrollable.scrollLeft > 0 && atStart) {
                            atStart = false
                        }
                        if(scrollable.scrollLeft == 0) {
                            atStart = true
                        }
                        if(scrollable.scrollLeft + scrollable.clientWidth >= scrollable.scrollWidth) {
                            atEnd = true
                        } else {
                            if(atEnd) {
                                atEnd = false
                            }
                        }
                    })
                })
            }
        }">
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeInLeft">
          <div class="service text-center">
            <div class="icon"><i class="icon-cart"></i></div>
            <div class="text">
              <h3>Point of sale</h3>
              <p>Manage retail operations from orders to billing to payments and everything in between such as handling returns and shifts, get comprehensive reports on sales, returns and payments.
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeIn">
          <div class="service text-center">
            <div class="icon"><i class="icon-clipboard"></i></div>
            <div class="text">
              <h3>Inventory Management</h3>
              <p>
                Keep track of stocks from delivery to issuing to sales via Hotelex' innovative store items integration and costing techniques. 
                Get accurate reports on stock movement per item and stock balances. 
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeInRight">
          <div class="service text-center">
            <div class="icon"><i class="icon-credit-card"></i></div>
            <div class="text">
              <h3>Finance and Accounting</h3>
              <p>
                360 degree view of your business finances accross all other modules (such as P.O.S and stocks). 
                The system jounals all transactions automatically to their respective accounts crediting and debiting them as should be.
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeInLeft">
          <div class="service text-center">
            <div class="icon"><i class="icon-home"></i></div>
            <div class="text">
              <h3>Rooms and Accomodation</h3>
              <p>
                Hotelex provides features to facilitate the business of rooms and accomodation e.g rooms register and reservation, 
                check-ins, invoicing, guest accounts management and payment processing.
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12 probootstrap-animate" data-animate-effect="fadeIn">
          <div class="service text-center">
            <div class="icon"><i class="icon-coin-dollar"></i></div>
            <div class="text">
              <h3>Payroll</h3>
              <p>
                Keep track of staff pay with hotelex payroll module which comes bundled with features that consider staff basic pay,
                benefits and deductions. Generate printouts such as Nssf Returns, Nhif Returns, Paye Monthly Returns, 
                Master Roll and Payslips.
              </p>
            </div>
          </div>
        </div>

        <button type="button" class="btn-control prev" x-cloak x-data x-show="!atStart" x-transition @click="()=>{
          console.log('left')
            $(scrollable).css('scroll-snap-type', 'none')
            $(scrollable).animate({scrollLeft: `-=${scrollItemSize}`}, 300, 'easeInCubic', ()=>{
                $(scrollable).css('scroll-snap-type', 'x mandatory')
            })
        }">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button type="button" class="btn-control next" x-cloak x-data x-show="!atEnd" x-transition @click="()=>{
          console.log('right')

            if(scrollable) {
                $(scrollable).css('scroll-snap-type', 'none')
                $(scrollable).animate({scrollLeft: `+=${scrollItemSize}`}, 300, 'easeInCubic', ()=>{
                    $(scrollable).css('scroll-snap-type', 'x mandatory')
                })
            } else {
              console.log('no scrollable')
            }
        }">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>
  </div>
</section>
>>>>>>> Stashed changes

  <!-- CONTACT BANNER -->
  <section class="probootstrap-hero probootstrap-xs-hero probootstrap-hero-colored" id="contact">
    <div class="container">
      <div class="row align-items-center py-4">
        <div class="col-md-6 text-left probootstrap-hero-text">
          <h1 class="probootstrap-animate" data-animate-effect="fadeIn">Contact Us</h1>
          <p class="probootstrap-animate" data-animate-effect="fadeIn">We'd love to get in touch with you about, feel
            free to inquire below</p>
        </div>
        <div class="col-md-6">
          <img src="img/mgx_illustration_2.jpg" 
            alt="before and after cartoon illustration image of using manageex vs without" 
            style="width: 100%; box-shadow: 10px 10px 29px -18px rgba(18,18,18,0.75);"
          >
        </div>
      </div>
    </div>
  </section>

  <!-- CONTACT SECTION -->
  <section class="probootstrap-section probootstrap-bg-white">
    <div class="container">
<<<<<<< Updated upstream
      <div class="row">
        <div class="col-md-5 probootstrap-animate" data-animate-effect="fadeIn">
          <h2>Drop us a line</h2>
          <form action="#" method="post" class="probootstrap-form">
=======
      <div class="row justify-content-center">
        <div class="col-md-6 col-xl-4 probootstrap-animate" data-animate-effect="fadeIn" x-data="{formLoading: false, sent: false}" x-show="!sent" x-transition>
          <h2>Send us a message</h2>
          <form action="#" method="post" class="probootstrap-form" x-data @submit.prevent="()=>{
            formLoading = true
            makeInquiry($event).then(inquiry => {
              document.getElementById('inquiry-response').innerHTML = `
              <div class='alert alert-success alert-dismissible fade show' role='alert'>
                We have received your message, thank you!
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                  <span aria-hidden='true'>&times;</span>
                </button>
              </div>
              `
              sent = true
            }).catch(e=> {
              document.getElementById('inquiry-response').innerHTML = `
              <div class='alert alert-danger alert-dismissible fade show' role='alert'>
                An error occured sending your message, please try again!
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                  <span aria-hidden='true'>&times;</span>
                </button>
              </div>
              `
            }).finally(() =>{
              formLoading = false
            })
          }">
>>>>>>> Stashed changes
            <div class="form-group">
              <label for="name">Full Name</label>
              <input type="text" class="form-control" id="name" name="name">
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="form-group">
              <label for="subject">Subject</label>
              <input type="text" class="form-control" id="subject" name="subject">
            </div>
            <div class="form-group">
              <label for="message">Message</label>
              <textarea cols="30" rows="10" class="form-control" id="message" name="message"></textarea>
            </div>
<<<<<<< Updated upstream
            <div class="form-group">
              <input type="submit" class="btn btn-primary btn-lg" id="submit" name="submit" value="Submit Form">
=======
            <div class="form-group d-flex align-items-center" style="gap: 1rem;">
              <input type="submit" class="btn btn-primary btn-lg" id="submit" name="submit" value="Send Message">
              <span class="loader" x-show="formLoading" x-cloak x-transition></span>
>>>>>>> Stashed changes
            </div>
          </form>
          <div id="inquiry-response">
          </div>
        </div>
        <div class="col-md-6 col-xl-4 col-md-push-1 probootstrap-animate" data-animate-effect="fadeIn" x-init="">
          <h2>Visit us in person</h2>
          <p>We are open for office visitation during business days from 9am to 6pm</p>

          <h4>Nairobi, Kenya</h4>
          <ul class="probootstrap-contact-info">
            <li><i class="icon-pin"></i> <span>Ciata city mall, Kiambu Road, Nairobi</span></li>
            <li><i class="icon-email"></i><span>info@kingsoft.biz</span></li>
            <li><i class="icon-phone"></i><span>0729 089 638</span></li>
          </ul>
<<<<<<< Updated upstream
          
=======
          <h4>Location</h4>
          <div class="map mt-1" id="map" style="height: 280px;" x-data x-init="()=>{
              const map = L.map('map').setView([-1.2258785, 36.8398238], 13);
              L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                  maxZoom: 19,
                  attribution: `&copy; <a href='http://www.openstreetmap.org/copyright'>OpenStreetMap</a>`
              }).addTo(map);
              L.marker([-1.2265015927026184, 36.83874248503855]).addTo(map);
          }"></div>
        </div>
        <div class="col-12 col-xl-4 probootstrap-animate" >
        <template x-data x-if="$store.clients.list.length" data-animate-effect="fadeIn">
            <div class="clientelle position-relative" >
              <h4 class="mb-3">Our Clients</h4>
              
              <div class="clients" x-data="{scrollable: null, scrollItemSize: null, atStart: true, atEnd: false}" >
                <template x-data x-for="client in $store.clients.list" :key="client.id">
                  <a 
                    :href="client.social.link"target="_blank" class="client"
                    x-id="['client']"
                    :id="$id('client')" 
                    x-data
                  >
                      <img src="img/client_image_placeholder.jpeg" :alt="`${client.name} logo`" x-init="()=>{
                        $el.setAttribute('src', `./uploads/${client.name}/${client.logo}`)
                      }">
                  </a>
                </template>
              </div>
            </div>
          </template>
>>>>>>> Stashed changes
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="probootstrap-footer">
    <div class="container">
      <div class="row">
        <div class="col-md-4 links">
          <div class="probootstrap-footer-widget">
            <h3>Links</h3>
            <ul>
            <li ><a href="#hero" >Plans</a></li>
            <li ><a href="#features" >Features</a></li>
            <li ><a href="#video-illustrations"> Video Illustrations</a></li>
            <li ><a href="managex_training_docs"  target="_blank">User Manual</a></li>
            <li ><a href="#contact" >Contact</a></li>
            </ul>
          </div>
        </div>

        <div class="col-md-4 probootstrap-animate">
          <div class="probootstrap-footer-widget">
            <h3>Our Socials</h3>
            <p>Find us on social media at the handles below</p>
            <ul class="probootstrap-footer-social">
              <!-- <li><a href="#"><i class="icon-twitter"></i></a></li> -->
              <li><a href="https://www.facebook.com/KingsoftCompany"><i class="icon-facebook"></i></a></li>
              <!-- <li><a href="#"><i class="icon-github"></i></a></li>
                <li><a href="#"><i class="icon-dribbble"></i></a></li>
                <li><a href="#"><i class="icon-linkedin"></i></a></li>
                <li><a href="#"><i class="icon-youtube"></i></a></li> -->
            </ul>
          </div>
        </div>
        <div class="col-md-3 probootstrap-animate">
          <img src="img/made_in_kenya.png" alt="Made in kenya badge" style="width: 100%;">
        </div>
      </div>
      <!-- END row -->
      <!-- <div class="row">
          <div class="col-md-12 copyright probootstrap-animate">
            <p><small>&copy; 2017 <a href="#">uiCookies:Inspire</a>. All Rights Reserved. <br> Designed &amp; Developed with <i class="icon icon-heart"></i> by <a href="https://uicookies.com/">uicookies.com</a></small></p>
          </div>
        </div> -->
      <div class="row">
        <div class="col-md-12 copyright probootstrap-animate">
          <p><small>&copy; 2024 <a href="#">Kingsoft Company Limited</a>. All Rights Reserved.</p>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="js/vendor/p5.min.js"></script>
  <script src="js/vendor/vanta.topology.min.js"></script>
  <script>
    // var viewportWidth = document.documentElement.clientWidth;
    VANTA.TOPOLOGY({
      el: "#hero",
      mouseControls: true,
      touchControls: true,
      gyroControls: false,
      minHeight: 200.00,
      minWidth: 200.00,
      scale: 1.00,
      scaleMobile: 1.00,
      backgroundColor: '#121420'
    })
  </script>
  <script src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous">
    </script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
    integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js"
    integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
    crossorigin="anonymous"></script>
  <script src="js/scripts.min.js"></script>
  <script src="js/custom.js"></script>
  <script src="js/app.js"></script>
  <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script> 
    <!-- Default Statcounter code for Kingsoft.biz
  http://kingsoft.biz -->
  <script type="text/javascript">
  var sc_project=11434423; 
  var sc_invisible=0; 
  var sc_security="bb4dff62"; 
  var scJsHost = "https://";
  document.write("<sc"+"ript type='text/javascript' src='" +
  scJsHost+
  "statcounter.com/counter/counter.js'></"+"script>");
  </script>
  <noscript><div class="statcounter"><a title="Web Analytics
  Made Easy - Statcounter" href="https://statcounter.com/"
  target="_blank"><img class="statcounter"
  src="https://c.statcounter.com/11434423/0/bb4dff62/0/"
  alt="Web Analytics Made Easy - Statcounter"
  referrerPolicy="no-referrer-when-downgrade"></a></div></noscript>
  <!-- End of Statcounter Code -->
</body>

</html>