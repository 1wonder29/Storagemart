(function () {
  const config = {
    position: 'bottom-right', // bottom-right | bottom-left | sidebar-right | sidebar-left | fullpage | inline
    page: 'widget',           // widget | full
    theme: 'dark',
    width: '420px',
    height: '600px'
  };

  const isInline = config.position === 'inline';

  // Container
  const container = document.createElement('div');
  container.id = 'dash-chat-container';

  if (isInline) {
    container.style.cssText = `
      width: ${config.width};
      max-width: 1200px;
      height: ${config.height};
      margin: 20px auto;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      background: #1a1a1a;
    `;
  } else {
    container.style.cssText = 'position: fixed; z-index: 9999;';
  }

  // Iframe (LOCAL)
  const iframe = document.createElement('iframe');

  let src = config.page === 'full'
    ? '/chat'
    : '/chat/widget';

  const params = [];
  if (config.theme) params.push(`theme=${encodeURIComponent(config.theme)}`);
  if (isInline) params.push('mode=inline');

  if (params.length) {
    src += '?' + params.join('&');
  }

  iframe.src = src;
  iframe.style.cssText = `
    border: none;
    width: 100%;
    height: 100%;
    background: transparent;
  `;

  // Positioning
  if (!isInline) {
    switch (config.position) {
      case 'fullpage':
        Object.assign(container.style, {
          top: 0,
          left: 0,
          width: '100%',
          height: '100%'
        });
        break;

      case 'sidebar-right':
        Object.assign(container.style, {
          top: 0,
          right: 0,
          width: '380px',
          height: '100vh',
          boxShadow: '-2px 0 10px rgba(0,0,0,0.2)'
        });
        break;

      case 'sidebar-left':
        Object.assign(container.style, {
          top: 0,
          left: 0,
          width: '380px',
          height: '100vh',
          boxShadow: '2px 0 10px rgba(0,0,0,0.2)'
        });
        break;

      default:
        Object.assign(container.style, {
          bottom: '20px',
          right: '20px',
          width: config.width,
          height: config.height,
          borderRadius: '16px',
          boxShadow: '0 10px 40px rgba(0,0,0,0.3)'
        });
    }

    // Mobile override
    if (window.innerWidth <= 768 && config.position !== 'fullpage') {
      Object.assign(container.style, {
        width: '100%',
        height: '100%',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        borderRadius: 0
      });
    }
  }

  container.appendChild(iframe);

  if (isInline) {
    document.currentScript.after(container);
  } else {
    document.body.appendChild(container);
  }
})();
