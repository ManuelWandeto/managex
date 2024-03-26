function clearFormErrors(fields) {
    Object.values(fields).forEach(field => {
        if (field?.error) {
            field.error = null
        }
    })
}