from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import logging
import re
import time

# Настройка логирования
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(message)s')
logger = logging.getLogger()

# Настройка драйвера Chrome
chrome_options = Options()
chrome_options.add_argument("--start-maximized")
chrome_options.add_experimental_option('prefs', {
    'intl.accept_languages': 'en,en_US'
})

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

def scroll_to_element(css_selector):
    # Прокручиваем страницу к элементу
    element = driver.find_element(By.CSS_SELECTOR, css_selector)
    driver.execute_script("arguments[0].scrollIntoView();", element)
    time.sleep(2)  # Ждем 2 секунды для загрузки новых элементов

def scroll_down():
    # Прокручиваем страницу вниз
    driver.execute_script("window.scrollTo(0, document.body.scrollHeight / 2);")
    time.sleep(2)  # Ждем 2 секунды для загрузки новых элементов

def click_element(css_selector):
    # Кликаем по элементу
    element = driver.find_element(By.CSS_SELECTOR, css_selector)
    element.click()
    time.sleep(2)  # Ждем 2 секунды для загрузки новых элементов

def collect_links():
        global previous_count
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

                    # Добавляем уникальные ссылки в множество
                    unique_links.add(modified_url)

                    logger.info(f"Ссылка {index + 1}: {modified_url}")
            except Exception as e:
                logger.warning(f"Не удалось обработать элемент {index + 1}: {e}")

        # Обновляем количество элементов
        previous_count = len(elements)


unique_links = set()
previous_count = 0


try:
    # Переходим по ссылке
    driver.get("https://www.booking.com/searchresults.html?ss=Bali%2C%20Indonesia&efdco=1&aid=304142&lang=en-us&sb=1&src_elem=sb&src=index&dest_id=835&dest_type=region&ac_position=1&ac_click_type=b&ac_langcode=en&ac_suggestion_list_length=5&search_selected=true&checkin=2025-02-27&checkout=2025-02-28&group_adults=1&no_rooms=1&group_children=0")

    time.sleep(2)  # Ждем загрузки страницы
    closeBanner()

    scroll_down()
    # Первый сбор ссылок
    collect_links()

    # Скроллинг к элементу и сбор новых ссылок
    scroll_to_element('button[type="button"] span:contains("Load more results")')

    # Проверяем количество элементов
    new_elements = driver.find_elements(By.CSS_SELECTOR, '[data-testid="title-link"]')
    if len(new_elements) != previous_count:
        collect_links()
    else:
        click_element('button[type="button"] span:contains("Load more results")')
        collect_links()

    # Вывод всех уникальных ссылок
    logger.info(f"Всего уникальных ссылок: {len(unique_links)}")
    for link in unique_links:
        logger.info(link)

finally:
    # Закрываем браузер
    driver.quit()
