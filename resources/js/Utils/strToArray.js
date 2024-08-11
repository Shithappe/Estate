/**
 * Преобразует строку, заменяет разрешение на указанное и возвращает массив строк.
 * @param {string} str - Строка для преобразования.
 * @param {number} resol - Разрешение, на которое нужно заменить.
 * @returns {Array<string>} - Массив строк.
 */

export function strToArray(str, resol) {
    return str.replace(/max\d+/g, `max${resol}`).split(',');
}
