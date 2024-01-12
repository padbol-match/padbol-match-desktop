import requests
import constants
from logger import logger

def post(username, title, vimeo_url, user_token, appointment_number):
    try:
        response = requests.post(
            constants.GATEWAY_URL + "/api/post",
            json = {
                "username": username,
                "title": title,
                "url": vimeo_url,
                "token": user_token,
                "appointment_number": appointment_number
            }
        )
        response.raise_for_status()
        logger.info("Return OK from Post")
        #result = response.json()
        return 1
    except requests.exceptions.HTTPError as err:
        logger.error("Return Error from Post: " + err.message)
        return err.message
        

