<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<section class="probootstrap-section probootstrap-pricing-table mt-5" x-show="false" x-data>
    <div class="container-xl">
      <div class="row probootstrap-pricing-wrap">
        <div class="col card probootstrap-pricing probootstrap-animate" data-animate-effect="fadeIn">
          <table class="table table-hover table-bordered" style="text-align:center;">
            <thead>
              <tr class="active">
                <th style="background:#fff">
                  <center></center>
                </th>
                <th>
                  <center>
                    <h3>Bronze</h3>
                    <p class="text-muted text-sm">Ideal for small busineses. Supports 1 computer only</p>
                  </center>
                </th>
                <th>
                  <center>
                    <h3>Silver</h3>
                    <p class="text-muted text-sm">Ideal for small busineses. Supports 1 computer only</p>
                  </center>
                </th>
                <th>
                  <center>
                    <h3>Gold</h3>
                    <p class="text-muted text-sm">Ideal for regular busineses. Supports 3 computers</p>
                  </center>
                </th>
                <th>
                  <center>
                    <h3>Free Trial</h3>
                    <p class="text-muted text-sm">Try out all features for a limited period of 7 days.</p>
                  </center>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><br />Price</td>
                <td>
                  <h3 class="panel-title price" x-data x-text="$store.plans.getPlan('BRONZE').price"></h3>
                </td>
                <td>
                  <h3 class="panel-title price" x-data x-text="$store.plans.getPlan('SILVER').price"></h3>
                </td>
                <td x-data="{monthly: false, plan: $store.plans.getPlan('GOLD')}" x-effect="()=>{
                  if(monthly) {
                    plan.model = 'MONTHLY'
                  } else {
                    plan.model = 'ONETIME'
                  }
                }">
                  <h3 class="panel-title price mb-0" x-text="plan.price"></h3>
                  <label class="switch mt-2">
                    <input type="checkbox" x-model="monthly">
                    <span class="slider round"></span>
                  </label>
                </td>
                <td>
                  <h3 class="panel-title price">FREE</h3>
                </td>
              </tr>
              <tr>
                <td colspan="5" align="left" style="padding-left:20px;" class="active"><b>Stocks</b></td>
              </tr>
              <tr>
                <td>Stocks Categorization</td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
              </tr>
              <tr>
                <td>Register & Management</td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
              </tr>
              <tr>
                <td>Stock balances & Movement</td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
              </tr>
              <tr>
                <td>Stocks Value</td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
              </tr>
              <tr>
                <td colspan="5" align="left" style="padding-left:20px;" class="active"><b>Purchases</b></td>
              </tr>
              <tr>
                <td>Supplier Records, statement, balances</td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
              </tr>
              <tr>
                <td>Goods Recieved G.R.N(s)</td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
              </tr>
              <tr>
                <td colspan="5" align="left" style="padding-left:20px;" class="active"><b>C.R.M Tools</b></td>
              </tr>
              <tr>
                <td>Credit customers register</td>
                <td><i style="color:darkgrey" class="icon icon-cross"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
              </tr>
              <tr>
                <td>Invoicing, debt tracking, Receipts</td>
                <td><i style="color:darkgrey" class="icon icon-cross"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td>Customer balances, Statements</td>
                <td><i style="color:darkgrey" class="icon icon-cross"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td>Receivables in Accounts</td>
                <td><i style="color:darkgrey" class="icon icon-cross"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td colspan="5" align="left" style="padding-left:20px;" class="active"><b>P.O.S Sales</b></td>
              </tr>
              <tr>
                <td>2 Levels of Packaging (Large & Small)</td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td>Receipt</td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td>Quotations and Invoices</td>
                <td><i style="color:darkgrey" class="icon icon-cross"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td>Delivery note</td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td>No Credit/invoice sales</td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td>Daily Summary</td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td>POS Reports (Discount, Qty sold, Sale per person)</td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td colspan="5" align="left" style="padding-left:20px;" class="active"><b>Finance</b></td>
              </tr>
              <tr>
                <td>Chart of accounts</td>
                <td><i style="color:darkgrey" class="icon icon-cross"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td>Integration to Stocks value, CRM Receivables, POS Income, Cost of goods, Payments</td>
                <td><i style="color:darkgrey" class="icon icon-cross"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td>Profit and Loss (Monthly, yearly, per Product)</td>
                <td><i style="color:darkgrey" class="icon icon-cross"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td>Balance sheet.</td>
                <td><i style="color:darkgrey" class="icon icon-cross"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr>
                <td>Cash flow (and other ledgers)</td>
                <td><i style="color:darkgrey" class="icon icon-cross"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
                <td><i style="color:limegreen" class="icon icon-checkmark"></i></i></td>
              </tr>
              <tr x-data>
                <td></td>
                <td><a class="btn btn-primary" style="margin-top:10px; margin-bottom:10px"
                    :href="`checkout.php?${$store.plans.getPlan('BRONZE').url}`">Download</a></td>
                <td><a class="btn btn-primary" style="margin-top:10px; margin-bottom:10px"
                    :href="`checkout.php?${$store.plans.getPlan('SILVER').url}`">Download</a></td>
                <td><a class="btn btn-primary" style="margin-top:10px; margin-bottom:10px"
                    :href="`checkout.php?${$store.plans.getPlan('GOLD').url}`">Download</a></td>
                <td><a class="btn btn-primary" style="margin-top:10px; margin-bottom:10px" data-toggle="modal"
                    data-target="#free-trial-modal">Download</a></td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- END row -->
      </div>
    </div>
  </section>
</body>
</html>