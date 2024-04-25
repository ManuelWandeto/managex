<!--  Row 1 -->
<div class="row">
    <div class="col">
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
    <div class="card w-100">
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



<template x-if="false">
    <div>
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex justify-content-between align-items-center" style="gap: 1rem">
                <h4>Registered Clients</h4>
                <span class="loader" x-show="!$store.clients.isLoaded"></span>
                </div>
            <input type="text" placeholder="search client" class="form-control" style="max-width: 250px;" x-model="clientSearch">
        </div>
        <template x-data x-if="$store.clients.isLoaded && $store.clients.list.length">
            <div class="row mt-3 py-3 clients">
                <template x-for="client in $store.clients.list.filter(c => c.name.toLowerCase().includes(clientSearch.toLowerCase()))">
                <div class="col-sm-6 col-xl-3 client" x-id="['client']">
                    <div class="card overflow-hidden rounded-2">
                    <div class="position-relative logo-container">
                        <a href="javascript:void(0)"><img :src="`../uploads/${client.name}/${client.logo}`" class="card-img-top rounded-0" :alt="`${client.name} logo`"></a>
                        <a href="javascript:void(0)" 
                        class="bg-primary rounded-circle p-2 text-white d-inline-flex position-absolute bottom-0 end-0 mb-n3 me-3 edit-client"
                        @click="()=>{
                            $dispatch('edit-client', client)
                            document.getElementById('add-client').scrollIntoView({behavior: 'smooth', block: 'end'})
                        }"
                        >
                        <i class="fa-solid fa-pen"></i>
                        </a>                      
                    </div>
                    <div class="card-body">
                        <div class="drawable">
                        <h6 class="fw-semibold fs-4" x-text="client.name"></h6>
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="fw-semibold fs-4 mb-0">
                            Since: 
                            <span class="ms-2 fw-normal text-muted fs-3" 
                                x-text="client.installation_year ? new Date(client.installation_year).getFullYear() : 'Not set'"
                            ></span>
                            </h6>
                        </div>
                        <div class="more">
                            <p x-text="client.testimonial" class="mt-3"></p>
                            <a href="#" data-bs-toggle="modal" :data-bs-target="`#${$id('client')}`"
                            x-show="client.images?.length">
                            View Client Images
                            </a>
                        </div>
                        </div>
                    </div>
                    </div>
                    <div class="modal fade" :id="$id('client')" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" x-text="`${client.name} Images`"></h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <template x-if="client.images?.length">
                            <div class="client-images">
                                <template x-for="image in client.images">
                                <img src="../Images/client_image_placeholder.jpeg" :alt="`${client.name} client image`" x-intersect="()=>{
                                    $el.setAttribute('src', `../uploads/${client.name}/images/${image}`)
                                }">
                                </template>
                            </div>
                            </template>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                        </div>
                    </div>
                    </div>
                </div>
                </template>
            </div>
        </template>
        <!-- Row 2 -->
        <div class="row">
            <div class="col d-flex align-items-strech">
                <div class="card w-100" id="add-client" x-data="{editMode: false, clientData: null}" :class="editMode && 'border border-info'">
                <div class="card-header d-flex justify-content-between">
                    Add A Client 
                    <button class="icon-button text-danger" x-show="editMode" @click="()=>{
                        editMode = false
                        $dispatch('clear-form')
                    }">
                    <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="card-body">
                    <form action="" x-data="clientFormData()" id="add-client-form" @submit.prevent="()=>{
                    if(editMode) {
                        submitEdit($event, clientData.id).finally(()=>{
                            editMode = false
                            $dispatch('clear-form')
                        })
                    } else {
                        submit($event).finally(()=>{
                            editMode = false
                            $dispatch('clear-form')
                        })
                    }
                    }" @edit-client.window="()=>{
                        editMode = true
                        editClient($event.detail)
                        clientData = $event.detail
                    }" @clear-form.window="()=>{
                        $el.reset()
                        if(fields.images?.length) {
                            fields.images = null
                        }
                        if(fields.logo) {
                            fields.logo = null
                        }
                    }">
                    <div class="form-group mb-3">
                        <label for="client-name" class="form-label">Name</label>
                        <input type="text" class="form-control" placeholder="Client name" id="client-name" name="client_name" required
                        x-model="fields.name.value" @blur="validateField(fields.name)" :class="fields.name.error && 'border-danger'">
                        <span class="form-text text-danger" x-text="fields.name.error"></span>
                    </div>
                    <div class="form-group">
                        <label for="testimonial" class="form-label">Client Testimonial</label>
                        <textarea name="testimonial" id="testimonial" cols="30" rows="3" placeholder="Client says..." class="form-control"
                        style="resize: none;" required
                        x-model="fields.testimonial.value"
                        @blur="validateField(fields.testimonial)" 
                        :class="fields.testimonial.error && 'border-danger'"
                        ></textarea>
                        <span class="form-text d-flex justify-content-between mb-1">
                        <span class="text-danger" x-text="fields.testimonial.error ?? ''"></span>
                        <span 
                            x-text="`${fields.testimonial.value?.length ?? 0}/350`" 
                            class="form-text" :class="fields.testimonial.value?.length > 350 && 'text-danger'"
                        >
                        </span>
                        </span>
                    </div>
                    <div class="form-group mb-3">
                        <label for="installation-year" class="form-label">Year of Installation</label>
                        <input type="date" class="form-control" name="installation_year" aria-describedby="installation-year-help"
                        x-model="fields.installationYear.value"
                        @blur="validateField(fields.installationYear)" 
                        :class="fields.installationYear.error && 'border-danger'">
                        <div class="form-text" id="installation-year-help" x-show="!fields.installationYear.error">
                        Doesn't have to be the exact date just the year
                        </div>
                        <span class="form-text text-danger" x-text="fields.installationYear.error"></span>
                    </div>
                    <div class="form-group mb-3">
                        <label for="client-logo" class="form-label">Client logo</label>
                        <input type="file" name="client_logo" class="form-control" accept="image/png, image/jpeg" :required="!fields.logo" id="client_logo"
                        @change="()=>{
                        fields.logo = $event.target.files[0]
                        }">
                        <template x-data x-if="fields.logo" :key="fields.logo?.name ?? fields.logo">
                        <div class="file mt-2" x-data="{loaded: false, src: null, error: null}" style="max-width: 200px;">
                            <img class="preview" :src="src || '../Images/client_image_placeholder.jpeg'" :alt="fields.logo?.name ?? fields.logo" x-effect="()=>{
                            if (fields.logo?.type) {
                                var reader = new FileReader();
                                reader.addEventListener('load', (e)=>{
                                src = reader.result
                                })
                                reader.addEventListener('error', (e)=>{
                                error = `Error reading file`
                                })
                                reader.readAsDataURL(fields.logo)
                            } else {
                                src = `../uploads/${clientData.name}/${fields.logo}`
                            }
                            }">
                            <div class="file-info">
                            <span class="file-size" x-text="returnFileSize(fields.logo.size)"></span>
                            <button type="button" class="remove" @click="()=>{
                                if (fields.logo?.type) {
                                removeFileFromFileList(fields.logo.name, 'client_logo')
                                fields.logo = null
                                } else {
                                fields.logo = null
                                }
                            }">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            </div>
                        </div>
                        </template>
                    </div>
                    <div class="form-group mb-3" x-data="{deleteImageModal: null}">
                        <label for="client-images" class="form-label">Client Images</label>
                        <input type="file" class="form-control" id="client-images" multiple accept="image/png, image/jpeg" name="client_images[]"
                        @change="()=>{
                            let images = Array.from($event.target.files)
                            if(editMode && fields.images?.length) {
                                fields.images = [...fields.images, ...images]
                            } else {
                                fields.images = images
                            }
                        }"
                        >
                        <template x-if="fields.images?.length">
                        <div class="selected-files my-3">
                            <template x-for="file in fields.images" :key="file?.name ?? file">
                            <div class="file" x-data="{loaded: false, src: null, error: null}">
                                <img class="preview" :src="src || '../Images/client_image_placeholder.jpeg'" :alt="file?.name ?? file" x-init="()=>{
                                if (file?.type) {
                                    var reader = new FileReader();
                                    reader.addEventListener('load', (e)=>{
                                    src = reader.result
                                    })
                                    reader.addEventListener('error', (e)=>{
                                    error = `Error reading file`
                                    })
                                    reader.readAsDataURL(file)
                                } else {
                                    src = `../uploads/${clientData.name}/images/${file}`
                                }
                                }">
                                <div class="file-info">
                                <span class="file-size" x-text="returnFileSize(file.size)"></span>
                                <button type="button" class="remove" @click="()=>{
                                    if(file?.type) {
                                    removeFileFromFileList(file.name, 'client-images')
                                    const i = fields.images.findIndex(f => f.name === file.name)
                                    fields.images.splice(i, 1)
                                    } else {
                                    clientData['file'] = file
                                    deleteImageModal = new bootstrap.Modal(document.getElementById('delete-client-image'))
                                    deleteImageModal.show()
                                    }
                                }">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                </div>
                            </div>
                            </template>
                        </div>
                        </template>
                        <div class="modal fade" id="delete-client-image" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Delete image</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Are you sure you want to delete the <q x-text="clientData?.file"></q> image from the server?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-danger" @click="()=>{
                                deleteImage(clientData.file, clientData.id, clientData.name).then(ok => {
                                    if(ok) {
                                    const i = fields.images.findIndex(f => f === clientData.file)
                                    fields.images.splice(i, 1)
                                    const newImages = clientData.images?.filter(i => i !== clientData.file)
                                    $store.clients.updateClient(clientData.id, {images: newImages?.length ? newImages : null})
                                    }
                                }).catch(e=>{
                                    console.log(e)
                                }).finally(()=>{
                                    // deleteImageModal.hide()
                                    bootstrap.Modal.getInstance(document.getElementById('delete-client-image')).hide()
                                })
                                }">Delete file</button>
                            </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" :disabled="isFormInvalid">Submit</button>
                    <button type="button" class="btn btn-danger ms-3" x-show="editMode" @click="()=>{
                        new bootstrap.Modal(document.getElementById('delete-client')).show()
                        
                    }">Remove client</button>
                    <div class="modal fade" id="delete-client" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Delete client</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                            Are you sure you want to delete <q x-text="clientData?.name"></q>?
                            </div>
                            <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger" @click="()=>{
                                deleteClient(clientData.id).then(ok=> {
                                if(ok) {
                                    $store.clients.deleteClient(clientData.id)
                                }
                                })
                                .catch(e=>console.log(e))
                                .finally(()=>{
                                bootstrap.Modal.getInstance(document.getElementById('delete-client')).hide()
                                editMode = false
                                $dispatch('clear-form')
                                })
                            }">Delete Client</button>
                            </div>
                        </div>
                        </div>
                    </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
    </div>
</template>
=======
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
