// assets/js/core/VMMScorm.js
import '@scss/main.scss';
import 'typeface-open-sans/files/open-sans-latin-400.woff2';
import 'typeface-open-sans/files/open-sans-latin-700.woff2';

import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.min.js';

import '@custom-uikit/custom-uikit.js';
import 'uikit';

class VMMScorm {
    constructor() {
        this.initialized = false;
        this.config = {
            debug: false,
            autoInit: true,
            dependencies: {
                dialog: false,
                fitvids: false
            }
        };

        this.modules = new Map();

        if (this.config.autoInit) {
            this.init();
        }
    }

    async init() {
        if (this.initialized) return;

        try {
            console.log('Initializing VMMScorm...');
            await this.loadModules();
            this.setupEventListeners();

            this.initialized = true;
            console.log('VMMScorm initialized successfully');
        } catch (error) {
            console.error('Failed to initialize VMMScorm:', error);
        }
    }

    async loadModules() {
        // Detect required modules
        this.detectRequiredModules();

        // Load required modules dynamically
        const modulePromises = [];

        if (this.config.dependencies.dialog) {
            modulePromises.push(
                import('@elementor/elements/Dialog')
                    .then(module => this.modules.set('dialog', new module.default()))
            );
        }

        if (this.config.dependencies.fitvids) {
            modulePromises.push(
                import('@elementor/elements/Fitvids')
                    .then(module => this.modules.set('fitvids', new module.default()))
            );
        }

        await Promise.all(modulePromises);
    }

    detectRequiredModules() {
        this.config.dependencies.dialog = !!document.querySelector('[data-dialog]');
        this.config.dependencies.fitvids = !!document.querySelector('video, iframe[src*="youtube"], iframe[src*="vimeo"]');
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            this.detectRequiredModules();
            this.loadModules();
        });
    }
}

export default new VMMScorm();
