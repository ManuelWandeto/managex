document.addEventListener('alpine:init', ()=>{
    Alpine.store('clients', {
        list: [],
        isLoaded: false,
        addClient(client) {
            this.list.push(client)
        },
        updateClient(clientId, fields) {
            index = this.list.findIndex(c => c.id == clientId);
            this.list[index] = {
                ...this.list[index],
                ...fields
            }
        },
        async getClients() {
            try {
                this.isLoaded = false
                const res = await axios.get('../api/clients/get_clients.php');
                if(!res.data?.length) {
                    throw new Error('Uncaught error getting clients')
                }
                this.list = res.data
            } catch (error) {
                console.log(error?.response?.data ?? error)
            } finally {
                this.isLoaded = true
            }
        },
        deleteClient(id) {
            index = this.list.findIndex(c => c.id == id);
            this.list.splice(index, 1);
        }
    })
    Alpine.store('inquiries', {
        list: [],
        isLoaded: false,
        async getInquiries() {
            try {
                this.isLoaded = false
                const res = await axios.get('../api/inquiries/get_inquiries.php');
                if(!res.data?.length) {
                    throw new Error('Uncaught error getting inquiries')
                }
                this.list = res.data
            } catch (error) {
                console.log(error?.response?.data ?? error)
            } finally {
                this.isLoaded = true
            }
        }
    })
    Alpine.store('clients').getClients()
    Alpine.store('inquiries').getInquiries()
})