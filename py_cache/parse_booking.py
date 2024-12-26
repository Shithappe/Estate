from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time
import logging
import re

# Настройка логирования
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(message)s')
logger = logging.getLogger()

# Настройка драйвера Chrome
chrome_options = Options()
chrome_options.add_argument("--start-maximized")  # Открыть браузер в полноэкранном режиме

# Открываем браузер
driver = webdriver.Chrome(options=chrome_options)

def closeBanner():
    # Ожидаем, пока элемент станет доступным
    close_button = WebDriverWait(driver, 10).until(
        EC.element_to_be_clickable((By.XPATH, '/html/body/div[25]/div/div/div/div[1]/div[1]/div/button'))
    )

    # Кликаем по кнопке
    close_button.click()
    logger.info("Баннер закрыт!")

try:
    # Переходим по ссылке
    driver.get("https://www.booking.com/searchresults.en-gb.html?ss=Bali%2C+Indonesia&ssne=Sidemen&ssne_untouched=Sidemen&label=gen173nr-1BCAEoggI46AdIM1gEaOkBiAEBmAEJuAEXyAEM2AEB6AEBiAIBqAIDuAKe86u7BsACAdICJGMxNjI1OWY1LTA4OWMtNGI3Yy1hMzdhLTBmYjU0ZjYxN2Q5ZtgCBeACAQ&sid=8477c19f566bbab252b6fab353369ac7&aid=304142&lang=en-gb&sb=1&src_elem=sb&src=searchresults&dest_id=835&dest_type=region&ac_position=0&ac_click_type=b&ac_langcode=en&ac_suggestion_list_length=5&search_selected=true&search_pageview_id=bcd19e535aa40280&ac_meta=GhBiY2QxOWU1MzVhYTQwMjgwIAAoATICZW46BGJhbGlAAEoAUAA%3D&checkin=2025-02-25&checkout=2025-02-26&group_adults=1&no_rooms=1&group_children=0")  # Замените на нужный URL

    time.sleep(2)  # Ждем загрузки страницы
    closeBanner()

    # Используем CSS-селектор для поиска всех элементов с атрибутом data-testid="title-link"
    elements = driver.find_elements(By.CSS_SELECTOR, '[data-testid="title-link"]')

    logger.info(f"Найдено {len(elements)} элементов.")

    for index, element in enumerate(elements):
        try:
            href = element.get_attribute('href')  # Получаем href

            if href:
                # Убираем query parameters из URL
                clean_url = href.split('?')[0]

                # Заменяем .*.html на .en-gb.html
                modified_url = re.sub(r'\.\w{2,3}\.html', '.en-gb.html', clean_url)

                logger.info(f"{index + 1}: {modified_url}")
        except Exception as e:
            logger.warning(f"Не удалось обработать элемент {index + 1}: {e}")

finally:
    # Закрываем браузер
    driver.quit()
