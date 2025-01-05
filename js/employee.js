/*!
   /* Start Bootstrap - SB Admin v7.0.7 (https://startbootstrap.com/template/sb-admin) */
   /* Copyright 2013-2023 Start Bootstrap */
   /* Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
    */
    // 
// Scripts
// 

//FOR LOGOUT CONFIRMATION
function confirmLogout(event) {
    event.preventDefault();
    if (confirm("Are you sure you want to log out?")) {
        window.location.href = "../../employee/staff/logout.php";
    }
}

window.addEventListener('DOMContentLoaded', event => {

    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        // Uncomment Below to persist sidebar toggle between refreshes
         if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
            document.body.classList.toggle('sb-sidenav-toggled');
         }
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

     document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.getElementById('sidenavAccordion').classList.toggle('collapsed');
        });

});









