import psutil

for proc in psutil.process_iter(['pid', 'name']):
    if('padbol' in proc.info['name'].lower() and 'match' in proc.info['name'].lower()):
        print(proc.info)
        proc.kill()