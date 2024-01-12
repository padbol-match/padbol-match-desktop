const BrowserWindow = require("electron").BrowserWindow;
const Menu = require("electron").Menu;
const path = require('path');
const log = require('electron-log');
const fs = require("fs");
const url = require("url");

function createWindow(app, win){
    log.info("Application started");
    
    //TODO - LEO - Check this
    const args = process.argv.slice(1);
    const serve = args.some(val => val === '--serve');

    Menu.setApplicationMenu(null);
  
    // Create the browser window.
    win = new BrowserWindow({
      width: 800, 
      height: 650,
      icon: __dirname + '/logo.png',
      webPreferences: {
        nodeIntegration: true,
        allowRunningInsecureContent: (serve) ? true : false,
        preload: path.join(__dirname, "preload.js"), // use a preload script
        contextIsolation: true,  // false if you want to run e2e test with Spectron
        enableRemoteModule : true // true if you want to run e2e test with Spectron or use remote module in renderer context (ie. Angular)
      },
      autoHideMenuBar: true
    });
  
  
    if (serve) {
      win.webContents.openDevTools();
      require('electron-reload')(__dirname, {
        electron: require(path.join(__dirname, '/../node_modules/electron'))
      });
      win.loadURL('http://localhost:4200');
    } else {
      // Path when running electron executable
      let pathIndex = './index.html';
  
      if (fs.existsSync(path.join(__dirname, '../dist/index.html'))) {
         // Path when running electron in local folder
        pathIndex = '../dist/index.html';
      }
  
      win.loadURL(url.format({
        pathname: path.join(__dirname, pathIndex),
        protocol: 'file:',
        slashes: true
      }));
    }
  
    win.on('close', function (event) {
        event.preventDefault();
        win.hide();
        event.returnValue = false;
    });
  
    // Emitted when the window is closed.
    win.on('closed', () => {
      // Dereference the window object, usually you would store window
      // in an array if your app supports multi windows, this is the time
      // when you should delete the corresponding element.
      win = null;
      app.quit();
    });
  
    win.on('minimize', (event) => {
        event.preventDefault();
        win.hide();
    })
  
    return win;
}

module.exports = { createWindow };