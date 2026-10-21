function updateInputState(input, valid) {
    const stateClasses = [
        'border-emerald-500',
        'border-red-500',
        'focus:border-emerald-400',
        'focus:border-red-500',
        'focus:ring-emerald-400/10',
        'focus:ring-red-500/10'
    ];

    input.classList.remove(...stateClasses);

    // Keep the default appearance for empty or untouched fields.
    if (valid === null || !input.value.trim()) {
        return;
    }

    input.classList.add(
        valid ? 'border-emerald-500' : 'border-red-500',
        valid ? 'focus:border-emerald-400' : 'focus:border-red-500',
        valid ? 'focus:ring-emerald-400/10' : 'focus:ring-red-500/10'
    );
}

/**
 * Registration password strength requirements.
 */
function isStrongPassword(value) {
    return (
        value.length >= 8 &&
        value.length <= 16 &&
        /[A-Z]/.test(value) &&
        /[a-z]/.test(value) &&
        /[0-9]/.test(value) &&
        /[^A-Za-z0-9]/.test(value)
    );
}

/**
 * Validate an individual field.
 */
function validateInput(input, form) {
    const name = input.name;
    const value = input.value;

    // Keep empty fields neutral until they contain a value.
    if (!value.trim()) {
        updateInputState(input, null);
        return !input.required;
    }

    let valid;

    if (name === 'name') {
        valid = value.trim().length > 0;
    } else if (input.type === 'email') {
        valid = input.validity.valid;
    } else if (
        name === 'password_confirmation' ||
        input.hasAttribute('data-password-confirmation')
    ) {
        const password = form.querySelector('[name="password"]');
        valid = Boolean(password && value === password.value);
    } else if (
        input.type === 'password' &&
        input.hasAttribute('data-password-strength')
    ) {
        valid = isStrongPassword(value);
    } else {
        // Generic validation for other fields and form types.
        valid = input.validity.valid;
    }

    updateInputState(input, valid);

    return valid;
}

/**
 * Enable reusable validation for forms marked with data-validate.
 */
document.querySelectorAll('form[data-validate]').forEach((form) => {
    const fields = form.querySelectorAll(
        'input:not([type="hidden"]):not([type="submit"]):not([type="button"]):not([type="reset"]):not([type="checkbox"]):not([type="radio"]):not([type="file"]), select, textarea'
    );

    const password = form.querySelector('[name="password"]');
    const confirmation = form.querySelector(
        '[name="password_confirmation"]'
    );

    fields.forEach((input) => {
        // Validate while typing.
        input.addEventListener('input', function () {
            validateInput(this, form);

            // Recheck confirmation when the password changes.
            if (this === password && confirmation && confirmation.value) {
                validateInput(confirmation, form);
            }
        });

        // Validate after leaving a field.
        input.addEventListener('blur', function () {
            validateInput(this, form);
        });
    });

    // Recheck all fields when the form is submitted.
    form.addEventListener('submit', function () {
        fields.forEach((input) => {
            validateInput(input, form);
        });
    });
});