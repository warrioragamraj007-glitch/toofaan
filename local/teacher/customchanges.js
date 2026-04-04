// Wait for the DOM to be ready
document.addEventListener('DOMContentLoaded', function () {
    // Find the "Home" node
    var homeNode = document.querySelector('.nav-item[data-key="home"]');

    // Remove the "Home" node if found
    if (homeNode) {
        homeNode.parentNode.removeChild(homeNode);
    }
   
   
});