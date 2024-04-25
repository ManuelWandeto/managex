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
    Alpine.store('pendingCodes', {
        list: [],
        isLoaded: false,
        async getCodes() {
            try {
                this.isLoaded = false
                const res = await axios.get('../api/payments/get_pending_payments.php');
                this.list = res.data
            } catch (error) {
                console.log(error?.response?.data ?? error)
            } finally {
                this.isLoaded = true
            }
        }
    })
    Alpine.store('reports', {
        general: {
            isLoaded: false,
            stats: null,
            async getStats() {
                try {
                    this.isLoaded = false
                    const res = await axios.get('../api/reports/get_general_stats.php')
                    this.stats = res.data
                } catch (error) {
                    console.log(error?.response?.data ?? error)
                } finally {
                    this.isLoaded = true
                }
            }
        },
        downloadsPerDay: {
            isLoaded: false,
            stats: null,
            filters: {
                timeUnit: 'day',
                period: {
                    allTime: false,
                    from: moment().subtract(28, 'days').format('YYYY-MM-DD'),
                    to: moment().format('YYYY-MM-DD'),
                },
                clearFilters() {
                    this.timeUnit = 'day'
                    this.period.allTime = false
                    this.period.from = moment().subtract(28, 'days').format('YYYY-MM-DD'),
                    this.period.to = moment().format('YYYY-MM-DD')
                }
            },
            async getStats() {
                try {
                    this.isLoaded = false
                    const res = await axios.get('../api/reports/get_downloads_per_day.php', {
                        params: {
                            unit: this.filters.timeUnit,
                            ...(
                                this.filters.period.allTime 
                                ? {'all-time': true} 
                                : {from: this.filters.period.from, to: this.filters.period.to}
                            ),
                        }
                    })
                    this.stats = res.data
                } catch (error) {
                    console.log(error?.response?.data ?? error)
                } finally {
                    this.isLoaded = true
                }
            }
        },
        downloadsPerBusiness: {
            isLoaded: false,
            stats: null,
            async getStats() {
                try {
                    this.isLoaded = false
                    const res = await axios.get('../api/reports/get_downloads_per_business.php')
                    this.stats = res.data
                } catch (error) {
                    console.log(error?.response?.data ?? error)
                } finally {
                    this.isLoaded = true
                }
            }
        }
    })
    // Alpine.store('clients').getClients()
    Alpine.store('inquiries').getInquiries()
    Alpine.store('pendingCodes').getCodes()
    Alpine.store('clients').getClients()
    Alpine.store('inquiries').getInquiries()
    Alpine.store('pendingCodes', {
        list: [],
        isLoaded: false,
        async getCodes() {
            try {
                this.isLoaded = false
                const res = await axios.get('../api/payments/get_pending_payments.php');
                this.list = res.data
            } catch (error) {
                console.log(error?.response?.data ?? error)
            } finally {
                this.isLoaded = true
            }
        }
    })
    Alpine.store('reports', {
        general: {
            isLoaded: false,
            stats: null,
            async getStats() {
                try {
                    this.isLoaded = false
                    const res = await axios.get('../api/reports/get_general_stats.php')
                    this.stats = res.data
                } catch (error) {
                    console.log(error?.response?.data ?? error)
                } finally {
                    this.isLoaded = true
                }
            }
        },
        downloadsPerDay: {
            isLoaded: false,
            stats: null,
            filters: {
                timeUnit: 'day',
                period: {
                    allTime: false,
                    from: moment().subtract(28, 'days').format('YYYY-MM-DD'),
                    to: moment().format('YYYY-MM-DD'),
                },
                clearFilters() {
                    this.timeUnit = 'day'
                    this.period.allTime = false
                    this.period.from = moment().subtract(28, 'days').format('YYYY-MM-DD'),
                    this.period.to = moment().format('YYYY-MM-DD')
                }
            },
            async getStats() {
                try {
                    this.isLoaded = false
                    const res = await axios.get('../api/reports/get_downloads_per_day.php', {
                        params: {
                            unit: this.filters.timeUnit,
                            ...(
                                this.filters.period.allTime 
                                ? {'all-time': true} 
                                : {from: this.filters.period.from, to: this.filters.period.to}
                            ),
                        }
                    })
                    this.stats = res.data
                } catch (error) {
                    console.log(error?.response?.data ?? error)
                } finally {
                    this.isLoaded = true
                }
            }
        },
        downloadsPerBusiness: {
            isLoaded: false,
            stats: null,
            async getStats() {
                try {
                    this.isLoaded = false
                    const res = await axios.get('../api/reports/get_downloads_per_business.php')
                    this.stats = res.data
                } catch (error) {
                    console.log(error?.response?.data ?? error)
                } finally {
                    this.isLoaded = true
                }
            }
        }
    })

})