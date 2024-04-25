<div class="row">
    <div class="col" x-init="()=>{
        $store.pendingCodes.getCodes()
    }">
        <div class="card w-100">
            <div class="card-header d-flex justify-content-between">
            <h5>Confirm Payments</h5>
            <button class="icon-button text-info" @click="()=>{
                $store.pendingCodes.getCodes()
            }">
                <i class="fa-solid fa-arrows-rotate"></i>
            </button>
            </div>
            <div class="card-body" x-data="{formLoading: false, confirmationCode: ''}">
                <div class="row">
                <div class="col-md-5">
                    <form action="" @submit.prevent="()=>{
                    formLoading = true
                    addPayment($event).then(data=>{
                        document.getElementById('status-response').innerHTML = `
                        <div class='alert alert-success alert-dismissible fade show' role='alert'>
                            Successfully added payment
                            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                <span aria-hidden='true'>&times;</span>
                            </button>
                        </div>
                        `
                        $store.pendingCodes.getCodes()

                    }).catch(e=>{
                        document.getElementById('status-response').innerHTML = `
                        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
                            ${e}
                            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                <span aria-hidden='true'>&times;</span>
                            </button>
                        </div>
                        `
                    }).finally(() => {
                        formLoading = false;
                    })
                    }">
                    <div class="form-group">
                        <label for="confirmation">Confirmation Code</label>
                        <input type="text" name="confirmation_code" id="confirmation" class="form-control" x-model="confirmationCode">
                    </div>
                    <div class="form-group mt-3">
                        <label for="confirmed_amount">Amount</label>
                        <input type="text" name="amount" id="confirmed_amount" class="form-control">
                    </div>
                    <button 
                        class="btn btn-primary w-100 mt-3 d-flex align-items-center justify-content-center" 
                        type="submit"
                        style="gap: 1rem;"
                    >
                        Add payment <span class="loader" x-show="formLoading" x-cloak x-transition></span>
                    </button>
                    
                    </form>
                    <div id="status-response" class="mt-3"></div>
                </div>
                <div class="col-md-7">
                    <div class="d-flex align-items-center justify-content-center" style="gap: 1rem;">
                    <h5>Pending codes</h5>
                    <span class="loader" x-show="!$store.pendingCodes.isLoaded"></span>
                    </div>
                    <template x-data x-if="$store.pendingCodes.list.length">
                    <div class="container codes-list" style="height: 350px; overflow-y: auto;">
                        <template x-for="code in $store.pendingCodes.list" :key="code.confirmation_code">
                        <div class="pending-code mb-3">
                            <div class="row align-items-center justify-content-center">
                            <div class="col-sm-8 d-flex flex-column">
                                <span x-text="code.confirmation_code" class="confirmation"></span>
                                <span x-text="code.created_at" class="paid_time"></span>
                            </div>
                            <div class="col-sm-4">
                                <span x-text="formatCurrency(code.invoice_amount, code.currency)" class="amount"></span>
                                <button class="icon-button" @click="()=>{
                                confirmationCode = code.confirmation_code
                                }">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </button>
                            </div>
                            </div>
                        </div>
                        </template>
                    </div>
                    </template>
                    <template x-if="$store.pendingCodes.isLoaded && !$store.pendingCodes.list.length">
                    <div class="text-center mt-3">
                        <img src="./assets/images/no_data.svg" alt="Empty clipboard illustration" style="width: 100%; height: 180px;">
                        <p class="mt-3">No pending codes at this time</p>
                    </div>
                    </template>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col">
        <div class="card w-100" x-init="()=>{
            $store.inquiries.getInquiries()
        }">
            <div class="card-body p-4">
            <!-- <h5 class="card-title fw-semibold mb-4">Customer Inquiries</h5> -->
            <div class="d-flex align-items-center mb-4" style="gap: 1rem">
                <h5>Customer Inquiries</h5>
                <span class="loader" x-show="!$store.inquiries.isLoaded"></span>
            </div>
            <template x-if="$store.inquiries.list.length">
                <div class="table-responsive">
                <table class="table text-nowrap mb-0 align-middle">
                    <thead class="text-dark fs-4">
                    <tr>
                        <th class="border-bottom-0">
                        <h6 class="fw-semibold mb-0">Inquiry</h6>
                        </th>
                        <th class="border-bottom-0">
                        <h6 class="fw-semibold mb-0">Customer name</h6>
                        </th>
                        <th class="border-bottom-0">
                        <h6 class="fw-semibold mb-0">Customer phone</h6>
                        </th>
                        <th class="border-bottom-0">
                        <h6 class="fw-semibold mb-0">Message</h6>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <template x-for="(inquiry, index) in $store.inquiries.list" :key="inquiry.id">
                        <tr>
                        <td class="border-bottom-0"><h6 class="fw-semibold mb-0" x-text="index + 1"></h6></td>
                        <td class="border-bottom-0">
                            <h6 class="fw-semibold mb-1" x-text="inquiry.customer_name"></h6>
                            <!-- <span class="fw-normal">Web Designer</span>                           -->
                        </td>
                        <td class="border-bottom-0">
                            <p class="mb-0 fw-normal" x-text="inquiry.customer_phone"></p>
                        </td>
                        <td class="border-bottom-0" x-id="['message-modal']">
                            <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-primary rounded-3 fw-semibold"
                            data-bs-toggle="modal" :data-bs-target="`#${$id('message-modal')}`"
                            >View Message</button>
                            <!-- Modal -->
                            <div class="modal fade" :id="$id('message-modal')" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                    <h1 class="modal-title fs-5">Customer says:</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                    <pre x-text="inquiry.message"></pre>
                                    </div>
                                    <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                                </div>
                            </div>
                            </div>
                        </td>
                        </tr>                       
                    </template>
                    </tbody>
                </table>
                </div>
            </template>
            </div>
        </div>
    </div>
</div>
