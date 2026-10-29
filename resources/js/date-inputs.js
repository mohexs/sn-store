const datePattern = /^(\d{2})\/(\d{2})\/(\d{4})$/;
const dateTimePattern = /^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/;

function isValidDate(year, month, day, hour = 0, minute = 0) {
    const date = new Date(Date.UTC(year, month - 1, day, hour, minute));

    return date.getUTCFullYear() === year
        && date.getUTCMonth() === month - 1
        && date.getUTCDate() === day
        && date.getUTCHours() === hour
        && date.getUTCMinutes() === minute;
}

function toIso(value, type) {
    const match = value.match(type === 'date' ? datePattern : dateTimePattern);

    if (!match) {
        return '';
    }

    const [, day, month, year, hour = '00', minute = '00'] = match;

    if (!isValidDate(Number(year), Number(month), Number(day), Number(hour), Number(minute))) {
        return '';
    }

    const date = `${year}-${month}-${day}`;

    return type === 'date' ? date : `${date}T${hour}:${minute}`;
}

function toDisplay(value, type) {
    const pattern = type === 'date'
        ? /^(\d{4})-(\d{2})-(\d{2})$/
        : /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})$/;
    const match = value.match(pattern);

    if (!match) {
        return '';
    }

    const [, year, month, day, hour = '', minute = ''] = match;

    if (!isValidDate(Number(year), Number(month), Number(day), Number(hour || 0), Number(minute || 0))) {
        return '';
    }

    return type === 'date' ? `${day}/${month}/${year}` : `${day}/${month}/${year} ${hour}:${minute}`;
}

export default function initializeDateInputs() {
    document.querySelectorAll('[data-date-control]').forEach(control => {
        const { dateType } = control.dataset;
        const display = control.querySelector('[data-date-display]');
        const value = control.querySelector('[data-date-value]');
        const picker = control.querySelector('[data-date-picker]');
        const pickerButton = control.querySelector('[data-date-picker-button]');

        const syncValue = () => {
            value.value = toIso(display.value.trim(), dateType);
            picker.value = value.value;
            display.setCustomValidity(display.value.trim() !== '' && value.value === ''
                ? 'Gunakan format dd/mm/yyyy yang valid.'
                : '');
        };

        display.addEventListener('input', syncValue);
        display.addEventListener('blur', syncValue);
        picker.addEventListener('change', () => {
            display.value = toDisplay(picker.value, dateType);
            syncValue();
        });
        pickerButton.addEventListener('click', () => {
            if (typeof picker.showPicker === 'function') {
                picker.showPicker();
            } else {
                picker.focus();
                picker.click();
            }
        });
        display.form?.addEventListener('submit', () => {
            syncValue();
            display.value = value.value;
        });

        syncValue();
    });
}
