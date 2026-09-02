document.addEventListener('DOMContentLoaded', function() {
    var path = window.location.pathname;
    var filename = path.split('/').pop();
    
    // Determine product based on filename prefix
    var product = null;
    if (filename.startsWith('D_Chem_A')) {
        product = 'D_Chem_A';
    } else if (filename.startsWith('D_Chem_B')) {
        product = 'D_Chem_B';
    } else if (filename.startsWith('S_Chem_A')) {
        product = 'S_Chem_A';
    } else if (filename.startsWith('S_Chem_B')) {
        product = 'S_Chem_B';
    } else if (filename.startsWith('OP_Flow')) {
        product = 'OP_Flow';
    } else if (filename.startsWith('Backfill_X')) {
        product = 'Backfill_X';
    } else if (filename.startsWith('Deeper25')) {
        product = 'Deeper25';
    }
    
    if (!product) return;

    // Create Header
    var header = document.createElement('header');
    header.style.borderBottom = '2px solid #333';
    header.style.paddingBottom = '10px';
    header.style.marginBottom = '20px';
    header.style.display = 'flex';
    header.style.alignItems = 'center';
    header.style.justifyContent = 'space-between';

    var logoContainer = document.createElement('div');
    logoContainer.style.display = 'flex';
    logoContainer.style.alignItems = 'center';

    var logoLink = document.createElement('a');
    logoLink.href = 'index.html';
    logoLink.style.display = 'flex';
    logoLink.style.alignItems = 'center';

    var logo = document.createElement('img');
    logo.src = '../img/glogo.jpeg';
    logo.alt = 'Daisho Chemical R&D Logo';
    logo.style.height = '50px'; // Adjust size as needed
    logo.style.marginRight = '15px';


    var companyName = document.createElement('span');
    companyName.textContent = 'Daisho Chemical R&D';
    companyName.style.fontSize = '24px';
    companyName.style.fontWeight = 'bold';
    companyName.style.color = '#333';

    logoLink.appendChild(logo);
    logoContainer.appendChild(logoLink);
    logoContainer.appendChild(companyName);
    header.appendChild(logoContainer);

    // Add print styles
    var style = document.createElement('style');
    style.innerHTML = '@media print { .no-print { display: none !important; } }';
    document.head.appendChild(style);

    // Navigation
    var nav = document.createElement('nav');
    nav.className = 'no-print';
    nav.innerHTML = 'Language: ' +
        '<a href="' + product + '_SDS_ja.html">日本語</a> | ' +
        '<a href="' + product + '_SDS_en.html">English</a> | ' +
        '<a href="' + product + '_SDS_fr.html">Français</a> | ' +
        '<a href="' + product + '_SDS_pl.html">Polski</a> | ' +
        '<a href="index.html">Portal</a>';
    
    header.appendChild(nav);
    
    // Insert Header at the top
    document.body.insertBefore(header, document.body.firstChild);

    // Create Footer
    var footer = document.createElement('footer');
    footer.style.marginTop = '40px';
    footer.style.borderTop = '1px solid #ccc';
    footer.style.paddingTop = '10px';
    footer.style.textAlign = 'center';
    footer.style.color = '#666';
    footer.style.fontSize = '14px';
    footer.innerHTML = '&copy; Daisho Chemical R&D. All rights reserved.';

    document.body.appendChild(footer);
});
