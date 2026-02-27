document.addEventListener('DOMContentLoaded', function () {
    const btn_register = document.getElementById('btn_register');
    const btn_connexion = document.getElementById('btn_connexion');
    const contenair_bottom = document.getElementById('contenair_bottom');
    const contenair_middle = document.getElementById('contenair_middle');
    const contenair_top = document.getElementById('contenair_top');
    const notification_btn = document.getElementById('notification_btn');
    const notification_modal = document.getElementById('notification_modal');
    const modal_details_cat = document.getElementById('modal_details_cat');
    const details_cat = document.getElementById('details_cat');
    const add_depense = document.getElementById('add_depense');
    const nouvelle_depense = document.getElementById('nouvelle_depense');
    const ajouter_colocation = document.getElementById('ajouter_colocation');
    const modal_ajout_colocation = document.getElementById('modal_ajout_colocation');
    const btn_submit_profile = document.getElementById('btn_submit_profile');
    const profile_img_container = document.getElementById('profile_img_container');
    const profile_upload = document.getElementById('profile_upload');
    const profile_display = document.getElementById('profile_display');
    const btn_edit_profile = document.getElementById('btn_edit_profile');
    const modal_details_membre = document.getElementById('modal_details_membre');
    const connection_depuis_creation = document.getElementById('connection_depuis_creation');
    const creation_depuis_connection = document.getElementById('creation_depuis_connection');

    if (connection_depuis_creation) {
        connection_depuis_creation.addEventListener('click', (e) => {
            e.preventDefault();
            contenair_middle.style.right = '-1000px'; // Slide out registration form
            contenair_bottom.style.right = '0';      // Bring back registration form
            contenair_bottom.style.zIndex = '25';     // Show login form
            contenair_middle.style.zIndex = '20';     // Show login form
        });
    }

    if (creation_depuis_connection) {
        creation_depuis_connection.addEventListener('click', (e) => {
            e.preventDefault();
            contenair_bottom.style.right = '-1000px'; // Slide out login form
            contenair_middle.style.right = '0';      // Bring back registration form
            contenair_middle.style.zIndex = '26';     // Ensure it's on top
            contenair_bottom.style.zIndex = '24';     // Show login form
        });
    }

    if (profile_img_container && profile_upload) {
        profile_img_container.addEventListener('click', () => {
            profile_upload.click();
        });

        profile_upload.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    if (profile_display) {
                        profile_display.src = event.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (btn_edit_profile) {
        btn_edit_profile.addEventListener('click', (e) => {
            e.preventDefault();
            const inputs = document.querySelectorAll('.champs input');
            inputs.forEach(input => {
                input.removeAttribute('readonly');
                input.style.border = '2px solid #BB060B';
            });
            if (btn_submit_profile) {
                btn_submit_profile.style.display = 'block';
            }
        });
    }

    if (ajouter_colocation && modal_ajout_colocation) {
        ajouter_colocation.addEventListener('click', () => {
            modal_ajout_colocation.style.display = 'flex';
        });
    }

    // Handle multiple member detail buttons
    const addRelationBtns = document.querySelectorAll('.ajouter_relation');
    addRelationBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (modal_details_membre) {
                modal_details_membre.style.display = 'flex';
            }
        });
    });




    if (btn_register) {
        btn_register.addEventListener('click', () => {
            contenair_top.style.right = '-1000px';
            contenair_middle.style.zIndex = '9';
        });
    }

    if (btn_connexion) {
        btn_connexion.addEventListener('click', () => {
            contenair_middle.style.right = '-1000px';
            contenair_top.style.right = '-1000px';
            contenair_bottom.style.zIndex = '20';
        });
    }

    if (notification_btn && notification_modal) {
        notification_btn.addEventListener('click', () => {
            if (notification_modal.style.display === 'block') {
                notification_modal.style.display = 'none';
            } else {
                notification_modal.style.display = 'block';
            }
        });
    }

    if (add_depense && nouvelle_depense) {
        add_depense.addEventListener('click', (e) => {
            e.preventDefault();
            nouvelle_depense.style.display = 'flex';
        });
    }

    // Handle multiple details buttons - only intercept if a modal exists on the page
    const detailsButtons = document.querySelectorAll('.details_cat');
    detailsButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const modal_details_cat = document.getElementById('modal_details_cat');
            if (modal_details_cat) {
                e.preventDefault();
                modal_details_cat.style.display = 'flex';
            }
            // Otherwise, let the <a href="..."> navigate normally to the collocation page
        });
    });

    // Handle colocation div clicks for redirection
    const colocationDivs = document.querySelectorAll('.colocation');
    colocationDivs.forEach(div => {
        div.addEventListener('click', (e) => {
            // Only redirect if the click wasn't on a button or link inside the div
            if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'A' && !e.target.closest('button') && !e.target.closest('a')) {
                const id = div.getAttribute('data-id');
                if (id) {
                    window.location.href = `/collocation/${id}`;
                }
            }
        });
    });

    // Close modals when clicking the close button
    const closeButtons = document.querySelectorAll('.btn_fermer');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const modal = btn.closest('.modal_ajout_colocation');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Chart.js - User Behavior Chart
    const ctx = document.getElementById('graph_comp');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['User 1', 'User 2', 'User 3'],
                datasets: [{
                    label: 'Dépenses par utilisateur',
                    data: [300, 450, 250],
                    backgroundColor: [
                        '#BB060B',
                        '#78080E',
                        '#ffc107'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%'
            }
        });
    }

    // Chart.js - Bar Chart (Not Circle)
    const ctx2 = document.getElementById('graph_not_circle');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['L', 'M', 'M', 'J', 'V', 'S', 'D'],
                datasets: [{
                    label: 'Activité de la semaine',
                    data: [12, 19, 3, 5, 2, 3, 10],
                    backgroundColor: '#BB060B',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
});




