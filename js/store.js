document.addEventListener('alpine:init', () => {
    Alpine.store('clients', {
        list: [],
        isLoaded: false,
        async getClients() {
            this.isLoaded = false
            try {
                const res = await axios.get('api/clients/get_clients.php');
                if(!res.data.length) {
                    throw new Error('Uncaught error getting clients')
                }
                this.list = Array.from(res.data)
            } catch (error) {
                console.error(error?.response?.data ?? error)
            } finally {
                this.isLoaded = true
            }
        }
    })
    Alpine.store('clients').getClients()
})