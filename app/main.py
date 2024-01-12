import generate_manifest
import generate_brand_video
import split_videos
import join_videos
import upload_video
import post_video
import sys
import os 
import datetime

current_dir = sys.argv[1]
file_name = sys.argv[2]
username = sys.argv[3]
user_token = sys.argv[4]
appointment_number = sys.argv[5]

video_path = current_dir + "\\"
file_video_name = file_name + ".mp4"
marks_file_name = file_name + ".txt"
marks_file_path = current_dir + "\\" + marks_file_name
status_file_name = "status.txt"
status_file_path = current_dir + "\\" + status_file_name

default_split_time = 15

def save_status(desc):
    with open(status_file_path, "a", encoding='utf-8') as file:
        file.write(desc + "\n")

def canStart():
    status_file_lines = []

    if not os.path.exists(status_file_path):
        return False
    
    if not os.path.exists(marks_file_path):
        return False

    with open(status_file_path) as f:
        status_file_lines = f.readlines()
    
    with open(marks_file_path) as f:
        marks_file_lines = f.readlines()

    if(len(status_file_lines)>0 and len(marks_file_lines)>0):
        return "start-processing\n" in status_file_lines
    return False

def finished():
    lines = []

    if not os.path.exists(status_file_path):
        return False

    with open(status_file_path) as f:
        lines = f.readlines()

    if(len(lines)>0):
        return "finished" in lines[len(lines)-1]
    return False

def video_generator():
    if(not finished()):
        save_status("generate=0")
        status = generate_manifest.generate(video_path, file_video_name, marks_file_name, default_split_time)
        save_status("generate=" + str(status))
    
        save_status("split=0")
        status = split_videos.split(video_path, file_video_name)
        save_status("split=" + str(status))

        save_status("brand_video=0")
        status = generate_brand_video.generate_brand_video(video_path, file_video_name)
        save_status("brand_video=" + str(status))
    
        save_status("join=0")
        status = join_videos.join(video_path, file_video_name)
        save_status("join=" + str(status))

        save_status("upload=0")
        name = "Padbol - " + file_video_name
        description = "Padbol - Mejores Jugadas - " + file_video_name
        status = upload_video.upload(video_path, file_video_name, name, description)
        save_status("upload=" + str(status))

        save_status("post=0")
        description = "Padbol - Mejores Jugadas - " + file_video_name
        vimeo_url = str(status).replace("videos", "video")
        status = post_video.post(username, description, vimeo_url, user_token, appointment_number)
        save_status("post=" + str(status))

        save_status("finished")
    
if __name__ == '__main__':
    if(canStart()):
        save_status("start=" + datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S'))
        video_generator()

    