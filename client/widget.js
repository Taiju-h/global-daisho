(function(d,t) {
  var BASE_URL="https://app.chatwoot.com";
  var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
  g.src=BASE_URL+"/packs/js/sdk.js";
  g.async = true;
  s.parentNode.insertBefore(g,s);
  g.onload=function(){
    window.chatwootSDK.run({
      websiteToken: '1zb8y5x1FM88AWH3RfLmcHzT',
      baseUrl: BASE_URL
    });

    var script = document.currentScript || (function() {
      var scripts = document.getElementsByTagName('script');
      return scripts[scripts.length - 1];
    })();
    var scriptUrl = script.src;
    var baseUrl = scriptUrl.substring(0, scriptUrl.lastIndexOf('/'));

    var img = document.createElement('img');
    img.src = baseUrl + '/agent_bubble.jpg';
    img.style.position = 'fixed';
    img.style.bottom = '80px';
    img.style.right = '26px';
    img.style.width = '70px';
    img.style.height = '79px'; // Vertical oval
    img.style.borderRadius = '50%';
    img.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
    img.style.border = '2px solid white';
    img.style.objectFit = 'cover';
    img.style.zIndex = '2147483647';
    img.style.cursor = 'pointer';
    img.id = 'agent-bubble-icon';
    
    // Translations

    var translations = {
      'en': 'Feel free to ask anything',
      'ja': '何でもご質問ください',
      'zh': '随时提问',
      'ko': '무엇이든 물어보세요',
      'de': 'Fragen Sie uns einfach alles',
      'fr': 'N\'hésitez pas à nous demander',
      'es': 'No dude en preguntar',
      'pl': 'Proszę śmiało pytać',
      'nl': 'Stel gerust uw vragen'
    };

    // Detect language
    var lang = document.documentElement.lang || 'en';
    // Handle cases like 'en-US' -> 'en'
    if (lang.indexOf('-') !== -1) {
      lang = lang.split('-')[0];
    }
    var bubbleText = translations[lang] || translations['en'];

    // Create speech bubble
    var bubble = document.createElement('div');
    bubble.textContent = bubbleText;
    bubble.style.position = 'fixed';
    bubble.style.bottom = '95px'; // Adjusted for taller head
    bubble.style.right = '100px';
    bubble.style.backgroundColor = '#FFEB3B'; // Yellow
    bubble.style.color = '#333';
    bubble.style.padding = '10px 16px';
    bubble.style.borderRadius = '20px';
    bubble.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
    bubble.style.fontSize = '14px';
    bubble.style.fontWeight = '500';
    bubble.style.zIndex = '2147483647';
    bubble.style.whiteSpace = 'nowrap';
    bubble.style.opacity = '0';
    bubble.style.transform = 'translateX(10px)';
    bubble.style.transition = 'all 0.3s ease';
    bubble.style.pointerEvents = 'none';

    // Add arrow to bubble
    var arrow = document.createElement('div');
    arrow.style.position = 'absolute';
    arrow.style.right = '-6px';
    arrow.style.top = '50%';
    arrow.style.transform = 'translateY(-50%)';
    arrow.style.width = '0';
    arrow.style.height = '0';
    arrow.style.borderTop = '6px solid transparent';
    arrow.style.borderBottom = '6px solid transparent';
    arrow.style.borderLeft = '6px solid #FFEB3B';
    bubble.appendChild(arrow);

    // Hide default Chatwoot launcher
    var style = document.createElement('style');
    style.innerHTML = `
      .woot-widget-bubble, .woot--bubble-holder {
        display: none !important;
      }
    `;
    document.head.appendChild(style);

    // Function to toggle custom elements based on chat visibility
    function updateVisibility() {
      var widgetHolder = document.querySelector('.woot-widget-holder');
      // Check if widget is visible (Chatwoot usually toggles visibility or display)
      var isVisible = widgetHolder && 
                      widgetHolder.style.visibility !== 'hidden' && 
                      widgetHolder.style.display !== 'none' &&
                      !widgetHolder.classList.contains('woot--hide');

      if (isVisible) {
        img.style.display = 'none';
        bubble.style.display = 'none';
      } else {
        img.style.display = 'block';
        bubble.style.display = 'block';
      }
    }

    // Observe DOM for chat window changes
    var observer = new MutationObserver(function(mutations) {
      updateVisibility();
    });
    
    observer.observe(document.body, { 
      childList: true, 
      subtree: true, 
      attributes: true, 
      attributeFilter: ['style', 'class'] 
    });

    // Add click handler to toggle chat
    img.onclick = function() {
      if (window.$chatwoot) {
        window.$chatwoot.toggle();
        // Check shortly after toggle
        setTimeout(updateVisibility, 100);
      }
    };
    
    // Show bubble after a delay
    setTimeout(function() {
        bubble.style.opacity = '1';
        bubble.style.transform = 'translateX(0)';
        // Initial check
        updateVisibility();
    }, 1000);

    document.body.appendChild(img);
    document.body.appendChild(bubble);
  }
})(document,"script");