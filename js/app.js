const navLinks = document.querySelectorAll('nav ul.navbar-nav li a')

navLinks.forEach(l => l.addEventListener('click', (e) => {
    if(l.getAttribute('href').includes('#')) {
        e.preventDefault();
        const section = document.querySelector(l.getAttribute('href'));
        section.scrollIntoView({behavior: 'smooth', block: 'start'})
    }
}))

const footerLinks = document.querySelectorAll('footer div.links ul li a')
footerLinks.forEach(l => l.addEventListener('click', (e) => {
    if(l.getAttribute('href').includes('#')) {
        e.preventDefault();
        const section = document.querySelector(l.getAttribute('href'));
        section.scrollIntoView({behavior: 'smooth', block: 'start'})
    }
}))

$(function () {
    $('[data-toggle="popover"]').popover()
  })

function formatCurrency(value, currency) {
    const locale = currency == 'KES' ? 'en-KE' : 'en-US'
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
    }).format(value);
}
<<<<<<< Updated upstream
async function retryPayment(url = "api/order_retry.php") {
    try {
=======
async function retryPayment(trackingId, type = 'order', apiUrl = 'api') {
    try {
        let url = `${apiUrl}/order_retry.php?tracking_id=${trackingId}`
        if(type !== 'order') {
            url += "&type=custom"
        }
>>>>>>> Stashed changes
        const res = await axios.get(url)
        if(!res.data.status || res.data.status != 200) {
            throw new Error('Uncaught error retrying order request')
        }
        return res.data
    } catch (error) {
        console.error(error?.response?.data ?? error)
        throw error;
    }
}
$.easing.easeInCubic = function (x, t, b, c, d) {
    return c * (t /= d) * t * t + b;
};