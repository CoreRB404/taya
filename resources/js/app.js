

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();

const bulkResetForm = document.getElementById('bulk-reset-form');

if (bulkResetForm) {
    const selectAllCheckbox = document.querySelector('[data-select-all-users]');
    const userCheckboxes = Array.from(document.querySelectorAll('[data-user-checkbox]'));
    const selectedUserInputs = document.getElementById('bulk-reset-user-inputs');
    const selectedUserCount = document.getElementById('bulk-reset-count');
    const bulkResetButton = document.getElementById('bulk-reset-button');

    const updateBulkSelection = () => {
        const selected = userCheckboxes.filter((checkbox) => checkbox.checked);

        selectedUserInputs.replaceChildren(...selected.map((checkbox) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = checkbox.value;
            return input;
        }));

        selectedUserCount.textContent = String(selected.length);
        bulkResetButton.disabled = selected.length === 0;

        if (selectAllCheckbox) {
            selectAllCheckbox.checked = userCheckboxes.length > 0 && selected.length === userCheckboxes.length;
            selectAllCheckbox.indeterminate = selected.length > 0 && selected.length < userCheckboxes.length;
        }
    };

    selectAllCheckbox?.addEventListener('change', () => {
        userCheckboxes.forEach((checkbox) => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateBulkSelection();
    });

    userCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateBulkSelection));

    bulkResetForm.addEventListener('submit', (event) => {
        const count = userCheckboxes.filter((checkbox) => checkbox.checked).length;
        if (count === 0 || !window.confirm(`Send password reset links to ${count} selected users?`)) {
            event.preventDefault();
        }
    });

    updateBulkSelection();
}

const passwordDialog = document.getElementById('change-password-dialog');

if (passwordDialog) {
    const passwordForm = document.getElementById('change-password-form');
    const userIdInput = document.getElementById('change-password-user-id');
    const userNameInput = document.getElementById('change-password-user-name-input');
    const userNameText = document.getElementById('change-password-user-name');
    const currentPasswordInput = document.getElementById('current_password');

    const showPasswordDialog = () => {
        if (typeof passwordDialog.showModal === 'function') {
            if (!passwordDialog.open) passwordDialog.showModal();
        } else {
            passwordDialog.setAttribute('open', '');
        }
    };

    document.querySelectorAll('[data-change-password]').forEach((button) => {
        button.addEventListener('click', () => {
            passwordForm.action = button.dataset.action;
            userIdInput.value = button.dataset.userId;
            userNameInput.value = button.dataset.userName;
            userNameText.textContent = button.dataset.userName;
            passwordForm.reset();
            userIdInput.value = button.dataset.userId;
            userNameInput.value = button.dataset.userName;
            showPasswordDialog();
            currentPasswordInput.focus();
        });
    });

    document.querySelector('[data-close-change-password]')?.addEventListener('click', () => {
        if (typeof passwordDialog.close === 'function') passwordDialog.close();
        else passwordDialog.removeAttribute('open');
    });

    passwordDialog.addEventListener('click', (event) => {
        if (event.target === passwordDialog && typeof passwordDialog.close === 'function') {
            passwordDialog.close();
        }
    });

    if (passwordDialog.dataset.openOnLoad === 'true') {
        showPasswordDialog();
    }
}
