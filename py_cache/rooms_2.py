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
        cursor = connection.cursor()
        return connection, cursor
    except mysql.connector.Error as err:
        print(f"Error: {err}")
        return None, None
    
def occupancy_calc(connection, cursor, booking_id):
    print(booking_id)

    query = '''
        SELECT 
            r2d.room_id, 
            SUM(r2d.available_rooms) AS sum, 
            COUNT(r2d.available_rooms) AS count,
            MAX(ri.max_available) AS max_available
        FROM 
            rooms_2_day r2d
        JOIN 
            rooms_id ri ON r2d.room_id = ri.room_id 
        WHERE 
            r2d.booking_id = %s
            AND r2d.created_at >= '2024-07-28'
        GROUP BY 
            r2d.room_id;
    '''
    cursor.execute(query, (booking_id,))
    data = cursor.fetchall()

    total_result = 0
    record_count = 0

    for row in data:
        room_id, sum_value, count_value, max_available = row

        if count_value != 0 and max_available > 0:
            occupancy = round(((max_available - (sum_value / count_value)) / max_available) * 100, 2)
            print(f"{room_id} - {occupancy:.2f}%")

            total_result += occupancy
            record_count += 1

    if record_count > 0:
        average_result = total_result / record_count
        cursor.execute('''UPDATE booking_data SET occupancy = %s WHERE id = %s''', (average_result, booking_id))
        connection.commit()
        print(f"{average_result:.2f}%")


def main():
    connection, cursor = connect_to_db()

    cursor.execute('''SELECT id FROM booking_data;''')
    booking_ids = cursor.fetchall()

    for booking_id in booking_ids:
        occupancy_calc(connection, cursor, booking_id[0])

    cursor.close()
    connection.close()

    print('\nDone')

main()
