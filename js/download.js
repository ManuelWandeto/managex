
async function downloadManagex(link, user) {
    let filename = null;
    const downloadLink = await new Promise((resolve, reject) => {
        const storage = new window.mega.Storage({
            email: user.username,
            password: user.password,
            userAgent: null
        })
        
        storage.once('ready', async () => {
            const file = window.mega.File.fromURL(link)
            await file.loadAttributes()
            filename = file.name
            const stream = await file.download()
            const chunks = [];

            stream.on('data', data => {
                if(!chunks.length) {
                    window.dispatchEvent(new CustomEvent('download-started'))
                }
                chunks.push(data)
            })
            stream.on('progress', info => {
                window.dispatchEvent(new CustomEvent('download-progress', {bubbles: true, detail: {
                    percentComplete: (info.bytesLoaded / info.bytesTotal) * 100,
                    loaded: bytesToMegabytes(info.bytesLoaded),
                    total: bytesToMegabytes(info.bytesTotal)
                }}))
            })
            stream.on('end', () => {
                const blob = new Blob(chunks, { type: 'application/vnd.microsoft.portable-executable' });
                resolve(URL.createObjectURL(blob))
            })
        })
        storage.once('error', error => {
            reject(error)
        })
    });
    const hyperlink = document.createElement('a');
    hyperlink.href = downloadLink;
    hyperlink.download = filename;
    hyperlink.click();
    URL.revokeObjectURL(downloadLink);
}

function bytesToMegabytes(bytes) {
    // 1 megabyte = 1024 * 1024 bytes
    const megabytes = bytes / (1024 * 1024);
    return megabytes;
}

async function updateDownloadStatus(downloadId, apiUrl = "api") {
    try {
        try {
            let url = `${apiUrl}/update_download_status.php`
            const formdata = new FormData()
            formdata.set('download_id', downloadId)
            const res = await axios.post(url, formdata)
            if(!res.data.id) {
                throw new Error('Uncaught error updating download status')
            }
            return res.data
        } catch (error) {
            console.error(error?.response?.data ?? error)
            throw error;
        }
    } catch (error) {
        
    }
}