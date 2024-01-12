const SerialPort = require('serialport');
const ReadLineParser = require('@serialport/parser-readline');
const log = require('electron-log');
const readButtons = require('./read_buttons');

const PORT_STATUS_INIT = 'PORT_STATUS_INIT';
const PORT_STATUS_OPEN = 'PORT_STATUS_OPEN';
const PORT_STATUS_CLOSE = 'PORT_STATUS_CLOSE';
const PORT_STATUS_ERROR = 'PORT_STATUS_ERROR';
const PORT_STATUS_NO_RECOGNIZED = 'PORT_STATUS_NO_RECOGNIZED';
const PORT_STATUS_CONNECTED = 'PORT_STATUS_CONNECTED';

let detectDeviceInterval;
let mainWindow;
let globalPorts = {};
let globalPortStatus = {};

class CustomReadLineParser extends ReadLineParser {
  constructor(options) {
    super(options);

    if(options['COM']){
      this.COM = options['COM'];
    }
  }
}

async function connect(win){
  mainWindow = win;
  let ports = await SerialPort.list();

  const scannerPorts = ports.filter(
    (port) => port.manufacturer === "wch.cn" && port.vendorId === '1A86' && port.productId === '7523'
  );

  scannerPorts.forEach((scannerPort) => {
    try{
      if(
        !globalPortStatus[scannerPort['path']] ||
        (globalPortStatus[scannerPort['path']] &&
        globalPortStatus[scannerPort['path']] != PORT_STATUS_CONNECTED &&
        globalPortStatus[scannerPort['path']] != PORT_STATUS_NO_RECOGNIZED)
      ){
        
        globalPorts[scannerPort['path']] = new SerialPort(scannerPort['path'], { 
          baudRate: 9600, COM: scannerPort['path'] 
        });
        globalPortStatus[scannerPort['path']] = PORT_STATUS_INIT;

        const parser = globalPorts[scannerPort['path']].pipe(
          new CustomReadLineParser({ delimiter: '\n', COM: scannerPort['path'] }));
        
        globalPorts[scannerPort['path']].on("open", onOpenPort);

        globalPorts[scannerPort['path']].on('close', onClosePort);

        globalPorts[scannerPort['path']].on("error", onErrorPort);

        parser.on('data', onDataPort);

        parser.on('error', function(err){
          log.error("Serial Port Error", err);
          globalPortStatus[this.COM] = PORT_STATUS_ERROR;
        });
      }
    }catch(e){
      log.error(e);
    }
  });

  searchPort();
}

function onOpenPort() {
  log.info('Serial Port Opened', this.settings['COM']);
  globalPortStatus[this.settings['COM']] = PORT_STATUS_OPEN;
  /*
  globalPorts[this.settings['COM']].write('RESET\n', function(err) {
    if (err) {
      return console.log('Error on write: ', err.message)
    }
    log.info("Sent RESET to MATCH HUB");
  });
  */
}

function onClosePort() {
  log.info("Serial Port Closed", this.settings['COM']);
  delete globalPorts[this.settings['COM']];
  clearInterval(detectDeviceInterval);
  if(globalPortStatus[this.settings['COM']] == PORT_STATUS_CONNECTED){
    connect(mainWindow);
  }
}

function onErrorPort(error) {
  log.info(error);
  globalPortStatus[this.settings['COM']] = PORT_STATUS_ERROR;
}

async function onDataPort(data) {
  data = data.replace('\r','');

  if (data == 'STATUS_READING'){
    log.info("Serial Port STATUS_READING", this.COM);
    globalPortStatus[this.COM] = PORT_STATUS_CONNECTED;
    clearInterval(detectDeviceInterval);

    for (let key in globalPorts) {
      if(key !== this.COM){
        try{
          globalPorts[key].close();
        } catch (e){
          log.info("Trying to close a none open port", key);
        }
      }
    }
  } else if(data == 'STATUS_NO_DEVICE_RECOGNIZED'){
    log.info("Serial Port Closed as Invalid DEVICE", this.COM);
    globalPortStatus[this.COM] = PORT_STATUS_NO_RECOGNIZED;
    globalPorts[this.COM].close();
  } else {
    log.info("Serial Port Data", data);

    mainWindow.webContents.send('SERIAL_PORT_AVAILABLE', {status: true});

    readButtons.readButtons(data, mainWindow);
  }
}

function searchPort(){
  detectDeviceInterval = setInterval(async (mainWindow) => {
    if(Object.keys(globalPorts).length == 0){
      log.error("No device connected");
      clearInterval(detectDeviceInterval);
      connect(mainWindow);
    }else{
      for (let key in globalPorts) {
        if(globalPortStatus[key] == PORT_STATUS_CONNECTED || globalPortStatus[key] == PORT_STATUS_NO_RECOGNIZED){
          break;
        }
        
        globalPort = globalPorts[key];
        
        globalPort.write('MATCH\n', function(err) {
          if (err) {
            return console.log('Error on write: ', err.message)
          }
          log.info("Sent WORD to MATCH on PORT: ", key);
        });
      };
    }
  }, 5000, mainWindow);
}
module.exports = { connect };