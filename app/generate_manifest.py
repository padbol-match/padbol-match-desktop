import json
import os
import datetime
from logger import logger

def generate(video_path, file_video_name, file_marks_name, default_split_time):
    min_diff_time = 30
    max_number_of_matches = 5

    try:
        if os.path.exists(video_path + file_video_name + '.json'):
            os.remove(video_path + file_video_name + '.json')

        if not os.path.exists(video_path):
            logger.error("File does not exist: %s" % video_path + file_marks_name)
            raise SystemExit

        # TODO - If not exist?
        with open(video_path + file_marks_name) as f:
            lines = f.readlines()

        marks = []
        count = 0
        last_line_time = 0

        for line in lines:
            line = line.replace('\n','').replace('\r','')
            if(line != ''):
                if count == 0:
                    initial_time = datetime.datetime.strptime(line, '%d/%m/%Y %H:%M:%S')
                    last_line_time = initial_time
                    count += 1
                else:
                    line_time = datetime.datetime.strptime(line, '%d/%m/%Y %H:%M:%S')
                    
                    if (line_time-last_line_time).total_seconds() >= min_diff_time and count < (max_number_of_matches + 1):
                        last_line_time = line_time
                        mark = {  
                            "mark_time": line,
                            "time": (line_time - datetime.timedelta(seconds=default_split_time)).strftime("%d/%m/%Y %H:%M:%S"),
                            "start_time": (line_time-initial_time).total_seconds() - default_split_time, # TODO - If not valid?
                            "length": default_split_time,
                            "rename_to": "%s%s_%d.mp4" % (video_path, file_video_name, count)
                        }  
                        marks.append(mark)
                        count += 1

        # TODO - If not exist?
        with open(video_path + file_video_name + '.json', 'w', encoding='utf-8') as f:
            json.dump(marks, f, ensure_ascii=False, indent=4)
        
        return 1
    except Exception as e:
        logger.error("Problemas: ",  str(e))
        return e.message