// assets/js/elementor/elements/Dialog.js
export default class Dialog {
    constructor() {
        this.initDialogs();
    }
    initDialogs() {
        const dialogs = document.querySelectorAll('[data-dialog]');

        dialogs.forEach(dialog => {
            const trigger = dialog.querySelector('[data-dialog-trigger]');
            const close = dialog.querySelector('[data-dialog-close]');

            if (trigger) {
                trigger.addEventListener('click', () => this.openDialog(dialog));
            }

            if (close) {
                close.addEventListener('click', () => this.closeDialog(dialog));
            }
        });
    }

    openDialog(dialog) {
        dialog.classList.add('active');
    }

    closeDialog(dialog) {
        dialog.classList.remove('active');
    }
}
