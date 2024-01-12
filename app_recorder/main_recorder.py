import cv2
import sys
import datetime
import logging
import os 

# Params
user = sys.argv[1]
password = sys.argv[2]
ip = sys.argv[3]
port = sys.argv[4]
channel = sys.argv[5]
path = sys.argv[6]
file_name = sys.argv[7]
time_minutes = sys.argv[8]
field = sys.argv[9]
fps = int(sys.argv[10])


lockFilePath = path + "\\" + file_name.replace(".mp4",".lock")
rtsp_url = 'rtsp://%s:%s@%s:%s/Streaming/channels/%s/'%(user, password, ip, port, channel)

logging.basicConfig(
    format='%(asctime)s :: %(levelname)s :: %(funcName)s :: %(lineno)d :: %(message)s', 
    level=logging.DEBUG, 
    filename='padbol-match-recorder.log')

logger = logging.getLogger(__name__)

def record_video():
    try:
        #Store start recording time
        now = datetime.datetime.now()
        date_time = now.strftime("%d/%m/%Y %H:%M:%S")

        stream = cv2.VideoCapture(rtsp_url, cv2.CAP_FFMPEG)

        if(not stream.isOpened()):
            logger.error("Connection refused. Check credentials: " + rtsp_url)
        else:
        
            width = 1280 #int(stream.get(cv2.CAP_PROP_FRAME_WIDTH) + 0.5)
            height = 720 #int(stream.get(cv2.CAP_PROP_FRAME_HEIGHT) + 0.5)
            size = (width, height)
            fourcc = cv2.VideoWriter_fourcc(*'mp4v')
            out = cv2.VideoWriter(path + "\\" + file_name, fourcc, fps, size)

            with open(path + "\\" + file_name.replace(".mp4",".txt"), "a+", encoding='utf-8') as file:
                file.write(date_time + "\n")
            
            with open(path + "\\status.txt", "a+", encoding='utf-8') as file:
                file.write("record-starting\n")

            finish_time = datetime.datetime.now() + datetime.timedelta(minutes=int(time_minutes))
            while datetime.datetime.now() < finish_time:
                ret, frame = stream.read()
                if ret == False:
                    logger.error("Connection refused. Check credentials: " + rtsp_url)
                    break
                #frameRotated = cv2.rotate(frame, cv2.ROTATE_90_CLOCKWISE)
                frameResized = cv2.resize(frame,(1280,720),fx=0, fy=0, interpolation = cv2.INTER_CUBIC)
                #cv2.imshow('Recording...', frameRotated)
                out.write(frameResized)
                #if cv2.waitKey(1) & 0xFF == ord('q'):
                #    break
            
            stream.release()
            out.release()

            with open(path + "\\status.txt", "a", encoding='utf-8') as file:
                file.write("record-complete\n")
    except cv2.error as e:
        logger.error(e)
    except Exception as e:
        logger.error(e)

    removeLockFile()

def lockFile():
    if not os.path.exists(lockFilePath):
        with open(lockFilePath, "w") as flock:
            flock.write(rtsp_url)
        return True
    return False

def removeLockFile():
    if os.path.exists(lockFilePath):
        os.remove(lockFilePath)

if __name__ == '__main__':
    try:
        if(lockFile()):
            record_video()
    except Exception as e:
        print(e)
    sys.exit(1)








