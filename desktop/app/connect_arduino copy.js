const SerialPort = require('serialport');
const Readline = require('@serialport/parser-readline');
const log = require('electron-log');
const readButtons = require('./read_buttons');

let detectDeviceInterval;

async function connect(mainWindow){
  let ports = await SerialPort.list();

  const scannerPorts = ports.filter(
    (port) => port.manufacturer === "wch.cn" && port.vendorId === '1A86' && port.productId === '7523'
  );

  scannerPorts.forEach(async (scannerPort) => {
    globalPort = new SerialPort(scannerPort['path'], { baudRate: 9600 });

    const parser = globalPort.pipe(new Readline({ delimiter: '\n' }));
    
    globalPort.on("open", () => {
      log.info('Serial Port Opened', scannerPort);
    });

    globalPort.on('close', function(){
      log.info("Serial Port Closed", scannerPort);
      delete globalPort;
      searchPort(mainWindow);
    });
    
    globalPort.on("error", function(error) {
      log.info(error);
    });

    parser.on('data', async (data) =>{
      data = data.replace('\r','');

      if (data == 'STATUS_READING'){
        clearInterval(detectDeviceInterval);
        mainWindow.webContents.send('SERIAL_PORT_AVAILABLE', {status: true});

      } else if(data == 'STATUS_NO_DEVICE_RECOGNIZED'){
        log.info("Serial Port Closed as Invalid DEVICE", err);
        globalPort.close();
      } else {

        readButtons.readButtons(data, mainWindow);
      }
    });

    parser.on('error', function(err){
      log.error("Serial Port Error", err);
    });
    
  });

  searchPort(mainWindow);
}


function searchPort(mainWindow){
  detectDeviceInterval = setInterval(async (mainWindow) => {
    try{
      globalPort.write('MATCH\n', function(err) {
        if (err) {
          return console.log('Error on write: ', err.message)
        }
        log.info("Sent WORD to ARDUINO");
      });
    }catch(e){
      log.error("No device connected");
      clearInterval(detectDeviceInterval);
      connect(mainWindow);
    }
  }, 1000, mainWindow);
}
module.exports = { connect };