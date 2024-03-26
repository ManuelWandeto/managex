function clientFormData() {
  Iodine.rule('beforeToday', (value) => {
      return new Date(value) < Date.now();
  });
  Iodine.setErrorMessage('beforeToday', "Installation date must be before today");
  const config = {
    withCredentials: true,
  };
  return {
    fields: {
      name: {value: null, error: null, rules: [
          'required', 'minLength:3'
      ]},
      testimonial: {
          value: null, error: null, rules: [
          'required', 'minLength:10', 'maxLength:350'
          ]
      },
      installationYear: {
          value: null, error: null, rules: [
          'optional', `beforeToday`
          ]
      },
      logo: null,
      images: []
    },
    validateField(field) {
      let res = Iodine.assert(field.value, field.rules);
      field.error = res.valid ? null : res.error;
      this.isFormValid();
    },
    isFormValid(){
      this.isFormInvalid = Object.values(this.fields).some(
          (field) => field?.error
      );
      return ! this.isFormInvalid ;
    },
    isFormInvalid: true,
    editClient({name, testimonial, logo, installation_year, images}) {
      clearFormErrors(this.fields)
      this.isFormValid()
      this.fields.name.value = name
      this.fields.testimonial.value = testimonial
      this.fields.logo = logo
      this.fields.installationYear.value = installation_year
      this.fields.images = images
    },
    async submit(e) {
      try {
        if(this.isFormInvalid) {
          return
        }
        Alpine.store('clients').isLoaded = false
        const formData = new FormData(e.target)
        const res = await axios.post('../api/clients/add_client.php', formData, config)
        if(!res.data?.id) {
          throw new Error('Uncaught error adding client')
        }
        Alpine.store('clients').addClient(res.data)
      } catch (error) {
        console.log(error?.response?.data ?? error)
      } finally {
        Alpine.store('clients').isLoaded = true
        e.target.reset()
      }
    },
    async submitEdit(e, id) {
      try {
        if(this.isFormInvalid) {
          return
        }
        Alpine.store('clients').isLoaded = false
        const formData = new FormData(e.target)
        formData.set('id', id)
        const res = await axios.post('../api/clients/update_client.php', formData, config)
        if(!res.data?.id) {
          throw new Error('Uncaught error adding client')
        }
        Alpine.store('clients').updateClient(id, res.data)
      } catch (error) {
        console.log(error?.response?.data ?? error)
      } finally {
        Alpine.store('clients').isLoaded = true
        e.target.reset()
      }
    }
  }
}

function shortenFileName(filename, size = 15) {
  let portions = filename.split('.')
  if (portions[0].length <= size) {
      return portions.join('.')
  }
  return [portions[0].slice(0, size) + '---' , portions.pop()].join('.');
}

function returnFileSize(number) {
  if (number < 1024) {
      return `${number} bytes`;
  } else if (number >= 1024 && number < 1048576) {
      return `${(number / 1024).toFixed(1)} KB`;
  } else if (number >= 1048576) {
      return `${(number / 1048576).toFixed(1)} MB`;
  }
}
function removeFileFromFileList(filename, inputId) {
  const dt = new DataTransfer()
  const input = document.getElementById(inputId)
  const { files } = input
  
  for (let i = 0; i < files.length; i++) {
    const file = files[i]
    if (filename !== file.name)
      dt.items.add(file) // here you exclude the file. thus removing it.
  }
  
  input.files = dt.files // Assign the updates list
}

async function deleteImage(image, id, clientName) {
  try {
    const res = await axios.delete('../api/clients/delete_image.php', {data: {filename: image, id, client: clientName}})
    if (!res.data) {
        throw new Error("unknown error occured deleting client image")
    }
    return true
  } catch (error) {
    console.error(error?.response?.data ?? error)
    return false
  }
}
async function deleteClient(id) {
  try {
    const res = await axios.delete('../api/clients/delete_client.php', {data: {id}})
    if (!res.data) {
        throw new Error("unknown error occured deleting client")
    }
    return true
  } catch (error) {
    console.error(error?.response?.data ?? error)
    return false
  }
}