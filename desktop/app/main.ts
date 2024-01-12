import { app, BrowserWindow, Tray, ipcMain, shell, Menu } from 'electron';
import * as path from 'path';
import * as constants from './constants.js';
import * as log from 'electron-log';
import * as connectArduino from './connect_arduino';
import { createFolders } from './background/create_folders.js';
import { processVideo, processVideoReady, checkRecordStatus } from './background/process_video.js';
import { recordVideoReady, scheduleRecordings } from './background/record_video.js';
import { Store } from './store.js';
import { createWindow } from './create_window.js';
const execFile = require('child_process').execFile;

// Initialize remote module
require('@electron/remote/main').initialize();

let win: BrowserWindow = null;
let serialPortRegistered = false;
const args = process.argv.slice(1);
const serve = args.some(val => val === '--serve');
let tray: any;

let store = new Store({
  // We'll call our data file 'user-preferences'
  configName: Store.USER_FILE_NAME,
  defaults: {
    dvr_settings: {
      ip: "",
      port: "",
      username: "",
      password: ""
    },
    user_settings: {
      username: ""
    }
  }
});

function createTray() {
  tray = new Tray(path.join(__dirname, 'logo.png'));
  
  tray.setContextMenu(Menu.buildFromTemplate([{
      label: 'Padbol Match',
      enabled: false,
    },{
      type: 'separator',
    },{
      label: 'Show', 
      click: function () {
        win.show();
      }
    },{
      label: 'Quit', 
      click: function () {
        killProcess();
        //app.quit();
      }
    }
  ]));
  
}

function killProcess(){
  //TODO-SWITCH-PROD
  const mainPath = path.join(constants.DIST_FOLDER_PATH, 'kill_process.exe');
  //TODO-SWITCH-DEV
  //const mainPath = path.join(__dirname, '/../../app_kill_process/dist/kill_process.exe');

  execFile(mainPath, [], function(err, data) {                    
    if(err){
      log.error("Problems ejecuting kill_process.exe", err);
    }
  });  
}

try {
  // This method will be called when Electron has finished
  // initialization and is ready to create browser windows.
  // Some APIs can only be used after this event occurs.
  // Added 400 ms to fix the black background issue while using transparent window. More detais at https://github.com/electron/electron/issues/15947
  app.on('ready', () => {
    win = createWindow(app, win);
    createTray();

    connectArduino.connect(win);
  });

  // Quit when all windows are closed.
  app.on('window-all-closed', () => {
    // On OS X it is common for applications and their menu bar
    // to stay active until the user quits explicitly with Cmd + Q
    if (process.platform !== 'darwin') {
      app.quit();
    }
  });

  app.on('activate', () => {
    // On OS X it's common to re-create a window in the app when the
    // dock icon is clicked and there are no other windows open.
    if (win === null) {
      win = createWindow(app, win);
    }
  });

  app.on('before-quit', function () {
    if(tray !== undefined)
      tray.destroy();
  });

  // Behaviour on second instance for parent process- Pretty much optional
  app.on('second-instance', (event, argv, cwd) => {
    if (win) {
      if (win.isMinimized()) win.restore()
      win.focus()
    }
  });

} catch (e) {
  log.error(e);
}

ipcMain.on('PROCESS_RECORD_READY', recordVideoReady);

ipcMain.on('PROCESS_PROCESS_VIDEO', processVideo);

ipcMain.on('PROCESS_PROCESS_VIDEO_READY', processVideoReady);

ipcMain.on('PROCESS_CREATE_FOLDERS', createFolders);

ipcMain.on('PROCESS_SCHEDULE_RECORDINGS', scheduleRecordings);

ipcMain.on('START_BACKGROUND_CHECK_STATUS', checkRecordStatus);

ipcMain.on('OPEN_DIRECTORY', function(event, args) {
	let dir = path.join(constants.UPLOADER_FOLDER_PATH, args.date, args.time);
	shell.openPath(dir);
});

ipcMain.on('OPEN_BROWSER', function(event, args) {
	shell.openExternal(args.url);
});

ipcMain.on('SAVE_DATA_DVR_SETTINGS', function(event, args) {
	store.set(Store.DVR_SETTINGS, args);
});

ipcMain.on('LOAD_DATA_DVR_SETTINGS', function(event, args) {
	event.reply('LOADED_DATA_DVR_SETTINGS', {
		data: store.get(Store.DVR_SETTINGS),
	});
});

//Read serial
/*
setInterval(async () => {
  if(await serialPortsAvailable(win) && !serialPortRegistered){
    serialPortRegistered = await readButtons(win);
    console.log("Padbol Match Hub detected");
  }else if(!await serialPortsAvailable(win)){
    serialPortRegistered = false;
    console.log("Padbol Match Hub not detected");
  }
}, 10000);
*/

/** Check if single instance, if not, simply quit new instance */
let isSingleInstance = app.requestSingleInstanceLock()
if (!isSingleInstance) {
  app.quit()
}

