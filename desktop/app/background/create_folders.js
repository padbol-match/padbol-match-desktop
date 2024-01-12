const path = require('path');
const fs = require("fs");
const constants = require('../constants.js');
const log = require('electron-log');

module.exports = {
    createFolders: (event, args) => {
        args.forEach((record) => {
            try{
                let dir = path.join(constants.UPLOADER_FOLDER_PATH);
                
                const timeStart = record.start_date.date.split(" ")[1].split(":");
                const date = record.start_date.date.split(" ")[0].replaceAll("-","_");
                const time = timeStart[0] + "_" + timeStart[1];
                const field = record.field;
        
                //Verifica/Crea carpetas
                if (!fs.existsSync(dir)){
                    fs.mkdirSync(dir);
                }
        
                //Create date folder
                dir = path.join(dir, date);
                if (!fs.existsSync(dir)){
                    fs.mkdirSync(dir);
                }

                //Create schedule file
                //TODO - Do this only one
                scheduleFile = path.join(dir, date + ".schedule.json");
                fs.writeFileSync(scheduleFile, JSON.stringify(args));
        
                //Create time folder
                dir = path.join(dir, time);
                if (!fs.existsSync(dir)){
                    fs.mkdirSync(dir);
                }

                //Create field folder
                dir = path.join(dir, field);
                if (!fs.existsSync(dir)){
                    fs.mkdirSync(dir);
                    log.info("create_folder.js-createFolders - Folder created at: ", dir);
                }

                /*
                marksFile = path.join(dir, date + "_" + time + "_" + field + ".txt");
                
                if (!fs.existsSync(marksFile)){
                    fs.appendFileSync(marksFile, new Date().toLocaleString() + "\n");
                }
                */
            }catch(error){
                log.error("create_folder.js", error);
            }
        });

        
    }

}