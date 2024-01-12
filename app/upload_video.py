import vimeo
import requests
import constants
from logger import logger


def getVimeoCredentials():
    try:
        logger.info('Getting credentials: %s' % constants.GATEWAY_URL + "/api/vimeo/get-credentials")
        response = requests.post(
            constants.GATEWAY_URL + "/api/vimeo/get-credentials"
        )
        return response.json()
    except Exception as e:
        logger.error('Problems getting credentials: %s' % e.message)
        return e.message

def upload(video_path, file_video_name, name, description):
    try:
        vimeoCredentials = getVimeoCredentials()
        
        client = vimeo.VimeoClient(
            token=vimeoCredentials["token"],
            key=vimeoCredentials["key"],
            secret=vimeoCredentials["secret"]
        )

        logger.info("Uploading to Vimeo")
        
        file_name = video_path + "upload_" + file_video_name
        #uri = "/video/23423423"
    
        uri = client.upload(file_name, data={
            'name': name,
            'description': description
        })
        
        logger.info('Your video URI is: %s' % (uri))

        return uri
    except vimeo.exceptions.BaseVimeoException as e:
        # report it to the user.
        logger.error('Error uploading %s' % file_name)
        logger.error('Server reported: %s' % e.message)

        return e.message
