// main.js - Application UI Interactivity & Helpers
 
document.addEventListener('DOMContentLoaded', () => {
    // Service Tabs Switcher (Ride vs Parcel Delivery)
    const tabBtns = document.querySelectorAll('.service-toggle-tabs .tab-btn');
    const rideForm = document.getElementById('form-ride');
    const deliveryForm = document.getElementById('form-delivery');
 
    if (tabBtns.length > 0) {
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                tabBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
 
                const target = btn.dataset.service;
                if (target === 'ride') {
                    if (rideForm) rideForm.style.display = 'block';
                    if (deliveryForm) deliveryForm.style.display = 'none';
                } else if (target === 'delivery') {
                    if (rideForm) rideForm.style.display = 'none';
                    if (deliveryForm) deliveryForm.style.display = 'block';
                }
            });
        });
    }
 
    // Tier selection card logic
    const tierCards = document.querySelectorAll('.tier-card');
    tierCards.forEach(card => {
        card.addEventListener('click', () => {
            const parent = card.closest('.tier-grid');
            if (parent) {
                parent.querySelectorAll('.tier-card').forEach(c => c.classList.remove('selected'));
            }
            card.classList.add('selected');
            const hiddenInput = document.getElementById('selected_tier');
            if (hiddenInput) {
                hiddenInput.value = card.dataset.tier;
            }
        });
    });
});
 
// Toast notification helper
function showToast(message, type = 'info') {
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;';
        document.body.appendChild(toastContainer);
    }
 
    const toast = document.createElement('div');
    toast.style.cssText = `
        background: #1C1C21;
        color: #fff;
        padding: 14px 20px;
        border-radius: 10px;
        border-left: 5px solid ${type === 'success' ? '#2ECC71' : type === 'error' ? '#E74C3C' : '#FFCC00'};
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 500;
        min-width: 280px;
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.3s ease;
    `;
    toast.innerHTML = message;
    toastContainer.appendChild(toast);
 
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);
 
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
 
