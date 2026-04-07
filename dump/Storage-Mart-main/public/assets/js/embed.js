(function() {
  const script = document.currentScript;
  const config = {
    position: script.getAttribute('data-position') || 'bottom-right',
    page: script.getAttribute('data-page') || 'widget',
    theme: script.getAttribute('data-theme') || 'default',
    width: script.getAttribute('data-width') || null,
    height: script.getAttribute('data-height') || '600px'
  };

  const isInline = config.position === 'inline';

  const container = document.createElement('div');
  container.id = 'dash-chat-container';

  if (isInline) {
    const defaultWidth = config.width || '80%';
    container.style.cssText = `
      width: ${defaultWidth};
      max-width: 1200px;
      height: ${config.height};
      margin: 20px auto;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      position: relative;
      background: #1a1a1a;
    `;

    if (window.innerWidth <= 768) {
      container.style.width = '100%';
      container.style.height = '500px';
      container.style.margin = '0';
      container.style.borderRadius = '0';
    }
  } else {
    container.style.cssText = 'position: fixed; z-index: 9999;';
  }

  const iframe = document.createElement('iframe');
  let src = config.page === 'full'
    ? 'https://dash-board.top/'
    : 'https://dash-board.top/widget';

  const params = [];
  if (config.theme !== 'default') {
    params.push(`theme=${encodeURIComponent(config.theme)}`);
  }
  if (isInline) {
    params.push('mode=inline');
  }

  if (params.length > 0) {
    src += '?' + params.join('&');
  }

  iframe.src = src;
  iframe.setAttribute('allowtransparency', 'true');

  iframe.style.cssText = `
    border: none;
    background: transparent;
    width: 100%;
    height: 100%;
  `;

  if (!isInline) {
    if (config.position === 'fullpage') {
      container.style.top = '0';
      container.style.left = '0';
      container.style.width = '100%';
      container.style.height = '100%';
    } else if (config.position === 'sidebar-right') {
      container.style.right = '0';
      container.style.top = '0';
      container.style.width = '380px';
      container.style.height = '100vh';
      container.style.boxShadow = '-2px 0 10px rgba(0,0,0,0.2)';
    } else if (config.position === 'sidebar-left') {
      container.style.left = '0';
      container.style.top = '0';
      container.style.width = '380px';
      container.style.height = '100vh';
      container.style.boxShadow = '2px 0 10px rgba(0,0,0,0.2)';
    } else {
      container.style.bottom = '20px';
      container.style[config.position === 'bottom-left' ? 'left' : 'right'] = '20px';
      container.style.width = '420px';
      container.style.height = '600px';
      container.style.borderRadius = '16px';
      container.style.boxShadow = '0 10px 40px rgba(0,0,0,0.3)';
    }

    if (window.innerWidth <= 768 && config.position !== 'fullpage') {
      container.style.width = '100%';
      container.style.height = '100%';
      container.style.bottom = '0';
      container.style.right = '0';
      container.style.left = '0';
      container.style.top = '0';
      container.style.borderRadius = '0';
    }
  }

  container.appendChild(iframe);

  if (isInline) {
    script.parentNode.insertBefore(container, script.nextSibling);
  } else {
    document.body.appendChild(container);
  }
})();