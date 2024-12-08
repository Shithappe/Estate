import mysql.connector

def connect_to_db():
    config = {
        'user': 'artnmo_estate',
        'password': 'gL8+8uBs2_',
        'host': 'artnmo.mysql.tools',
        'database': 'artnmo_estate',
        'raise_on_warnings': True
    }
    
    try:
        connection = mysql.connector.connect(**config)
        cursor = connection.cursor(dictionary=True)
        return connection, cursor
    except mysql.connector.Error as err:
        print(f"Error: {err}")
        return None, None
    
def occupancy_calc(connection, cursor, booking_id):
    print(booking_id)

    # query = '''
    #     SELECT 
    #         r2d.room_id, 
    #         SUM(r2d.available_rooms) AS sum, 
    #         COUNT(r2d.available_rooms) AS count,
    #         MAX(ri.max_available) AS max_available
    #     FROM 
    #         rooms_2_day r2d
    #     JOIN 
    #         rooms_id ri ON r2d.room_id = ri.room_id 
    #     WHERE 
    #         r2d.booking_id = %s
    #         AND r2d.checkin >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    #     GROUP BY 
    #         r2d.room_id;
    # '''
    query = '''
        SELECT 
            r2d.room_id, 
            SUM(r2d.available_rooms) AS sum, 
            COUNT(r2d.available_rooms) AS count,
            MAX(ri.max_available) AS max_available,
            global_prices.min_price, 
            global_prices.max_price
        FROM 
            rooms_2_day r2d
        JOIN 
            rooms_id ri 
            ON r2d.room_id = ri.room_id
        JOIN (
            SELECT 
                MIN(r2d.price) AS min_price, 
                MAX(r2d.price) AS max_price
            FROM 
                rooms_2_day r2d
            WHERE 
                r2d.booking_id = %s
                AND r2d.checkin >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
        ) AS global_prices ON 1 = 1
        WHERE 
            r2d.booking_id = %s
            AND r2d.checkin >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
        GROUP BY 
            r2d.room_id;
    '''
    cursor.execute(query, (booking_id, booking_id))
    data = cursor.fetchall()

    total_result = 0
    record_count = 0
    update_data = []
    
    min_price = max_price = None
    if data:
        min_price = data[0]['min_price']
        max_price = data[0]['max_price']
    

    for row in data:
        if row['count'] != 0 and row['max_available'] > 0 and row['sum'] / row['count'] < row['max_available']:
            occupancy = ((row['max_available'] - (row['sum'] / row['count'])) / row['max_available']) * 100
            print(f"{row['room_id']} - {occupancy:.2f}%")

            total_result += occupancy
            record_count += 1
            update_data.append((occupancy, row['room_id']))

    if record_count > 0:
        if update_data:
            try:
                update_query = """
                    UPDATE rooms_id
                    SET occupancy = %s
                    WHERE room_id = %s
                """
                cursor.executemany(update_query, update_data)
                connection.commit()
                print(f"Обновлено {cursor.rowcount} записей")
            except mysql.connector.Error as err:
                connection.rollback()
                print(f"Ошибка: {err}")

        average_result = total_result / record_count
        cursor.execute('''
                UPDATE booking_data
                SET occupancy = %s, min_price = %s, max_price = %s
                WHERE id = %s
                ''', (average_result, min_price, max_price, booking_id))
        connection.commit()
        print(f"{average_result:.2f}%\n")


def main():
    connection, cursor = connect_to_db()

    cursor.execute('''SELECT id FROM booking_data;''')
    booking_ids = cursor.fetchall()

    for booking_id in booking_ids:
        occupancy_calc(connection, cursor, booking_id['id'])

    cursor.close()
    connection.close()

    print('Done')

main()
