import DialogUtility from "./DialogUtility.js";

/**
 * FilesUploadingUtility helper util
 * for load more functionality
 *
 * @author Aram Atanesyan
 * @site https://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2015-2019
 */



//these selectors (id and more) should be exactly like this

let FilesUploadingUtility = {

    allowedFileTypes: [],

    // 1. the part below is for adding files on uploading
    initAdding: function (allowedTypes) {
        let fileUploadInput = document.getElementById('attachedFile_input');
        let copyOfFileInput = document.getElementById('copyForAttachedFilesInput');
        let namesContainer = document.getElementById('attached-files-names-container');

        this.allowedFileTypes = allowedTypes;

        if(!namesContainer || !fileUploadInput || !copyOfFileInput) {
            return;
        }

        fileUploadInput.addEventListener('change', (e)=> {

            if(fileUploadInput.files && fileUploadInput.files.length) {

                if(!copyOfFileInput.files.length) {
                    fileUploadInput.files = this.setFilesAsValueToInput(fileUploadInput.files);
                    copyOfFileInput.files = this.setFilesAsValueToInput(fileUploadInput.files);
                }else {
                    fileUploadInput.files = this.setFilesAsValueToInput(copyOfFileInput.files, fileUploadInput.files, null);
                    copyOfFileInput.files = this.setFilesAsValueToInput(fileUploadInput.files);
                }

            }else {
                let copyOfFileInput = document.getElementById('copyForAttachedFilesInput');
                if(copyOfFileInput.files) {
                    fileUploadInput.files = this.setFilesAsValueToInput(copyOfFileInput.files);
                }
            }

            this._refreshItemsListInUi(fileUploadInput.files);
        });

    },

    /**
     * in ui need to show files names, or the text "no file chosen' if no files are uploaded yet
     * @param files
     */
    _refreshItemsListInUi: function(files) {
        let namesContainer = document.getElementById('attached-files-names-container');
        let noFileTitle = document.getElementById('no-file-title');
        namesContainer.innerHTML = '';

        if(!files.length) {
            noFileTitle.classList.remove('is_hidden');
        }else {
            noFileTitle.classList.add('is_hidden');

            for(let i = 0; i < files.length; i++) {
                let li = document.createElement('li');
                li.classList.add('f_attached-yet-not-saved-file', 'form-item', 'view-mode', 'attach-file');

                li.textContent = files[i].name;

                let removeButton = document.createElement('button');
                removeButton.classList.add('f_remove-attached-file', 'button', 'small-button', 'btn-link', 'outline', 'with-icon');

                let removeButtonIcon = document.createElement('i');
                removeButtonIcon.classList.add('icon-delete');

                removeButton.appendChild(removeButtonIcon);
                li.appendChild(removeButton);

                namesContainer.appendChild(li);

                removeButton.addEventListener('click', this.removeYetNotSavedFileFromFileList.bind(this));


            }
        }
        this._setIndexesToListItems();
        this._refreshAddButtonTitle();

    },


    /**
     * every file item in ui should have an index attribute using which remove button works correct
     * @private
     */
    _setIndexesToListItems: function() {
        let fileItems = document.querySelectorAll('#attached-files-names-container .f_attached-yet-not-saved-file');
        if(!fileItems.length) {
            document.getElementById('no-file-title').classList.remove('is_hidden');
        }

        fileItems.forEach((listItem, i) => {
            listItem.setAttribute('data-file-index', i + '');
            listItem.querySelector('button').setAttribute('data-file-index', i + '');
        })
    },


    /**
     * when file is not saved yet we can remove it
     * @param e
     */
    removeYetNotSavedFileFromFileList: function(e) {
        e.stopPropagation();
        e.preventDefault();

        let button = e.target.closest('button');
        let indexOfFile = button.getAttribute('data-file-index');
        let fileUploadInput = document.getElementById('attachedFile_input');
        let copyOfFileInput = document.getElementById('copyForAttachedFilesInput');

        if(!fileUploadInput) {
            return;
        }

        fileUploadInput.files = this.setFilesAsValueToInput(fileUploadInput.files, null, indexOfFile);
        copyOfFileInput.files = this.setFilesAsValueToInput(fileUploadInput.files);
        button.closest('li').remove();
        this._refreshItemsListInUi(fileUploadInput.files);
    },


    /**
     * helper function which is taken from stackoverflow to set files as value to some input
     * @param files
     * @param filesToConcat
     * @param indexToNotAdd
     * @returns {FileList}
     */
    setFilesAsValueToInput: function (files, filesToConcat = null, indexToNotAdd = null) {
        let buffer = new ClipboardEvent("").clipboardData || new DataTransfer();
        let invalidFileNames = [];
        for (let i = 0; i < files.length; i++) {
            if(indexToNotAdd && +indexToNotAdd === i) {
                continue;
            }
            if(!this.allowedFileTypes.includes(files[i].type)) {
                invalidFileNames.push(files[i].name);
                continue;
            }

            buffer.items.add(files[i]);
        }

        if(filesToConcat) {
            for (let i = 0; i < filesToConcat.length; i++) {
                if(!this.allowedFileTypes.includes(filesToConcat[i].type)) {
                    invalidFileNames.push(filesToConcat[i].name);
                    continue;
                }

                buffer.items.add(filesToConcat[i]);
            }
        }

        if(invalidFileNames.length) {
            let theWordFile = 'File';
            let theWordIs = 'is';
            let theWordIt = 'It ';
            if(invalidFileNames.length > 1) {
                theWordFile = 'Files';
                theWordIs = 'are';
                theWordIt = 'They ';
            }
            DialogUtility.showErrorDialog('Error',  theWordFile + ' "' + invalidFileNames.join(', <br/ >') + '" ' + theWordIs + ' not pdf. <br />' + theWordIt + theWordIs + ' skipped', {actionResultShow: true, noButton: true, 'timeout' : 3000});

        }
        return buffer.files
    },


    /**
     * add button should be 'choose file' if fileList is empty yet, or 'add file' if there are already added files
     * @private
     */
    _refreshAddButtonTitle: function() {
        let addButton = document.getElementById('add-files-title');
        let fileList = document.querySelectorAll('#attached-files-names-container .f_attached-yet-not-saved-file');
        if(!fileList.length) {
            addButton.textContent = "Choose file";
        }else {
            addButton.textContent = "Add file";
        }
    },



    //2. the part below is for showing existing files, and delete functionality
    initShowingAndDeleting: function(files, filesToRemoveInputNameAttribute) {
        let fileInput = document.getElementById('attachedFile_input');
        let fileInputContainer = fileInput.closest('.form-items-container');

        for (let i = 0; i < files.length; i++) {

            if(i % 2 === 0 && i !== 0){
                let newLine = document.createElement('div');
                newLine.classList.add('new-line');
                fileInputContainer.appendChild(newLine);
            }

            let formItem = document.createElement('div');
            formItem.classList.add('f_attach-file', 'form-item', 'view-mode', 'attach-file');
            let span = document.createElement('span');
            let a = document.createElement('a');
            a.href = files[i].url;
            a.classList.add('button', 'small-button', 'btn-link', 'outline', 'with-icon');
            a.setAttribute('title', 'Download the file');
            a.innerHTML = '<i class="icon-svg193"></i>';
            span.innerText = files[i].name;

            let deleteButton = document.createElement('button');
            deleteButton.innerHTML = '<i class="icon-delete-trash"></i>';
            deleteButton.classList.add('button', 'small-button', 'btn-link', 'outline', 'with-icon', 'f_file-delete-btn');
            deleteButton.setAttribute('file-id', files[i].id);

            deleteButton.addEventListener('click', (e) => {
                this._removeAttachedFile(e, filesToRemoveInputNameAttribute);
            });

            let containerForButtons = document.createElement('div');
            containerForButtons.appendChild(a);
            containerForButtons.appendChild(deleteButton);

            formItem.appendChild(span);
            formItem.appendChild(containerForButtons);
            fileInputContainer.prepend(formItem);

        }

        let newLine = document.createElement('div');
        newLine.classList.add('new-line');
        fileInputContainer.appendChild(newLine);
    },


    _removeAttachedFile: function(e, filesToRemoveInputNameAttribute) {
        DialogUtility.showAlertDialog("Delete file", "Do you want to remove this file?").then(function (confirmationMessage) {
            let id = e.target.closest('.f_file-delete-btn').getAttribute('file-id');
            if(!document.getElementById('attachedFilesRemoveSelection')){
                let input = document.createElement('input');
                input.id = 'attachedFilesRemoveSelection';
                input.type = 'hidden';
                input.name = filesToRemoveInputNameAttribute;
                input.value = '';
                e.target.closest('.f_addUpdateForm').appendChild(input);
            }
            let inputField = document.getElementById('attachedFilesRemoveSelection');

            let sendData = [];
            if(inputField.value){
                sendData = inputField.value.split(',');
            }
            if(!sendData.includes(id)){
                sendData.push(id);
            }
            inputField.value = sendData.join(',');
            e.target.closest('.f_attach-file').remove();
        }.bind(this));
    },


    //3. the part below is for page view mode
    initViewMode: function (tabId) {
        this._removeFilesRemoveBtns();
        this._removeFileInputField();

        if(!this.filesExist(tabId)) {
            document.getElementById('page-for-show-no-files-title').classList.remove('is_hidden');
        }
    },

    /**
     *
     * @private
     */
    _removeFilesRemoveBtns: function() {
        let allBtns = document.querySelectorAll('.f_file-delete-btn');
        if(!allBtns){
            return;
        }
        allBtns.forEach((btn)=> {
            btn.remove();
        });
    },

    _removeFileInputField: function() {
        let input = document.getElementById('attachedFile_input');
        if(input){
            let formItem = input.closest('.form-item');
            if(formItem){
                formItem.remove();
            }
        }
    },

    /**
     *
     * @returns {boolean}
     */
    filesExist: function(tabId) {
        let filesTab = document.getElementById(tabId);
        return !!filesTab.querySelector('.f_attach-file')
    }





};

export default FilesUploadingUtility;