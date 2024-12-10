/**
 * Преобразует строку и возвращает строку с точками через 3 символа.
 * @param {string} str - Строка для преобразования.
 * @returns {string} - Строка с точками.
 */

export function addDots (str) {
    str = String(str)
    // Преобразуем строку в массив символов и перевернем его
    let reversed = str.split('').reverse().join('');

    // Используем регулярное выражение для добавления точек через каждые три символа
    let withDots = reversed.replace(/(\d{3})/g, '$1.');

    // Удалим последнюю точку, если она есть, и перевернем строку обратно
    withDots = withDots.split('').reverse().join('');
    if (withDots.startsWith('.')) withDots = withDots.slice(1);

    return withDots;
}
