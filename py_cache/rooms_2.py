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
        cnx = mysql.connector.connect(**config)
        return cnx
    except mysql.connector.Error as err:
        print(err)

def main():

    connection = connect_to_db()
    if connection and connection.is_connected():
        print('\nConnection to DB success\n')
    else:
        raise SystemExit("Failed to connect to DB")
    
    cursor = connection.cursor()

    # cursor.execute('SELECT id FROM booking_data')
    cursor.execute("SELECT id FROM booking_data")
    arr_id = cursor.fetchall()

    for id in arr_id:
        # get max_available_rooms 
        cursor.execute('''SELECT room_type, MAX(max_available_rooms) AS max_available
                            FROM rooms_30_day
                            WHERE booking_id = %s
                            GROUP BY room_type''', (id[0],))
        max_available = cursor.fetchall()
        # print(max_available)


        # get available rooms
        cursor.execute('''SELECT room_type, available_rooms
                            FROM rooms_2_day
                            WHERE booking_id = %s AND DATE(checkin) = DATE(created_at)''', (id[0],))
        available_rooms = cursor.fetchall()

        # delete old data
        cursor.execute("DELETE FROM room_cache WHERE booking_id = %s", (id[0],))
        connection.commit()


        # available rooms sum
        available_rooms_sum = {}

        # Суммы свободных комнат по типам
        for room_type, count in available_rooms:
            if room_type in available_rooms_sum:
                # Увеличиваем количество повторений для этого типа комнаты
                available_rooms_sum[room_type]['count_type'] += 1
                # Прибавляем значение count к уже имеющемуся суммарному значению
                available_rooms_sum[room_type]['count'] += count
            else:
                # Если тип комнаты встречается впервые, создаем новую запись в словаре
                available_rooms_sum[room_type] = {'count_type': 1, 'count': count}

        # print(f'\n\n\navailable_rooms_sum: {available_rooms_sum}')


        # Среднее для каждого типа (средняя свободность комнат)
        for room_type, data in available_rooms_sum.items():
            count = data['count']
            count_type = data['count_type']
            
            if count_type != 0:
                # Рассчитываем и добавляем новое значение в словарь для каждого room_type
                available_rooms_sum[room_type]['result'] = count / count_type
            else:
                available_rooms_sum[room_type]['result'] = 0  # Чтобы обозначить деление на ноль

        # print(f'\n\n\navailable_rooms_sum: {available_rooms_sum}')
                

        # busy rooms sum срденяя занятасть комнат
        busy_rooms_sum = {}
        for room_type, max_count in max_available:
            if room_type in available_rooms_sum:
                # Вычитаем значение из max_available на основе result из available_rooms_sum
                busy_rooms_sum[room_type] = max_count - available_rooms_sum[room_type]['result']
            else:
                # Если room_type отсутствует в available_rooms_sum, просто присваиваем значение из max_available
                busy_rooms_sum[room_type] = None

        # print(f'\nbusy_rooms_sum: {busy_rooms_sum}')


        occupancy_rate = {}
        for room_type, max_count in max_available:
            if max_count > 0 and room_type in busy_rooms_sum and busy_rooms_sum[room_type] is not None:
                occupancy_rate[room_type] = round((busy_rooms_sum[room_type] / max_count) * 100, 2) 


        # join data
        combined_data = {}

        for room_type, max_capacity in max_available:
            if room_type in occupancy_rate:
                combined_data[room_type] = {
                    'max_available': max_capacity,
                    'occupancy_rate': occupancy_rate[room_type]
                }
            else:
                combined_data[room_type] = {
                    'max_available': max_capacity,
                    'occupancy_rate': None  
                }

        sum_occupancy_rate = [0, 0]

        for room_type, data in combined_data.items():
            max_available = data['max_available']
            occupancy_rate = data['occupancy_rate']

            if isinstance(data['occupancy_rate'], numbers.Number):
                sum_occupancy_rate[0] += data['occupancy_rate']
                sum_occupancy_rate[1] += 1

            cursor.execute("INSERT INTO room_cache (booking_id, room_type, max_available, occupancy_rate) VALUES (%s, %s, %s, %s)",
                        (id[0], room_type, max_available, occupancy_rate))
            connection.commit()

        if sum_occupancy_rate[1] > 0:
            sum_occupancy_rate[0] = round(sum_occupancy_rate[0] / sum_occupancy_rate[1], 2)
            # print(sum_occupancy_rate[0])
            cursor.execute("UPDATE booking_data SET occupancy = %s WHERE id = %s", (sum_occupancy_rate[0], id[0]))
            connection.commit()

        print(id[0], sum_occupancy_rate[0])

    print('\n\nDone')

main()
