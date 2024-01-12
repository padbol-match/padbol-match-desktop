const path = require('path');
const fs = require("fs");
const url = require('url');
const electron = require('electron');
const { BrowserWindow } = electron;
const constants = require('../constants.js');
const log = require('electron-log');

// a window object outside the function scope prevents
// the object from being garbage collected
let recordHiddenWindow;

// temporary variable to store data while background
// process is ready to start processing
let recordCache = {
	data: undefined,
};

module.exports = {
    recordVideoReady: (event, args) => {
        event.reply('PROCESS_RECORD', {
            data: recordCache,
        });
    },
    scheduleRecordings: (event, args) => {
        const records = args.data;
        const dvrSettings = args["dvr-settings"];

        records.forEach((record) => {
            try{
                const timeStart = record.start_date.date.split(" ")[1].split(":");
                const date = record.start_date.date.split(" ")[0].replaceAll("-","_");
                const time = timeStart[0] + "_" + timeStart[1];
                const field = record.field;
                const dir = path.join(constants.UPLOADER_FOLDER_PATH, date, time, field);
                const video_name = date + "_" + time + "_" + field + ".mp4";
                
                const recordDate = [parseInt(date.split("_")[0]), parseInt(date.split("_")[1]), parseInt(date.split("_")[2])];
                const recordTime = [parseInt(time.split("_")[0]), parseInt(time.split("_")[1])];
                const actualDate = new Date();
                const currentDate = [actualDate.getFullYear(), actualDate.getMonth() + 1, actualDate.getDate()];
                const currentTime = [actualDate.getHours(), actualDate.getMinutes()];

                const isValidDate = recordDate[0] === currentDate[0] &&
                    recordDate[1] === currentDate[1] &&
                    recordDate[2] === currentDate[2];
                const isValidTime = recordTime[0] === currentTime[0] && (
                    currentTime[1] >= recordTime[1] &&
                    currentTime[1] <= recordTime[1] + 5
                );
                
                if (!fs.existsSync(path.join(dir, constants.STATUS_FILE_NAME)) && isValidDate && isValidTime){
                    statusFile = path.join(dir, constants.STATUS_FILE_NAME);
                    fs.appendFileSync(statusFile, "");

                    const backgroundFileUrl = url.format({
                        //TODO-SWITCH-PROD
                        pathname: path.join(constants.ROOT_FOLDER_PATH, 'background_tasks', 'record_video.html'),
                        //TODO-SWITCH-DEV
                        //pathname: path.join(constants.ROOT_FOLDER_PATH, 'app', 'background_tasks', 'record_video.html'),
                        protocol: 'file:',
                        slashes: true,
                    });

                    log.debug("record_video.js-scheduleRecordings", "call html", backgroundFileUrl);

                    recordCache[video_name] = {
                        username: dvrSettings.username,
                        password: dvrSettings.password,
                        ip: dvrSettings.ip,
                        port: dvrSettings.port,
                        channel: record.field + "01",
                        path: dir,
                        filename: video_name,
                        duration: parseInt(record.duration),
                        field: record.field,
                        fps: dvrSettings.fps
                    };

                    recordHiddenWindow = new BrowserWindow({
                        show: false,
                        webPreferences: {
                            contextIsolation: false,
                            nodeIntegration: true,
                            additionalArguments: [video_name],
                            enableRemoteModule: true
                        },
                    });
                    recordHiddenWindow.loadURL(backgroundFileUrl);
                    
                    //TODO-SWITCH-DEV
                    recordHiddenWindow.webContents.openDevTools();
                    
                    recordHiddenWindow.on('closed', () => {
                        recordHiddenWindow = null;
                    });
                }
            }catch(error){
                log.error("record_video.js-scheduleRecordings", error);
            }
        });
    }
}