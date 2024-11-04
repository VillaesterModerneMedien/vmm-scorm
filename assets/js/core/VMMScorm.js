// assets/js/core/VMMScorm.js
class VMMScorm {
    constructor() {
        this.initialized = false;
        this.config = {
            debug: false,
            autoInit: true,
            dependencies: {
                dialog: false,
                elcourse: false,
                fitvids: false,
                snc: false
            }
        };

        this.modules = new Map();
        this.state = {
            isReady: false,
            currentPage: null,
            sessionData: this.initLocalSession()
        };

        if (this.config.autoInit) {
            this.init();
        }
    }

    async init() {
        if (this.initialized) return;

        try {
            console.log('Initializing VMMScorm...');

            // Initialize core functionality
            await this.initCore();

            // Load required modules
            await this.loadModules();

            // Set up event listeners
            this.setupEventListeners();

            this.initialized = true;
            this.state.isReady = true;

            // Trigger ready event
            window.dispatchEvent(new CustomEvent('vmmscorm:ready'));

            console.log('VMMScorm initialized successfully');
        } catch (error) {
            console.error('Failed to initialize VMMScorm:', error);
            throw error;
        }
    }

    // Utility functions to replace jQuery selectors
    $(selector, context = document) {
        return context.querySelector(selector);
    }

    $$(selector, context = document) {
        return Array.from(context.querySelectorAll(selector));
    }

    // Event delegation helper
    delegate(element, eventType, selector, handler) {
        element.addEventListener(eventType, event => {
            const target = event.target.closest(selector);
            if (target && element.contains(target)) {
                handler.call(target, event);
            }
        });
    }

    // DOM Ready replacement for jQuery's $(document).ready()
    onReady(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    async initCore() {
        // Load bundled templates
        await this.loadBundledTemplates();

        // Initialize page tracking
        this.initPageTracking();

        // Initialize templates if needed
        if (this.$('[data-template]')) {
            await this.initTemplates();
        }
    }

    async loadModules() {
        this.detectRequiredModules();

        const moduleLoaders = {
            dialog: () => import('./elements/Dialog'),
            elcourse: () => import('./elements/Elcourse'),
            fitvids: () => import('./elements/Fitvids'),
            snc: () => import('./elements/Snc')
        };

        const loadPromises = Object.entries(this.config.dependencies)
            .filter(([_, isRequired]) => isRequired)
            .map(([name]) => this.loadModule(name, moduleLoaders[name]));

        await Promise.all(loadPromises);
    }

    detectRequiredModules() {
        this.config.dependencies = {
            dialog: !!this.$('[data-dialog]'),
            elcourse: !!this.$('[data-elcourse]'),
            fitvids: !!this.$('video, iframe[src*="youtube"], iframe[src*="vimeo"]'),
            snc: !!this.$('[data-snc]')
        };
    }

    setupEventListeners() {
        // Example of event delegation
        this.delegate(document, 'click', '[data-action]', event => {
            const action = event.target.dataset.action;
            if (typeof this[action] === 'function') {
                this[action](event);
            }
        });

        // Custom event handling
        window.addEventListener('vmmscorm:templateLoad', event => {
            this.detectRequiredModules();
            this.loadModules();
        });
    }

    // AJAX replacement using fetch
    async fetchData(url, options = {}) {
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Fetch error:', error);
            throw error;
        }
    }

    // Animation helper (replacing jQuery animations)
    animate(element, keyframes, options) {
        return element.animate(keyframes, {
            duration: 300, // Default duration
            easing: 'ease', // Default easing
            ...options
        }).finished;
    }

    // DOM manipulation helpers
    addClass(element, className) {
        element.classList.add(className);
    }

    removeClass(element, className) {
        element.classList.remove(className);
    }

    toggleClass(element, className) {
        element.classList.toggle(className);
    }

    // Storage helpers
    setLocalData(key, value) {
        try {
            localStorage.setItem(`vmmscorm_${key}`, JSON.stringify(value));
        } catch (e) {
            console.error('Storage error:', e);
        }
    }

    getLocalData(key) {
        try {
            const item = localStorage.getItem(`vmmscorm_${key}`);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            console.error('Storage error:', e);
            return null;
        }
    }
}

export default new VMMScorm();
