function checkoutFormData() {
    const config = {
        withCredentials: true,
    };

    return {
        fields: {
            fullName: {
                value: null, error: null, rules: [
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
            },
            address: {
                value: null, error: null, rules: [
                    'optional'
                ]
            },
            city: {
                value: null, error: null, rules: [
                    'optional'
                ]
            },
            state: {
                value: undefined, error: null, rules: [
                    'optional'
                ]
            },
            postalCode: {
                value: null, error: null, rules: [
                    'optional'
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
                formData.set("merchant_id", randomIdGenerator(16));
                formData.set("start_date", startDate.format('YYYY-MM-DD HH:mm:ss'));
                formData.set("end_date", endDate.format('YYYY-MM-DD HH:mm:ss'));
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

function formatCurrency(value, currency, locale) {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
    }).format(value);
}