import subprocess
import os
import json
from logger import logger

# Generate video from IMG
#ffmpeg -loop 1 -f image2 -i logo_1280_720.jpg -r 30 -t 3 video_from_img.mp4

'''
ffmpeg ^
-i video_from_img.mp4 ^
-i video1.mp4 ^
-i video_from_img.mp4 ^
-i video2.mp4 ^
-i video_from_img.mp4 ^
-i video3.mp4 ^
-i video_from_img.mp4 ^
-i video4.mp4 ^
-i video_from_img.mp4 ^
-i video5.mp4 ^
-i video_from_img.mp4 ^
-filter_complex "[0][1][2][3][4][5][6][7][8][9][10]concat=n=11:v=1:a=0" ^
-vsync 2 ^
-an ^
video_concat.mp4
''' 

def join(video_path, file_video_name):
    current_dir = os.path.dirname(__file__)

    command = current_dir + "\\ffmpeg -y %s -filter_complex \"%sconcat=n=%d:v=1:a=0\" -vsync 2 -an %s"
    manifest = video_path + file_video_name + ".json"

    if not os.path.exists(manifest):
        logger.error("File does not exist: %s" % manifest)
        raise SystemExit

    with open(manifest) as manifest_file:
        manifest_type = manifest.split(".")[-1]
        if manifest_type == "json":
            config = json.load(manifest_file)
        else:
            logger.error("Format not supported. File must be a json file")
            raise SystemExit

        video_commands = ""
        video_inputs = ""

        counter = 0
        for video_config in config:
            filebase = video_config["rename_to"]
            video_commands += "-i " + video_path + "video_logo.mp4 -i %s " % (filebase)
            video_inputs += "[%d][%d]" % (counter, counter + 1)
            counter += 2
        video_commands += "-i " + video_path + "video_logo.mp4 "

    command = command % (video_commands, video_inputs, len(config) * 2 + 1, video_path + "upload_" + file_video_name)
    try:
        logger.info("########################################################")
        logger.info("About to run: "+" " + command)
        logger.info("########################################################")
        subprocess.check_output(command)

        return 1
    except Exception as e:
        logger.error("Problemas: ",  e.message)

        return e.message