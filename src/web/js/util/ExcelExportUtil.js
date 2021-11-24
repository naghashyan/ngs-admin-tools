
export default class ExcelExportUtil {

    /**
     * creates instance of excel exporter
     *
     * @param selectedItemsData
     * @param exportAction
     * @param downloadLoad
     */
    constructor(selectedItemsData, exportAction, downloadLoad) {
        if(!Object.keys(selectedItemsData).length) {
            throw new Error('no items selected');
        }
        if(!exportAction) {
            throw new Error('no action to export');
        }
        this.selectedItemsData = selectedItemsData;
        this.exportAction = exportAction;
        this.downloadLoad = downloadLoad;
    }


    /**
     *
     * @param fields
     */
    exportFile(fields) {
        this.selectedItemsData.fields = fields;
        NGS.action(this.exportAction, this.selectedItemsData, (params) => {
            if(params.error) {
                alert(params.error);
                return;
            }
            this._checkStatus(params.jobId);
        });
    }


    /**
     *
     * @param jobId
     * @private
     */
    _checkStatus(jobId) {
        NGS.action("admin.actions.job.get_status", {job_id: jobId}, (response) => {
            if(response.error) {
                alert(response.message);
                return;
            }
            if(!response.success) {
                alert(response.message);
                return;
            }
            if(response.status === 'finished') {
                if(response.data.success) {
                    NGS.load(this.downloadLoad, {fileName: response.data.fileName, fileFolder: 'download_files'});
                }
                else {
                    alert(response.data.message);
                }
                return;
            }
            setTimeout(() => {
                this._checkStatus(jobId);
            }, 500);
        });
    }
};