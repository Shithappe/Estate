from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import logging
import re
import time
import mysql.connector
from mysql.connector import Error
from dotenv import load_dotenv
import os

# Загрузка переменных окружения из файла .env
load_dotenv()

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

# Подключение к MySQL
def connect_to_mysql():
    try:
        connection = mysql.connector.connect(
            host=os.getenv('DB_HOST'),
            user=os.getenv('DB_USERNAME'),
            password=os.getenv('DB_PASSWORD'),
            database=os.getenv('DB_DATABASE')
        )
        if connection.is_connected():
            logger.info("Successfully connected to the database")
            return connection
    except Error as e:
        logger.error(f"Error while connecting to MySQL: {e}")
        return None

def closeBanner():
    # Ожидаем, пока элемент станет доступным
    close_button = WebDriverWait(driver, 10).until(
        EC.element_to_be_clickable((By.XPATH, '/html/body/div[25]/div/div/div/div[1]/div[1]/div/button'))
    )

    # Кликаем по кнопке
    close_button.click()
    logger.info("Баннер закрыт!")

def scroll_down():
    # Прокручиваем страницу вниз на половину высоты страницы
    driver.execute_script("window.scrollBy(0, window.innerHeight / 2);")

def scroll_to_element(xpath):
    # Прокручиваем страницу к элементу
    element = driver.find_element(By.XPATH, xpath)
    driver.execute_script("arguments[0].scrollIntoView();", element)

def click_element(xpath):
    # Кликаем по элементу
    element = driver.find_element(By.XPATH, xpath)
    element.click()
    time.sleep(2)  # Ждем 2 секунды для загрузки новых элементов

def collect_links(connection):
    # Используем CSS-селектор для поиска всех элементов с атрибутом data-testid="title-link"
    elements = driver.find_elements(By.CSS_SELECTOR, '[data-testid="title-link"]')

    logger.info(f"Найдено {len(elements)} элементов.")

    cursor = connection.cursor()
    new_links = []

    for index, element in enumerate(elements):
        try:
            href = element.get_attribute('href')  # Получаем href

            if href:
                # Убираем query parameters из URL
                clean_url = href.split('?')[0]

                # Заменяем .*.html на .en-gb.html
                # modified_url = re.sub(r'\.\w{2,3}\.html', '.en-gb.html', clean_url)
                modified_url = re.sub(
                    r'(?<!\.en-gb)(\.\w+)?\.html$',  # Находим либо .xx.html, либо просто .html, исключая уже .en-gb.html
                    lambda match: '.en-gb.html' if match.group(1) else '.en-gb.html',
                    clean_url
                )

                # Добавляем уникальные ссылки в множество
                if modified_url not in unique_links:
                    unique_links.add(modified_url)
                    new_links.append(modified_url)
                    cursor.execute("INSERT INTO booking_data (link) VALUES (%s)", (modified_url,))
                    connection.commit()
                    logger.info(f"Ссылка {index + 1}: {modified_url}")
        except Exception as e:
            logger.warning(f"Не удалось обработать элемент {index + 1}: {e}")

    
    cursor.close()

    return len(elements), new_links

def is_load_more_button_visible():
    try:
        # Проверяем, видима ли кнопка "Load more results"
        load_more_button = driver.find_element(By.XPATH, '//button[contains(.,"Load more results")]')
        return True
    except:
        return False

unique_links = set()
previous_count = 0

try:
    # Подключение к MySQL
    connection = connect_to_mysql()
    if connection is None:
        raise Exception("Failed to connect to the database")

    # Переходим по ссылке
    driver.get("https://www.booking.com/searchresults.en-gb.html?ss=Sidemen&ssne=Sidemen&ssne_untouched=Sidemen&highlighted_hotels=2005189&efdco=1&lang=en-gb&sb=1&src_elem=sb&src=searchresults&dest_id=-2696826&dest_type=city&checkin=2025-02-27&checkout=2025-02-28&group_adults=1&no_rooms=1&group_children=0")

    time.sleep(2)  # Ждем загрузки страницы
    closeBanner()

    # Первый сбор ссылок
    previous_count, _ = collect_links(connection)

    while True:
        scroll_down()
        new_count, new_links = collect_links(connection)

        if new_count > previous_count:
            # Если появились новые ссылки, обновляем количество и продолжаем
            previous_count = new_count
        else:
            # Если новые ссылки не появились, проверяем наличие кнопки "Load more results"
            if is_load_more_button_visible():
                # Скроллим к кнопке и кликаем по ней
                scroll_to_element('//button[contains(.,"Load more results")]')
                click_element('//button[contains(.,"Load more results")]')
                previous_count, _ = collect_links(connection)  # Обновляем количество элементов после клика

        # Проверяем, достигли ли конца страницы
        if driver.execute_script("return window.innerHeight + window.scrollY") >= driver.execute_script("return document.body.scrollHeight"):
            break

    # Вывод всех уникальных ссылок
    logger.info(f"Всего уникальных ссылок: {len(unique_links)}")
    for link in unique_links:
        logger.info(link)

finally:
    # Закрываем браузер
    driver.quit()
    # Закрываем соединение с MySQL
    if connection.is_connected():
        connection.close()
