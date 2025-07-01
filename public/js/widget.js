(function() {
    'use strict';

    // Default configuration
    const defaultConfig = {
        chatbotUrl: '',
        position: 'bottom-right',
        offset: '20px',
        buttonIcon: '',
        buttonText: 'Chat with us',
        buttonColor: '#0059e1',
        buttonTextColor: '#ffffff',
        height: '600px'
    };

    let config = { ...defaultConfig };
    let widgetContainer = null;
    let iframeContainer = null;
    let isOpen = false;

    function validateConfig(options) {
        if (!options || typeof options !== 'object') {
            throw new Error('Widget configuration must be an object');
        }

        if (!options.chatbotUrl) {
            throw new Error('chatbotUrl is required in widget configuration');
        }

        return {
            ...defaultConfig,
            ...options
        };
    }

    function createStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .iframeai-widget-container {
                position: fixed;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 1rem;
                font-family: system-ui, -apple-system, sans-serif;
            }
            .iframeai-widget-button {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1.5rem;
                border-radius: 9999px;
                cursor: pointer;
                transition: all 0.2s ease-in-out;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                margin-left: auto;
                border: none;
                outline: none;
            }
            .iframeai-widget-button > * {
                flex-shrink: 0;
            }
            .iframeai-widget-button > svg,
            .iframeai-widget-button > img {
                width: 30px;
            }
            .iframeai-widget-button:hover {
                opacity: 0.9;
                transform: translateY(-1px);
                box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.12), 0 3px 6px -1px rgba(0, 0, 0, 0.08);
            }
            .iframeai-widget-iframe-container {
                display: none;
                background: white;
                border-radius: 0.75rem;
                overflow: hidden;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                height: var(--height);
                width: 400px;
                max-width: calc(100vw - 2rem);
                max-height: calc(100vh - 2rem);
                transition: all 0.3s ease-in-out;
                opacity: 0;
                transform: translateY(20px);
            }
            .iframeai-widget-iframe-container.open {
                display: block;
                opacity: 1;
                transform: translateY(0);
            }
            .iframeai-widget-iframe {
                width: 100%;
                height: 100%;
                border: none;
            }
            .iframeai-widget-container[data-position="bottom-right"] {
                bottom: var(--offset);
                right: var(--offset);
                align-items: flex-end;
            }
            .iframeai-widget-container[data-position="bottom-left"] {
                bottom: var(--offset);
                left: var(--offset);
                align-items: flex-start;
            }
            .iframeai-widget-container[data-position="top-right"] {
                top: var(--offset);
                right: var(--offset);
                align-items: flex-end;
            }
            .iframeai-widget-container[data-position="top-left"] {
                top: var(--offset);
                left: var(--offset);
                align-items: flex-start;
            }
        `;
        document.head.appendChild(style);
    }

    function createWidget() {
        try {
            // Create widget container
            widgetContainer = document.createElement('div');
            widgetContainer.className = 'iframeai-widget-container';
            widgetContainer.style.setProperty('--offset', config.offset);
            widgetContainer.style.setProperty('--height', config.height);
            widgetContainer.setAttribute('data-position', config.position);

            // Create iframe container
            iframeContainer = document.createElement('div');
            iframeContainer.className = 'iframeai-widget-iframe-container';

            // Create iframe
            const iframe = document.createElement('iframe');
            iframe.className = 'iframeai-widget-iframe';
            iframe.src = config.chatbotUrl;
            iframeContainer.appendChild(iframe);

            // Create chat button
            const button = document.createElement('button');
            button.className = 'iframeai-widget-button';
            button.style.backgroundColor = config.buttonColor;
            button.style.color = config.buttonTextColor;
            button.innerHTML = `
                ${config.buttonIcon || ''}
                <span>${config.buttonText}</span>
            `;

            // Add click handler
            button.addEventListener('click', toggleWidget);

            // Append elements
            widgetContainer.appendChild(iframeContainer);
            widgetContainer.appendChild(button);
            document.body.appendChild(widgetContainer);
        } catch (error) {
            console.error('Failed to create widget:', error);
            throw error;
        }
    }

    function toggleWidget() {
        isOpen = !isOpen;
        if (isOpen) {
            iframeContainer.style.display = 'block';
            // Force reflow
            iframeContainer.offsetHeight;
            iframeContainer.classList.add('open');
        } else {
            iframeContainer.classList.remove('open');
            // Wait for animation to complete before hiding
            setTimeout(() => {
                if (!isOpen) {
                    iframeContainer.style.display = 'none';
                }
            }, 300);
        }
    }

    function init(options) {
        try {
            // Validate and merge configuration
            config = validateConfig(options);

            // Create and inject styles
            createStyles();

            // Create and inject widget
            createWidget();
        } catch (error) {
            console.error('Widget initialization failed:', error);
            throw error;
        }
    }

    // Export to window
    window.iframeAI = function() {
        try {
            const args = Array.prototype.slice.call(arguments);
            const method = args[0];
            const params = args.slice(1);

            switch (method) {
                case 'init':
                    init.apply(null, params);
                    break;
                default:
                    throw new Error(`Unknown method: ${method}`);
            }
        } catch (error) {
            console.error('iframeAI error:', error);
            throw error;
        }
    };
})(); 