/**
 * Validation utilities for questionnaire forms
 */

/**
 * Validate question answer based on question type and settings
 */
export function validateAnswer(question, answer) {
    const errors = [];

    // Required validation
    if (question.required) {
        if (answer === null || answer === undefined || answer === '') {
            errors.push('This field is required');
            return errors;
        }

        // For arrays (checkbox), check if not empty
        if (Array.isArray(answer) && answer.length === 0) {
            errors.push('Please select at least one option');
            return errors;
        }
    }

    // Skip further validation if answer is empty and not required
    if (!answer && !question.required) {
        return errors;
    }

    // Type-specific validation
    switch (question.type) {
        case 'text':
            validateTextAnswer(question, answer, errors);
            break;
        case 'textarea':
            validateTextAnswer(question, answer, errors);
            break;
        case 'number':
            validateNumberAnswer(question, answer, errors);
            break;
        case 'email':
            validateEmailAnswer(answer, errors);
            break;
        case 'date':
            validateDateAnswer(question, answer, errors);
            break;
        case 'checkbox':
            validateCheckboxAnswer(question, answer, errors);
            break;
        case 'radio':
        case 'select':
            validateChoiceAnswer(question, answer, errors);
            break;
    }

    return errors;
}

/**
 * Validate text/textarea answers
 */
function validateTextAnswer(question, answer, errors) {
    const settings = question.settings || {};

    if (settings.min_length && answer.length < settings.min_length) {
        errors.push(`Minimum length is ${settings.min_length} characters`);
    }

    if (settings.max_length && answer.length > settings.max_length) {
        errors.push(`Maximum length is ${settings.max_length} characters`);
    }

    if (settings.pattern) {
        const regex = new RegExp(settings.pattern);
        if (!regex.test(answer)) {
            errors.push(settings.pattern_message || 'Invalid format');
        }
    }
}

/**
 * Validate number answers
 */
function validateNumberAnswer(question, answer, errors) {
    const settings = question.settings || {};
    const numValue = parseFloat(answer);

    if (isNaN(numValue)) {
        errors.push('Please enter a valid number');
        return;
    }

    if (settings.min !== undefined && numValue < settings.min) {
        errors.push(`Minimum value is ${settings.min}`);
    }

    if (settings.max !== undefined && numValue > settings.max) {
        errors.push(`Maximum value is ${settings.max}`);
    }
}

/**
 * Validate email answers
 */
function validateEmailAnswer(answer, errors) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(answer)) {
        errors.push('Please enter a valid email address');
    }
}

/**
 * Validate date answers
 */
function validateDateAnswer(question, answer, errors) {
    const settings = question.settings || {};
    const dateValue = new Date(answer);

    if (isNaN(dateValue.getTime())) {
        errors.push('Please enter a valid date');
        return;
    }

    if (settings.min_date) {
        const minDate = new Date(settings.min_date);
        if (dateValue < minDate) {
            errors.push(`Date must be on or after ${formatDate(minDate)}`);
        }
    }

    if (settings.max_date) {
        const maxDate = new Date(settings.max_date);
        if (dateValue > maxDate) {
            errors.push(`Date must be on or before ${formatDate(maxDate)}`);
        }
    }
}

/**
 * Validate checkbox answers
 */
function validateCheckboxAnswer(question, answer, errors) {
    const settings = question.settings || {};

    if (!Array.isArray(answer)) {
        errors.push('Invalid selection');
        return;
    }

    if (settings.min_selections && answer.length < settings.min_selections) {
        errors.push(`Please select at least ${settings.min_selections} option(s)`);
    }

    if (settings.max_selections && answer.length > settings.max_selections) {
        errors.push(`Please select at most ${settings.max_selections} option(s)`);
    }

    // Validate all selected values are valid options
    const validOptions = question.options || [];
    const invalidSelections = answer.filter(val => !validOptions.includes(val));
    if (invalidSelections.length > 0) {
        errors.push('Invalid option selected');
    }
}

/**
 * Validate radio/select answers
 */
function validateChoiceAnswer(question, answer, errors) {
    const validOptions = question.options || [];
    if (!validOptions.includes(answer)) {
        errors.push('Please select a valid option');
    }
}

/**
 * Validate all answers in a form
 */
export function validateForm(questions, answers) {
    const formErrors = {};
    let isValid = true;

    questions.forEach(question => {
        const answer = answers[question.id];
        const errors = validateAnswer(question, answer);

        if (errors.length > 0) {
            formErrors[question.id] = errors[0]; // Show first error
            isValid = false;
        }
    });

    return {
        isValid,
        errors: formErrors,
    };
}

/**
 * Format date for display
 */
function formatDate(date) {
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}
