function clearFormErrors(fields) {
    Object.values(fields).forEach(field => {
        if (field?.error) {
            field.error = null
        }
    })
}
function formatCurrency(value, currency) {
    const locale = currency == 'KES' ? 'en-KE' : 'en-US'
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
    }).format(value);
}