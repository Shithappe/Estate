import numbers
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
        print(err)

def main():
    connection, cursor = connect_to_db()

    cursor.execute("SELECT id FROM booking_data WHERE")
    arr_id = cursor.fetchall()

    for id in arr_id:
        # Get max_available_rooms 
        cursor.execute('''SELECT room_id, room_type, MAX(max_available) AS max_available
                            FROM rooms_id
                            WHERE booking_id = %s AND active = 1
                            GROUP BY room_id, room_type''', (id[0],))
        max_available = cursor.fetchall()

        cursor.execute('''SELECT MIN(price) AS min_price, MAX(price) AS max_price
                            FROM rooms_id
                            WHERE booking_id = %s AND active = 1''', (id[0],))
        price = cursor.fetchall()

        if price is not None:
            min_price, max_price = price[0]
            if min_price is not None and max_price is not None:
                cursor.execute(
                    "UPDATE booking_data SET min_price = %s, max_price = %s WHERE id = %s",
                    (int(min_price), int(max_price), id[0])
                )
                connection.commit()

        # Get available rooms
        cursor.execute('''SELECT room_id, room_type, available_rooms
                            FROM rooms_2_day
                            WHERE booking_id = %s 
                            AND DATE(checkin) = DATE(created_at) 
                            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)''', (id[0],))
        available_rooms = cursor.fetchall()

        # Available rooms sum
        available_rooms_sum = {}

        for room_id, room_type, count in available_rooms:
            key = room_id if room_id else room_type
            if key in available_rooms_sum:
                available_rooms_sum[key]['count_type'] += 1
                available_rooms_sum[key]['count'] += count
            else:
                available_rooms_sum[key] = {'count_type': 1, 'count': count}

        # Calculate average available rooms
        for key, data in available_rooms_sum.items():
            count = data['count']
            count_type = data['count_type']
            
            if count_type != 0:
                available_rooms_sum[key]['result'] = count / count_type
            else:
                available_rooms_sum[key]['result'] = 0

        # Busy rooms sum
        busy_rooms_sum = {}
        for room_id, room_type, max_count in max_available:
            key = room_id if room_id else room_type
            if key in available_rooms_sum:
                busy_rooms_sum[key] = max_count - available_rooms_sum[key]['result']
            else:
                busy_rooms_sum[key] = None

        # Calculate occupancy rate
        occupancy_rate = {}
        for room_id, room_type, max_count in max_available:
            key = room_id if room_id else room_type
            if max_count > 0 and key in busy_rooms_sum and busy_rooms_sum[key] is not None:
                occupancy_rate[key] = round((busy_rooms_sum[key] / max_count) * 100, 2)

        # Join data
        combined_data = {}

        for room_id, room_type, max_capacity in max_available:
            key = room_id if room_id else room_type
            combined_data[key] = {
                'max_available': max_capacity,
                'occupancy_rate': occupancy_rate.get(key, None)
            }

        sum_occupancy_rate = [0, 0]

        for key, data in combined_data.items():
            max_available = data['max_available']
            occupancy_rate = data['occupancy_rate']

            if isinstance(occupancy_rate, numbers.Number):
                sum_occupancy_rate[0] += occupancy_rate
                sum_occupancy_rate[1] += 1
            # print(id[0], room_id, room_type, occupancy_rate)
            cursor.execute("UPDATE rooms_id SET occupancy = %s WHERE booking_id = %s AND room_id = %s",
                           (occupancy_rate, id[0], key if isinstance(key, int) else None))
            connection.commit()

        if sum_occupancy_rate[1] > 0:
            sum_occupancy_rate[0] = round(sum_occupancy_rate[0] / sum_occupancy_rate[1], 2)
            cursor.execute("UPDATE booking_data SET occupancy = %s WHERE id = %s",
                           (sum_occupancy_rate[0], id[0]))
            connection.commit()

        print(id[0], sum_occupancy_rate[0])

    print('Done')

main()
