document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-password-confirmation]').forEach((confirmationInput) => {
        const form = confirmationInput.closest('form');
        const passwordInput = form?.querySelector('[name="password"]');
        const feedback = document.getElementById(`${confirmationInput.name}-feedback`);

        if (!passwordInput || !feedback) {
            return;
        }

        const updateConfirmationState = () => {
            const hasConfirmation = confirmationInput.value.length > 0;
            const passwordsMatch = confirmationInput.value === passwordInput.value;
            const isMismatch = hasConfirmation && !passwordsMatch;

            feedback.textContent = isMismatch ? 'Passwords do not match.' : '';
            feedback.classList.toggle('hidden', !isMismatch);

            if (hasConfirmation) {
                confirmationInput.setAttribute('aria-invalid', String(isMismatch));
            } else {
                confirmationInput.removeAttribute('aria-invalid');
            }
        };

        confirmationInput.addEventListener('input', updateConfirmationState);
        passwordInput.addEventListener('input', updateConfirmationState);
        updateConfirmationState();
    });

    document.querySelectorAll('[data-password-strength-container]').forEach((container) => {
        const inputId = container.dataset.passwordTarget;
        const passwordInput = inputId ? document.getElementById(inputId) : null;

        if (!passwordInput) {
            return;
        }

        const bar = container.querySelector('[data-password-strength-bar]');
        const strengthText = container.querySelector('[data-password-strength-label]');
        const requirementText = container.querySelector('[data-password-strength-copy]');

        if (!bar || !strengthText || !requirementText) {
            return;
        }

        const updateStrengthState = () => {
            const password = passwordInput.value;

            if (!password) {
                container.classList.add('hidden');
                bar.style.width = '0%';
                bar.style.backgroundColor = 'transparent';
                strengthText.textContent = '—';
                strengthText.style.color = '#94a3b8';
                requirementText.textContent = 'At least 8 characters with letters and numbers';
                requirementText.style.color = '#94a3b8';
                return;
            }

            container.classList.remove('hidden');

            const checks = {
                length: password.length >= 8,
                upper: /[A-Z]/.test(password),
                lower: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password),
            };

            const score = Object.values(checks).filter(Boolean).length;

            let strength;

            if (score <= 1) {
                strength = { label: 'Bad', width: '25%', color: '#f43f5e' };
            } else if (score <= 2) {
                strength = { label: 'Fair', width: '40%', color: '#f59e0b' };
            } else if (score <= 3) {
                strength = { label: 'Good', width: '65%', color: '#84cc16' };
            } else {
                strength = { label: 'Excellent', width: '100%', color: '#22c55e' };
            }

            strengthText.textContent = strength.label;
            strengthText.style.color = strength.color;

            bar.style.width = strength.width;
            bar.style.backgroundColor = strength.color;

            const missing = [];

            if (!checks.length) missing.push('8+ characters');
            if (!checks.upper) missing.push('uppercase');
            if (!checks.lower) missing.push('lowercase');
            if (!checks.number) missing.push('number');
            if (!checks.special) missing.push('symbol');

            if (missing.length === 0) {
                requirementText.textContent = 'All requirements met';
                requirementText.style.color = '#4ade80';
            } else {
                requirementText.textContent = 'Needs: ' + missing.join(', ');
                requirementText.style.color = '#94a3b8';
            }
        };

        passwordInput.addEventListener('input', updateStrengthState);
        updateStrengthState();
    });
});
