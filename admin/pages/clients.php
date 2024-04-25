<!-- Row 2 -->
<div class="row">
    <div class="col d-flex align-items-strech">
        <div class="card w-100" id="add-client" x-data="{editMode: false, clientData: null}"
            :class="editMode && 'border border-info'">
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
                        $nextTick(() => { 
                            editMode = true
                            clientData = $event.detail
                            editClient($event.detail)
                            console.log(clientData)
                        });
                    }" @clear-form.window="()=>{
                        $el.reset()
                        if(fields.images?.length) {
                            fields.images = null
                        }
                        if(fields.socials?.length) {
                            fields.socials = null
                        }
                        if(fields.logo) {
                            fields.logo = null
                        }
                    }"
                >
                    <div class="form-group mb-3">
                        <label for="client-name" class="form-label">Name</label>
                        <input type="text" class="form-control" placeholder="Client name" id="client-name"
                            name="client_name" required x-model="fields.name.value" @blur="validateField(fields.name)"
                            :class="fields.name.error && 'border-danger'">
                        <span class="form-text text-danger" x-text="fields.name.error"></span>
                    </div>
                    <div class="form-group mb-3 socials" >
                        <label for="socials" class="form-label">Social</label>
                        <div class="d-flex">
                            <select id="socials" class="form-control" name="platform" required 
                                x-model="fields.platform.value" 
                                @blur="validateField(fields.platform)"
                                :class="fields.platform?.error && 'border-danger'"
                            >
                                <option value="" selected disabled>Choose platform</option>
                                <option value="facebook">Facebook <i class="fa-brands fa-facebook"></i></option>
                                <option value="instagram">Instagram</option>
                                <option value="twitter">Twitter</option>
                                <option value="youtube">Youtube</option>
                                <option value="tiktok">TikTok</option>
                                <option value="linked-in">Linked-In</option>
                            </select>
                            <input type="text" class="form-control" placeholder="Platform Link" name="platform_link" 
                                x-model="fields.platformLink.value" 
                                @blur="validateField(fields.platformLink)"
                                :class="fields.platformLink?.error && 'border-danger'"
                            >
                        </div>
                        <span class="form-text text-danger" x-text="fields.platform?.error || fields.platformLink?.error"></span>
                    </div>
                    <div class="form-group mb-3">
                        <label for="client-logo" class="form-label">Client logo</label>
                        <input type="file" name="client_logo" class="form-control" accept="image/png, image/jpeg"
                            :required="!fields.logo" id="client_logo" @change="()=>{
                                fields.logo = $event.target.files[0]
                            }"
                        >
                        <template x-data x-if="fields.logo" :key="fields.logo?.name ?? fields.logo">
                            <div class="file mt-2" x-data="{loaded: false, src: null, error: null}"
                                style="max-width: 200px;">
                                <img class="preview" :src="src || '../assets/img/client_image_placeholder.jpeg'"
                                    :alt="fields.logo?.name ?? fields.logo" x-effect="()=>{
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
                                    }"
                                >
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
                    <button type="submit" class="btn btn-primary" :disabled="isFormInvalid">Submit</button>
                    <button type="button" class="btn btn-danger ms-3" x-show="editMode" @click="()=>{
                        new bootstrap.Modal(document.getElementById('delete-client')).show()
                        
                    }">Remove client</button>
                    <div class="modal fade" id="delete-client" tabindex="-1"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Delete client</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete <q x-text="clientData?.name"></q>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
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

<div class="d-flex justify-content-between align-items-center" x-init="()=>{
    $store.clients.getClients()
}">
    <div class="d-flex justify-content-between align-items-center" style="gap: 1rem">
        <h4>Registered Clients</h4>
        <span class="loader" x-show="!$store.clients.isLoaded"></span>
    </div>
    <input type="text" placeholder="search client" class="form-control" style="max-width: 250px;"
        x-model="clientSearch">
</div>
<template x-data x-if="$store.clients.isLoaded && $store.clients.list.length">
    <div class="row mt-3 py-3 clients">
        <template
            x-for="client in $store.clients.list.filter(c => c.name.toLowerCase().includes(clientSearch.toLowerCase()))" :key="client.id">
            <div class="col-sm-6 col-xl-3 client" x-id="['client']">
                <div class="card overflow-hidden rounded-2">
                    <div class="position-relative logo-container">
                        <a href="javascript:void(0)"><img :src="`../uploads/${client.name}/${client.logo}`"
                                class="card-img-top rounded-0" :alt="`${client.name} logo`"></a>
                        <a href="javascript:void(0)"
                            class="bg-primary rounded-circle p-2 text-white d-inline-flex position-absolute bottom-0 end-0 mb-n3 me-3 edit-client"
                            @click="()=>{
                                $dispatch('edit-client', client)
                                document.getElementById('add-client').scrollIntoView({behavior: 'smooth', block: 'end'})
                            }">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="drawable">
                            <h6 class="fw-semibold fs-4" x-text="client.name"></h6>
                        </div>
                    </div>
                </div>
                <div class="modal fade" :id="$id('client')" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" x-text="`${client.name} Images`"></h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <template x-if="client.images?.length">
                                    <div class="client-images">
                                        <template x-for="image in client.images">
                                            <img src="../assets/img/client_image_placeholder.jpeg"
                                                :alt="`${client.name} client image`" x-intersect="()=>{
                                                    $el.setAttribute('src', `../uploads/${client.name}/images/${image}`)
                                                }"
                                            >
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