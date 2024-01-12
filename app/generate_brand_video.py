import os
import subprocess
import re
from logger import logger

def create_video_from_image(video_path, stats):
    current_dir = os.path.dirname(__file__)
    split_cmd = [current_dir + "\\ffmpeg", 
        "-framerate", "24", 
        "-loop", "1", 
        "-t", "2",
        "-i", current_dir + "\\brand.png", 
        "-s", stats['resolution'],
        "-c:v", "libx264",
        "-r", "30",
        "-pix_fmt", "yuv420p",
        "-aspect", stats['dar'],
        video_path + "video_logo.mp4",
        "-y" ]

    try:
        logger.info("########################################################")
        logger.info("About to run: "+" ".join(split_cmd))
        logger.info("########################################################")
        #subprocess.check_output(split_cmd)
        os.system(" ".join(split_cmd))
    except subprocess.CalledProcessError as e:
        logger.error(e)

def create_stats_from_video(video_path, file_video_name):
    current_dir = os.path.dirname(__file__)

    #split_cmd = [current_dir + "\\ffmpeg", "-i", file_video_name]
    split_cmd = [
        current_dir + "\\ffmpeg", 
        "-i", video_path + "\\" + file_video_name, 
        ">",  video_path + "\\" + file_video_name + ".stats.txt",
        "2>&1"]


    try:
        logger.info("########################################################")
        logger.info("About to run: "+" ".join(split_cmd))
        logger.info("########################################################")
        #subprocess.check_output(split_cmd)
        os.system(" ".join(split_cmd))
    except subprocess.CalledProcessError as e:
        logger.error(e)

def get_stats_from_txt(video_path, file_video_name):
    lines = []
    stats_line = ""
    stats_file_path = video_path + "\\" + file_video_name + ".stats.txt"

    if not os.path.exists(stats_file_path):
        return False

    with open(stats_file_path) as f:
        lines = f.readlines()

    for line in lines:
        if("Stream #0:0" in line):
            stats_line = line

    resolutionRegExp = re.compile('\s\d{2,4}x\d{2,4}\s')
    resultResolution = resolutionRegExp.search(stats_line)

    sarRegExp = re.compile('\[SAR\s\d+\:\d+\s')
    resultSar = sarRegExp.search(stats_line)

    darRegExp = re.compile('\sDAR\s\d+\:\d+')
    resultDar = darRegExp.search(stats_line)

    result = {
        'resolution': resultResolution.group(0).replace(" ", ""),
        'sar': resultSar.group(0).replace("[SAR ", "").replace(" ", ""),
        'dar': resultDar.group(0).replace("DAR ", "").replace(" ", "")
    }

    logger.info(result)

    return result

def generate_brand_video(path, video_name):
    create_stats_from_video(path, video_name)
    stats = get_stats_from_txt(path, video_name)
    create_video_from_image(path, stats)