import json
import os
import csv
import shlex
import subprocess
from logger import logger

def split_by_manifest(filename, manifest, vcodec="copy", acodec="copy",
                      extra="", **kwargs):
    """ Split video into segments based on the given manifest file.
    Arguments:
        filename (str)      - Location of the video.
        manifest (str)      - Location of the manifest file.
        vcodec (str)        - Controls the video codec for the ffmpeg video
                            output.
        acodec (str)        - Controls the audio codec for the ffmpeg video
                            output.
        extra (str)         - Extra options for ffmpeg.
    """
    if not os.path.exists(manifest):
        logger.error("File does not exist: %s" % manifest)
        raise SystemExit

    with open(manifest) as manifest_file:
        current_dir = os.path.dirname(__file__)

        manifest_type = manifest.split(".")[-1]
        if manifest_type == "json":
            config = json.load(manifest_file)
        elif manifest_type == "csv":
            config = csv.DictReader(manifest_file)
        else:
            logger.error("Format not supported. File must be a csv or json file")
            raise SystemExit


        split_cmd = [current_dir + "\\ffmpeg", "-i", filename, "-vcodec", vcodec,
                     "-acodec", acodec, "-y"] + shlex.split(extra)
        try:
            fileext = filename.split(".")[-1]
        except IndexError as e:
            raise IndexError("No . in filename. Error: " + str(e))
        for video_config in config:
            split_str = ""
            split_args = []
            try:
                split_start = video_config["start_time"]
                split_length = video_config.get("end_time", None)
                if not split_length:
                    split_length = video_config["length"]
                filebase = video_config["rename_to"]
                if fileext in filebase:
                    filebase = ".".join(filebase.split(".")[:-1])

                split_args += ["-ss", str(split_start), "-t",
                    str(split_length), filebase + "." + fileext]
                logger.info("########################################################")
                logger.info("About to run: "+" ".join(split_cmd+split_args))
                logger.info("########################################################")
                subprocess.check_output(split_cmd+split_args)
            except KeyError as e:
                logger.error("############# Incorrect format ##############")
                if manifest_type == "json":
                    logger.error("The format of each json array should be:")
                    logger.error("{start_time: <int>, length: <int>, rename_to: <string>}")
                elif manifest_type == "csv":
                    logger.error("start_time,length,rename_to should be the first line ")
                    logger.error("in the csv file.")
                logger.error("#############################################")
                logger.error(e)
                raise SystemExit
    return 1

def split(video_path, file_video_name):
    split_by_manifest(
        filename=video_path + file_video_name,
        manifest=video_path + file_video_name + ".json",
        vcodec="h264"
    )