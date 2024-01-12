const path = require('path');
const fs = require("fs");
const dateFormat = require('dateformat');
const constants = require('./constants.js');
const log = require('electron-log');

async function readButtons(data, mainWindow){
  try{
    const now = new Date();
    const record = {
      date: dateFormat(now, "yyyy_mm_dd"),
      field: parseInt(data.replace("Cancha ", "")).toString()
    };

    const dir = path.join(constants.UPLOADER_FOLDER_PATH, record.date);
    const scheduleFile = path.join(dir, record.date + ".schedule.json");

    if (fs.existsSync(scheduleFile)){
      const schedules = JSON.parse(fs.readFileSync(scheduleFile));

      //log.debug("read_buttons_1", schedules);

      schedules.forEach((schedule) => {
        const scheduleStartDate = new Date(schedule["start_date"]["date"]);
        const scheduleEndDate = new Date(schedule["end_date"]["date"]);
        
        //log.debug("read_buttons_2", scheduleStartDate, now, scheduleEndDate);
        
        if(now >= scheduleStartDate &&  now <= scheduleEndDate && parseInt(schedule.field) == parseInt(record.field)){
          const timeFolder = dateFormat(scheduleStartDate, "HH_MM");
          const marksFile = path.join(dir, timeFolder, record.field, record.date + '_' + timeFolder + '_' + record.field + '.txt');

          //log.debug("read_buttons_3", fs.existsSync(marksFile));

          if (fs.existsSync(marksFile)){
            //const seconds = Math.round((now.getTime() - scheduleStartDate.getTime()) / 1000);
            fs.appendFileSync(marksFile, new Date().toLocaleString() + '\n');
          }
        }

      });
    }
    
    mainWindow.webContents.send('PUSH_BUTTON_PRESSED', {button: data});
    log.info("PUSH_BUTTON_PRESSED", data);
  } catch(error) {
    log.error("Error:", error);
    return false;
  }
}

/*
async function serialPortsAvailable(mainWindow){
  const ports = await SerialPort.list();

  const scannerPort = ports.filter(
    (port) => port.manufacturer === "wch.cn" && port.vendorId === '1A86' && port.productId === '7523'
  );
  
  const serialPortAvailable = scannerPort.length !== 0;

  mainWindow.webContents.send('SERIAL_PORT_AVAILABLE', {status: serialPortAvailable});
  //log.info("SERIAL_PORT_AVAILABLE", serialPortAvailable);

  return serialPortAvailable;
}

async function readButtons(mainWindow){
  const ports = await SerialPort.list();
  
  const scannerPort = ports.filter(
    (port) => port.manufacturer === "wch.cn" && port.vendorId === '1A86' && port.productId === '7523'
  );

  if (scannerPort.length !== 0) {
    const port = new SerialPort(scannerPort[0].path, { baudRate: 9600 });
    const parser = port.pipe(new Readline({ delimiter: '\n' }));

    // Read the port data
    port.on("open", () => {
      log.info("readButtons", 'Connected to ' + scannerPort[0].path);
    });

    parser.on('data', data =>{
      
      const now = new Date();
      const record = {
        date: dateFormat(now, "yyyy_mm_dd"),
        field: parseInt(data.replace("Cancha ", "")).toString()
      };

      const dir = path.join(constants.UPLOADER_FOLDER_PATH, record.date);
      const scheduleFile = path.join(dir, record.date + ".schedule.json");

      if (fs.existsSync(scheduleFile)){
        const schedules = JSON.parse(fs.readFileSync(scheduleFile));

        //log.debug("read_buttons_1", schedules);

        schedules.forEach((schedule) => {
          const scheduleStartDate = new Date(schedule["start_date"]["date"]);
          const scheduleEndDate = new Date(schedule["end_date"]["date"]);
          
          //log.debug("read_buttons_2", scheduleStartDate, now, scheduleEndDate);
          
          if(now >= scheduleStartDate &&  now <= scheduleEndDate && parseInt(schedule.field) == parseInt(record.field)){
            const timeFolder = dateFormat(scheduleStartDate, "HH_MM");
            const marksFile = path.join(dir, timeFolder, record.field, record.date + '_' + timeFolder + '_' + record.field + '.txt');

            //log.debug("read_buttons_3", fs.existsSync(marksFile));

            if (fs.existsSync(marksFile)){
              //const seconds = Math.round((now.getTime() - scheduleStartDate.getTime()) / 1000);
              fs.appendFileSync(marksFile, new Date().toLocaleString() + '\n');
            }
          }

        });
      }
      
      mainWindow.webContents.send('PUSH_BUTTON_PRESSED', {button: data});
      log.info("PUSH_BUTTON_PRESSED", data);
    });
    
    return true;
  }

  return false;
}
*/


module.exports = { readButtons };