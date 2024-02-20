const navLinks = document.querySelectorAll('nav ul.navbar-nav li a')

navLinks.forEach(l => l.addEventListener('click', (e) => {
    e.preventDefault();
    const section = document.querySelector(l.getAttribute('href'));
    section.scrollIntoView({behavior: 'smooth', block: 'start'})
}))

const footerLinks = document.querySelectorAll('footer div.links ul li a')
footerLinks.forEach(l => l.addEventListener('click', (e) => {
    e.preventDefault();
    const section = document.querySelector(l.getAttribute('href'));
    section.scrollIntoView({behavior: 'smooth', block: 'start'})
}))

$(function () {
    $('[data-toggle="popover"]').popover()
  })