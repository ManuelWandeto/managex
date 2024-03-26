function checkoutFormData() {
    const config = {
        withCredentials: true,
    };

    return {
        fields: {
            businessName: {
                value: null, error: null, rules: [
                    'required'
                ]
            },
            businessType: {
                value: undefined, error: null, rules: [
                    'required'
                ]
            },
            email: {
                value: null, error: null, rules: [
                    'required', 'email'
                ]
            },
            phone: {
                value: null, error: null, rules: [
                    'optional', 'numeric'
                ]
            }
        },
        clearForm() {
            Object.values(this.fields).forEach(f => {
                f.value = null
                f.error = null
            })
        },
        validateField(field) {
            let res = Iodine.assert(field.value, field.rules);
            field.error = res.valid ? null : res.error;
        },
        isFormInvalid: false,
        isFormValid() {
            this.isFormInvalid = false
            for (const field of Object.values(this.fields)) {
                this.validateField(field)
                if(field.error) {
                    this.isFormInvalid = true
                }
            }
            return !this.isFormInvalid
        },
        async submit(e) {
            try {
                const formData = new FormData(e.target)
                formData.set("start_date", startDate.format('YYYY-MM-DD HH:mm:ss'));
                if(endDate) {
                    formData.set("end_date", endDate.format('YYYY-MM-DD HH:mm:ss'));
                }
                formData.set("total", Alpine.store('price').total);
                if(Alpine.store('price').discounts.length) {
                    formData.set("discounts", Alpine.store('price').discounts.map(d => d.id));
                }
                formData.set('referrer', window.location.href)
                const res = await axios.post("api/order.php", formData)
                if(!res.data.status || res.data.status != 200) {
                    throw new Error('Uncaught error handling order request')
                }
                return res.data
            } catch (error) {
                console.error(error?.response?.data ?? error)
                throw error;
            }
        },
        async submitTrial(e, plan) {
            try {
                const formData = new FormData(e.target)
                formData.set('plan', plan)
                const res = await axios.post("api/register_trial.php", formData)
                if(!res.data) {
                    throw new Error('Uncaught error handling free trial request')
                }
                return res.data
            } catch (error) {
                console.error(error?.response?.data ?? error)
                throw error;
            }
        },
    }
}

function randomIdGenerator(n) {
    // This function takes a positive integer n as an argument and returns a random string of length n
    // Define the possible characters to use in the string
    let chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    // Initialize an empty string to store the result
    let result = "";
    // Loop n times
    for (let i = 0; i < n; i++) {
    // Pick a random index from 0 to chars.length - 1
    let index = Math.floor(Math.random() * chars.length);
    // Append the character at that index to the result string
    result += chars[index];
    }
    // Return the result string
    return result;
}


async function processDiscount(code) {
    try {
        const res = await axios.get(`api/process_discount.php?code=${code}`)
        return res.data
    } catch (error) {
        throw new Error(error?.response?.data ?? error);
    }
}

