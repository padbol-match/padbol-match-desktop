import logging

logging.basicConfig(
    format='%(asctime)s :: %(levelname)s :: %(funcName)s :: %(lineno)d :: %(message)s', 
    level=logging.DEBUG, 
    filename='padbol-match-main.log')

logger = logging.getLogger(__name__)