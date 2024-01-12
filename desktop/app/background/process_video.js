const path = require('path');
const fs = require("fs");
const url = require('url');
const electron = require('electron');
const { BrowserWindow } = electron;
const constants = require('../constants.js');
const log = require('electron-log');

// a window object outside the function scope prevents
// the object from being garbage collected
let processHiddenWindow;

// temporary variable to store data while background
// process is ready to start processing
let processCache = {
	data: undefined,
};

module.exports = {
    processVideo: (event, args) => {
        let dir = path.join(constants.UPLOADER_FOLDER_PATH, args.date, args.time, args.field);
        let video_name = args.date + '_' + args.time + '_' + args.field + ".mp4";
        let video = path.join(dir, video_name);
        let status_file = path.join(dir, constants.STATUS_FILE_NAME);
        let video_processed = false;

        try{
            if (fs.existsSync(status_file)){
                let lines = fs.readFileSync(status_file).toString().split("\n");
                last_line = lines.filter(item => item).pop(-1); //Get the last line
                last_line = last_line ? last_line : '';// In case is empty, return ''
                video_processed = last_line.indexOf('record-complete') === -1;
            }

        
            if (fs.existsSync(video) && fs.existsSync(status_file) && !video_processed){
                fs.appendFileSync(status_file, "start-processing\n");

                const backgroundFileUrl = url.format({
                    //TODO-SWITCH-PROD
                    pathname: path.join(constants.ROOT_FOLDER_PATH, 'background_tasks', 'process_video.html'),
                    //TODO-SWITCH-DEV
                    //pathname: path.join(constants.ROOT_FOLDER_PATH, 'app', 'background_tasks', 'process_video.html'),
                    protocol: 'file:',
                    slashes: true,
                });
                
                processCache[video_name] = args;

                processHiddenWindow = new BrowserWindow({
                    show: false,
                    webPreferences: {
                        contextIsolation: false,
                        nodeIntegration: true,
                        additionalArguments: [video_name],
                        enableRemoteModule: true
                    },
                });
                processHiddenWindow.loadURL(backgroundFileUrl);

                //TODO-SWITCH-DEV
                processHiddenWindow.webContents.openDevTools();

                processHiddenWindow.on('closed', () => {
                    processHiddenWindow = null;
                });
            }else{
                log.error("PROCESS_PROCESS_VIDEO -- NO VIDEO", args, video);
            }
        }catch(error){
            log.error("process_video.js-processVideo", error);
        }
    },
    processVideoReady: (event, args) => {
        event.reply('PROCESS_PROCESS_START', {
            data: processCache,
        });
    },
    checkRecordStatus: (event, args) => {
        try{
            if(args.length > 0){
                const data = args.map((record) =>{
                    const dir = path.join(constants.UPLOADER_FOLDER_PATH, record.date, record.time, record.field);
                    const status_file = path.join(dir, constants.STATUS_FILE_NAME);
                    let last_line = "";
                    let video_line = "";
                    
                    if (fs.existsSync(status_file)){
                        let lines = fs.readFileSync(status_file).toString().split("\n");
                        
                        last_line = lines.filter(item => item).pop(-1); //Get the last line
                        last_line = last_line ? last_line : '';// In case is empty, return ''
                        let video_lines = lines.filter(item => { return item.indexOf("upload=/video") !== -1});
                        video_line = video_lines.length > 0 ? video_lines[0].replace("upload=","").replace("\r","") : '';
                    }
        
                    return { id: record.id, status: last_line, video_line: video_line}
                });
                
                event.reply('MESSAGE_FROM_BACKGROUND_CHECK_STATUS', {
                    data: data
                });
            }
        }catch(error){
            log.error("process_video.js-checkRecordStatus", error);
        }
    }
}